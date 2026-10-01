<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('release_payments', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('qr_data')->index();
        });

        DB::table('release_payments')
            ->where('provider', 'myanmyanpay')->where('status', 'pending')
            ->whereNull('expires_at')->orderBy('id')->chunkById(250, function ($payments) {
                foreach ($payments as $payment) {
                    DB::table('release_payments')->where('id', $payment->id)
                        ->update(['expires_at' => Carbon::parse($payment->created_at)->addMinutes(15)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('release_payments', fn (Blueprint $table) => $table->dropColumn('expires_at'));
    }
};
