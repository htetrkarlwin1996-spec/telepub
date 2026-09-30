<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL = 'irenezinmarmyint20224@gmail.com';

    private const PREVIOUS_SOURCE = 'Irene Jan-May 2026 track allocation 2026-09-30';

    private const SOURCE = 'Irene Jan-May 2026 platform streams import 2026-09-30';

    private const MARKER = 'data_fix.irene_jan_may_2026_platform_streams';

    private const STORE_MAP = [
        'YouTube Audio Content ID' => ['YouTube Audio Content ID', 'youtube-audio-content-id'],
        'YouTube Art Tracks' => ['YouTube Art Tracks', 'youtube-art-tracks'],
        'TikTok' => ['TikTok & ByteDance', 'tiktok'],
        'Apple Music' => ['Apple Music', 'apple-music'],
        'Spotify' => ['Spotify', 'spotify'],
        'META' => ['Instagram & Facebook (Meta)', 'instagram-facebook-meta'],
        'Audible Magic Fingerprinting' => ['Audible Magic Fingerprinting', 'audible-magic-fingerprinting'],
        'iTunes / Apple Music' => ['iTunes', 'itunes'],
        'Tidal' => ['TIDAL', 'tidal'],
        'Tencent' => ['Tencent Music', 'tencent'],
        'Amazon Prime' => ['Amazon Music', 'amazon'],
        'Kkbox' => ['KKBOX', 'kkbox'],
        'Soundcloud' => ['SoundCloud', 'soundcloud'],
        'SnapChat' => ['Snapchat', 'snapchat'],
        'Pandora' => ['Pandora', 'pandora'],
        'Qobuz' => ['Qobuz', 'qobuz'],
        'NetEase Cloud Music' => ['NetEase Cloud Music', 'netease'],
        'Deezer' => ['Deezer', 'deezer'],
        'Anghami' => ['Anghami', 'anghami'],
        'Amazon Ads' => ['Amazon Ads', 'amazon-ads'],
    ];

    public function up(): void
    {
        $artist = DB::table('users')
            ->join('artists', 'artists.user_id', '=', 'users.id')
            ->where('users.email', self::EMAIL)
            ->select('artists.*')
            ->first();

        if (! $artist || DB::table('app_settings')->where('key', self::MARKER)->exists()) {
            return;
        }

        $months = $this->parsePlatformData();

        DB::transaction(function () use ($artist, $months) {
            $storeIds = $this->ensureStores();

            foreach ($months as $month => $platforms) {
                $sourceRows = DB::table('royalties')
                    ->where('artist_id', $artist->id)
                    ->where('year', 2026)
                    ->where('month', $month)
                    ->whereNotNull('song_id')
                    ->where('notes', 'like', self::PREVIOUS_SOURCE.';%')
                    ->get();

                if ($sourceRows->isEmpty()) {
                    throw new RuntimeException("Irene track-linked royalties are missing for 2026-{$month}.");
                }

                $tracks = $sourceRows->groupBy('song_id')->map(function ($rows) {
                    $first = $rows->first();

                    return [
                        'song_id' => $first->song_id,
                        'album_id' => $first->album_id,
                        'weight' => $rows->sum(fn ($row) => (int) round((float) $row->amount * 10_000_000_000)),
                    ];
                })->values()->all();

                $totalUnits = $sourceRows->sum(fn ($row) => (int) round((float) $row->amount * 10_000_000_000));
                $trackWeightTotal = array_sum(array_column($tracks, 'weight'));
                $platformWeightTotal = array_sum(array_column($platforms, 'amount_cents'));
                $platformAmountRemaining = $totalUnits;

                foreach (array_values($platforms) as $platformIndex => $platform) {
                    $lastPlatform = $platformIndex === count($platforms) - 1;
                    $platformUnits = $lastPlatform
                        ? $platformAmountRemaining
                        : (int) round($totalUnits * $platform['amount_cents'] / $platformWeightTotal);
                    $platformUnits = min($platformUnits, $platformAmountRemaining);
                    $platformAmountRemaining -= $platformUnits;

                    $trackAmountRemaining = $platformUnits;
                    $trackStreamsRemaining = $platform['streams'];

                    foreach (array_values($tracks) as $trackIndex => $track) {
                        $lastTrack = $trackIndex === count($tracks) - 1;
                        $trackUnits = $lastTrack
                            ? $trackAmountRemaining
                            : (int) round($platformUnits * $track['weight'] / $trackWeightTotal);
                        $trackStreams = $lastTrack
                            ? $trackStreamsRemaining
                            : (int) round($platform['streams'] * $track['weight'] / $trackWeightTotal);
                        $trackUnits = min($trackUnits, $trackAmountRemaining);
                        $trackStreams = min($trackStreams, $trackStreamsRemaining);
                        $trackAmountRemaining -= $trackUnits;
                        $trackStreamsRemaining -= $trackStreams;

                        DB::table('royalties')->insert([
                            'artist_id' => $artist->id,
                            'song_id' => $track['song_id'],
                            'album_id' => $track['album_id'],
                            'store_id' => $storeIds[$platform['name']],
                            'royalty_type' => 'streaming',
                            'month' => $month,
                            'year' => 2026,
                            'amount' => number_format($trackUnits / 10_000_000_000, 10, '.', ''),
                            'currency' => $sourceRows->first()->currency,
                            'exchange_rate' => $sourceRows->first()->exchange_rate,
                            'streams' => $trackStreams,
                            'notes' => self::SOURCE."; platform={$platform['name']}.",
                            'entered_by' => $sourceRows->first()->entered_by,
                            'created_at' => $sourceRows->min('created_at'),
                            'updated_at' => now(),
                        ]);
                    }

                    if ($trackAmountRemaining !== 0 || $trackStreamsRemaining !== 0) {
                        throw new RuntimeException("Irene platform allocation did not balance for {$platform['name']} in 2026-{$month}.");
                    }
                }

                if ($platformAmountRemaining !== 0) {
                    throw new RuntimeException("Irene platform amounts did not balance for 2026-{$month}.");
                }

                DB::table('royalties')->whereIn('id', $sourceRows->pluck('id'))->delete();
            }

            DB::table('app_settings')->updateOrInsert(
                ['key' => self::MARKER],
                ['value' => now()->toDateTimeString(), 'created_at' => now(), 'updated_at' => now()],
            );
        });
    }

    public function down(): void
    {
        // Historical stream allocations are deliberately preserved.
    }

    private function ensureStores(): array
    {
        $ids = [];
        foreach (self::STORE_MAP as $sourceName => [$name, $slug]) {
            DB::table('music_stores')->insertOrIgnore([
                'name' => $name,
                'slug' => $slug,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('music_stores')->where('slug', $slug)->update([
                'name' => $name,
                'is_active' => true,
                'updated_at' => now(),
            ]);
            $ids[$sourceName] = DB::table('music_stores')->where('slug', $slug)->value('id');
        }

        return $ids;
    }

    private function parsePlatformData(): array
    {
        $path = database_path('data/irene_platform_streams_jan_may_2026.txt');
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $monthNumbers = ['January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5];
        $months = [];
        $currentMonth = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^\[([A-Za-z]+) 2026\]$/', $line, $matches)) {
                $currentMonth = $monthNumbers[$matches[1]] ?? null;

                continue;
            }

            if ($currentMonth && preg_match('/^(.+?)\s*\|\s*[\d.]+\s*\|\s*[\d.]+\s*\|\s*(\d+)\s*\|\s*(\d+\.\d{2})$/', $line, $matches)) {
                $name = trim($matches[1]);
                if (! isset(self::STORE_MAP[$name])) {
                    throw new RuntimeException("No music store mapping exists for {$name}.");
                }
                $months[$currentMonth][] = [
                    'name' => $name,
                    'streams' => (int) $matches[2],
                    'amount_cents' => (int) round((float) $matches[3] * 100),
                ];
            }
        }

        if (array_keys($months) !== [1, 2, 3, 4, 5]) {
            throw new RuntimeException('Irene platform stream data must contain January through May 2026.');
        }

        return $months;
    }
};
