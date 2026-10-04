<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\ReleasePayment;
use App\Services\LoginAnnouncement;
use App\Services\MaintenanceMode;
use App\Services\ReleasePricing;
use App\Services\WithdrawalFee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(MaintenanceMode $maintenanceMode, ReleasePricing $releasePricing, WithdrawalFee $withdrawalFee, LoginAnnouncement $loginAnnouncement): View
    {
        $announcementSettings = $loginAnnouncement->settings();

        return view('admin.settings.index', [
            'displayCurrency' => display_currency(),
            'maintenanceActive' => $maintenanceMode->active(),
            'maintenanceRemaining' => $maintenanceMode->remainingSeconds(),
            'maintenanceEndsAt' => $maintenanceMode->endsAt(),
            'releasePricing' => $releasePricing->settings(),
            'withdrawalFeePercentage' => $withdrawalFee->percentage(),
            'minimumWithdrawalAmount' => $withdrawalFee->minimumAmount(),
            'loginAnnouncement' => $announcementSettings,
            'loginAnnouncementEmbedUrl' => $loginAnnouncement->youtubeEmbedUrl($announcementSettings['youtube_url'] ?? ''),
            'pendingOfflinePayments' => ReleasePayment::with(['album.artist', 'user'])->where('provider', 'offline')->where('status', 'pending')->latest()->get(),
        ]);
    }

    public function updateLoginAnnouncement(Request $request, LoginAnnouncement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'title' => ['nullable', 'string', 'max:200'],
            'body' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'remove_image' => ['nullable', 'boolean'],
            'youtube_url' => ['nullable', 'url', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'url', 'max:500'],
        ]);
        if (filled($validated['youtube_url'] ?? null) && ! $announcement->youtubeEmbedUrl($validated['youtube_url'])) {
            throw ValidationException::withMessages(['youtube_url' => 'Enter a valid YouTube video, Shorts, or youtu.be URL.']);
        }
        $current = $announcement->settings();
        $keepsCurrentImage = filled($current['image_path'] ?? null) && ! ($validated['remove_image'] ?? false);
        if (($validated['enabled'] ?? false) && blank($validated['title'] ?? null) && blank($validated['body'] ?? null)
            && ! $request->hasFile('image') && blank($validated['youtube_url'] ?? null) && ! $keepsCurrentImage) {
            throw ValidationException::withMessages(['title' => 'Add text, an image, or a YouTube video before enabling the announcement.']);
        }

        $imagePath = $current['image_path'] ?? null;
        if (($validated['remove_image'] ?? false) || $request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = null;
        }
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements', 'public');
        }

        AppSetting::updateOrCreate(['key' => 'login_announcement'], ['value' => json_encode([
            'enabled' => (bool) ($validated['enabled'] ?? false),
            'title' => $validated['title'] ?? '',
            'body' => $validated['body'] ?? '',
            'image_path' => $imagePath,
            'youtube_url' => $validated['youtube_url'] ?? '',
            'button_text' => $validated['button_text'] ?? '',
            'button_url' => $validated['button_url'] ?? '',
            'updated_at' => now()->toIso8601String(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
        $announcement->clearCache();

        return back()->with('success', 'Login announcement updated. Users will see it once after their next login.');
    }

    public function updateWithdrawalFee(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'withdrawal_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'minimum_withdrawal_amount' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'withdrawal_fee_percentage'],
            ['value' => (string) $validated['withdrawal_fee_percentage']],
        );
        AppSetting::updateOrCreate(
            ['key' => 'minimum_withdrawal_amount'],
            ['value' => (string) $validated['minimum_withdrawal_amount']],
        );

        return back()->with('success', 'Withdrawal fee and minimum amount updated.');
    }

    public function updateReleasePricing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'release_price_first_usd' => ['required', 'numeric', 'min:0', 'max:9999'],
            'release_price_single_usd' => ['required', 'numeric', 'min:0', 'max:9999'],
            'release_price_ep_usd' => ['required', 'numeric', 'min:0', 'max:9999'],
            'release_price_album_usd' => ['required', 'numeric', 'min:0', 'max:9999'],
            'usd_to_thb_rate' => ['required', 'numeric', 'min:0.0001'],
            'usd_to_mmk_rate' => ['required', 'numeric', 'min:0.0001'],
            'offline_bank_instructions' => ['required', 'string', 'max:5000'],
            'addon_composer_songwriter_usd' => ['required', 'numeric', 'min:0', 'max:9999'],
            'addon_global_performance_usd' => ['required', 'numeric', 'min:0', 'max:9999'],
            'addon_mechanical_usd' => ['required', 'numeric', 'min:0', 'max:9999'],
        ]);

        foreach ($validated as $key => $value) {
            AppSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return back()->with('success', 'Release pricing and exchange rates updated.');
    }

    public function updateCurrency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_currency' => ['required', 'in:USD,EUR'],
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'display_currency'],
            ['value' => $validated['display_currency']],
        );
        Cache::forget('app_setting.display_currency');

        return back()->with('success', 'Display currency updated across the application. Amounts were not converted.');
    }
}
