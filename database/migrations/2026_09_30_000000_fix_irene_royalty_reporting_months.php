<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL = 'irenezinmarmyint20224@gmail.com';

    private const SOURCE = 'Irene historical earnings and withdrawal import 2026-09-21';

    public function up(): void
    {
        $this->moveRoyalties([
            3 => 2,
            6 => 5,
            7 => 6,
            8 => 7,
            9 => 8,
        ]);
    }

    public function down(): void
    {
        $this->moveRoyalties([
            2 => 3,
            5 => 6,
            6 => 7,
            7 => 8,
            8 => 9,
        ], true);
    }

    private function moveRoyalties(array $months, bool $reverse = false): void
    {
        $artist = DB::table('users')
            ->join('artists', 'artists.user_id', '=', 'users.id')
            ->where('users.email', self::EMAIL)
            ->select('artists.id')
            ->first();

        if (! $artist) {
            return;
        }

        $stores = DB::table('music_stores')->pluck('slug', 'id');

        foreach ($months as $sourceMonth => $targetMonth) {
            foreach ($stores as $storeId => $slug) {
                $noteMonth = $reverse ? $targetMonth : $sourceMonth;
                $note = self::SOURCE."; month={$noteMonth}; store={$slug};";

                DB::table('royalties')
                    ->where('artist_id', $artist->id)
                    ->where('year', 2026)
                    ->where('month', $sourceMonth)
                    ->where('notes', 'like', $note.'%')
                    ->update([
                        'month' => $targetMonth,
                        'created_at' => sprintf('2026-%02d-01 00:00:00', $targetMonth),
                        'updated_at' => now(),
                    ]);
            }
        }
    }
};
