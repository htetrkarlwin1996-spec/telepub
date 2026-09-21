<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL = 'irenezinmarmyint20224@gmail.com';

    private const SOURCE = 'Irene historical earnings and withdrawal import 2026-09-21';

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
            DB::table('artists')->where('id', $artist->id)->update([
                'revenue_share_percentage' => 85,
                'updated_at' => now(),
            ]);

            $stores = DB::table('music_stores')
                ->whereIn('slug', ['youtube-music', 'spotify', 'tiktok', 'instagram-facebook-meta', 'apple-music'])
                ->pluck('id', 'slug');

            foreach (['youtube-music', 'spotify', 'tiktok', 'instagram-facebook-meta', 'apple-music'] as $slug) {
                if (! isset($stores[$slug])) {
                    throw new RuntimeException("Required music store is missing: {$slug}");
                }
            }

            $balanceDelta = 0.0;
            $earningsDelta = 0.0;

            foreach ($this->months() as $month => $data) {
                $date = sprintf('2026-%02d-01', $month);

                foreach ($data['stores'] as $slug => $netAmount) {
                    $note = self::SOURCE."; month={$month}; store={$slug}; artist net USD {$netAmount}.";
                    $exists = DB::table('royalties')
                        ->where('artist_id', $artist->id)
                        ->where('notes', $note)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    // Royalties are stored gross; artist-facing reports apply the 85% share.
                    $grossAmount = number_format($netAmount / 0.85, 10, '.', '');
                    DB::table('royalties')->insert([
                        'artist_id' => $artist->id,
                        'store_id' => $stores[$slug],
                        'royalty_type' => 'royalties',
                        'month' => $month,
                        'year' => 2026,
                        'amount' => $grossAmount,
                        'currency' => 'USD',
                        'notes' => $note,
                        'entered_by' => $artist->user_id,
                        'created_at' => $date.' 00:00:00',
                        'updated_at' => now(),
                    ]);

                    $balanceDelta += $netAmount;
                    $earningsDelta += $netAmount;
                }

                $withdrawalNote = self::SOURCE."; month={$month}; approved historical withdrawal.";
                $withdrawalExists = DB::table('withdrawals')
                    ->where('artist_id', $artist->id)
                    ->where('notes', $withdrawalNote)
                    ->exists();

                if (! $withdrawalExists) {
                    DB::table('withdrawals')->insert([
                        'artist_id' => $artist->id,
                        'amount' => $data['total'],
                        'fee' => 0,
                        'total' => $data['total'],
                        'currency' => 'USD',
                        'status' => 'approved',
                        'notes' => $withdrawalNote,
                        'admin_notes' => 'Historical withdrawal supplied as approved; payment method not supplied.',
                        'requested_at' => $date.' 00:00:00',
                        'processed_at' => $date.' 00:00:00',
                        'created_at' => $date.' 00:00:00',
                        'updated_at' => now(),
                    ]);

                    $balanceDelta -= $data['total'];
                }
            }

            if ($earningsDelta != 0.0 || $balanceDelta != 0.0) {
                DB::table('artists')->where('id', $artist->id)->update([
                    'total_earnings' => DB::raw('total_earnings + '.number_format($earningsDelta, 2, '.', '')),
                    'available_balance' => DB::raw('available_balance + '.number_format($balanceDelta, 2, '.', '')),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    private function months(): array
    {
        return [
            3 => ['total' => 1378, 'stores' => [
                'youtube-music' => 1008, 'spotify' => 200, 'tiktok' => 90, 'instagram-facebook-meta' => 80,
            ]],
            6 => ['total' => 876, 'stores' => [
                'youtube-music' => 776, 'tiktok' => 45, 'apple-music' => 25, 'instagram-facebook-meta' => 30,
            ]],
            7 => ['total' => 996, 'stores' => [
                'youtube-music' => 790, 'spotify' => 90, 'apple-music' => 70, 'tiktok' => 38, 'instagram-facebook-meta' => 8,
            ]],
            8 => ['total' => 1173, 'stores' => [
                'youtube-music' => 998, 'tiktok' => 93, 'spotify' => 43, 'apple-music' => 39,
            ]],
            9 => ['total' => 723, 'stores' => [
                'youtube-music' => 688, 'tiktok' => 23, 'spotify' => 12,
            ]],
        ];
    }

    public function down(): void
    {
        // Historical financial records are intentionally not deleted by rollback.
    }
};
