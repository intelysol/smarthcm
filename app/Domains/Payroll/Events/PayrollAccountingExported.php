<?php

namespace App\Domains\Payroll\Events;

use App\Domains\Payroll\Models\PayrollAccountingExport;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayrollAccountingExported
{
    use Dispatchable, SerializesModels;

    public function __construct(public PayrollAccountingExport $export) {}
}
