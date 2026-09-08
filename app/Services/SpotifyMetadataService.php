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
        if (! config('services.spotify23.key')) {
            throw new RuntimeException('RapidAPI key is not configured. Add RAPIDAPI_SPOTIFY23_KEY to .env.');
        }

        return array_map(fn ($reference) => $this->fetchAlbum($this->albumId($reference)), $references);
    }

    public function fetchAlbum(string $id): array
    {
        $response = Http::acceptJson()->timeout(30)->withHeaders([
            'X-RapidAPI-Key' => config('services.spotify23.key'),
            'X-RapidAPI-Host' => config('services.spotify23.host'),
        ])->get(rtrim(config('services.spotify23.base_url'), '/').'/albums/', ['ids' => $id]);

        if ($response->failed()) {
            throw new RuntimeException("Spotify metadata request failed for {$id} (HTTP {$response->status()}).");
        }

        $album = $response->json('albums.0') ?? $response->json('data.albums.0');
        if (! is_array($album)) {
            throw new RuntimeException("Spotify album {$id} was not found.");
        }

        $tracks = data_get($album, 'tracks.items', []);
        if (! is_array($tracks) || count($tracks) === 0) {
            throw new RuntimeException("Spotify album {$id} has no tracks.");
        }

        $artists = collect($album['artists'] ?? [])->pluck('name')->filter()->values()->all();
        $copyrights = collect($album['copyrights'] ?? [])->pluck('text')->filter()->implode(' / ');
        $images = collect($album['images'] ?? [])->sortByDesc('width');

        return [
            'spotify_id' => $id,
            'spotify_url' => data_get($album, 'external_urls.spotify', "https://open.spotify.com/album/{$id}"),
            'title' => (string) ($album['name'] ?? 'Untitled'),
            'artist_names' => $artists,
            'release_type' => $this->releaseType((string) ($album['album_type'] ?? ''), count($tracks)),
            'release_date' => $this->date((string) ($album['release_date'] ?? '')),
            'cover_url' => (string) data_get($images->first(), 'url', ''),
            'label' => (string) ($album['label'] ?? ''),
            'upc_code' => (string) data_get($album, 'external_ids.upc', ''),
            'copyright' => $copyrights,
            'tracks' => collect($tracks)->values()->map(fn ($track, $index) => [
                'spotify_id' => (string) ($track['id'] ?? ''),
                'title' => (string) ($track['name'] ?? 'Untitled Track'),
                'track_number' => (int) ($track['track_number'] ?? $index + 1),
                'duration' => $this->duration((int) ($track['duration_ms'] ?? 0)),
                'explicit' => (bool) ($track['explicit'] ?? false),
                'artist_names' => collect($track['artists'] ?? [])->pluck('name')->filter()->values()->all(),
            ])->all(),
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
