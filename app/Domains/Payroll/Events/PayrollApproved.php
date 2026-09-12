<?php

namespace App\Domains\Payroll\Events;

use App\Domains\Payroll\Models\PayrollRun;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayrollApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public PayrollRun $run) {}
}
