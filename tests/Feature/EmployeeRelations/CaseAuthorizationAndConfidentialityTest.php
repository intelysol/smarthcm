<?php

namespace Tests\Feature\EmployeeRelations;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeRelations\Enums\ConfidentialityLevel;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseNote;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Domains\EmployeeRelations\Services\CaseAuthorizationService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\EmployeeRelationsCaseTypeSeeder;
use Database\Seeders\EmployeeRelationsPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseAuthorizationAndConfidentialityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EmployeeRelationsPermissionSeeder::class);
        $this->seed(EmployeeRelationsCaseTypeSeeder::class);
    }

    public function test_case_level_authorization_and_confidentiality_boundaries(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $hrUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($hrUser, ['hcm.employee_relations.view', 'hcm.employee_relations.case.view', 'hcm.employee_relations.case.edit']);

        $normalEmployeeUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($normalEmployeeUser, ['hcm.employee.view']); // Normal employee permission does NOT grant case access

        $caseType = EmployeeRelationCaseType::query()->where('code', 'GRIEVANCE')->first();

        // 1. Create standard confidential case
        $case = EmployeeRelationCase::query()->create([
            'tenant_id' => $tenant->id,
            'case_number' => 'ER-2026-00001',
            'case_type_id' => $caseType->id,
            'title' => 'Confidential Grievance',
            'summary' => 'Confidential facts and sensitive statements.',
            'status' => 'submitted',
            'confidentiality_level' => ConfidentialityLevel::STANDARD_CONFIDENTIAL->value,
            'created_by' => $hrUser->id,
        ]);

        $authService = app(CaseAuthorizationService::class);

        // HR user with case.view can view
        $this->assertTrue($authService->canViewCase($hrUser, $case));

        // Normal employee with only hcm.employee.view CANNOT view case
        $this->assertFalse($authService->canViewCase($normalEmployeeUser, $case));

        // API verification: normal employee gets 404
        $resp = $this->actingAs($normalEmployeeUser)->getJson("/api/v1/hcm/employee-relations/cases/{$case->id}");
        $resp->assertStatus(404);

        // 2. Highly Confidential & Restricted Case Check (Created by third party)
        $creatorUser = User::factory()->create(['tenant_id' => $tenant->id]);

        $restrictedCase = EmployeeRelationCase::query()->create([
            'tenant_id' => $tenant->id,
            'case_number' => 'ER-2026-00002',
            'case_type_id' => $caseType->id,
            'title' => 'Executive Investigation',
            'summary' => 'Restricted executive inquiry.',
            'status' => 'submitted',
            'confidentiality_level' => ConfidentialityLevel::LEGAL_RESTRICTED->value,
            'created_by' => $creatorUser->id,
        ]);

        // HR user WITHOUT hcm.employee_relations.restricted.view cannot view restricted case
        $this->assertFalse($authService->canViewCase($hrUser, $restrictedCase));

        // Grant legal restricted permission
        $this->grant($hrUser, ['hcm.employee_relations.restricted.view']);
        $this->assertTrue($authService->canViewCase($hrUser, $restrictedCase));

        // 3. Field-Level Note Visibility Check
        $legalNote = EmployeeRelationCaseNote::query()->create([
            'tenant_id' => $tenant->id,
            'case_id' => $case->id,
            'author_id' => $creatorUser->id,
            'note_type' => 'legal_note',
            'visibility' => 'legal_only',
            'content' => 'Privileged legal attorney-client communication.',
        ]);

        $investigatorUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($investigatorUser, ['hcm.employee_relations.case.view', 'hcm.employee_relations.investigation.view']);
        $case->assignments()->create([
            'tenant_id' => $tenant->id,
            'case_id' => $case->id,
            'user_id' => $investigatorUser->id,
            'role' => 'investigator',
            'status' => 'active',
        ]);

        // Investigator cannot view legal-only notes
        $this->assertFalse($authService->canViewNote($investigatorUser, $case, $legalNote));
    }

    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'er_test'], ['label' => 'ER Test']);
        foreach ($permissions as $p) {
            $perm = Permission::query()->firstOrCreate(['name' => $p], ['permission_group_id' => $group->id, 'label' => $p]);
            $user->permissions()->syncWithoutDetaching([$perm->id]);
        }
        \Illuminate\Support\Facades\Cache::flush();
    }
}
