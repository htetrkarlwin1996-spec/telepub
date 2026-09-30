<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL = 'irenezinmarmyint20224@gmail.com';

    private const SOURCE = 'Irene historical earnings and withdrawal import 2026-09-21';

    public function up(): void
    {
        $this->setReportingMonths([
            3 => 1,
            6 => 2,
            7 => 3,
            8 => 4,
            9 => 5,
        ]);
    }

    public function down(): void
    {
        $this->setReportingMonths([
            3 => 2,
            6 => 5,
            7 => 6,
            8 => 7,
            9 => 8,
        ]);
    }

    private function setReportingMonths(array $reportingMonths): void
    {
        $artist = DB::table('users')
            ->join('artists', 'artists.user_id', '=', 'users.id')
            ->where('users.email', self::EMAIL)
            ->select('artists.id')
            ->first();

        if (! $artist) {
            return;
        }

        foreach ($reportingMonths as $sourceMonth => $reportingMonth) {
            DB::table('royalties')
                ->where('artist_id', $artist->id)
                ->where('year', 2026)
                ->where('notes', 'like', self::SOURCE."; month={$sourceMonth}; store=%")
                ->update([
                    'month' => $reportingMonth,
                    'created_at' => sprintf('2026-%02d-01 00:00:00', $reportingMonth),
                    'updated_at' => now(),
                ]);
        }
    }
};
