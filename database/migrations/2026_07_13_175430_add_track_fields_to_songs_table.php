<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->string('version')->nullable()->after('title');
            $table->string('primary_artists')->nullable()->after('version');
            $table->string('lyricist')->nullable()->after('composers');
            $table->string('vocals')->nullable()->after('producers');
            $table->boolean('request_new_isrc')->default(false)->after('isrc_code');
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn(['version', 'primary_artists', 'lyricist', 'vocals', 'request_new_isrc']);
        });
    }
};
