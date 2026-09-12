<?php

namespace Tests\Feature\EmployeeRelations;

use App\Domains\EmployeeRelations\Enums\CasePriority;
use App\Domains\EmployeeRelations\Enums\CaseSeverity;
use App\Domains\EmployeeRelations\Enums\CaseStatus;
use App\Domains\EmployeeRelations\Jobs\EvaluateCaseSlaJob;
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
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CaseLifecycleAndTriageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EmployeeRelationsPermissionSeeder::class);
        $this->seed(EmployeeRelationsCaseTypeSeeder::class);
    }

    public function test_case_lifecycle_triage_conflict_checks_and_sla(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $hrAdmin = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($hrAdmin, ['hcm.employee_relations.manage', 'hcm.employee_relations.case.view', 'hcm.employee_relations.case.edit', 'hcm.employee_relations.case.assign', 'hcm.employee_relations.case.close']);

        $caseService = app(EmployeeRelationCaseService::class);
        $caseType = EmployeeRelationCaseType::query()->where('code', 'DISCIPLINARY')->first();

        // 1. Create Case
        $case = $caseService->createCase($tenant->id, [
            'case_type_id' => $caseType->id,
            'title' => 'Disciplinary Inquiry',
            'summary' => 'Repeated unauthorized access to financial records.',
            'priority' => CasePriority::HIGH->value,
            'severity' => CaseSeverity::SERIOUS->value,
        ], $hrAdmin);

        $this->assertEquals(CaseStatus::SUBMITTED->value, $case->status);
        $this->assertStringStartsWith('ER-' . date('Y'), $case->case_number);

        // 2. Triage
        $triage = $caseService->triageCase($case, [
            'recommended_priority' => CasePriority::URGENT->value,
            'recommended_severity' => CaseSeverity::CRITICAL->value,
            'requires_investigation' => true,
            'triage_notes' => 'Escalated to urgent following initial review.',
        ], $hrAdmin);

        $this->assertEquals(CaseStatus::TRIAGE->value, $case->fresh()->status);
        $this->assertEquals(CasePriority::URGENT->value, $case->fresh()->priority);

        // 3. Conflict of interest declaration
        $investigatorUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $case->conflicts()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $investigatorUser->id,
            'declaration' => 'conflict_exists',
            'reason' => 'Direct reporting manager to subject.',
            'relationship_type' => 'manages_subject',
            'status' => 'disqualified',
        ]);

        // Attempting to assign disqualified investigator should throw ValidationException
        $this->expectException(ValidationException::class);
        $caseService->assignUser($case, $investigatorUser, 'investigator', $hrAdmin);
    }

    public function test_sla_evaluation_job(): void
    {
        $tenant = Tenant::factory()->create();
        $caseType = EmployeeRelationCaseType::query()->where('code', 'COMPLAINT')->first();
        $caseService = app(EmployeeRelationCaseService::class);

        $case = $caseService->createCase($tenant->id, [
            'case_type_id' => $caseType->id,
            'title' => 'SLA Test Case',
            'summary' => 'Testing automatic SLA breach evaluation.',
        ]);

        // Manually age the initial response SLA past due
        $case->slas()->where('sla_type', 'initial_response')->update([
            'due_at' => now()->subHours(5),
            'status' => 'pending',
        ]);

        // Run SLA Job
        dispatch_sync(new EvaluateCaseSlaJob($tenant->id));

        $breachedSla = $case->slas()->where('sla_type', 'initial_response')->first();
        $this->assertEquals('breached', $breachedSla->status);
        $this->assertNotNull($breachedSla->breached_at);
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
