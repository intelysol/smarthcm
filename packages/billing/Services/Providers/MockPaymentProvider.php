<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services\Providers;

use Flow\Packages\Billing\Contracts\PaymentProviderInterface;
use Illuminate\Support\Str;

class MockPaymentProvider implements PaymentProviderInterface
{
    public function getProviderCode(): string
    {
        return 'mock';
    }

    public function charge(float $amount, string $currency, array $paymentMethodDetails, array $metadata = []): array
    {
        // Support deterministic test failure triggers
        if (($paymentMethodDetails['token'] ?? '') === 'tok_fail' || ($paymentMethodDetails['card_number'] ?? '') === '4000000000000002') {
            return [
                'success' => false,
                'transaction_id' => 'mock_txn_' . Str::random(16),
                'status' => 'failed',
                'error_message' => 'Card was declined by issuing bank (Test Simulation)',
            ];
        }

        return [
            'success' => true,
            'transaction_id' => 'mock_txn_' . Str::random(16),
            'status' => 'succeeded',
            'error_message' => null,
        ];
    }

    public function refund(string $transactionId, float $amount, string $reason = ''): array
    {
        return [
            'success' => true,
            'refund_id' => 'mock_rfnd_' . Str::random(16),
            'status' => 'completed',
            'error_message' => null,
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
