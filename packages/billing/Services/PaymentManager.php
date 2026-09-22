<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services;

use Carbon\Carbon;
use Flow\Packages\Billing\Contracts\PaymentProviderInterface;
use Flow\Packages\Billing\Domain\Enums\InvoiceStatus;
use Flow\Packages\Billing\Domain\Enums\PaymentStatus;
use Flow\Packages\Billing\Domain\Enums\SubscriptionStatus;
use Flow\Packages\Billing\Domain\Models\BillingEvent;
use Flow\Packages\Billing\Domain\Models\BillingInvoice;
use Flow\Packages\Billing\Domain\Models\BillingPayment;
use Flow\Packages\Billing\Domain\Models\BillingRefund;
use Flow\Packages\Billing\Services\Providers\MockPaymentProvider;
use Flow\Packages\Billing\Services\Providers\StripeSandboxProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentManager
{
    /** @var array<string, PaymentProviderInterface> */
    protected array $providers = [];

    public function __construct(
        MockPaymentProvider $mockProvider,
        StripeSandboxProvider $stripeProvider
    ) {
        $this->providers['mock'] = $mockProvider;
        $this->providers['stripe'] = $stripeProvider;
    }

    public function getProvider(string $code = 'mock'): PaymentProviderInterface
    {
        return $this->providers[$code] ?? $this->providers['mock'];
    }

    /**
     * Process payment for an invoice.
     *
     * @param BillingInvoice $invoice
     * @param float $amount
     * @param string $providerCode
     * @param array $paymentMethodDetails
     * @return BillingPayment
     */
    public function processPayment(
        BillingInvoice $invoice,
        float $amount,
        string $providerCode = 'mock',
        array $paymentMethodDetails = []
    ): BillingPayment {
        $provider = $this->getProvider($providerCode);
        $now = Carbon::now();
        $paymentNumber = 'PAY-' . $now->format('Ym') . '-' . strtoupper(Str::random(6));

        $payment = BillingPayment::create([
            'id' => (string) Str::uuid(),
            'payment_number' => $paymentNumber,
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'provider' => $provider->getProviderCode(),
            'amount' => $amount,
            'currency' => $invoice->currency,
            'status' => PaymentStatus::PENDING,
            'payment_method' => $paymentMethodDetails['type'] ?? 'card',
            'initiated_at' => $now,
        ]);

        $chargeResult = $provider->charge(
            $amount,
            $invoice->currency,
            $paymentMethodDetails,
            ['invoice_id' => $invoice->id, 'tenant_id' => $invoice->tenant_id]
        );

        if ($chargeResult['success']) {
            return DB::transaction(function () use ($payment, $invoice, $chargeResult, $amount, $now) {
                $payment->update([
                    'status' => PaymentStatus::SUCCEEDED,
                    'provider_transaction_id' => $chargeResult['transaction_id'],
                    'completed_at' => $now,
                ]);

                // Update invoice
                $invoice->amount_paid = round((float) $invoice->amount_paid + $amount, 2);
                $invoice->balance_due = max(0.00, round((float) $invoice->total_amount - (float) $invoice->amount_paid, 2));

                if ($invoice->balance_due <= 0.00) {
                    $invoice->status = InvoiceStatus::PAID;
                } else {
                    $invoice->status = InvoiceStatus::PARTIALLY_PAID;
                }
                $invoice->save();

                // If subscription was past due and invoice is now settled, reactivate subscription
                if ($invoice->subscription && $invoice->subscription->status === SubscriptionStatus::PAST_DUE && $invoice->isPaid()) {
                    $invoice->subscription->update([
                        'status' => SubscriptionStatus::ACTIVE,
                        'grace_ends_at' => null,
                    ]);
                }

                $this->recordEvent($invoice->tenant_id, 'PaymentSucceeded', [
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $amount,
                    'transaction_id' => $chargeResult['transaction_id'],
                ]);

                return $payment;
            });
        } else {
            $payment->update([
                'status' => PaymentStatus::FAILED,
                'failure_reason' => $chargeResult['error_message'],
            ]);

            $this->recordEvent($invoice->tenant_id, 'PaymentFailed', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'reason' => $chargeResult['error_message'],
            ]);

            return $payment;
        }
    }

    /**
     * Process full or partial refund.
     */
    public function processRefund(
        BillingPayment $payment,
        float $amount,
        string $reason = 'Customer requested refund'
    ): BillingRefund {
        $provider = $this->getProvider($payment->provider);
        $now = Carbon::now();
        $refundNumber = 'REF-' . $now->format('Ym') . '-' . strtoupper(Str::random(6));

        $refundResult = $provider->refund($payment->provider_transaction_id ?? '', $amount, $reason);

        return DB::transaction(function () use ($payment, $amount, $reason, $refundNumber, $refundResult, $now) {
            $refund = BillingRefund::create([
                'id' => (string) Str::uuid(),
                'refund_number' => $refundNumber,
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'tenant_id' => $payment->tenant_id,
                'amount' => $amount,
                'currency' => $payment->currency,
                'reason' => $reason,
                'status' => $refundResult['success'] ? 'completed' : 'failed',
                'provider_refund_id' => $refundResult['refund_id'] ?? null,
                'created_at' => $now,
            ]);

            if ($refundResult['success']) {
                $payment->update([
                    'status' => $amount >= (float) $payment->amount ? PaymentStatus::REFUNDED : PaymentStatus::PARTIALLY_REFUNDED,
                ]);

                // Update invoice balance
                $invoice = $payment->invoice;
                if ($invoice) {
                    $invoice->amount_paid = max(0.00, round((float) $invoice->amount_paid - $amount, 2));
                    $invoice->balance_due = round((float) $invoice->total_amount - (float) $invoice->amount_paid, 2);
                    if ($invoice->amount_paid < (float) $invoice->total_amount) {
                        $invoice->status = $invoice->amount_paid > 0 ? InvoiceStatus::PARTIALLY_PAID : InvoiceStatus::ISSUED;
                    }
                    $invoice->save();
                }

                $this->recordEvent($payment->tenant_id, 'PaymentRefunded', [
                    'refund_id' => $refund->id,
                    'payment_id' => $payment->id,
                    'amount' => $amount,
                    'reason' => $reason,
                ]);
            }

            return $refund;
        });
    }

    protected function recordEvent(string $tenantId, string $eventType, array $payload): void
    {
        BillingEvent::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'event_type' => $eventType,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
