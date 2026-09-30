<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL = 'irenezinmarmyint20224@gmail.com';

    private const ORIGINAL_SOURCE = 'Irene historical earnings and withdrawal import 2026-09-21';

    private const SOURCE = 'Irene Jan-May 2026 track allocation 2026-09-30';

    private const MARKER = 'data_fix.irene_jan_may_2026_track_allocation';

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

        $months = $this->parseTrackData();

        DB::transaction(function () use ($artist, $months) {
            $tracks = collect($months)->flatten(1)->unique('isrc')->values();
            $songs = DB::table('songs')
                ->where('artist_id', $artist->id)
                ->whereIn('isrc_code', $tracks->pluck('isrc'))
                ->get()
                ->keyBy('isrc_code');

            $missing = $tracks->reject(fn ($track) => isset($songs[$track['isrc']]));
            if ($missing->isNotEmpty()) {
                $albumId = DB::table('albums')->where('artist_id', $artist->id)
                    ->where('slug', 'unmatched-catalog-entries-irene')
                    ->value('id');

                if (! $albumId) {
                    $albumId = DB::table('albums')->insertGetId([
                        'artist_id' => $artist->id,
                        'title' => 'Unmatched Catalog Entries',
                        'slug' => 'unmatched-catalog-entries-irene',
                        'release_type' => 'album',
                        'status' => 'approved',
                        'label' => 'TeleMusic',
                        'notes' => 'Placeholder for royalty ISRCs whose release metadata is not yet present.',
                        'approved_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                foreach ($missing->values() as $index => $track) {
                    DB::table('songs')->insert([
                        'album_id' => $albumId,
                        'artist_id' => $artist->id,
                        'title' => $track['title'],
                        'track_number' => $index + 1,
                        'isrc_code' => $track['isrc'],
                        'request_new_isrc' => false,
                        'explicit' => false,
                        'status' => 'approved',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $songs = DB::table('songs')
                    ->where('artist_id', $artist->id)
                    ->whereIn('isrc_code', $tracks->pluck('isrc'))
                    ->get()
                    ->keyBy('isrc_code');
            }

            foreach ($months as $month => $monthTracks) {
                $sourceRows = DB::table('royalties')
                    ->where('artist_id', $artist->id)
                    ->where('year', 2026)
                    ->where('month', $month)
                    ->whereNull('song_id')
                    ->whereNull('album_id')
                    ->where('notes', 'like', self::ORIGINAL_SOURCE.';%')
                    ->get();

                if ($sourceRows->isEmpty()) {
                    throw new RuntimeException("Irene source royalties are missing for 2026-{$month}.");
                }

                $weightTotal = collect($monthTracks)->sum('cents');
                if ($weightTotal <= 0) {
                    throw new RuntimeException("Irene track allocation has no value for 2026-{$month}.");
                }

                foreach ($sourceRows as $sourceRow) {
                    $amountUnits = (int) round((float) $sourceRow->amount * 10_000_000_000);
                    $amountRemaining = $amountUnits;
                    $streamsRemaining = (int) ($sourceRow->streams ?? 0);

                    foreach (array_values($monthTracks) as $index => $track) {
                        $isLast = $index === count($monthTracks) - 1;
                        $trackUnits = $isLast
                            ? $amountRemaining
                            : (int) round($amountUnits * $track['cents'] / $weightTotal);
                        $trackStreams = $isLast
                            ? $streamsRemaining
                            : (int) round(((int) ($sourceRow->streams ?? 0)) * $track['cents'] / $weightTotal);
                        $trackUnits = min($trackUnits, $amountRemaining);
                        $trackStreams = min($trackStreams, $streamsRemaining);
                        $amountRemaining -= $trackUnits;
                        $streamsRemaining -= $trackStreams;

                        $song = $songs[$track['isrc']];
                        DB::table('royalties')->insert([
                            'artist_id' => $artist->id,
                            'song_id' => $song->id,
                            'album_id' => $song->album_id,
                            'store_id' => $sourceRow->store_id,
                            'royalty_type' => $sourceRow->royalty_type,
                            'month' => $month,
                            'year' => 2026,
                            'amount' => number_format($trackUnits / 10_000_000_000, 10, '.', ''),
                            'currency' => 'USD',
                            'exchange_rate' => $sourceRow->exchange_rate,
                            'streams' => $trackStreams,
                            'notes' => self::SOURCE."; ISRC={$track['isrc']}; source_row={$sourceRow->id}.",
                            'entered_by' => $sourceRow->entered_by,
                            'created_at' => $sourceRow->created_at,
                            'updated_at' => now(),
                        ]);
                    }

                    if ($amountRemaining !== 0 || $streamsRemaining !== 0) {
                        throw new RuntimeException("Irene track allocation did not balance for royalty {$sourceRow->id}.");
                    }
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
        // Historical financial allocations are deliberately preserved.
    }

    private function parseTrackData(): array
    {
        $path = database_path('data/irene_track_royalties_jan_may_2026.txt');
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

            if ($currentMonth && preg_match('/^(.+?)\s*\|\s*([A-Z0-9]+)\s*\|\s*(\d+\.\d{2})$/', $line, $matches)) {
                $months[$currentMonth][] = [
                    'title' => trim($matches[1]),
                    'isrc' => $matches[2],
                    'cents' => (int) round((float) $matches[3] * 100),
                ];
            }
        }

        if (array_keys($months) !== [1, 2, 3, 4, 5]) {
            throw new RuntimeException('Irene track royalty data must contain January through May 2026.');
        }

        return $months;
    }
};
