<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->string('spotify_profile_url')->nullable()->after('payment_email');
            $table->string('apple_music_profile_url')->nullable()->after('spotify_profile_url');
            $table->string('youtube_profile_url')->nullable()->after('apple_music_profile_url');
            $table->string('tidal_profile_url')->nullable()->after('youtube_profile_url');
        });
    }

    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn([
                'spotify_profile_url',
                'apple_music_profile_url',
                'youtube_profile_url',
                'tidal_profile_url',
            ]);
        });
    }
};
