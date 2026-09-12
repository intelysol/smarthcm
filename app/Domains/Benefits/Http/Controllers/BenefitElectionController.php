<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\BenefitElection;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Services\BenefitElectionService;
use App\Domains\Benefits\Services\BenefitEligibilityService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitElectionController extends Controller
{
    public function __construct(
        protected BenefitElectionService $electionService,
        protected BenefitEligibilityService $eligibilityService
    ) {}

    public function eligiblePlans(Employee $employee, Request $request): JsonResponse
    {
        $asOfDate = $request->query('as_of_date');
        $plans = $this->eligibilityService->getEligiblePlansForEmployee($employee, $asOfDate);

        return response()->json([
            'success' => true,
            'employee_id' => $employee->id,
            'data' => $plans,
        ]);
    }

    public function index(Employee $employee, Request $request): JsonResponse
    {
        $windowId = $request->query('window_id');
        $elections = $this->electionService->getElectionsForEmployee($employee, $windowId);

        return response()->json([
            'success' => true,
            'employee_id' => $employee->id,
            'data' => $elections,
        ]);
    }

    public function store(Employee $employee, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'benefit_plan_id' => 'required|uuid|exists:benefit_plans,id',
            'benefit_enrollment_window_id' => 'nullable|uuid|exists:benefit_enrollment_windows,id',
            'benefit_life_event_id' => 'nullable|uuid|exists:benefit_life_events,id',
            'benefit_coverage_id' => 'nullable|uuid|exists:benefit_coverages,id',
            'coverage_level' => 'nullable|string|max:50',
            'is_waived' => 'nullable|boolean',
            'waiver_reason' => 'nullable|string',
            'election_date' => 'nullable|date',
            'effective_date' => 'nullable|date',
            'dependents' => 'nullable|array',
            'beneficiaries' => 'nullable|array',
            'notes' => 'nullable|string',
            'supporting_document_id' => 'nullable|uuid',
        ]);

        $plan = BenefitPlan::findOrFail($validated['benefit_plan_id']);
        $election = $this->electionService->elect($employee, $plan, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Benefit election saved successfully.',
            'data' => $election->load(['plan', 'coverage']),
        ], 201);
    }

    public function confirm(BenefitElection $election, Request $request): JsonResponse
    {
        $confirmed = $this->electionService->confirmElection($election, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Benefit election confirmed.',
            'data' => $confirmed,
        ]);
    }

    public function approve(BenefitElection $election, Request $request): JsonResponse
    {
        $enrollment = $this->electionService->approveElection($election, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Benefit election approved and active enrollment created.',
            'data' => $enrollment,
        ]);
    }
}
