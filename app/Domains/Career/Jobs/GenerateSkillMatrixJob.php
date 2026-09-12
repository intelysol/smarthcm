<?php

namespace App\Domains\Career\Jobs;

use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class GenerateSkillMatrixJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $tenantId) {}

    public function handle(?TenantContext $tenantContext = null): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) return;
        if ($tenantContext && ! $tenantContext->id()) {
            $tenantContext->set($tenant);
        }

        $skills = CareerSkill::query()->where('tenant_id', $this->tenantId)->get(['id', 'code', 'name', 'skill_type']);
        $employeeSkills = EmployeeSkill::query()
            ->where('tenant_id', $this->tenantId)
            ->with(['employee:id,first_name,last_name,employee_number,department_id', 'skill:id,name,code'])
            ->get();

        $matrix = [
            'generated_at' => now()->toIso8601String(),
            'skills_count' => $skills->count(),
            'records_count' => $employeeSkills->count(),
        ];

        Cache::put("tenant:{$this->tenantId}:skill_matrix_summary", $matrix, 3600);
    }
}
