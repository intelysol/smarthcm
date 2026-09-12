<?php

namespace App\Domains\Payroll\Jobs;

use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\PayrollRunService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculatePayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PayrollRun $run) {}

    public function handle(PayrollRunService $runService): void
    {
        $runService->calculateRun($this->run);
    }
}
