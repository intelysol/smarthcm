<?php

namespace App\Domains\Learning\Jobs;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Services\LearningRequirementService;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AssignLearningRequirementsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly ?string $employeeId = null,
        public readonly string $trigger = 'hired'
    ) {}

    public function handle(LearningRequirementService $requirementService, ?TenantContext $tenantContext = null): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) return;
        if ($tenantContext && ! $tenantContext->id()) {
            $tenantContext->set($tenant);
        }

        if ($this->employeeId) {
            $employee = Employee::query()->find($this->employeeId);
            if ($employee) {
                $requirementService->evaluateRequirementsForEmployee($employee, $this->trigger);
            }
        } else {
            $requirementService->checkDeadlines($this->tenantId);
        }
    }
}
