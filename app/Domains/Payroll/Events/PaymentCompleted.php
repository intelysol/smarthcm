<?php

namespace App\Domains\Payroll\Events;

use App\Domains\Payroll\Models\PayrollPaymentBatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public PayrollPaymentBatch $batch) {}
}
