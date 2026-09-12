<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Contracts\PayrollPaymentProviderInterface;
use App\Domains\Payroll\Enums\PaymentBatchStatus;
use App\Domains\Payroll\Models\PayrollPaymentBatch;
use App\Domains\Payroll\Models\PayrollPaymentLine;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Providers\StandardCsvPaymentProvider;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PaymentBatchService
{
    public function __construct(
        protected ?PayrollPaymentProviderInterface $paymentProvider = null
    ) {
        $this->paymentProvider = $paymentProvider ?? new StandardCsvPaymentProvider();
    }

    public function createPaymentBatch(PayrollRun $run, array $data = [], ?User $actor = null): PayrollPaymentBatch
    {
        $tenantId = $run->tenant_id;
        $run->loadMissing(['calculationSnapshots.employee']);

        $batchNumber = 'BATCH-' . strtoupper(uniqid());

        /** @var PayrollPaymentBatch $batch */
        $batch = PayrollPaymentBatch::query()->create([
            'tenant_id' => $tenantId,
            'payroll_run_id' => $run->id,
            'batch_number' => $batchNumber,
            'payment_method' => $data['payment_method'] ?? 'bank_transfer',
            'bank_format' => $data['bank_format'] ?? 'standard_csv',
            'currency' => $run->currency,
            'total_records' => $run->calculationSnapshots->count(),
            'total_amount' => $run->net_total,
            'status' => PaymentBatchStatus::DRAFT->value,
            'created_by' => $actor?->id,
            'updated_by' => $actor?->id,
        ]);

        foreach ($run->calculationSnapshots as $snapshot) {
            $emp = $snapshot->employee;
            $netPay = (float) $snapshot->net_pay;

            if ($netPay <= 0) {
                continue;
            }

            $batch->lines()->create([
                'tenant_id' => $tenantId,
                'employee_id' => $emp->id,
                'bank_name' => 'Corporate Direct Bank',
                'routing_number' => '121000358',
                'masked_account_number' => '**** **** ' . rand(1000, 9999),
                'account_holder_name' => $emp->fullName(),
                'amount' => $netPay,
                'currency' => $run->currency,
                'payment_status' => 'pending',
                'transaction_reference' => "TXN-{$batch->id}-{$emp->id}",
            ]);
        }

        return $batch->fresh('lines');
    }

    public function generateExportFile(PayrollPaymentBatch $batch): string
    {
        $content = $this->paymentProvider->generatePaymentFile($batch);

        $batch->update([
            'status' => PaymentBatchStatus::GENERATED->value,
            'generated_at' => now(),
        ]);

        return $content;
    }

    public function submitPaymentBatch(PayrollPaymentBatch $batch, ?User $actor = null): PayrollPaymentBatch
    {
        $batch->update([
            'status' => PaymentBatchStatus::SUBMITTED->value,
            'submitted_at' => now(),
            'updated_by' => $actor?->id,
        ]);

        return $batch;
    }

    public function markAsPaid(PayrollPaymentBatch $batch, ?User $actor = null): PayrollPaymentBatch
    {
        $batch->update([
            'status' => PaymentBatchStatus::PAID->value,
            'paid_at' => now(),
            'updated_by' => $actor?->id,
        ]);

        $batch->lines()->update(['payment_status' => 'processed']);

        return $batch;
    }
}
