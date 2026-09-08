<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->enum('release_type', ['single', 'ep', 'album'])->default('single')->after('title');
            $table->string('copyright_holder')->nullable()->after('notes');
            $table->string('phonogram_right_holder')->nullable()->after('copyright_holder');
            $table->date('physical_release_date')->nullable()->after('release_date');
            $table->decimal('price', 10, 2)->nullable()->after('physical_release_date');
            $table->boolean('request_new_isrc')->default(false)->after('price');
            $table->timestamp('approved_at')->nullable()->after('updated_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn([
                'release_type', 'copyright_holder', 'phonogram_right_holder',
                'physical_release_date', 'price', 'request_new_isrc',
                'approved_at', 'rejected_at', 'rejection_reason',
            ]);
        });
    }
};
