<?php

namespace Tests\Unit\Career;

use App\Domains\Career\Enums\CareerReadinessLevel;
use App\Domains\Career\Models\CareerJobSkillRequirement;
use App\Domains\Career\Models\CareerPlan;
use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Career\Services\CareerReadinessEngine;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Job;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerReadinessEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_readiness_calculation_and_auditable_override(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-RDY-1',
            'employee_code' => 'EMP-RDY-1',
            'first_name' => 'Ali',
            'last_name' => 'Raza',
            'joining_date' => now()->subYears(4)->toDateString(),
        ]);

        $overridingManager = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'MGR-RDY-1',
            'employee_code' => 'MGR-RDY-1',
            'first_name' => 'Zain',
            'last_name' => 'Malik',
            'joining_date' => now()->subYears(8)->toDateString(),
        ]);

        $job = Job::query()->create([
            'tenant_id' => $tenant->id,
            'job_code' => 'JOB-LEAD',
            'title' => 'Tech Lead',
        ]);

        $skill = CareerSkill::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SK-SYS-ARCH',
            'name' => 'System Architecture',
            'skill_type' => 'technical',
        ]);

        CareerJobSkillRequirement::query()->create([
            'tenant_id' => $tenant->id,
            'job_id' => $job->id,
            'skill_id' => $skill->id,
            'required_level' => 4,
            'importance' => 'critical',
        ]);

        EmployeeSkill::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'skill_id' => $skill->id,
            'current_level' => 4, // 100% skill match
        ]);

        $engine = app(CareerReadinessEngine::class);
        $readiness = $engine->calculateReadiness($employee, $job);

        $this->assertGreaterThanOrEqual(75.0, $readiness['score']);
        $this->assertContains($readiness['level'], [CareerReadinessLevel::Ready->value, CareerReadinessLevel::ReadyNow->value]);

        // Create Plan and test manual override
        $plan = CareerPlan::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'target_job_id' => $job->id,
            'readiness_score' => $readiness['score'],
            'readiness_level' => $readiness['level'],
            'status' => 'approved',
        ]);

        $overridden = $engine->overrideReadiness(
            $plan,
            CareerReadinessLevel::ReadyNow->value,
            $overridingManager,
            'Exceptional performance on major cloud migration initiative.'
        );

        $this->assertEquals(CareerReadinessLevel::ReadyNow->value, $overridden->readiness_level);
        $this->assertEquals($readiness['score'], (float) $overridden->readiness_score); // Calculated score preserved
    }
}
