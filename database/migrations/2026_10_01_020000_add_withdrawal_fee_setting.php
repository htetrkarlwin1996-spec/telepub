<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['key' => 'withdrawal_fee_percentage'],
            ['value' => '2', 'created_at' => now(), 'updated_at' => now()],
        );
    }

    public function down(): void
    {
        DB::table('app_settings')->where('key', 'withdrawal_fee_percentage')->delete();
    }
};
