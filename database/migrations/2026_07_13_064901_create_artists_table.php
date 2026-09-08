<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('artist_name');
            $table->string('genre')->nullable();
            $table->text('bio')->nullable();
            $table->string('country')->nullable();
            $table->string('avatar')->nullable();
            $table->string('banner')->nullable();
            $table->string('payment_email')->nullable();
            $table->string('paypal_email')->nullable();
            $table->text('bank_account_info')->nullable();
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->decimal('available_balance', 12, 2)->default(0);
            $table->decimal('pending_balance', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artists');
    }
};
