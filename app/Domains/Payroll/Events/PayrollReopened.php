<?php

namespace App\Domains\Payroll\Events;

use App\Domains\Payroll\Models\PayrollPeriod;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayrollReopened
{
    use Dispatchable, SerializesModels;

    public function __construct(public PayrollPeriod $period) {}
}
