<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            if (!Schema::hasColumn('albums', 'registration_type')) {
                $table->string('registration_type')->nullable()->after('status');
            }

            if (!Schema::hasColumn('albums', 'registration_fee')) {
                $table->decimal('registration_fee', 15, 2)->default(0)->after('registration_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            if (Schema::hasColumn('albums', 'registration_fee')) {
                $table->dropColumn('registration_fee');
            }

            if (Schema::hasColumn('albums', 'registration_type')) {
                $table->dropColumn('registration_type');
            }
        });
    }
};