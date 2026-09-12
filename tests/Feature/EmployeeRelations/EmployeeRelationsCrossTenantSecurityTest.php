<?php

namespace Tests\Feature\EmployeeRelations;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Domains\EmployeeRelations\Services\EmployeeRelationCaseService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\EmployeeRelationsCaseTypeSeeder;
use Database\Seeders\EmployeeRelationsPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeRelationsCrossTenantSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EmployeeRelationsPermissionSeeder::class);
        $this->seed(EmployeeRelationsCaseTypeSeeder::class);
    }

    public function test_strict_cross_tenant_isolation(): void
    {
        $caseService = app(EmployeeRelationCaseService::class);
        $caseType = EmployeeRelationCaseType::query()->where('code', 'GRIEVANCE')->first();

        // Tenant A
        $tenantA = Tenant::factory()->create();
        $companyA = Company::factory()->create(['tenant_id' => $tenantA->id]);
        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $this->grant($userA, ['hcm.employee_relations.manage', 'hcm.employee_relations.case.view', 'hcm.employee_relations.case.edit']);

        $caseA = $caseService->createCase($tenantA->id, [
            'case_type_id' => $caseType->id,
            'title' => 'Tenant A Confidential Case',
            'summary' => 'Tenant A sensitive relations matter.',
        ], $userA);

        // Tenant B
        $tenantB = Tenant::factory()->create();
        $companyB = Company::factory()->create(['tenant_id' => $tenantB->id]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);
        $this->grant($userB, ['hcm.employee_relations.manage', 'hcm.employee_relations.case.view', 'hcm.employee_relations.case.edit']);

        // 1. User B tries to view Tenant A's case via API -> Must receive 404
        $resp = $this->actingAs($userB)->getJson("/api/v1/hcm/employee-relations/cases/{$caseA->id}");
        $resp->assertStatus(404);

        // 2. User B tries to mutate Tenant A's case -> Must receive 404/403
        $respEdit = $this->actingAs($userB)->patchJson("/api/v1/hcm/employee-relations/cases/{$caseA->id}", [
            'title' => 'Compromised Title',
        ]);
        $respEdit->assertStatus(404);

        // 3. User B case list does NOT leak Tenant A's case
        $listResp = $this->actingAs($userB)->getJson('/api/v1/hcm/employee-relations/cases');
        $listResp->assertStatus(200);
        $caseIds = collect($listResp->json('data'))->pluck('id')->all();
        $this->assertNotContains($caseA->id, $caseIds);
    }

    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'er_test'], ['label' => 'ER Test']);
        foreach ($permissions as $p) {
            $perm = Permission::query()->firstOrCreate(['name' => $p], ['permission_group_id' => $group->id, 'label' => $p]);
            $user->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }
}
