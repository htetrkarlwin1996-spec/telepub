<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('royalties', function (Blueprint $table) {
            $table->unique(['royalty_import_id', 'import_row'], 'royalties_import_row_unique');
        });
    }

    public function down(): void
    {
        Schema::table('royalties', function (Blueprint $table) {
            $table->dropUnique('royalties_import_row_unique');
        });
    }
};
