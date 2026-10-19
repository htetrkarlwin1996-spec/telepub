<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Services\TeleMusicAppDelivery;
use Illuminate\Http\RedirectResponse;

class AdminTeleMusicDeliveryController extends Controller
{
    public function retry(Distribution $distribution, TeleMusicAppDelivery $delivery): RedirectResponse
    {
        abort_unless($distribution->store?->delivery_driver === 'telemusic_api', 422);
        abort_unless($distribution->album?->status === 'approved', 422, 'Only approved releases can be delivered.');

        $distribution->album->distributions()
            ->where('store_id', $distribution->store_id)
            ->update(['delivery_status' => 'pending', 'delivery_error' => null]);
        $delivery->dispatchIfSelected($distribution->album);

        return back()->with('success', 'TeleMusic App delivery has been queued again.');
    }
}
