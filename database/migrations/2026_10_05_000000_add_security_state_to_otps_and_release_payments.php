<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            $table->unsignedTinyInteger('failed_attempts')->default(0)->after('type');
        });
        Schema::table('release_payments', function (Blueprint $table) {
            $table->json('selected_store_ids')->nullable()->after('addon_services');
        });
    }

    public function down(): void
    {
        Schema::table('otps', fn (Blueprint $table) => $table->dropColumn('failed_attempts'));
        Schema::table('release_payments', fn (Blueprint $table) => $table->dropColumn('selected_store_ids'));
    }
};
