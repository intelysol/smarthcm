<?php

namespace App\Domains\EmployeeRelations\Http\Controllers;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Requests\EmployeeReportRequest;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationCaseResource;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationCorrectiveActionResource;
use App\Domains\EmployeeRelations\Services\CaseAuthorizationService;
use App\Domains\EmployeeRelations\Services\EmployeeRelationCaseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeePortalController extends Controller
{
    public function __construct(
        protected EmployeeRelationCaseService $caseService,
        protected CaseAuthorizationService $authService
    ) {}

    public function submitReport(EmployeeReportRequest $request): JsonResponse
    {
        $case = $this->caseService->submitEmployeeReport(
            $request->user()->tenant_id,
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Your report has been submitted to Employee Relations.',
            'case' => new EmployeeRelationCaseResource($case),
        ], 201);
    }

    public function myCases(Request $request): JsonResponse
    {
        $user = $request->user();
        $employeeId = $user->employee?->id;

        $cases = EmployeeRelationCase::query()
            ->where('tenant_id', $user->tenant_id)
            ->where(function ($q) use ($user, $employeeId) {
                $q->where('created_by', $user->id)
                    ->orWhere('subject_employee_id', $employeeId)
                    ->orWhereHas('participants', fn ($p) => $p->where('user_id', $user->id)->orWhere('employee_id', $employeeId));
            })
            ->with(['caseType', 'correctiveActions'])
            ->latest('opened_at')
            ->get();

        return response()->json(EmployeeRelationCaseResource::collection($cases));
    }

    public function myCaseDetail(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $user = $request->user();
        $employeeId = $user->employee?->id;

        // Verify that the employee is actually party to this case
        $isParty = $case->created_by === $user->id
            || $case->subject_employee_id === $employeeId
            || $case->participants()->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('employee_id', $employeeId))->exists();

        abort_unless($isParty && $case->tenant_id === $user->tenant_id, 404);

        // Filtered employee-visible view: NO internal notes, NO investigator notes, NO confidential witness statements, NO restricted evidence
        $employeeStatements = $case->statements()
            ->whereHas('participant', fn ($p) => $p->where('user_id', $user->id)->orWhere('employee_id', $employeeId))
            ->get();

        $employeeActions = $case->correctiveActions()
            ->where('assigned_to_employee_id', $employeeId)
            ->get();

        $employeeAppeals = $case->appeals()
            ->where('submitted_by', $user->id)
            ->get();

        return response()->json([
            'case' => new EmployeeRelationCaseResource($case->load('caseType')),
            'my_statements' => $employeeStatements,
            'required_actions' => EmployeeRelationCorrectiveActionResource::collection($employeeActions),
            'my_appeals' => $employeeAppeals,
            'notices' => $case->correspondence()->where('recipient_user_id', $user->id)->get(),
        ]);
    }
}
