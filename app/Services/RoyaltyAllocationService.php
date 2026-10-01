<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\MasterAccount;
use App\Models\Royalty;
use App\Models\RoyaltyAllocation;
use Illuminate\Support\Facades\DB;

class RoyaltyAllocationService
{
    public function __construct(private readonly RevenueSplitService $splits) {}

    public function allocate(Royalty $royalty): void
    {
        DB::transaction(function () use ($royalty) {
            $royalty = Royalty::with(['album.artist.masterAccounts', 'album.collaboratingArtists', 'artist.masterAccounts'])
                ->lockForUpdate()->findOrFail($royalty->id);

            if ($royalty->allocations()->exists()) {
                return;
            }

            $gross = (float) $royalty->amount;
            $artist = $royalty->artist;
            $album = $royalty->album;
            $version = $album ? $this->splits->versionFor($album, $royalty->created_at) : null;
            $snapshot = $version?->splits;

            if (! $snapshot) {
                $master = $artist->activeMasterAccount();
                $snapshot = [
                    'platform_fee_percentage' => $master ? (float) $master->platform_fee_percentage : 100 - (float) $artist->revenue_share_percentage,
                    'master_account_id' => $master?->id,
                    'management_fee_percentage' => $master ? $master->managementFeeFor($artist) : 0,
                    'primary_artist_id' => $artist->id,
                    'primary_artist_percentage' => 100,
                    'collaborators' => [],
                ];
            }

            $platformPct = (float) $snapshot['platform_fee_percentage'];
            $platformAmount = round($gross * $platformPct / 100, 10);
            $afterPlatform = $gross - $platformAmount;
            $managementPct = (float) ($snapshot['management_fee_percentage'] ?? 0);
            $managementAmount = round($afterPlatform * $managementPct / 100, 10);
            $artistPool = $afterPlatform - $managementAmount;

            $this->record($royalty, $version?->id, 'platform', null, 'platform_fee', $platformPct, $gross, $platformAmount);

            if (($snapshot['master_account_id'] ?? null) && $managementAmount != 0.0) {
                $masterId = (int) $snapshot['master_account_id'];
                $this->record($royalty, $version?->id, 'master_account', $masterId, 'management_fee', $managementPct, $gross, $managementAmount);
                MasterAccount::whereKey($masterId)->incrementEach([
                    'total_earnings' => $managementAmount,
                    'available_balance' => $managementAmount,
                ]);
            }

            $distributed = 0.0;
            foreach ($snapshot['collaborators'] ?? [] as $collaborator) {
                $percentage = (float) $collaborator['percentage'];
                $amount = round($artistPool * $percentage / 100, 10);
                $distributed += $amount;
                $this->creditArtist($royalty, $version?->id, (int) $collaborator['artist_id'], 'collaborator', $percentage, $gross, $amount);
            }

            $primaryPercentage = (float) ($snapshot['primary_artist_percentage'] ?? 100);
            $this->creditArtist(
                $royalty,
                $version?->id,
                (int) $snapshot['primary_artist_id'],
                'primary_artist',
                $primaryPercentage,
                $gross,
                round($artistPool - $distributed, 10),
            );
        });
    }

    public function reverse(Royalty $royalty): void
    {
        DB::transaction(function () use ($royalty) {
            foreach ($royalty->allocations()->lockForUpdate()->get() as $allocation) {
                if ($allocation->beneficiary_type === 'artist' && $allocation->beneficiary_id) {
                    Artist::whereKey($allocation->beneficiary_id)->decrementEach([
                        'total_earnings' => (float) $allocation->allocated_amount,
                        'available_balance' => (float) $allocation->allocated_amount,
                    ]);
                }
                if ($allocation->beneficiary_type === 'master_account' && $allocation->beneficiary_id) {
                    MasterAccount::whereKey($allocation->beneficiary_id)->decrementEach([
                        'total_earnings' => (float) $allocation->allocated_amount,
                        'available_balance' => (float) $allocation->allocated_amount,
                    ]);
                }
            }
            $royalty->allocations()->delete();
        });
    }

    private function creditArtist(Royalty $royalty, ?int $versionId, int $artistId, string $type, float $percentage, float $gross, float $amount): void
    {
        $this->record($royalty, $versionId, 'artist', $artistId, $type, $percentage, $gross, $amount);
        Artist::whereKey($artistId)->incrementEach(['total_earnings' => $amount, 'available_balance' => $amount]);
    }

    private function record(Royalty $royalty, ?int $versionId, string $beneficiaryType, ?int $beneficiaryId, string $shareType, float $percentage, float $gross, float $amount): void
    {
        RoyaltyAllocation::create([
            'royalty_id' => $royalty->id,
            'split_version_id' => $versionId,
            'beneficiary_type' => $beneficiaryType,
            'beneficiary_id' => $beneficiaryId,
            'share_type' => $shareType,
            'percentage' => $percentage,
            'gross_amount' => $gross,
            'allocated_amount' => $amount,
            'currency' => 'USD',
        ]);
    }
}
