<?php

namespace App\Domains\Compliance\Jobs;

use App\Domains\Compliance\Services\ComplianceEvaluationService;
use App\Domains\Employee\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RebuildComplianceSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(ComplianceEvaluationService $service): void
    {
        $employeeIds = Employee::where('tenant_id', $this->tenantId)->pluck('id');

        foreach ($employeeIds as $empId) {
            $service->evaluateEmployee($empId);
        }
    }
}
