<?php

namespace App\Domains\Compliance\Jobs;

use App\Domains\Compliance\Services\ComplianceEvaluationService;
use App\Domains\Employee\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluateEmployeeComplianceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $employeeId = null,
        public ?string $tenantId = null
    ) {}

    public function handle(ComplianceEvaluationService $service): void
    {
        if ($this->employeeId) {
            $service->evaluateEmployee($this->employeeId);
        } elseif ($this->tenantId) {
            $employees = Employee::where('tenant_id', $this->tenantId)->pluck('id');
            foreach ($employees as $empId) {
                $service->evaluateEmployee($empId);
            }
        }
    }
}
