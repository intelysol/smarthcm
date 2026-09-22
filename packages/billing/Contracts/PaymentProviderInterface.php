<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Contracts;

interface PaymentProviderInterface
{
    public function getProviderCode(): string;

    /**
     * Process a payment intent/charge.
     *
     * @param float $amount
     * @param string $currency
     * @param array $paymentMethodDetails
     * @param array $metadata
     * @return array{success: bool, transaction_id: string, status: string, error_message: ?string}
     */
    public function charge(float $amount, string $currency, array $paymentMethodDetails, array $metadata = []): array;

    /**
     * Process a full or partial refund.
     */
    public function refund(string $transactionId, float $amount, string $reason = ''): array;

    /**
     * Verify incoming webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool;
}
