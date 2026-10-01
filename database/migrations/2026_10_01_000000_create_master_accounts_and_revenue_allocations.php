<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('country')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('platform_fee_percentage', 5, 2)->default(15);
            $table->decimal('default_management_fee_percentage', 5, 2)->default(0);
            $table->decimal('maximum_management_fee_percentage', 5, 2)->default(30);
            $table->decimal('total_earnings', 18, 10)->default(0);
            $table->decimal('available_balance', 18, 10)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('master_account_artist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('management_fee_percentage', 5, 2)->nullable();
            $table->string('access_level')->default('report_only');
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['master_account_id', 'artist_id']);
        });

        Schema::table('artists', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('created_by_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->timestamp('split_locked_at')->nullable()->after('payment_status');
            $table->foreignId('split_locked_by')->nullable()->after('split_locked_at')->constrained('users')->nullOnDelete();
        });

        Schema::create('revenue_split_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('splits');
            $table->timestamp('effective_from');
            $table->timestamp('locked_at');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['album_id', 'version']);
        });

        Schema::create('revenue_split_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->json('current_splits');
            $table->json('proposed_splits');
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('royalty_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('royalty_id')->constrained()->cascadeOnDelete();
            $table->foreignId('split_version_id')->nullable()->constrained('revenue_split_versions')->nullOnDelete();
            $table->string('beneficiary_type');
            $table->unsignedBigInteger('beneficiary_id')->nullable();
            $table->string('share_type');
            $table->decimal('percentage', 8, 4);
            $table->decimal('gross_amount', 18, 10);
            $table->decimal('allocated_amount', 18, 10);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
            $table->index(['beneficiary_type', 'beneficiary_id']);
        });

        Schema::table('artists', function (Blueprint $table) {
            $table->decimal('revenue_share_percentage', 5, 2)->default(85)->change();
        });

        // Preserve existing royalty history as immutable allocations. Artist balances
        // were already credited by the old system, so this deliberately does not
        // change any balance columns.
        DB::table('royalties')
            ->join('artists', 'royalties.artist_id', '=', 'artists.id')
            ->select([
                'royalties.id as royalty_id', 'royalties.artist_id', 'royalties.amount',
                'artists.revenue_share_percentage',
            ])
            ->orderBy('royalties.id')
            ->chunkById(500, function ($rows) {
                $now = now();
                $allocations = [];
                foreach ($rows as $row) {
                    $gross = (float) $row->amount;
                    $artistPct = (float) $row->revenue_share_percentage;
                    $platformPct = 100 - $artistPct;
                    $allocations[] = [
                        'royalty_id' => $row->royalty_id, 'split_version_id' => null,
                        'beneficiary_type' => 'platform', 'beneficiary_id' => null,
                        'share_type' => 'platform_fee', 'percentage' => $platformPct,
                        'gross_amount' => $gross, 'allocated_amount' => round($gross * $platformPct / 100, 10),
                        'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now,
                    ];
                    $allocations[] = [
                        'royalty_id' => $row->royalty_id, 'split_version_id' => null,
                        'beneficiary_type' => 'artist', 'beneficiary_id' => $row->artist_id,
                        'share_type' => 'primary_artist', 'percentage' => $artistPct,
                        'gross_amount' => $gross, 'allocated_amount' => round($gross * $artistPct / 100, 10),
                        'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now,
                    ];
                }
                DB::table('royalty_allocations')->insert($allocations);
            }, 'royalties.id', 'royalty_id');
    }

    public function down(): void
    {
        Schema::dropIfExists('royalty_allocations');
        Schema::dropIfExists('revenue_split_change_requests');
        Schema::dropIfExists('revenue_split_versions');
        Schema::table('albums', fn (Blueprint $table) => $table->dropConstrainedForeignId('split_locked_by'));
        Schema::table('albums', fn (Blueprint $table) => $table->dropColumn('split_locked_at'));
        Schema::table('artists', fn (Blueprint $table) => $table->dropConstrainedForeignId('created_by_user_id'));
        Schema::dropIfExists('master_account_artist');
        Schema::dropIfExists('master_accounts');
        Schema::table('artists', function (Blueprint $table) {
            $table->decimal('revenue_share_percentage', 5, 2)->default(70)->change();
        });
    }
};
