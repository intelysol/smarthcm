<?php

namespace App\Domains\Payroll\Jobs;

use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\PayrollAccountingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAccountingExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PayrollRun $run) {}

    public function handle(PayrollAccountingService $accountingService): void
    {
        $accountingService->generateAccountingExport($this->run);
    }
}
