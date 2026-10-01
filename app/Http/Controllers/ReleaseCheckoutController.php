<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\ReleasePayment;
use App\Services\MyanMyanPayService;
use App\Services\ReleasePricing;
use App\Services\ReleaseSubmission;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ReleaseCheckoutController extends Controller
{
    public function show(Album $album, ReleasePricing $pricing)
    {
        $this->authorizeAlbum($album);
        $this->expireStaleMmqr($album);
        $payments = $album->hasMany(ReleasePayment::class)->latest()->get();
        $qrPayment = $payments->first(fn ($payment) => $payment->provider === 'myanmyanpay' && $payment->status === 'pending' && filled($payment->qr_data)
            && ($payment->expires_at ?? $payment->created_at->copy()->addMinutes(15))->isFuture());
        $qrImage = $qrPayment ? (new SvgWriter)->write(new QrCode($qrPayment->qr_data, size: 320))->getDataUri() : null;
        $qrExpiresAt = $qrPayment ? ($qrPayment->expires_at ?? $qrPayment->created_at->copy()->addMinutes(15)) : null;

        return view('artist.catalog.checkout', compact('album', 'payments', 'qrPayment', 'qrImage', 'qrExpiresAt') + ['pricing' => $pricing->for($album)]);
    }

    public function pay(Request $request, Album $album, ReleasePricing $pricing, MyanMyanPayService $mmpay)
    {
        $this->authorizeAlbum($album);
        $data = $request->validate(['method' => ['required', 'in:stripe,paypal,offline,myanmyanpay']]);
        $prices = $pricing->for($album);
        $method = $data['method'];
        $currency = match ($method) {
            'offline' => 'THB', 'myanmyanpay' => 'MMK', default => 'USD'
        };
        $amount = match ($method) {
            'offline' => $prices['thb'], 'myanmyanpay' => $prices['mmk'], default => $prices['usd']
        };
        if ($method === 'myanmyanpay') {
            return Cache::lock('mmqr-payment-album-'.$album->id, 30)->block(10, fn () => $this->myanmyanpay($request, $album, (float) $amount, $mmpay));
        }
        $reference = 'REL-'.strtoupper(substr($method, 0, 3)).'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        $payment = ReleasePayment::create(['album_id' => $album->id, 'user_id' => $request->user()->id, 'provider' => $method, 'amount' => $amount, 'currency' => $currency, 'reference' => $reference]);
        $album->update(['payment_status' => 'pending']);

        try {
            if ($method === 'offline') {
                return back()->with('success', 'Bank transfer request created. Use the instructions below and wait for admin approval.');
            }
            if ($method === 'stripe') {
                return $this->stripe($payment, $album);
            }
            if ($method === 'paypal') {
                return $this->paypal($payment, $album);
            }
            throw new RuntimeException('Unsupported payment method.');
        } catch (\Throwable $e) {
            report($e);
            $payment->update(['status' => 'failed', 'gateway_response' => ['error' => $e->getMessage()]]);

            return back()->withErrors(['payment' => 'Payment could not be started: '.$e->getMessage()]);
        }
    }

    private function myanmyanpay(Request $request, Album $album, float $amount, MyanMyanPayService $mmpay)
    {
        $this->expireStaleMmqr($album);
        $existing = ReleasePayment::where('album_id', $album->id)
            ->where('user_id', $request->user()->id)->where('provider', 'myanmyanpay')
            ->where('status', 'pending')->whereNotNull('qr_data')
            ->where(fn ($query) => $query->where('expires_at', '>', now())
                ->orWhere(fn ($legacy) => $legacy->whereNull('expires_at')->where('created_at', '>', now()->subMinutes(15))))
            ->latest()->first();
        if ($existing) {
            return redirect()->route('artist.catalog.checkout', $album)
                ->with('success', 'Your existing MMQR order is still active and has been reopened.');
        }

        abort_unless($mmpay->configured(), 503, 'MyanMyanPay is not configured.');
        $reference = 'REL-MYA-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        $payment = ReleasePayment::create([
            'album_id' => $album->id, 'user_id' => $request->user()->id,
            'provider' => 'myanmyanpay', 'amount' => $amount, 'currency' => 'MMK',
            'reference' => $reference, 'expires_at' => now()->addMinutes(15),
        ]);
        $album->update(['payment_status' => 'pending']);

        try {
            $response = $mmpay->pay(['orderId' => $reference, 'amount' => (int) $amount, 'currency' => 'MMK', 'callbackUrl' => route('webhooks.myanmyanpay'), 'customMessage' => 'TeleMusic release: '.$album->title, 'items' => [['name' => $album->title, 'amount' => (int) $amount, 'quantity' => 1]]]);
            $qr = data_get($response, 'qr') ?? data_get($response, 'data.qr') ?? data_get($response, 'qrCode') ?? data_get($response, 'data.qrCode');
            $url = data_get($response, 'paymentUrl') ?? data_get($response, 'data.paymentUrl') ?? data_get($response, 'url');
            $gatewayStatus = strtoupper((string) (data_get($response, 'status') ?? data_get($response, 'data.status') ?? 'PENDING'));
            if (in_array($gatewayStatus, ['FAILED', 'CANCELLED', 'CANCELED', 'EXPIRED'], true)) {
                throw new RuntimeException('MyanMyanPay returned payment status '.$gatewayStatus.'.');
            }
            if (! is_string($qr) || trim($qr) === '') {
                throw new RuntimeException('MyanMyanPay did not return an MMQR payment code.');
            }
            $payment->update(['qr_data' => $qr, 'checkout_url' => $url, 'gateway_response' => $response]);

            return redirect()->route('artist.catalog.checkout', $album)->with('success', 'MMQR payment request created. Scan the QR code in the popup.');
        } catch (\Throwable $e) {
            report($e);
            $payment->update(['status' => 'failed', 'gateway_response' => ['error' => $e->getMessage()]]);

            return back()->withErrors(['payment' => 'Payment could not be started: '.$e->getMessage()]);
        }
    }

    private function expireStaleMmqr(Album $album): void
    {
        $expired = ReleasePayment::where('album_id', $album->id)->where('provider', 'myanmyanpay')->where('status', 'pending')
            ->where(fn ($query) => $query->where('expires_at', '<=', now())
                ->orWhere(fn ($legacy) => $legacy->whereNull('expires_at')->where('created_at', '<=', now()->subMinutes(15))))
            ->update(['status' => 'expired', 'qr_data' => null, 'checkout_url' => null]);

        if ($expired && ! ReleasePayment::where('album_id', $album->id)->whereIn('status', ['pending', 'paid'])->exists()) {
            $album->update(['payment_status' => 'unpaid']);
        }
    }

    public function cancel(Request $request, ReleasePayment $payment, MyanMyanPayService $mmpay)
    {
        abort_unless($payment->user_id === $request->user()->id, 403);
        abort_unless($payment->provider === 'myanmyanpay', 422);

        if ($payment->status !== 'pending') {
            return redirect()->route('artist.catalog.checkout', $payment->album)
                ->withErrors(['payment' => 'Only a pending MMQR transaction can be cancelled.']);
        }

        try {
            abort_unless($mmpay->configured(), 503, 'MyanMyanPay is not configured.');
            $response = $mmpay->cancel(['orderId' => $payment->reference]);
            $gatewayStatus = strtoupper((string) (data_get($response, 'status') ?? data_get($response, 'data.status') ?? ''));

            if (! in_array($gatewayStatus, ['CANCELLED', 'CANCELED', 'EXPIRED'], true)) {
                throw new RuntimeException('MyanMyanPay returned cancellation status '.($gatewayStatus ?: 'UNKNOWN').'.');
            }

            $this->cancelLocally($payment, $response, $gatewayStatus === 'EXPIRED' ? 'expired' : 'cancelled');

            return redirect()->route('artist.catalog.checkout', $payment->album)
                ->with('success', 'MMQR transaction cancelled. You can choose another payment method.');
        } catch (\Throwable $e) {
            report($e);

            if (str_contains($e->getMessage(), '404') && str_contains(strtolower($e->getMessage()), 'order not found')) {
                $this->cancelLocally($payment, ['error' => $e->getMessage()], 'cancelled');

                return redirect()->route('artist.catalog.checkout', $payment->album)
                    ->with('success', 'The MMQR order no longer exists at MyanMyanPay. Its local pending record was cleared.');
            }

            return redirect()->route('artist.catalog.checkout', $payment->album)
                ->withErrors(['payment' => 'Transaction could not be cancelled: '.$e->getMessage()]);
        }
    }

    private function stripe(ReleasePayment $payment, Album $album)
    {
        abort_unless(config('services.stripe.secret'), 503, 'Stripe is not configured.');
        $session = Http::withToken(config('services.stripe.secret'))->asForm()->post('https://api.stripe.com/v1/checkout/sessions', [
            'mode' => 'payment', 'success_url' => route('artist.catalog.checkout', $album).'?payment=success', 'cancel_url' => route('artist.catalog.checkout', $album),
            'client_reference_id' => $payment->reference, 'customer_email' => auth()->user()->email,
            'line_items' => [['price_data' => ['currency' => 'usd', 'unit_amount' => (int) round($payment->amount * 100), 'product_data' => ['name' => 'TeleMusic release — '.$album->title]], 'quantity' => 1]],
        ])->throw()->json();
        $payment->update(['checkout_url' => $session['url'], 'gateway_response' => ['session_id' => $session['id']]]);

        return redirect()->away($session['url']);
    }

    private function paypal(ReleasePayment $payment, Album $album)
    {
        abort_unless(config('services.paypal.client_id') && config('services.paypal.secret'), 503, 'PayPal is not configured.');
        $base = config('services.paypal.base_url');
        $token = Http::asForm()->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.secret'))->post($base.'/v1/oauth2/token', ['grant_type' => 'client_credentials'])->throw()->json('access_token');
        $order = Http::withToken($token)->post($base.'/v2/checkout/orders', ['intent' => 'CAPTURE', 'purchase_units' => [['reference_id' => $payment->reference, 'custom_id' => $payment->reference, 'amount' => ['currency_code' => 'USD', 'value' => number_format((float) $payment->amount, 2, '.', '')]]], 'application_context' => ['return_url' => route('payments.paypal.return', $payment), 'cancel_url' => route('artist.catalog.checkout', $album)]])->throw()->json();
        $url = collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null;
        $payment->update(['checkout_url' => $url, 'gateway_response' => ['order_id' => $order['id']]]);

        return redirect()->away($url);
    }

    public function paypalReturn(Request $request, ReleasePayment $payment, ReleaseSubmission $submission)
    {
        abort_unless($payment->user_id === $request->user()->id, 403);
        $base = config('services.paypal.base_url');
        $token = Http::asForm()->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.secret'))->post($base.'/v1/oauth2/token', ['grant_type' => 'client_credentials'])->throw()->json('access_token');
        $orderId = data_get($payment->gateway_response, 'order_id');
        $capture = Http::withToken($token)->withBody('', 'application/json')->post($base.'/v2/checkout/orders/'.$orderId.'/capture')->throw()->json();
        abort_unless(($capture['status'] ?? '') === 'COMPLETED', 422);
        $this->markPaid($payment, $submission);

        return redirect()->route('artist.catalog.show', $payment->album)->with('success', 'Payment completed and release submitted.');
    }

    public function stripeWebhook(Request $request, ReleaseSubmission $submission)
    {
        $secret = config('services.stripe.webhook_secret');
        abort_unless($secret, 503);
        $parts = collect(explode(',', $request->header('Stripe-Signature')))->mapWithKeys(fn ($part) => [strtok($part, '=') => substr(strstr($part, '='), 1)]);
        abort_unless(isset($parts['t']) && ctype_digit($parts['t']) && abs(time() - (int) $parts['t']) <= 300, 400);
        $expected = hash_hmac('sha256', $parts['t'].'.'.$request->getContent(), $secret);
        abort_unless(hash_equals($expected, (string) ($parts['v1'] ?? '')), 400);
        $event = $request->json()->all();
        if (($event['type'] ?? '') === 'checkout.session.completed' && data_get($event, 'data.object.payment_status') === 'paid') {
            $payment = ReleasePayment::where('reference', data_get($event, 'data.object.client_reference_id'))->firstOrFail();
            $this->markPaid($payment, $submission);
        }

        return response()->json(['received' => true]);
    }

    public function myanWebhook(Request $request, MyanMyanPayService $mmpay, ReleaseSubmission $submission)
    {
        abort_unless($mmpay->verify($request->getContent(), $request->header('X-Mmpay-Nonce', ''), $request->header('X-Mmpay-Signature', '')), 400);
        $data = $request->json()->all();
        if (in_array(strtoupper($data['status'] ?? ''), ['SUCCESS', 'COMPLETED', 'PAID'], true)) {
            $payment = ReleasePayment::where('reference', $data['orderId'] ?? '')->firstOrFail();
            abort_unless(strtoupper((string) ($data['currency'] ?? 'MMK')) === 'MMK', 400);
            abort_unless((int) $payment->amount === (int) ($data['amount'] ?? 0), 400);
            abort_unless(! isset($data['condition']) || strtoupper((string) $data['condition']) === 'TOUCHED', 400);
            $this->markPaid($payment, $submission);
        }

        return response()->json(['received' => true]);
    }

    public function approve(ReleasePayment $payment, ReleaseSubmission $submission)
    {
        abort_unless($payment->provider === 'offline', 422);
        $this->markPaid($payment, $submission);

        return back()->with('success', 'Offline payment approved and release submitted.');
    }

    private function markPaid(ReleasePayment $payment, ReleaseSubmission $submission): void
    {
        DB::transaction(function () use ($payment, $submission) {
            $payment = ReleasePayment::lockForUpdate()->findOrFail($payment->id);
            if ($payment->status === 'paid') {
                return;
            } $payment->update(['status' => 'paid', 'paid_at' => now()]);
            $submission->finalize($payment->album);
        });
    }

    private function cancelLocally(ReleasePayment $payment, array $gatewayResponse, string $status): void
    {
        DB::transaction(function () use ($payment, $gatewayResponse, $status) {
            $payment = ReleasePayment::lockForUpdate()->findOrFail($payment->id);

            if ($payment->status !== 'pending') {
                return;
            }

            $payment->update([
                'status' => $status,
                'qr_data' => null,
                'expires_at' => null,
                'checkout_url' => null,
                'gateway_response' => $gatewayResponse,
            ]);

            $hasActivePayment = ReleasePayment::where('album_id', $payment->album_id)
                ->whereKeyNot($payment->id)
                ->whereIn('status', ['pending', 'paid'])
                ->exists();

            if (! $hasActivePayment) {
                $payment->album()->update(['payment_status' => 'unpaid']);
            }
        });
    }

    private function authorizeAlbum(Album $album): void
    {
        abort_unless($album->artist_id === current_artist()?->id, 403);
    }
}
