<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->json('selected_store_ids')->nullable()->after('price');
            $table->string('payment_status')->default('unpaid')->after('selected_store_ids');
        });

        Schema::create('release_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('status')->default('pending');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3);
            $table->string('reference')->unique();
            $table->text('checkout_url')->nullable();
            $table->longText('qr_data')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        $defaults = [
            'release_price_first_usd' => '14.99',
            'release_price_single_usd' => '9.99',
            'release_price_ep_usd' => '19.99',
            'release_price_album_usd' => '29.99',
            'usd_to_thb_rate' => '36.00',
            'usd_to_mmk_rate' => '4500.00',
            'offline_bank_instructions' => 'Thai bank transfer details will be provided by TeleMusic.',
        ];
        foreach ($defaults as $key => $value) {
            DB::table('app_settings')->insertOrIgnore(['key' => $key, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]);
        }

        DB::table('albums')->whereIn('status', ['submitted', 'approved'])->update(['payment_status' => 'paid']);
    }

    public function down(): void
    {
        Schema::dropIfExists('release_payments');
        Schema::table('albums', fn (Blueprint $table) => $table->dropColumn(['selected_store_ids', 'payment_status']));
    }
};
