<?php

namespace App\Http\Resources;

use App\Models\Royalty;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $balanceBreakdown = $this->royalties()
            ->selectRaw('royalty_type, COALESCE(SUM(amount), 0) as gross_amount')
            ->groupBy('royalty_type')
            ->pluck('gross_amount', 'royalty_type');

        $balances = collect(Royalty::TYPES)->mapWithKeys(function ($label, $type) use ($balanceBreakdown) {
            $gross = (float) ($balanceBreakdown[$type] ?? 0);

            return [$type => round($this->getArtistShareAttribute($gross), 2)];
        });
        $grossAmount = (float) $balanceBreakdown->sum();
        $artistShareAmount = round($balances->sum(), 2);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'artist_name' => $this->artist_name,
            'genre' => $this->genre,
            'bio' => $this->bio,
            'country' => $this->country,
            'avatar' => $this->avatar,
            'banner' => $this->banner,
            'payment_email' => $this->payment_email,
            'paypal_email' => $this->paypal_email,
            'bank_account_name' => $this->bank_account_name,
            'bank_name' => $this->bank_name,
            'bank_country' => $this->bank_country,
            'bank_account_no' => $this->bank_account_no,
            'kbz_pay_name' => $this->kbz_pay_name,
            'kbz_pay_phone' => $this->kbz_pay_phone,
            'wave_pay_name' => $this->wave_pay_name,
            'wave_pay_phone' => $this->wave_pay_phone,
            'spotify_profile_url' => $this->spotify_profile_url,
            'apple_music_profile_url' => $this->apple_music_profile_url,
            'youtube_profile_url' => $this->youtube_profile_url,
            'tidal_profile_url' => $this->tidal_profile_url,
            'total_earnings' => (float) $this->total_earnings,
            'available_balance' => (float) $this->available_balance,
            'balance_breakdown' => [
                ...$balances->all(),
                'total_balance' => $artistShareAmount,
                'gross_amount' => $grossAmount,
                'revenue_share_percentage' => (float) $this->revenue_share_percentage,
                'artist_share_amount' => $artistShareAmount,
                'telemusic_fee_percentage' => (float) $this->tele_music_fee_percentage,
                'telemusic_fee_amount' => round($this->getTeleMusicFeeAttribute($grossAmount), 2),
                'currency' => 'USD',
            ],
            'pending_balance' => (float) $this->pending_balance,
            'revenue_share_percentage' => (float) $this->revenue_share_percentage,
            'created_at' => $this->created_at,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
