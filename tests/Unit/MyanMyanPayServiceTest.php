<?php

namespace Tests\Unit;

use App\Services\MyanMyanPayService;
use MMPay\MMPay;
use PHPUnit\Framework\TestCase;

class MyanMyanPayServiceTest extends TestCase
{
    public function test_payment_uses_the_sdk_pay_method_for_sandbox_and_live_keys(): void
    {
        $sdk = new class extends MMPay
        {
            public array $received = [];

            public function __construct() {}

            public function pay(array $params): array
            {
                $this->received = $params;

                return ['qr' => 'test-qr'];
            }
        };

        $service = new class($sdk) extends MyanMyanPayService
        {
            public function __construct(private readonly MMPay $sdk) {}

            protected function client(): MMPay
            {
                return $this->sdk;
            }
        };

        $payload = ['orderId' => 'release-1', 'amount' => 15000];

        $this->assertSame(['qr' => 'test-qr'], $service->pay($payload));
        $this->assertSame($payload, $sdk->received);
    }
}
