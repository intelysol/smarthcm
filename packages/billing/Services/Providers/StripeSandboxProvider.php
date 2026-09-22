<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services\Providers;

use Flow\Packages\Billing\Contracts\PaymentProviderInterface;
use Illuminate\Support\Str;

class StripeSandboxProvider implements PaymentProviderInterface
{
    public function getProviderCode(): string
    {
        return 'stripe';
    }

    public function charge(float $amount, string $currency, array $paymentMethodDetails, array $metadata = []): array
    {
        // Sandbox implementation: tokenized charges
        $token = $paymentMethodDetails['token'] ?? 'tok_visa';

        if ($token === 'tok_chargeCustomerFail') {
            return [
                'success' => false,
                'transaction_id' => 'ch_' . Str::random(24),
                'status' => 'failed',
                'error_message' => 'Your card has insufficient funds.',
            ];
        }

        return [
            'success' => true,
            'transaction_id' => 'ch_' . Str::random(24),
            'status' => 'succeeded',
            'error_message' => null,
        ];
    }

    public function refund(string $transactionId, float $amount, string $reason = ''): array
    {
        return [
            'success' => true,
            'refund_id' => 're_' . Str::random(24),
            'status' => 'completed',
            'error_message' => null,
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        // Stripe webhook signature header usually contains: t=...,v1=...
        if (str_contains($signature, 'v1=')) {
            preg_match('/t=([^,]+)/', $signature, $timestampMatch);
            preg_match('/v1=([^,]+)/', $signature, $v1Match);

            if (! empty($timestampMatch[1]) && ! empty($v1Match[1])) {
                $signedPayload = $timestampMatch[1] . '.' . $payload;
                $expected = hash_hmac('sha256', $signedPayload, $secret);

                return hash_equals($expected, $v1Match[1]);
            }
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
