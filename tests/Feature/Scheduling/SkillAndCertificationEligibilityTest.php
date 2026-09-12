<?php

namespace Tests\Feature\Scheduling;

use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\Scheduling\ScheduleEligibilityService;
use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\CareerSkillCategory;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningRequirement;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillAndCertificationEligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_skill_and_proficiency_level_enforcement(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $category = CareerSkillCategory::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'TECH',
            'name' => 'Technical Skills',
            'is_active' => true,
        ]);

        $skill = CareerSkill::query()->create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'code' => 'K8S',
            'name' => 'Kubernetes Administration',
            'skill_type' => 'technical',
            'status' => 'active',
        ]);

        $qualifiedEmp = $this->createEmployee($tenant->id, $company->id, ['first_name' => 'Qualified']);
        $underQualifiedEmp = $this->createEmployee($tenant->id, $company->id, ['first_name' => 'Junior']);
        $unqualifiedEmp = $this->createEmployee($tenant->id, $company->id, ['first_name' => 'NoSkill']);

        // Qualified: level 3 (Senior)
        EmployeeSkill::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $qualifiedEmp->id,
            'skill_id' => $skill->id,
            'current_level' => 3,
            'target_level' => 4,
            'verification_status' => 'verified',
        ]);

        // Under-qualified: level 1 (Beginner)
        EmployeeSkill::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $underQualifiedEmp->id,
            'skill_id' => $skill->id,
            'current_level' => 1,
            'target_level' => 2,
            'verification_status' => 'verified',
        ]);

        $shift = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'DEV-OPS',
            'name' => 'DevOps Shift',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $eligibilityService = app(ScheduleEligibilityService::class);

        // 1. Qualified employee (requires level 2) -> eligible
        $resQualified = $eligibilityService->checkEligibility(
            employee: $qualifiedEmp,
            date: '2026-10-12',
            shift: $shift,
            requiredSkillId: $skill->id,
            minSkillProficiency: 2
        );
        $this->assertTrue($resQualified['is_eligible']);
        $this->assertTrue($resQualified['qualification_details']['skill_matched']);

        // 2. Under-qualified employee (level 1 < 2) -> hard violation
        $resUnder = $eligibilityService->checkEligibility(
            employee: $underQualifiedEmp,
            date: '2026-10-12',
            shift: $shift,
            requiredSkillId: $skill->id,
            minSkillProficiency: 2
        );
        $this->assertFalse($resUnder['is_eligible']);
        $this->assertStringContainsString('proficiency (1) is below required level (2)', $resUnder['hard_violations'][0]);

        // 3. Unqualified employee (no skill record) -> hard violation
        $resUnqualified = $eligibilityService->checkEligibility(
            employee: $unqualifiedEmp,
            date: '2026-10-12',
            shift: $shift,
            requiredSkillId: $skill->id,
            minSkillProficiency: 2
        );
        $this->assertFalse($resUnqualified['is_eligible']);
        $this->assertStringContainsString('does not possess required skill', $resUnqualified['hard_violations'][0]);
    }

    public function test_expired_mandatory_compliance_certification_blocks_eligibility(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $course = \App\Domains\Learning\Models\LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SAFE-101',
            'title' => 'Electrical Safety Course',
        ]);

        LearningRequirement::query()->create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'target_type' => 'individual',
            'target_id' => $employee->id,
            'title' => 'Electrical Safety License',
            'status' => 'expired',
            'due_date' => '2026-09-01',
        ]);

        $shift = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'ELEC',
            'name' => 'Electrician Shift',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $eligibilityService = app(ScheduleEligibilityService::class);
        $res = $eligibilityService->checkEligibility($employee, '2026-10-12', $shift);

        $this->assertFalse($res['is_eligible']);
        $this->assertStringContainsString('mandatory certifications or compliance requirements that are expired', $res['hard_violations'][0]);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Tariq',
            'last_name' => 'Malik',
            'official_email' => 'tariq.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
