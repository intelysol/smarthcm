<?php

namespace Tests\Unit\Career;

use App\Domains\Career\Models\CareerPath;
use App\Domains\Career\Models\CareerPathStep;
use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Career\Services\CareerEligibilityService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Job;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerEligibilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_career_path_step_eligibility_evaluation(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-ELIG-1',
            'employee_code' => 'EMP-ELIG-1',
            'first_name' => 'Fahad',
            'last_name' => 'Mustafa',
            'joining_date' => now()->subYears(4)->toDateString(),
        ]);

        $job = Job::query()->create([
            'tenant_id' => $tenant->id,
            'job_code' => 'JOB-SR-ARCH',
            'title' => 'Principal Architect',
        ]);

        $path = CareerPath::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'PATH-ENG',
            'title' => 'Engineering Progression',
        ]);

        $skill = CareerSkill::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SK-K8S',
            'name' => 'Kubernetes Orchestration',
            'skill_type' => 'technical',
        ]);

        $step = CareerPathStep::query()->create([
            'tenant_id' => $tenant->id,
            'career_path_id' => $path->id,
            'job_id' => $job->id,
            'sequence' => 3,
            'minimum_experience_years' => 3.0,
            'performance_min_rating' => 3.5,
            'required_skills' => [$skill->id => 3],
        ]);

        $service = app(CareerEligibilityService::class);

        // 1. Missing skill proficiency -> Not eligible
        $evalBefore = $service->evaluateStepEligibility($employee, $step);
        $this->assertFalse($evalBefore['eligible']);
        $this->assertNotEmpty($evalBefore['unmet_criteria']);

        // 2. Add required skill level & satisfactory performance
        \App\Domains\Performance\Models\PerformanceFinalOutcome::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => (string) \Illuminate\Support\Str::uuid(),
            'employee_id' => $employee->id,
            'final_rating' => 4.0,
            'finalized_at' => now(),
        ]);

        EmployeeSkill::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'skill_id' => $skill->id,
            'current_level' => 4,
        ]);

        $evalAfter = $service->evaluateStepEligibility($employee, $step);
        $this->assertTrue($evalAfter['eligible']);
        $this->assertEmpty($evalAfter['unmet_criteria']);
    }
}
