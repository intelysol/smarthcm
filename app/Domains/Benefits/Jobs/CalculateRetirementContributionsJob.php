<?php

namespace App\Domains\Benefits\Jobs;

use App\Domains\Benefits\Models\RetirementEnrollment;
use App\Domains\Benefits\Services\RetirementContributionService;
use App\Domains\Payroll\Models\PayrollPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateRetirementContributionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public ?string $payrollPeriodId = null
    ) {}

    public function handle(RetirementContributionService $service): void
    {
        $enrollments = RetirementEnrollment::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->with(['employee', 'plan'])
            ->get();

        foreach ($enrollments as $enrollment) {
            $employee = $enrollment->employee;
            $plan = $enrollment->plan;
            if ($employee && $plan) {
                $basicSalary = 5000.00; // Base reference
                $service->calculateAndPostMonthlyContribution($employee, $plan, $basicSalary, now()->toDateString(), $this->payrollPeriodId);
            }
        }
    }
}
