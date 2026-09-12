<?php

namespace App\Domains\EmployeeRelations\Http\Controllers;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Domains\EmployeeRelations\Models\EmployeeRelationPolicyReference;
use App\Domains\EmployeeRelations\Services\CaseAuthorizationService;
use App\Domains\EmployeeRelations\Services\EmployeeRelationAnalyticsService;
use App\Domains\EmployeeRelations\Services\EmployeeRelationCaseService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EmployeeRelationsUIController extends Controller
{
    public function __construct(
        protected EmployeeRelationCaseService $caseService,
        protected CaseAuthorizationService $authService,
        protected EmployeeRelationAnalyticsService $analyticsService
    ) {}

    // HR Dashboard
    public function adminDashboard(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $metrics = $this->analyticsService->generateMetrics($tenantId);

        $recentCases = EmployeeRelationCase::query()
            ->where('tenant_id', $tenantId)
            ->with(['caseType', 'subjectEmployee'])
            ->latest('opened_at')
            ->limit(10)
            ->get();

        return view('employee_relations.admin.dashboard', compact('metrics', 'recentCases'));
    }

    // HR Case Queue
    public function adminCases(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $caseTypes = EmployeeRelationCaseType::query()->where(fn ($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'))->get();

        $cases = EmployeeRelationCase::query()
            ->where('tenant_id', $tenantId)
            ->with(['caseType', 'subjectEmployee', 'assignments.user'])
            ->latest('opened_at')
            ->get();

        return view('employee_relations.admin.cases', compact('cases', 'caseTypes'));
    }

    // HR Case Intake form
    public function adminIntake(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $caseTypes = EmployeeRelationCaseType::query()->where(fn ($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'))->get();

        return view('employee_relations.admin.intake', compact('caseTypes'));
    }

    // Case Workspace / Detail
    public function adminCaseDetail(Request $request, EmployeeRelationCase $case): View
    {
        abort_unless($this->authService->canViewCase($request->user(), $case), 404);

        $case->load([
            'caseType',
            'subjectEmployee',
            'reporters',
            'triage.recommendedCaseType',
            'assignments.user',
            'participants.user',
            'conflicts.user',
            'allegations.findings',
            'statements.participant',
            'interviews.participant',
            'evidence.collector',
            'caseNotes.author',
            'tasks.assignee',
            'slas',
            'hearings.chairperson',
            'decisions.decisionMaker',
            'correctiveActions.assignedEmployee',
            'appeals.submitter',
            'correspondence',
            'legalHolds',
        ]);

        $timeline = $this->caseService->getTimeline($case, $request->user(), $this->authService);

        return view('employee_relations.admin.case_detail', compact('case', 'timeline'));
    }

    // Investigation Workspace
    public function adminInvestigation(Request $request, EmployeeRelationCase $case): View
    {
        abort_unless($this->authService->canViewInvestigation($request->user(), $case), 403);

        $investigation = $case->investigations()->with(['investigator', 'steps', 'questions'])->latest()->first();

        return view('employee_relations.admin.workspace_investigation', compact('case', 'investigation'));
    }

    // Evidence Workspace
    public function adminEvidence(Request $request, EmployeeRelationCase $case): View
    {
        abort_unless($this->authService->canViewEvidence($request->user(), $case), 403);

        $evidence = $case->evidence()->with(['collector', 'history.user'])->get();

        return view('employee_relations.admin.workspace_evidence', compact('case', 'evidence'));
    }

    // Hearing Workspace
    public function adminHearing(Request $request, EmployeeRelationCase $case): View
    {
        abort_unless($this->authService->canViewCase($request->user(), $case), 404);

        $hearings = $case->hearings()->with(['chairperson', 'outcome'])->get();

        return view('employee_relations.admin.workspace_hearing', compact('case', 'hearings'));
    }

    // Decision Workspace
    public function adminDecision(Request $request, EmployeeRelationCase $case): View
    {
        abort_unless($this->authService->canViewCase($request->user(), $case), 404);

        $decisions = $case->decisions()->with(['decisionMaker', 'correctiveActions.assignedEmployee'])->get();
        $allegations = $case->allegations()->with('findings')->get();

        return view('employee_relations.admin.workspace_decision', compact('case', 'decisions', 'allegations'));
    }

    // Employee Self-Service Case Portal
    public function employeeDashboard(Request $request): View
    {
        $user = $request->user();
        $employeeId = $user->employee?->id;

        $myCases = EmployeeRelationCase::query()
            ->where('tenant_id', $user->tenant_id)
            ->where(function ($q) use ($user, $employeeId) {
                $q->where('created_by', $user->id)
                    ->orWhere('subject_employee_id', $employeeId)
                    ->orWhereHas('participants', fn ($p) => $p->where('user_id', $user->id)->orWhere('employee_id', $employeeId));
            })
            ->with(['caseType', 'correctiveActions'])
            ->latest('opened_at')
            ->get();

        return view('employee_relations.employee.portal', compact('myCases'));
    }

    // Employee Self-Service Intake Report
    public function employeeReport(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $caseTypes = EmployeeRelationCaseType::query()
            ->where(fn ($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'))
            ->where('is_self_service_allowed', true)
            ->get();

        return view('employee_relations.employee.report', compact('caseTypes'));
    }

    // Employee Case Detail (Safe view)
    public function employeeCaseDetail(Request $request, EmployeeRelationCase $case): View
    {
        $user = $request->user();
        $employeeId = $user->employee?->id;

        $isParty = $case->created_by === $user->id
            || $case->subject_employee_id === $employeeId
            || $case->participants()->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('employee_id', $employeeId))->exists();

        abort_unless($isParty && $case->tenant_id === $user->tenant_id, 404);

        $employeeActions = $case->correctiveActions()->where('assigned_to_employee_id', $employeeId)->get();
        $employeeAppeals = $case->appeals()->where('submitted_by', $user->id)->get();

        return view('employee_relations.employee.case_detail', compact('case', 'employeeActions', 'employeeAppeals'));
    }

    // Anonymous Public Intake
    public function anonymousIntake(Request $request): View
    {
        $caseTypes = EmployeeRelationCaseType::query()
            ->where('is_anonymous_allowed', true)
            ->get();

        return view('employee_relations.anonymous.intake', compact('caseTypes'));
    }

    // Anonymous Case Tracking
    public function anonymousTracking(Request $request, string $token): View
    {
        $case = $this->caseService->resolveAnonymousToken($token);

        return view('employee_relations.anonymous.tracking', compact('case', 'token'));
    }
}
