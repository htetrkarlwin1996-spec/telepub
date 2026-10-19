<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('music_stores', function (Blueprint $table) {
            $table->string('delivery_driver')->nullable()->after('url');
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->string('delivery_status')->default('pending')->after('status');
            $table->unsignedInteger('delivery_attempts')->default(0)->after('delivery_status');
            $table->string('external_release_id')->nullable()->after('store_url');
            $table->json('delivery_response')->nullable()->after('notes');
            $table->text('delivery_error')->nullable()->after('delivery_response');
            $table->timestamp('last_delivery_attempt_at')->nullable()->after('delivery_error');
            $table->timestamp('delivered_at')->nullable()->after('last_delivery_attempt_at');
        });

        DB::table('music_stores')->updateOrInsert(
            ['slug' => 'telemusic-app'],
            [
                'name' => 'TeleMusic App',
                'description' => 'Publish directly to the TeleMusic streaming app.',
                'logo' => rtrim((string) config('app.url'), '/').'/logo.png',
                'url' => config('services.telemusic_app.public_url'),
                'delivery_driver' => 'telemusic_api',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('music_stores')->where('slug', 'telemusic-app')->delete();

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_status', 'delivery_attempts', 'external_release_id', 'delivery_response',
                'delivery_error', 'last_delivery_attempt_at', 'delivered_at',
            ]);
        });
        Schema::table('music_stores', fn (Blueprint $table) => $table->dropColumn('delivery_driver'));
    }
};
