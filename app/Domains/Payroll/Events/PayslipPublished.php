<?php

namespace App\Domains\Payroll\Events;

use App\Domains\Payroll\Models\PayrollPayslip;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayslipPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public PayrollPayslip $payslip) {}
}
