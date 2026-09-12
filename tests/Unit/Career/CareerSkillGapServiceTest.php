<?php

namespace Tests\Unit\Career;

use App\Domains\Career\Models\CareerJobSkillRequirement;
use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Career\Services\CareerSkillGapService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Job;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerSkillGapServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_gap_formula_and_no_negative_deficiency(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-GAP-1',
            'employee_code' => 'EMP-GAP-1',
            'first_name' => 'Sara',
            'last_name' => 'Ahmed',
            'joining_date' => now()->toDateString(),
        ]);

        $job = Job::query()->create([
            'tenant_id' => $tenant->id,
            'job_code' => 'JOB-SR-DEV',
            'title' => 'Senior Developer',
        ]);

        $skill1 = CareerSkill::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SK-LARAVEL',
            'name' => 'Laravel PHP Framework',
            'skill_type' => 'technical',
        ]);

        $skill2 = CareerSkill::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SK-DOCKER',
            'name' => 'Docker & Containers',
            'skill_type' => 'technical',
        ]);

        // Job requires Laravel Level 4 (High) and Docker Level 2 (Medium)
        CareerJobSkillRequirement::query()->create([
            'tenant_id' => $tenant->id,
            'job_id' => $job->id,
            'skill_id' => $skill1->id,
            'required_level' => 4,
            'importance' => 'high',
        ]);

        CareerJobSkillRequirement::query()->create([
            'tenant_id' => $tenant->id,
            'job_id' => $job->id,
            'skill_id' => $skill2->id,
            'required_level' => 2,
            'importance' => 'medium',
        ]);

        // Employee has Laravel Level 2 (Gap of 2) and Docker Level 5 (Surplus of 3, gap must be 0)
        EmployeeSkill::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'skill_id' => $skill1->id,
            'current_level' => 2,
        ]);

        EmployeeSkill::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'skill_id' => $skill2->id,
            'current_level' => 5,
        ]);

        $service = app(CareerSkillGapService::class);
        $gaps = $service->analyzeGapsForTargetJob($employee, $job);

        // Only 1 gap detected (Laravel: 4 - 2 = 2)
        $this->assertCount(1, $gaps);
        $this->assertEquals(2, $gaps[0]->gap);
        $this->assertEquals('critical', $gaps[0]->priority);

        // Calculate skill match %
        $match = $service->calculateSkillMatchPercentage($employee, $job);
        // Laravel: req 4 * weight 2 = 8, earned 2 * 2 = 4. Docker: req 2 * weight 1 = 2, earned 2 * 1 = 2.
        // Total req = 10, Total earned = 6 -> 60%
        $this->assertEquals(60.0, $match);
    }
}
