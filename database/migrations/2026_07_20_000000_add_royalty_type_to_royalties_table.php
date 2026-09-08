<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('royalties', function (Blueprint $table) {
            $table->string('royalty_type', 40)
                ->default('royalties')
                ->after('store_id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('royalties', function (Blueprint $table) {
            $table->dropIndex(['royalty_type']);
            $table->dropColumn('royalty_type');
        });
    }
};
