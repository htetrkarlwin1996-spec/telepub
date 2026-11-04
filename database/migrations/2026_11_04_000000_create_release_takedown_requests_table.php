<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_takedown_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->string('status', 20)->default('pending')->index();
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->timestamp('taken_down_at')->nullable()->after('approved_at')->index();
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->timestamp('taken_down_at')->nullable()->after('live_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('distributions', fn (Blueprint $table) => $table->dropColumn('taken_down_at'));
        Schema::table('albums', fn (Blueprint $table) => $table->dropColumn('taken_down_at'));
        Schema::dropIfExists('release_takedown_requests');
    }
};
