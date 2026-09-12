<?php

namespace Tests\Feature\WorkforceOptimization;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceOptimization\Services\SkillsOptimizationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SkillsOptimizationAndSpofTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_detects_single_point_of_failure_spof_skills(): void
    {
        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $this->tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'name' => 'Data BU', 'code' => 'DATA']);
        $dept = Department::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'department_name' => 'Data Science', 'department_code' => 'DS-01', 'business_unit_id' => $bu->id]);

        $emp1 = Employee::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_number' => 'EMP-000101',
            'employee_code' => 'E-101',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        DB::table('employee_profile_skills')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'employee_id' => $emp1->id,
            'skill_name' => 'Distributed Quantum Computing',
            'skill_level' => 'EXPERT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $skillsService = app(SkillsOptimizationService::class);
        $spofs = $skillsService->detectSinglePointsOfFailure($this->tenant->id);

        $this->assertNotEmpty($spofs);
        $this->assertEquals('SKILLS', $spofs[0]->category);
        $this->assertStringContainsString('Distributed Quantum Computing', $spofs[0]->title);

        $this->assertDatabaseHas('hcm_workforce_optimization_opportunities', [
            'tenant_id' => $this->tenant->id,
            'category' => 'SKILLS',
            'role_or_skill' => 'Distributed Quantum Computing',
        ]);
    }
}
