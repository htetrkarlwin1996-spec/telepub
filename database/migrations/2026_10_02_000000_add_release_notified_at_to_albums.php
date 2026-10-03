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
            $table->timestamp('release_notified_at')->nullable()->after('rejected_at')->index();
        });
        DB::table('albums')
            ->where('status', 'approved')
            ->whereNotNull('release_date')
            ->whereDate('release_date', '<=', now()->toDateString())
            ->update(['release_notified_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('release_notified_at');
        });
    }
};
