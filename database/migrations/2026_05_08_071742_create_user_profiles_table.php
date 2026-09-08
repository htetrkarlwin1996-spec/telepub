<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('display_name')->nullable();
            $table->string('artist_name')->nullable();
            $table->string('publisher_name')->nullable();
            $table->string('ipi_name')->nullable();
            $table->string('ipi_number')->nullable();

            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};