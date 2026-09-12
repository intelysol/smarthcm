<?php

namespace Tests\Feature\Career;

use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\CareerSkillCategory;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CareerPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerSkillMasterAndProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CareerPermissionSeeder::class);
    }

    public function test_admin_can_manage_skills_and_employee_can_declare_skill(): void
    {
        $tenant = Tenant::factory()->create();
        $adminUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $empUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $empUser->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-SK-01',
            'employee_code' => 'EMP-SK-01',
            'first_name' => 'Adnan',
            'last_name' => 'Siddiqui',
            'joining_date' => now()->toDateString(),
        ]);

        $this->grant($adminUser, ['hcm.career.skills.manage', 'hcm.career.skills.view', 'hcm.career.view']);

        // 1. Admin creates category & skill
        $catResponse = $this->actingAs($adminUser)->postJson('/api/v1/hcm/career/categories', [
            'code' => 'CAT-CLOUD',
            'name' => 'Cloud Technologies',
            'description' => 'AWS, Azure, and GCP competencies',
        ]);
        $catResponse->assertStatus(201);
        $catId = $catResponse->json('data.id');

        $skillResponse = $this->actingAs($adminUser)->postJson('/api/v1/hcm/career/skills', [
            'code' => 'SK-AWS-ARCH',
            'name' => 'AWS Solutions Architecture',
            'category_id' => $catId,
            'skill_type' => 'technical',
            'assessment_interval_months' => 12,
        ]);
        $skillResponse->assertStatus(201);
        $skillId = $skillResponse->json('data.id');

        // 2. Employee self-declares skill with evidence
        $declareResponse = $this->actingAs($empUser)->postJson('/api/v1/hcm/me/skills', [
            'skill_id' => $skillId,
            'current_level' => 3,
            'target_level' => 5,
            'notes' => 'Completed AWS Certified Solutions Architect Associate exam.',
        ]);
        $declareResponse->assertStatus(201);
        $empSkillId = $declareResponse->json('data.id');

        $this->assertDatabaseHas('employee_skills', [
            'id' => $empSkillId,
            'employee_id' => $employee->id,
            'current_level' => 3,
            'verification_status' => 'self_declared',
        ]);

        // 3. Employee attaches evidence
        $evidenceResponse = $this->actingAs($empUser)->postJson("/api/v1/hcm/me/skills/{$empSkillId}/evidence", [
            'evidence_type' => 'certification',
            'title' => 'AWS Certified Solutions Architect Certificate',
            'description' => 'Credential ID: AWS-99283719',
        ]);
        $evidenceResponse->assertStatus(201);

        $this->assertDatabaseHas('employee_skill_evidence', [
            'employee_skill_id' => $empSkillId,
            'title' => 'AWS Certified Solutions Architect Certificate',
        ]);
    }

    public function test_manager_can_verify_employee_skill(): void
    {
        $tenant = Tenant::factory()->create();
        $mgrUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $manager = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $mgrUser->id,
            'company_id' => $company->id,
            'employee_number' => 'MGR-SK-01',
            'employee_code' => 'MGR-SK-01',
            'first_name' => 'Imran',
            'last_name' => 'Khan',
            'joining_date' => now()->toDateString(),
        ]);

        $subordinate = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'reporting_manager_id' => $manager->id,
            'employee_number' => 'EMP-SK-02',
            'employee_code' => 'EMP-SK-02',
            'first_name' => 'Waqar',
            'last_name' => 'Younis',
            'joining_date' => now()->toDateString(),
        ]);

        $skill = CareerSkill::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SK-DB-OPT',
            'name' => 'High-Performance SQL Optimization',
            'skill_type' => 'technical',
        ]);

        $empSkill = EmployeeSkill::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $subordinate->id,
            'skill_id' => $skill->id,
            'current_level' => 4,
            'verification_status' => 'self_declared',
        ]);

        // Manager verifies subordinate's skill
        $response = $this->actingAs($mgrUser)->postJson("/api/v1/hcm/manager/career/skills/{$empSkill->id}/verify");
        $response->assertStatus(200);

        $this->assertDatabaseHas('employee_skills', [
            'id' => $empSkill->id,
            'verification_status' => 'manager_verified',
            'verified_by' => $manager->id,
        ]);
    }

    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'career'], ['label' => 'Career']);
        foreach ($permissions as $p) {
            $perm = Permission::query()->firstOrCreate(['name' => $p], ['permission_group_id' => $group->id, 'label' => $p]);
            $user->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }
}
