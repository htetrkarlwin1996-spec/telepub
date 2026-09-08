<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['royalties', 'withdrawals', 'payouts', 'invoices'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'currency')) {
                DB::table($table)->where('currency', '!=', 'USD')->update(['currency' => 'USD']);
            }
        }
    }

    public function down(): void
    {
        // Previous labels cannot be reconstructed after standardization.
    }
};
