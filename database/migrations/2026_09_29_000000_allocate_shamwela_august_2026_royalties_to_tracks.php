<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL = 'shamwela2023@gmail.com';

    private const SOURCE = 'Shamwela August 2026 track allocation 2026-09-29';

    public function up(): void
    {
        $artist = DB::table('users')
            ->join('artists', 'artists.user_id', '=', 'users.id')
            ->where('users.email', self::EMAIL)
            ->select('artists.*')
            ->first();

        if (! $artist) {
            return;
        }

        DB::transaction(function () use ($artist) {
            if (DB::table('royalties')->where('artist_id', $artist->id)->where('notes', 'like', self::SOURCE.';%')->count() === 50) {
                return;
            }

            $stores = DB::table('music_stores')
                ->whereIn('slug', ['apple-music', 'instagram-facebook-meta'])
                ->pluck('id', 'slug');
            foreach (['apple-music', 'instagram-facebook-meta'] as $slug) {
                if (! isset($stores[$slug])) {
                    throw new RuntimeException("Required music store is missing: {$slug}");
                }
            }

            $tracks = collect($this->tracks());
            $songs = DB::table('songs')
                ->where('artist_id', $artist->id)
                ->whereIn('isrc_code', $tracks->pluck('isrc'))
                ->get()
                ->keyBy('isrc_code');
            $missing = $tracks->pluck('isrc')->reject(fn ($isrc) => isset($songs[$isrc]));
            if ($missing->isNotEmpty()) {
                $albumId = DB::table('albums')->where('artist_id', $artist->id)
                    ->where('slug', 'unmatched-catalog-entries-shamwela')
                    ->value('id');
                if (! $albumId) {
                    $albumId = DB::table('albums')->insertGetId([
                        'artist_id' => $artist->id,
                        'title' => 'Unmatched Catalog Entries',
                        'slug' => 'unmatched-catalog-entries-shamwela',
                        'release_type' => 'album',
                        'status' => 'approved',
                        'label' => 'TeleMusic',
                        'notes' => 'Placeholder for royalty ISRCs whose release metadata is not yet present.',
                        'approved_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                foreach ($missing->values() as $index => $isrc) {
                    DB::table('songs')->insert([
                        'album_id' => $albumId,
                        'artist_id' => $artist->id,
                        'title' => $isrc,
                        'track_number' => $index + 1,
                        'isrc_code' => $isrc,
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

            $shareRate = ((float) $artist->revenue_share_percentage) / 100;
            if ($shareRate <= 0) {
                throw new RuntimeException('Shamwela revenue share must be greater than zero.');
            }

            $oldGross = (float) DB::table('royalties')
                ->where('artist_id', $artist->id)
                ->where('year', 2026)
                ->where('month', 8)
                ->whereNull('song_id')
                ->whereNull('album_id')
                ->sum('amount');
            $oldNet = round($oldGross * $shareRate, 2);

            DB::table('royalties')
                ->where('artist_id', $artist->id)
                ->where('year', 2026)
                ->where('month', 8)
                ->whereNull('song_id')
                ->whereNull('album_id')
                ->delete();

            $appleCentsRemaining = 13000;
            $totalCentsRemaining = 15390;
            foreach ($tracks->values() as $index => $track) {
                $trackCents = (int) round($track['net'] * 100);
                $isLast = $index === $tracks->count() - 1;
                $appleCents = $isLast
                    ? $appleCentsRemaining
                    : (int) round($trackCents * 13000 / 15390);
                $appleCents = min($appleCents, $trackCents, $appleCentsRemaining);
                $metaCents = $trackCents - $appleCents;
                $appleCentsRemaining -= $appleCents;
                $totalCentsRemaining -= $trackCents;

                $song = $songs[$track['isrc']];
                foreach (['apple-music' => $appleCents, 'instagram-facebook-meta' => $metaCents] as $slug => $netCents) {
                    $net = $netCents / 100;
                    DB::table('royalties')->insert([
                        'artist_id' => $artist->id,
                        'song_id' => $song->id,
                        'album_id' => $song->album_id,
                        'store_id' => $stores[$slug],
                        'royalty_type' => 'royalties',
                        'month' => 8,
                        'year' => 2026,
                        'amount' => number_format($net / $shareRate, 10, '.', ''),
                        'currency' => 'USD',
                        'notes' => self::SOURCE."; ISRC={$track['isrc']}; store={$slug}; artist net USD ".number_format($net, 2, '.', '').'.',
                        'entered_by' => $artist->user_id,
                        'created_at' => '2026-08-01 00:00:00',
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($appleCentsRemaining !== 0 || $totalCentsRemaining !== 0) {
                throw new RuntimeException('Shamwela royalty allocation did not balance.');
            }

            $delta = 153.90 - $oldNet;
            DB::table('artists')->where('id', $artist->id)->update([
                'total_earnings' => DB::raw('total_earnings + '.number_format($delta, 2, '.', '')),
                'available_balance' => DB::raw('available_balance + '.number_format($delta, 2, '.', '')),
                'updated_at' => now(),
            ]);
        });
    }

    private function tracks(): array
    {
        return collect([
            ['QZXLZ2510079', 32.98], ['QZXLZ2506838', 13.45], ['QZXLZ2536917', 12.72],
            ['QZXLZ2508076', 11.56], ['QZXLZ2507807', 10.13], ['QZXLZ2507808', 9.56],
            ['QZTB72414925', 8.79], ['QZXLZ2553841', 7.12], ['QZXLZ2557683', 6.56],
            ['QZXLZ2557682', 5.61], ['QZXLZ2558794', 4.38], ['QZXLZ2558795', 2.14],
            ['QZXLZ2524477', 4.32], ['QZXLZ2524478', 4.19], ['QZXLZ2558793', 4.19],
            ['QZXLZ2526886', 1.79], ['QZXLZ2526888', 1.72], ['QZXLZ2526887', 1.65],
            ['QZXLZ2526889', 1.65], ['QZXLZ2526894', 1.62], ['QZXLZ2526892', 1.57],
            ['QZXLZ2526896', 1.57], ['QZXLZ2526895', 1.56], ['QZXLZ2526893', 1.53],
            ['QZXLZ2526890', 1.54],
        ])->map(fn ($track) => ['isrc' => $track[0], 'net' => $track[1]])->all();
    }
};
