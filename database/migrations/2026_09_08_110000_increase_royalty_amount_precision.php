<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE royalties MODIFY amount DECIMAL(18,10) NOT NULL');
        DB::statement('ALTER TABLE artists MODIFY total_earnings DECIMAL(18,10) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE artists MODIFY available_balance DECIMAL(18,10) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE artists MODIFY pending_balance DECIMAL(18,10) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE royalties MODIFY amount DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE artists MODIFY total_earnings DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE artists MODIFY available_balance DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE artists MODIFY pending_balance DECIMAL(12,2) NOT NULL DEFAULT 0');
    }
};
