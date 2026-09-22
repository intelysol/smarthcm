<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services;

use Carbon\Carbon;
use Flow\Packages\Billing\Domain\Enums\InvoiceStatus;
use Flow\Packages\Billing\Domain\Enums\PaymentStatus;
use Flow\Packages\Billing\Domain\Models\BillingInvoice;
use Flow\Packages\Billing\Domain\Models\BillingPayment;
use Flow\Packages\Billing\Domain\Models\BillingReconciliationRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingReconciliationService
{
    /**
     * Run automated reconciliation audit for a specific date.
     *
     * @param Carbon|null $auditDate
     * @return array{total_audited: int, discrepancies_found: int, records: array}
     */
    public function auditDaily(?Carbon $auditDate = null): array
    {
        $date = $auditDate ?? Carbon::today();
        $dateString = $date->toDateString();

        $invoices = BillingInvoice::whereDate('created_at', '<=', $date)
            ->whereIn('status', [InvoiceStatus::PAID->value, InvoiceStatus::PARTIALLY_PAID->value])
            ->get();

        $discrepancies = [];

        foreach ($invoices as $invoice) {
            $sumPayments = (float) BillingPayment::where('invoice_id', $invoice->id)
                ->where('status', PaymentStatus::SUCCEEDED->value)
                ->sum('amount');

            $expectedPaid = (float) $invoice->amount_paid;

            if (abs($sumPayments - $expectedPaid) > 0.01) {
                // Check if already logged today
                $exists = BillingReconciliationRecord::where('reconciliation_date', $dateString)
                    ->where('invoice_id', $invoice->id)
                    ->exists();

                if (! $exists) {
                    $discrepancyType = $sumPayments === 0.0 ? 'missing_payment' : 'amount_mismatch';
                    $diff = abs($sumPayments - $expectedPaid);

                    $rec = BillingReconciliationRecord::create([
                        'id' => (string) Str::uuid(),
                        'reconciliation_date' => $dateString,
                        'invoice_id' => $invoice->id,
                        'payment_id' => null,
                        'status' => 'discrepancy',
                        'discrepancy_type' => $discrepancyType,
                        'discrepancy_amount' => $diff,
                        'notes' => "Invoice {$invoice->invoice_number} recorded paid: {$expectedPaid}, but succeeded payments sum: {$sumPayments}.",
                    ]);

                    $discrepancies[] = $rec;
                }
            }
        }

        // Check for orphan payments
        $orphanPayments = BillingPayment::whereDate('created_at', '<=', $date)
            ->where('status', PaymentStatus::SUCCEEDED->value)
            ->whereDoesntHave('invoice')
            ->get();

        foreach ($orphanPayments as $payment) {
            $exists = BillingReconciliationRecord::where('reconciliation_date', $dateString)
                ->where('payment_id', $payment->id)
                ->exists();

            if (! $exists) {
                $rec = BillingReconciliationRecord::create([
                    'id' => (string) Str::uuid(),
                    'reconciliation_date' => $dateString,
                    'invoice_id' => null,
                    'payment_id' => $payment->id,
                    'status' => 'discrepancy',
                    'discrepancy_type' => 'orphan_transaction',
                    'discrepancy_amount' => (float) $payment->amount,
                    'notes' => "Payment {$payment->payment_number} has no associated invoice.",
                ]);

                $discrepancies[] = $rec;
            }
        }

        return [
            'total_audited' => $invoices->count(),
            'discrepancies_found' => count($discrepancies),
            'records' => $discrepancies,
        ];
    }

    /**
     * Resolve a discrepancy record with actor and reason notes.
     */
    public function resolveDiscrepancy(
        BillingReconciliationRecord $record,
        int $resolverUserId,
        string $resolutionNotes
    ): BillingReconciliationRecord {
        $record->update([
            'status' => 'resolved',
            'notes' => trim($record->notes . " | RESOLUTION: " . $resolutionNotes),
            'resolved_at' => now(),
            'resolved_by' => $resolverUserId,
        ]);

        return $record;
    }
}
