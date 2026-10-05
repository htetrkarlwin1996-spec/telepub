<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach ([
            'release_price_per_track_usd' => '9.99',
            'release_price_over_8_tracks_usd' => '29.99',
        ] as $key => $value) {
            DB::table('app_settings')->updateOrInsert(['key' => $key], [
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Accounts existing before OTP enforcement were accepted under the former login rules.
        DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at' => $now]);
    }

    public function down(): void
    {
        DB::table('app_settings')->whereIn('key', [
            'release_price_per_track_usd',
            'release_price_over_8_tracks_usd',
        ])->delete();
    }
};
