<?php

namespace App\Services;

use App\Models\Album;
use App\Models\AppSetting;

class ReleasePricing
{
    public function settings(): array
    {
        $values = AppSetting::whereIn('key', [
            'release_price_first_usd', 'release_price_single_usd', 'release_price_ep_usd',
            'release_price_album_usd', 'usd_to_thb_rate', 'usd_to_mmk_rate', 'offline_bank_instructions',
            'addon_composer_songwriter_usd', 'addon_global_performance_usd', 'addon_mechanical_usd',
        ])->pluck('value', 'key');

        return [
            'first' => (float) ($values['release_price_first_usd'] ?? 14.99),
            'single' => (float) ($values['release_price_single_usd'] ?? 9.99),
            'ep' => (float) ($values['release_price_ep_usd'] ?? 19.99),
            'album' => (float) ($values['release_price_album_usd'] ?? 29.99),
            'usd_to_thb' => (float) ($values['usd_to_thb_rate'] ?? 36),
            'usd_to_mmk' => (float) ($values['usd_to_mmk_rate'] ?? 4500),
            'offline_instructions' => (string) ($values['offline_bank_instructions'] ?? ''),
            'addons' => [
                'composer_songwriter' => ['name' => 'Composer / Songwriter Royalties', 'price' => (float) ($values['addon_composer_songwriter_usd'] ?? 0)],
                'global_performance' => ['name' => 'Global Performance Royalties', 'price' => (float) ($values['addon_global_performance_usd'] ?? 0)],
                'mechanical' => ['name' => 'Mechanical Royalties', 'price' => (float) ($values['addon_mechanical_usd'] ?? 0)],
            ],
        ];
    }

    public function for(Album $album): array
    {
        $settings = $this->settings();
        $hasPrevious = Album::where('artist_id', $album->artist_id)->whereKeyNot($album->id)
            ->where(fn ($q) => $q->where('payment_status', 'paid')->orWhereIn('status', ['submitted', 'approved']))->exists();
        $usd = $hasPrevious ? $settings[$album->release_type] : $settings['first'];

        return ['usd' => $usd, 'thb' => round($usd * $settings['usd_to_thb'], 2), 'mmk' => round($usd * $settings['usd_to_mmk']), 'is_first' => ! $hasPrevious] + $settings;
    }

    public function totalFor(Album $album, array $selectedAddons): array
    {
        $pricing = $this->for($album);
        $selected = collect($selectedAddons)->unique()->filter(fn ($key) => isset($pricing['addons'][$key]))->values();
        $addonTotal = $selected->sum(fn ($key) => $pricing['addons'][$key]['price']);
        $usd = round($pricing['usd'] + $addonTotal, 2);

        return $pricing + [
            'selected_addons' => $selected->all(),
            'addon_total_usd' => $addonTotal,
            'total_usd' => $usd,
            'total_thb' => round($usd * $pricing['usd_to_thb'], 2),
            'total_mmk' => round($usd * $pricing['usd_to_mmk']),
        ];
    }
}
