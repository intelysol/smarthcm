<?php

namespace App\Domains\Payroll\Contracts;

use App\Domains\Payroll\Models\PayrollPaymentBatch;

interface PayrollPaymentProviderInterface
{
    /**
     * Generate bank export file string / payload from payment batch.
     */
    public function generatePaymentFile(PayrollPaymentBatch $batch): string;
}
