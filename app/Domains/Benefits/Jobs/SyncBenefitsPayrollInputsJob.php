<?php

namespace App\Domains\Benefits\Jobs;

use App\Domains\Benefits\Services\BenefitsPayrollIntegrationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncBenefitsPayrollInputsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public PayrollPeriod $period
    ) {}

    public function handle(BenefitsPayrollIntegrationService $service): void
    {
        $employees = Employee::where('tenant_id', $this->period->tenant_id)->get();
        foreach ($employees as $employee) {
            $service->syncPayrollInputsForPeriod($this->period, $employee);
        }
    }
}
