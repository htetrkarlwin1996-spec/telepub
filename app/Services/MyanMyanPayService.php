<?php

namespace App\Services;

use MMPay\MMPay;

class MyanMyanPayService
{
    public function configured(): bool
    {
        return filled(config('services.myanmyanpay.app_id')) && filled(config('services.myanmyanpay.publishable_key'))
            && filled(config('services.myanmyanpay.secret_key'));
    }

    public function pay(array $payload): array
    {
        $sdk = new MMPay([
            'appId' => config('services.myanmyanpay.app_id'),
            'publishableKey' => config('services.myanmyanpay.publishable_key'),
            'secretKey' => config('services.myanmyanpay.secret_key'),
            'apiBaseUrl' => config('services.myanmyanpay.api_base_url'),
        ]);

        return config('services.myanmyanpay.sandbox') ? $sdk->sandboxPay($payload) : $sdk->pay($payload);
    }

    public function verify(string $payload, string $nonce, string $signature): bool
    {
        $sdk = new MMPay([
            'appId' => config('services.myanmyanpay.app_id'), 'publishableKey' => config('services.myanmyanpay.publishable_key'),
            'secretKey' => config('services.myanmyanpay.secret_key'), 'apiBaseUrl' => config('services.myanmyanpay.api_base_url'),
        ]);

        return $sdk->verifyCb($payload, $nonce, $signature);
    }
}
