<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitWaiver;
use App\Domains\Benefits\Services\BenefitWaiverService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitWaiverController extends Controller
{
    public function __construct(
        protected BenefitWaiverService $waiverService
    ) {}

    public function index(Employee $employee): JsonResponse
    {
        $waivers = $this->waiverService->getWaiversForEmployee($employee);

        return response()->json([
            'success' => true,
            'employee_id' => $employee->id,
            'data' => $waivers,
        ]);
    }

    public function store(Employee $employee, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'benefit_plan_id' => 'required|uuid|exists:benefit_plans,id',
            'benefit_enrollment_window_id' => 'nullable|uuid|exists:benefit_enrollment_windows,id',
            'reason' => 'required|string|max:255',
            'supporting_document_id' => 'nullable|uuid',
            'waiver_date' => 'nullable|date',
        ]);

        $plan = BenefitPlan::findOrFail($validated['benefit_plan_id']);
        $waiver = $this->waiverService->submitWaiver($employee, $plan, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Benefit waiver request submitted.',
            'data' => $waiver->load('plan'),
        ], 201);
    }

    public function approve(BenefitWaiver $waiver, Request $request): JsonResponse
    {
        $approved = $this->waiverService->approveWaiver($waiver, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Benefit waiver approved.',
            'data' => $approved,
        ]);
    }

    public function reject(BenefitWaiver $waiver, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $rejected = $this->waiverService->rejectWaiver($waiver, $validated['rejection_reason'], $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Benefit waiver rejected.',
            'data' => $rejected,
        ]);
    }
}
