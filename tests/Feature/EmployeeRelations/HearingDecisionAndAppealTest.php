<?php

namespace Tests\Feature\EmployeeRelations;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeRelations\Enums\CaseStatus;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Domains\EmployeeRelations\Services\DecisionAndAppealService;
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

class HearingDecisionAndAppealTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EmployeeRelationsPermissionSeeder::class);
        $this->seed(EmployeeRelationsCaseTypeSeeder::class);
    }

    public function test_hearings_decisions_corrective_actions_and_independent_appeals(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $hrAdmin = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($hrAdmin, [
            'hcm.employee_relations.manage',
            'hcm.employee_relations.case.view',
            'hcm.employee_relations.hearing.manage',
            'hcm.employee_relations.decision.manage',
            'hcm.employee_relations.action.manage',
            'hcm.employee_relations.appeal.manage',
        ]);

        $subjectUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $subjectEmployee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $subjectUser->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-001',
            'employee_code' => 'EMP-001',
            'first_name' => 'Alice',
            'last_name' => 'Subject',
            'joining_date' => now()->toDateString(),
        ]);

        $caseService = app(EmployeeRelationCaseService::class);
        $decisionService = app(DecisionAndAppealService::class);

        $caseType = EmployeeRelationCaseType::query()->where('code', 'POLICY_VIOLATION')->first();
        $case = $caseService->createCase($tenant->id, [
            'case_type_id' => $caseType->id,
            'subject_employee_id' => $subjectEmployee->id,
            'title' => 'Policy Violation Review',
            'summary' => 'Attendance policy non-compliance.',
        ], $hrAdmin);

        // 1. Schedule Hearing & Record Outcome
        $hearing = $decisionService->scheduleHearing($case, $hrAdmin, [
            'title' => 'Formal Review Hearing',
            'scheduled_at' => now()->addDays(2)->toDateTimeString(),
            'location' => 'Room 402',
        ]);
        $this->assertEquals('scheduled', $hearing->status);

        $outcome = $decisionService->recordHearingOutcome($hearing, $hrAdmin, [
            'summary' => 'Hearing conducted. Employee provided explanation of commuting challenges.',
            'recommendations' => 'Implement structured 30-day attendance improvement plan.',
        ]);
        $this->assertEquals('completed', $hearing->fresh()->status);
        $this->assertNotNull($outcome);

        // 2. Record Formal Decision with Corrective Actions
        $decision = $decisionService->recordDecision($case, $hrAdmin, [
            'decision' => 'written_warning',
            'reason' => 'Multiple unexcused absences without prior notification.',
            'effective_date' => now()->toDateString(),
            'is_draft' => false,
            'actions' => [
                [
                    'action_type' => 'attendance_improvement',
                    'description' => 'Complete 30-day Attendance Improvement Plan.',
                    'due_date' => now()->addDays(30)->toDateString(),
                ],
            ],
        ]);

        $this->assertEquals('written_warning', $decision->decision);
        $this->assertEquals(1, $decision->correctiveActions()->count());
        $action = $decision->correctiveActions()->first();
        $this->assertEquals('assigned', $action->status);
        $this->assertEquals('pending', $action->employee_acknowledgement_status);

        // 3. Employee Acknowledges Action with Comment
        $acknowledgedAction = $decisionService->acknowledgeAction($action, 'commented', 'I acknowledge receipt and will adhere to the agreed schedule.');
        $this->assertEquals('commented', $acknowledgedAction->employee_acknowledgement_status);
        $this->assertEquals('in_progress', $acknowledgedAction->status);

        // Complete and Verify Action
        $completedAction = $decisionService->completeAction($acknowledgedAction);
        $this->assertEquals('completed', $completedAction->status);

        $verifiedAction = $decisionService->verifyAction($completedAction, $hrAdmin);
        $this->assertEquals('verified', $verifiedAction->status);

        // 4. Employee Submits Appeal
        $appeal = $decisionService->submitAppeal($case, $subjectUser, [
            'reason' => 'Disproportionate sanction given mitigating circumstances.',
            'grounds' => 'Prior medical documentation was not fully reviewed.',
        ]);
        $this->assertEquals('submitted', $appeal->status);
        $this->assertEquals(CaseStatus::APPEAL->value, $case->fresh()->status);

        // 5. Independent Reviewer Resolves Appeal
        $independentReviewer = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($independentReviewer, ['hcm.employee_relations.appeal.manage']);

        $resolvedAppeal = $decisionService->resolveAppeal($appeal, $independentReviewer, [
            'decision' => 'modified',
            'decision_reason' => 'Sanction adjusted to formal counselling with acknowledged medical record consideration.',
        ]);

        $this->assertEquals('resolved', $resolvedAppeal->status);
        $this->assertEquals('modified', $resolvedAppeal->decision);
        $this->assertEquals(CaseStatus::CLOSED->value, $case->fresh()->status);
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
