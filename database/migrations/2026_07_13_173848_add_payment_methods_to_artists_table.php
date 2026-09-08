<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            // Bank Transfer fields
            $table->string('bank_account_name')->nullable()->after('bank_account_info');
            $table->string('bank_name')->nullable()->after('bank_account_name');
            $table->string('bank_country')->nullable()->after('bank_name');
            $table->string('bank_account_no')->nullable()->after('bank_country');

            // KBZ Pay fields
            $table->string('kbz_pay_name')->nullable()->after('bank_account_no');
            $table->string('kbz_pay_phone')->nullable()->after('kbz_pay_name');

            // Wave Pay fields
            $table->string('wave_pay_name')->nullable()->after('kbz_pay_phone');
            $table->string('wave_pay_phone')->nullable()->after('wave_pay_name');
        });
    }

    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn([
                'bank_account_name',
                'bank_name',
                'bank_country',
                'bank_account_no',
                'kbz_pay_name',
                'kbz_pay_phone',
                'wave_pay_name',
                'wave_pay_phone',
            ]);
        });
    }
};
