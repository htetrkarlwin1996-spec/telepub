<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SpotifyMetadataService
{
    public function fetchMany(array $references): array
    {
        if (! config('services.spotify_scraper.key')) {
            throw new RuntimeException('RapidAPI key is not configured. Add RAPIDAPI_SPOTIFY_SCRAPER_KEY to .env.');
        }

        return array_map(fn ($reference) => $this->fetchAlbum($this->albumId($reference)), $references);
    }

    public function fetchAlbum(string $id): array
    {
        $response = Http::acceptJson()->timeout(30)->withHeaders([
            'X-RapidAPI-Key' => config('services.spotify_scraper.key'),
            'X-RapidAPI-Host' => config('services.spotify_scraper.host'),
        ])->get(rtrim(config('services.spotify_scraper.base_url'), '/').'/v1/album/metadata', ['albumId' => $id]);

        if ($response->failed()) {
            throw new RuntimeException("Spotify metadata request failed for {$id} (HTTP {$response->status()}).");
        }

        $payload = $response->json();
        $album = data_get($payload, 'data.album') ?? data_get($payload, 'album') ?? data_get($payload, 'data') ?? $payload;
        if (! is_array($album)) {
            throw new RuntimeException("Spotify album {$id} was not found.");
        }

        $tracks = data_get($album, 'tracks.items')
            ?? data_get($album, 'tracks')
            ?? data_get($album, 'trackList')
            ?? data_get($payload, 'data.tracks.items')
            ?? data_get($payload, 'data.tracks')
            ?? data_get($payload, 'data.trackList.items')
            ?? data_get($payload, 'data.trackList')
            ?? data_get($payload, 'tracks.items')
            ?? data_get($payload, 'tracks')
            ?? data_get($payload, 'trackList')
            ?? [];
        if (! is_array($tracks) || count($tracks) === 0) {
            $tracks = $this->fetchAlbumTracks($id);
        }
        if (! is_array($tracks) || count($tracks) === 0) {
            throw new RuntimeException("Spotify album {$id} has no tracks.");
        }

        $artists = $this->artistNames($album['artists'] ?? data_get($album, 'artist.items', []));
        if (empty($artists) && data_get($album, 'artist.name')) {
            $artists = [data_get($album, 'artist.name')];
        }
        $copyrights = collect($album['copyrights'] ?? [])->pluck('text')->filter()->implode(' / ');
        $images = collect($album['images'] ?? $album['cover'] ?? data_get($album, 'cover.images', []))->sortByDesc('width');

        return [
            'spotify_id' => $id,
            'spotify_url' => data_get($album, 'external_urls.spotify', data_get($album, 'shareUrl', "https://open.spotify.com/album/{$id}")),
            'title' => (string) ($album['name'] ?? $album['title'] ?? 'Untitled'),
            'artist_names' => $artists,
            'release_type' => $this->releaseType((string) ($album['album_type'] ?? $album['type'] ?? ''), count($tracks)),
            'release_date' => $this->date((string) ($album['release_date'] ?? $album['releaseDate'] ?? (is_string($album['date'] ?? null) ? $album['date'] : data_get($album, 'date.isoString', '')))),
            'cover_url' => (string) (data_get($images->first(), 'url') ?? data_get($album, 'cover.url') ?? data_get($album, 'coverArt.sources.0.url') ?? ''),
            'label' => (string) ($album['label'] ?? ''),
            'upc_code' => (string) data_get($album, 'external_ids.upc', ''),
            'copyright' => $copyrights,
            'tracks' => collect($tracks)->values()->map(function ($item, $index) {
                $track = data_get($item, 'track') ?? $item;

                return [
                    'spotify_id' => (string) ($track['id'] ?? $track['uid'] ?? ''),
                    'title' => (string) ($track['name'] ?? $track['title'] ?? 'Untitled Track'),
                    'track_number' => (int) ($track['track_number'] ?? $track['trackNumber'] ?? $index + 1),
                    'duration' => $this->trackDuration($track),
                    'explicit' => (bool) ($track['explicit'] ?? (data_get($track, 'contentRating.label') === 'EXPLICIT')),
                    'artist_names' => $this->artistNames($track['artists'] ?? data_get($track, 'artists.items', [])),
                ];
            })->all(),
        ];
    }

    public function storeCover(?string $url, string $spotifyId): ?string
    {
        if (! $url || ! Str::startsWith($url, ['https://', 'http://'])) {
            return null;
        }

        try {
            $response = Http::timeout(20)->get($url);
            if ($response->failed()) {
                return null;
            }
            $path = 'cover_art/spotify-'.preg_replace('/[^A-Za-z0-9_-]/', '', $spotifyId).'.jpg';
            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }

    private function fetchAlbumTracks(string $id): array
    {
        $response = Http::acceptJson()->timeout(30)->withHeaders([
            'X-RapidAPI-Key' => config('services.spotify_scraper.key'),
            'X-RapidAPI-Host' => config('services.spotify_scraper.host'),
        ])->get(rtrim(config('services.spotify_scraper.base_url'), '/').'/v1/album/tracks', ['albumId' => $id]);

        if ($response->failed()) {
            throw new RuntimeException("Spotify track-list request failed for {$id} (HTTP {$response->status()}).");
        }

        $payload = $response->json();

        return data_get($payload, 'data.tracks.items')
            ?? data_get($payload, 'data.tracks')
            ?? data_get($payload, 'data.items')
            ?? data_get($payload, 'tracks.items')
            ?? data_get($payload, 'tracks')
            ?? data_get($payload, 'items')
            ?? (array_is_list($payload ?? []) ? $payload : []);
    }

    private function albumId(string $reference): string
    {
        $reference = trim($reference);
        if (preg_match('~(?:open\.spotify\.com/album/|spotify:album:)([A-Za-z0-9]+)~', $reference, $match)) {
            return $match[1];
        }
        if (preg_match('/^[A-Za-z0-9]{10,40}$/', $reference)) {
            return $reference;
        }
        throw new RuntimeException("Invalid Spotify album URL or ID: {$reference}");
    }

    private function date(string $date): ?string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $date, $match)) {
            return $match[0];
        }
        if (preg_match('/^\d{4}$/', $date)) {
            return $date.'-01-01';
        }
        if (preg_match('/^\d{4}-\d{2}$/', $date)) {
            return $date.'-01';
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
    }

    private function duration(int $milliseconds): string
    {
        $seconds = intdiv(max(0, $milliseconds), 1000);

        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }

    private function trackDuration(array $track): string
    {
        $milliseconds = (int) ($track['duration_ms'] ?? $track['durationMs'] ?? data_get($track, 'duration.totalMilliseconds', 0));
        if ($milliseconds > 0) {
            return $this->duration($milliseconds);
        }

        return (string) ($track['duration'] ?? '');
    }

    private function artistNames(mixed $artists): array
    {
        return collect(is_array($artists) ? $artists : [])
            ->map(fn ($artist) => is_string($artist) ? $artist : ($artist['name'] ?? data_get($artist, 'profile.name')))
            ->filter()->values()->all();
    }

    private function releaseType(string $type, int $trackCount): string
    {
        if ($type === 'single' || $trackCount === 1) {
            return 'single';
        }
        if ($type === 'compilation') {
            return 'album';
        }

        return $trackCount <= 6 ? 'ep' : 'album';
    }
}
