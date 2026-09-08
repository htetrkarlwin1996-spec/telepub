<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('agreement_name')->default('Music Publishing Administration Agreement');
            $table->string('signed_name');
            $table->date('signed_date');
            $table->string('pdf_path');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_agreements');
    }
};