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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReleaseCheckoutController extends Controller
{
    public function show(Album $album, ReleasePricing $pricing)
    {
        $this->authorizeAlbum($album);
        $payments = $album->hasMany(ReleasePayment::class)->latest()->get();
        $qrPayment = $payments->first(fn ($payment) => $payment->provider === 'myanmyanpay' && $payment->status === 'pending' && filled($payment->qr_data));
        $qrImage = $qrPayment ? (new SvgWriter)->write(new QrCode($qrPayment->qr_data, size: 320))->getDataUri() : null;

        return view('artist.catalog.checkout', compact('album', 'payments', 'qrPayment', 'qrImage') + ['pricing' => $pricing->for($album)]);
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
            abort_unless($mmpay->configured(), 503, 'MyanMyanPay is not configured.');
            $response = $mmpay->pay(['orderId' => $reference, 'amount' => (int) $amount, 'currency' => 'MMK', 'callbackUrl' => route('webhooks.myanmyanpay'), 'customMessage' => 'TeleMusic release: '.$album->title, 'items' => [['name' => $album->title, 'amount' => (int) $amount, 'quantity' => 1]]]);
            $qr = data_get($response, 'qr') ?? data_get($response, 'data.qr') ?? data_get($response, 'qrCode') ?? data_get($response, 'data.qrCode');
            $url = data_get($response, 'paymentUrl') ?? data_get($response, 'data.paymentUrl') ?? data_get($response, 'url');
            $payment->update(['qr_data' => is_string($qr) ? $qr : json_encode($qr), 'checkout_url' => $url, 'gateway_response' => $response]);

            return redirect()->route('artist.catalog.checkout', $album)->with('success', 'MMQR payment request created. Scan the QR code below.');
        } catch (\Throwable $e) {
            report($e);
            $payment->update(['status' => 'failed', 'gateway_response' => ['error' => $e->getMessage()]]);

            return back()->withErrors(['payment' => 'Payment could not be started: '.$e->getMessage()]);
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

    public function paypalWebhook(Request $request, ReleaseSubmission $submission)
    {
        abort_unless(config('services.paypal.webhook_id'), 503);
        $base = config('services.paypal.base_url');
        $token = Http::asForm()->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.secret'))->post($base.'/v1/oauth2/token', ['grant_type' => 'client_credentials'])->throw()->json('access_token');
        $verification = Http::withToken($token)->post($base.'/v1/notifications/verify-webhook-signature', [
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'webhook_id' => config('services.paypal.webhook_id'),
            'webhook_event' => $request->json()->all(),
        ])->throw()->json();
        abort_unless(($verification['verification_status'] ?? '') === 'SUCCESS', 400);
        if ($request->json('event_type') === 'PAYMENT.CAPTURE.COMPLETED') {
            $reference = $request->json('resource.custom_id') ?? $request->json('resource.invoice_id');
            if ($reference && ($payment = ReleasePayment::where('reference', $reference)->first())) {
                $this->markPaid($payment, $submission);
            }
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

    private function authorizeAlbum(Album $album): void
    {
        abort_unless($album->artist_id === auth()->user()->artist->id, 403);
    }
}
