<?php

namespace App\Domains\Career\Jobs;

use App\Domains\Career\Services\CareerSkillGapService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Job;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateEmployeeSkillGapsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $employeeId,
        public readonly string $targetJobId
    ) {}

    public function handle(CareerSkillGapService $gapService, ?TenantContext $tenantContext = null): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) return;
        if ($tenantContext && ! $tenantContext->id()) {
            $tenantContext->set($tenant);
        }

        $employee = Employee::query()->find($this->employeeId);
        $job = Job::query()->find($this->targetJobId);

        if ($employee && $job) {
            $gapService->analyzeGapsForTargetJob($employee, $job);
        }
    }
}
