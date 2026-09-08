<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('takedown_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('song_name');
            $table->string('album_name')->nullable();

            $table->text('links');
            $table->text('reason')->nullable();

            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');

            $table->text('admin_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('takedown_requests');
    }
};