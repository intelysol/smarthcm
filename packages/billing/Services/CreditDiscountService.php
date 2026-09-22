<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services;

use Flow\Packages\Billing\Domain\Models\BillingCredit;
use Flow\Packages\Billing\Domain\Models\BillingCreditTransaction;
use Flow\Packages\Billing\Domain\Models\BillingDiscount;
use Flow\Packages\Billing\Domain\Models\BillingDiscountRedemption;
use Flow\Packages\Billing\Domain\Models\BillingInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreditDiscountService
{
    /**
     * Issue a credit to a tenant wallet.
     */
    public function issueCredit(
        string $tenantId,
        float $amount,
        string $reason,
        string $currency = 'USD',
        ?\DateTimeInterface $expiresAt = null
    ): BillingCredit {
        return DB::transaction(function () use ($tenantId, $amount, $reason, $currency, $expiresAt) {
            $credit = BillingCredit::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'amount' => $amount,
                'balance' => $amount,
                'currency' => $currency,
                'reason' => $reason,
                'status' => 'active',
                'expires_at' => $expiresAt,
            ]);

            BillingCreditTransaction::create([
                'id' => (string) Str::uuid(),
                'credit_id' => $credit->id,
                'amount' => $amount,
                'type' => 'issued',
                'description' => "Credit issued: {$reason}",
            ]);

            return $credit;
        });
    }

    /**
     * Get available credit balance for a tenant.
     */
    public function getAvailableBalance(string $tenantId): float
    {
        return (float) BillingCredit::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->sum('balance');
    }

    /**
     * Validate and apply a coupon/discount code to an invoice.
     *
     * @param BillingInvoice $invoice
     * @param string $discountCode
     * @return array{success: bool, discount_amount: float, message: string}
     */
    public function applyDiscount(BillingInvoice $invoice, string $discountCode): array
    {
        $discount = BillingDiscount::where('code', strtoupper(trim($discountCode)))->first();

        if (! $discount || ! $discount->isValid()) {
            return [
                'success' => false,
                'discount_amount' => 0.00,
                'message' => 'Invalid or expired promotional code.',
            ];
        }

        $subtotal = (float) $invoice->subtotal;
        $discountAmount = 0.00;

        if ($discount->discount_type === 'percentage') {
            $discountAmount = round($subtotal * ((float) $discount->value / 100), 2);
        } else {
            $discountAmount = min($subtotal, (float) $discount->value);
        }

        return DB::transaction(function () use ($invoice, $discount, $discountAmount) {
            $discount->increment('redeemed_count');

            BillingDiscountRedemption::create([
                'id' => (string) Str::uuid(),
                'discount_id' => $discount->id,
                'tenant_id' => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'amount_discounted' => $discountAmount,
                'redeemed_at' => now(),
            ]);

            $invoice->discount_amount = $discountAmount;
            $invoice->total_amount = max(0.00, round((float) $invoice->subtotal - $discountAmount + (float) $invoice->tax_amount, 2));
            $invoice->balance_due = max(0.00, round((float) $invoice->total_amount - (float) $invoice->amount_paid, 2));
            $invoice->save();

            return [
                'success' => true,
                'discount_amount' => $discountAmount,
                'message' => "Discount '{$discount->name}' applied successfully.",
            ];
        });
    }
}
