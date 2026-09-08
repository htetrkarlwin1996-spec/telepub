<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            // Change credit fields to JSON to support multiple people
            $table->json('primary_artists')->nullable()->change();
            $table->json('composers')->nullable()->change();
            $table->json('lyricist')->nullable()->change();
            $table->json('producers')->nullable()->change();
            $table->json('vocals')->nullable()->change();
            $table->json('featuring')->nullable()->change();
        });

        // Convert existing comma-separated data to JSON arrays
        \App\Models\Song::chunk(100, function ($songs) {
            foreach ($songs as $song) {
                $dirty = false;

                foreach (['primary_artists', 'composers', 'lyricist', 'producers', 'vocals', 'featuring'] as $field) {
                    if (!empty($song->$field) && is_string($song->$field)) {
                        // Split by comma or "&" and trim whitespace
                        $parts = preg_split('/\s*(?:,|&| and )\s*/', $song->$field);
                        $parts = array_map('trim', $parts);
                        $parts = array_filter($parts, fn($v) => !empty($v));
                        $song->$field = array_values($parts);
                        $dirty = true;
                    }
                }

                if ($dirty) {
                    $song->save();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->string('primary_artists')->nullable()->change();
            $table->string('composers')->nullable()->change();
            $table->string('lyricist')->nullable()->change();
            $table->string('producers')->nullable()->change();
            $table->string('vocals')->nullable()->change();
            $table->string('featuring')->nullable()->change();
        });

        // Convert JSON arrays back to comma-separated strings
        \App\Models\Song::chunk(100, function ($songs) {
            foreach ($songs as $song) {
                $dirty = false;

                foreach (['primary_artists', 'composers', 'lyricist', 'producers', 'vocals', 'featuring'] as $field) {
                    if (!empty($song->$field) && is_array($song->$field)) {
                        $song->$field = implode(', ', $song->$field);
                        $dirty = true;
                    }
                }

                if ($dirty) {
                    $song->save();
                }
            }
        });
    }
};
