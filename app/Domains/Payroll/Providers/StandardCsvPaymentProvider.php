<?php

namespace App\Domains\Payroll\Providers;

use App\Domains\Payroll\Contracts\PayrollPaymentProviderInterface;
use App\Domains\Payroll\Models\PayrollPaymentBatch;

class StandardCsvPaymentProvider implements PayrollPaymentProviderInterface
{
    public function generatePaymentFile(PayrollPaymentBatch $batch): string
    {
        $batch->loadMissing(['lines.employee']);

        $output = fopen('php://temp', 'r+');
        fputcsv($output, [
            'Batch Number',
            'Employee Number',
            'Employee Name',
            'Bank Name',
            'Routing Number',
            'Account Number',
            'Amount',
            'Currency',
            'Payment Reference',
        ]);

        foreach ($batch->lines as $line) {
            $emp = $line->employee;
            fputcsv($output, [
                $batch->batch_number,
                $emp?->employee_number ?? $emp?->employee_code,
                $line->account_holder_name,
                $line->bank_name ?? 'Primary Bank',
                $line->routing_number ?? '',
                $line->masked_account_number,
                number_format((float) $line->amount, 2, '.', ''),
                $line->currency,
                $line->transaction_reference ?? "PAY-{$line->id}",
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv ?: '';
    }
}
