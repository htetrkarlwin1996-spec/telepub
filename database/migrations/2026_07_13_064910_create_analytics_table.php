<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->onDelete('cascade');
            $table->foreignId('song_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('album_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained('music_stores')->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->bigInteger('streams')->default(0);
            $table->bigInteger('downloads')->default(0);
            $table->bigInteger('likes')->nullable();
            $table->bigInteger('playlist_adds')->nullable();
            $table->decimal('revenue', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics');
    }
};
