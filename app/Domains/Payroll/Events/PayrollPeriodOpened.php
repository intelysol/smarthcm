<?php

namespace App\Domains\Payroll\Events;

use App\Domains\Payroll\Models\PayrollPeriod;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayrollPeriodOpened
{
    use Dispatchable, SerializesModels;

    public function __construct(public PayrollPeriod $period) {}
}
