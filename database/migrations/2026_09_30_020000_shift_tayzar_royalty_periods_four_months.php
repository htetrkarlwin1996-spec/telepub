<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL = 'tayzarst91@gmail.com';

    private const MARKER = 'data_fix.tayzar_royalty_periods_minus_4_months';

    public function up(): void
    {
        $this->shift(-4);
    }

    public function down(): void
    {
        $this->shift(4, true);
    }

    private function shift(int $months, bool $rollback = false): void
    {
        $artist = DB::table('users')
            ->join('artists', 'artists.user_id', '=', 'users.id')
            ->where('users.email', self::EMAIL)
            ->select('artists.id')
            ->first();

        if (! $artist) {
            return;
        }

        if (! $rollback && DB::table('app_settings')->where('key', self::MARKER)->exists()) {
            return;
        }

        DB::transaction(function () use ($artist, $months, $rollback) {
            DB::table('royalties')
                ->where('artist_id', $artist->id)
                ->select(['id', 'year', 'month'])
                ->orderBy('id')
                ->get()
                ->each(function ($royalty) use ($months) {
                    $period = (new DateTimeImmutable(sprintf('%04d-%02d-01', $royalty->year, $royalty->month)))
                        ->modify(($months > 0 ? '+' : '').$months.' months');

                    DB::table('royalties')->where('id', $royalty->id)->update([
                        'year' => (int) $period->format('Y'),
                        'month' => (int) $period->format('n'),
                        'updated_at' => now(),
                    ]);
                });

            if ($rollback) {
                DB::table('app_settings')->where('key', self::MARKER)->delete();
            } else {
                DB::table('app_settings')->updateOrInsert(
                    ['key' => self::MARKER],
                    ['value' => now()->toDateTimeString(), 'created_at' => now(), 'updated_at' => now()],
                );
            }
        });
    }
};
