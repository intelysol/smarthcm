<?php

namespace App\Domains\Career\Http\Controllers;

use App\Domains\Career\Enums\NineBoxPosition;
use App\Domains\Career\Models\TalentReviewRecord;
use App\Domains\Career\Models\TalentReviewSession;
use App\Domains\Career\Requests\TalentPlacementRequest;
use App\Domains\Career\Resources\TalentReviewResource;
use App\Domains\Career\Services\TalentReviewService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTalentReviewController extends Controller
{
    public function __construct(protected TalentReviewService $reviewService) {}

    public function sessions(Request $request): JsonResponse
    {
        $sessions = TalentReviewSession::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->withCount('records')
            ->latest('review_date')
            ->get();

        return response()->json(TalentReviewResource::collection($sessions));
    }

    public function storeSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'scope_type' => ['nullable', 'string', 'in:organization,department,business_unit,location,leadership_group'],
            'scope_id' => ['nullable', 'string'],
            'review_date' => ['nullable', 'date'],
        ]);

        $session = $this->reviewService->createSession(
            $request->user()->tenant_id,
            $validated['title'],
            $validated['scope_type'] ?? 'organization',
            $validated['scope_id'] ?? null,
            $validated['review_date'] ?? null
        );

        return response()->json(['data' => new TalentReviewResource($session)], 201);
    }

    public function records(Request $request, string $sessionId): JsonResponse
    {
        $session = TalentReviewSession::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($sessionId);

        $records = $session->records()->with(['employee.department', 'employee.designation'])->get();
        return response()->json(['data' => $records]);
    }

    public function recordPlacement(TalentPlacementRequest $request, string $sessionId): JsonResponse
    {
        $session = TalentReviewSession::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($sessionId);

        $employee = Employee::query()
            ->where('tenant_id', $session->tenant_id)
            ->findOrFail($request->validated('employee_id'));

        $record = $this->reviewService->recordPlacement(
            $session,
            $employee,
            (float) $request->validated('performance_rating'),
            (float) $request->validated('potential_rating'),
            $request->validated('readiness_level', 'developing'),
            $request->validated('retention_risk', 'low'),
            $request->validated('vacancy_risk', 'low'),
            $request->validated('mobility_rating', 'high'),
            $request->validated('development_priority', 'core_development'),
            $request->validated('manager_notes')
        );

        return response()->json(['data' => $record->load('employee')], 201);
    }

    public function overridePlacement(Request $request, string $recordId): JsonResponse
    {
        $record = TalentReviewRecord::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($recordId);

        $validated = $request->validate([
            'nine_box_position' => ['required', 'string'],
            'reason' => ['required', 'string'],
            'calibration_notes' => ['nullable', 'string'],
        ]);

        $employeeUser = Employee::query()->where('user_id', $request->user()->id)->first() ?? $record->employee;
        $newPosition = NineBoxPosition::from($validated['nine_box_position']);

        $updated = $this->reviewService->overridePlacement(
            $record,
            $newPosition,
            $employeeUser,
            $validated['reason'],
            $validated['calibration_notes'] ?? null
        );

        return response()->json(['data' => $updated], 200);
    }

    public function nineBoxMatrix(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $records = TalentReviewRecord::query()
            ->where('tenant_id', $tenantId)
            ->with(['employee.department', 'employee.designation'])
            ->get();

        $matrix = [];
        foreach (NineBoxPosition::cases() as $pos) {
            $matching = $records->where('nine_box_position', $pos->value);
            $matrix[$pos->value] = [
                'label' => $pos->label(),
                'count' => $matching->count(),
                'employees' => $matching->values(),
            ];
        }

        return response()->json([
            'matrix' => $matrix,
            'total_evaluated' => $records->count(),
        ]);
    }
}
