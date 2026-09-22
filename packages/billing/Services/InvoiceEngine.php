<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services;

use Carbon\Carbon;
use Flow\Packages\Billing\Domain\Enums\InvoiceStatus;
use Flow\Packages\Billing\Domain\Models\BillingCredit;
use Flow\Packages\Billing\Domain\Models\BillingCreditTransaction;
use Flow\Packages\Billing\Domain\Models\BillingInvoice;
use Flow\Packages\Billing\Domain\Models\BillingInvoiceItem;
use Flow\Packages\Billing\Domain\Models\BillingSubscription;
use Flow\Packages\Billing\Domain\Models\BillingTaxRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceEngine
{
    public function __construct(
        protected PricingEngine $pricingEngine,
        protected UsageMeteringService $usageMeteringService
    ) {}

    /**
     * Generate a new commercial invoice for a subscription renewal or plan change.
     */
    public function generateSubscriptionInvoice(
        BillingSubscription $subscription,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null
    ): BillingInvoice {
        $now = Carbon::now();
        $start = $periodStart ?? $subscription->current_cycle_start ?? $now;
        $end = $periodEnd ?? $subscription->current_cycle_end ?? (clone $now)->addMonth();

        $plan = $subscription->plan;

        // Calculate line items
        $calc = $this->pricingEngine->calculatePlanTotal($plan, ['seat' => $subscription->quantity]);

        return DB::transaction(function () use ($subscription, $plan, $start, $end, $now, $calc) {
            $invoiceNumber = 'INV-' . strtoupper(Carbon::now()->format('Ym')) . '-' . strtoupper(Str::random(6));

            $subtotal = $calc['subtotal'];
            $discountAmount = 0.00;

            // Calculate Tax
            $taxAmount = $this->calculateTax($subscription->tenant_id, $subtotal);
            $totalAmount = round($subtotal - $discountAmount + $taxAmount, 2);

            $invoice = BillingInvoice::create([
                'id' => (string) Str::uuid(),
                'invoice_number' => $invoiceNumber,
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'billing_period_start' => $start,
                'billing_period_end' => $end,
                'issue_date' => $now->toDateString(),
                'due_date' => (clone $now)->addDays(14)->toDateString(),
                'currency' => $subscription->currency,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'credit_amount' => 0.00,
                'total_amount' => $totalAmount,
                'amount_paid' => 0.00,
                'balance_due' => $totalAmount,
                'status' => InvoiceStatus::ISSUED,
            ]);

            // Create preserved historical line items
            foreach ($calc['items'] as $item) {
                BillingInvoiceItem::create([
                    'id' => (string) Str::uuid(),
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_name' => $item['component_key'] === 'base_platform' ? 'plan' : 'seat',
                    'unit_price' => $item['unit_price'],
                    'amount' => $item['amount'],
                    'pricing_source' => 'plan_config',
                    'metadata' => $item['breakdown'] ?? [],
                ]);
            }

            // Automatically apply any available tenant credit balance
            $this->applyAvailableCredits($invoice);

            return $invoice;
        });
    }

    /**
     * Calculate tax rate for tenant jurisdiction.
     */
    public function calculateTax(string $tenantId, float $taxableSubtotal): float
    {
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        $countryCode = $tenant->country_code ?? 'US';

        $taxRecord = BillingTaxRecord::where('country_code', $countryCode)
            ->where('is_active', true)
            ->first();

        if (! $taxRecord) {
            return 0.00;
        }

        $rate = (float) $taxRecord->rate_percent;

        return round($taxableSubtotal * ($rate / 100), 2);
    }

    /**
     * Apply available tenant credit balance against invoice.
     */
    public function applyAvailableCredits(BillingInvoice $invoice): float
    {
        if ($invoice->balance_due <= 0) {
            return 0.00;
        }

        $credits = BillingCredit::where('tenant_id', $invoice->tenant_id)
            ->where('status', 'active')
            ->where('balance', '>', 0)
            ->orderBy('created_at')
            ->get();

        $totalApplied = 0.00;

        foreach ($credits as $credit) {
            if ($invoice->balance_due <= 0) {
                break;
            }

            $applyAmount = min((float) $credit->balance, (float) $invoice->balance_due);
            if ($applyAmount <= 0) {
                continue;
            }

            $credit->balance = round($credit->balance - $applyAmount, 2);
            if ($credit->balance <= 0) {
                $credit->status = 'depleted';
            }
            $credit->save();

            BillingCreditTransaction::create([
                'id' => (string) Str::uuid(),
                'credit_id' => $credit->id,
                'invoice_id' => $invoice->id,
                'amount' => $applyAmount,
                'type' => 'applied',
                'description' => "Applied credit to invoice {$invoice->invoice_number}",
            ]);

            $invoice->credit_amount = round($invoice->credit_amount + $applyAmount, 2);
            $invoice->amount_paid = round($invoice->amount_paid + $applyAmount, 2);
            $invoice->balance_due = round($invoice->balance_due - $applyAmount, 2);

            $totalApplied += $applyAmount;
        }

        if ($invoice->balance_due <= 0) {
            $invoice->status = InvoiceStatus::PAID;
        } elseif ($invoice->amount_paid > 0) {
            $invoice->status = InvoiceStatus::PARTIALLY_PAID;
        }

        $invoice->save();

        return $totalApplied;
    }
}
