<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach ([
            ['name' => 'YouTube Audio Content ID', 'slug' => 'youtube-audio-content-id'],
            ['name' => 'YouTube Art Tracks', 'slug' => 'youtube-art-tracks'],
        ] as $store) {
            DB::table('music_stores')->updateOrInsert(
                ['slug' => $store['slug']],
                $store + [
                    'description' => $store['name'].' royalty channel',
                    'url' => 'https://www.youtube.com',
                    'logo' => 'https://www.gstatic.com/youtube/img/branding/favicon/favicon_32x32.png',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        // Retained because imported royalties may reference these stores.
    }
};
