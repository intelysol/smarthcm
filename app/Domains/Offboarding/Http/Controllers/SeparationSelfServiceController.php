<?php

namespace App\Domains\Offboarding\Http\Controllers;

use App\Domains\Offboarding\Models\SeparationExitInterview;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SeparationSelfServiceController extends Controller
{
    public function __construct(protected SeparationService $separationService)
    {
    }

    public function employeeView(Request $request): View
    {
        $employeeId = $request->user()->employee_id;
        $separation = SeparationRequest::where('employee_id', $employeeId)
            ->with(['separationType', 'noticePeriod', 'clearances.items', 'handoverRecord.items', 'finalSettlement', 'exitInterview', 'documents'])
            ->latest()
            ->first();

        return view('offboarding.employee.portal', compact('separation'));
    }

    public function getMySeparation(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        if (!$employeeId) {
            throw ValidationException::withMessages(['employee' => 'User does not have an active employee profile.']);
        }

        $separation = SeparationRequest::where('employee_id', $employeeId)
            ->with(['separationType', 'noticePeriod', 'clearances.items', 'handoverRecord.items', 'finalSettlement', 'exitInterview', 'documents'])
            ->latest()
            ->first();

        return response()->json($separation);
    }

    public function submitResignation(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        if (!$employeeId) {
            throw ValidationException::withMessages(['employee' => 'User does not have an active employee profile.']);
        }

        $validated = $request->validate([
            'proposed_last_working_day' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:255',
            'comments' => 'nullable|string',
        ]);

        $resignationType = SeparationType::where('tenant_id', $request->user()->tenant_id)
            ->where('code', 'RESIGNATION')
            ->firstOrFail();

        $separation = $this->separationService->createRequest($request->user(), [
            'employee_id' => $employeeId,
            'separation_type_id' => $resignationType->id,
            'proposed_last_working_day' => $validated['proposed_last_working_day'],
            'reason' => $validated['reason'],
            'comments' => $validated['comments'] ?? null,
            'source' => 'self_service',
        ]);

        $submitted = $this->separationService->submitRequest($separation, $request->user());

        return response()->json($submitted, 201);
    }

    public function submitExitInterview(Request $request, string $id): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        $separation = SeparationRequest::where('id', $id)
            ->where('employee_id', $employeeId)
            ->firstOrFail();

        $validated = $request->validate([
            'responses' => 'required|array',
            'is_anonymous' => 'nullable|boolean',
            'primary_reason_category' => 'nullable|string|max:50',
            'overall_sentiment' => 'nullable|string|in:positive,neutral,negative',
            'confidential_notes' => 'nullable|string',
        ]);

        $interview = SeparationExitInterview::updateOrCreate(
            ['separation_request_id' => $separation->id],
            [
                'tenant_id' => $separation->tenant_id,
                'interviewer_id' => $request->user()->id,
                'responses' => $validated['responses'],
                'is_anonymous' => $validated['is_anonymous'] ?? false,
                'primary_reason_category' => $validated['primary_reason_category'] ?? null,
                'overall_sentiment' => $validated['overall_sentiment'] ?? 'neutral',
                'confidential_notes' => $validated['confidential_notes'] ?? null,
                'completed_at' => now(),
            ]
        );

        return response()->json($interview);
    }
}
