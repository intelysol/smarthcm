<?php

namespace Tests\Feature\EmployeeRelations;

use App\Domains\EmployeeRelations\Enums\CaseStatus;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Domains\EmployeeRelations\Services\EmployeeRelationCaseService;
use App\Domains\EmployeeRelations\Services\RetentionAndLegalHoldService;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\EmployeeRelationsCaseTypeSeeder;
use Database\Seeders\EmployeeRelationsPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RetentionAndLegalHoldTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EmployeeRelationsPermissionSeeder::class);
        $this->seed(EmployeeRelationsCaseTypeSeeder::class);
    }

    public function test_legal_hold_blocks_disposal_and_release_allows_retention_disposal(): void
    {
        $tenant = Tenant::factory()->create();
        $legalUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($legalUser, [
            'hcm.employee_relations.manage',
            'hcm.employee_relations.legal_hold.manage',
            'hcm.employee_relations.retention.manage',
        ]);

        $caseService = app(EmployeeRelationCaseService::class);
        $retentionService = app(RetentionAndLegalHoldService::class);

        $caseType = EmployeeRelationCaseType::query()->where('code', 'HARASSMENT')->first();
        $case = $caseService->createCase($tenant->id, [
            'case_type_id' => $caseType->id,
            'title' => 'Harassment Investigation with Pending Litigation',
            'summary' => 'External counsel requested preservation of all records.',
        ], $legalUser);

        // 1. Place Legal Hold
        $hold = $retentionService->placeLegalHold($case, $legalUser, 'Litigation Hold issued by Legal Counsel.');
        $this->assertEquals('active', $hold->status);
        $this->assertTrue($case->fresh()->is_locked);
        $this->assertTrue($case->fresh()->hasActiveLegalHold());

        // 2. Attempt disposal while legal hold active -> must throw ValidationException
        $exceptionThrown = false;
        try {
            $retentionService->disposeCase($case->fresh(), $legalUser, 'Routine retention cleanup.');
        } catch (ValidationException $e) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown, 'Disposal must be prevented while legal hold is active.');

        // 3. Release Legal Hold
        $releasedHold = $retentionService->releaseLegalHold($hold, $legalUser, 'Litigation resolved and settled.');
        $this->assertEquals('released', $releasedHold->status);
        $this->assertFalse($case->fresh()->hasActiveLegalHold());
        $this->assertFalse($case->fresh()->is_locked);

        // 4. Controlled Disposal succeeds after legal hold release
        $retentionService->disposeCase($case->fresh(), $legalUser, 'Retention period concluded.');
        $this->assertSoftDeleted('employee_relation_cases', ['id' => $case->id]);
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
