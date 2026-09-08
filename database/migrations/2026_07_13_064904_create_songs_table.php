<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->onDelete('cascade');
            $table->foreignId('artist_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->integer('track_number')->nullable();
            $table->string('duration')->nullable();
            $table->string('file_path')->nullable();
            $table->string('audio_file')->nullable();
            $table->string('isrc_code')->nullable();
            $table->boolean('explicit')->default(false);
            $table->string('genre')->nullable();
            $table->string('language')->nullable();
            $table->string('composers')->nullable();
            $table->string('producers')->nullable();
            $table->string('featuring')->nullable();
            $table->string('lyrics')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
