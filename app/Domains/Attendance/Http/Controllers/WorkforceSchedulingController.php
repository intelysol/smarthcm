<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\HcmEmployeeAvailability;
use App\Domains\Attendance\Models\HcmEmployeeShiftPreference;
use App\Domains\Attendance\Models\HcmOpenShift;
use App\Domains\Attendance\Models\HcmOpenShiftBid;
use App\Domains\Attendance\Models\HcmScheduleOptimizationRun;
use App\Domains\Attendance\Models\HcmShiftSwapRequest;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\Scheduling\AdvisorySchedulingAiService;
use App\Domains\Attendance\Services\Scheduling\CoverageCalculationService;
use App\Domains\Attendance\Services\Scheduling\OpenShiftService;
use App\Domains\Attendance\Services\Scheduling\RealTimeCoverageService;
use App\Domains\Attendance\Services\Scheduling\ScheduleCostEstimationService;
use App\Domains\Attendance\Services\Scheduling\ScheduleOptimizationService;
use App\Domains\Attendance\Services\Scheduling\SchedulePublicationService;
use App\Domains\Attendance\Services\Scheduling\ScheduleValidationService;
use App\Domains\Attendance\Services\Scheduling\ShiftSwapService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkforceSchedulingController extends Controller
{
    public function __construct(
        protected ScheduleValidationService $validationService,
        protected SchedulePublicationService $publicationService,
        protected CoverageCalculationService $coverageService,
        protected ScheduleOptimizationService $optimizationService,
        protected ShiftSwapService $swapService,
        protected OpenShiftService $openShiftService,
        protected RealTimeCoverageService $realTimeService,
        protected ScheduleCostEstimationService $costService,
        protected AdvisorySchedulingAiService $aiService
    ) {}

    public function validatePeriod(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $period = RosterPeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $result = $this->validationService->validatePeriod($period);

        return response()->json([
            'message' => 'Schedule validation complete.',
            'data' => $result,
        ]);
    }

    public function publishPeriod(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $period = RosterPeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $force = $request->boolean('force_override', false);
        $reason = $request->input('override_reason');

        $published = $this->publicationService->publish($period, $user, $force, $reason);

        return response()->json([
            'message' => 'Schedule published successfully.',
            'data' => $published,
        ]);
    }

    public function lockPeriod(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $period = RosterPeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $locked = $this->publicationService->lock($period, $user);

        return response()->json([
            'message' => 'Schedule locked successfully.',
            'data' => $locked,
        ]);
    }

    public function coverageMatrix(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $period = RosterPeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $matrix = $this->coverageService->getCoverageMatrix($period);

        return response()->json([
            'data' => $matrix,
        ]);
    }

    public function ingestCoverage(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $period = RosterPeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $request->validate([
            'requirements' => ['required', 'array'],
            'requirements.*.date' => ['required', 'date'],
            'requirements.*.shift_definition_id' => ['required', 'string'],
            'requirements.*.required_headcount' => ['required', 'integer', 'min:1'],
        ]);

        $created = $this->coverageService->ingestRequirements($period, $request->input('requirements'));

        return response()->json([
            'message' => 'Coverage requirements ingested successfully.',
            'count' => $created->count(),
            'data' => $created,
        ]);
    }

    public function optimize(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $period = RosterPeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $strategy = $request->input('strategy', 'rule_based_heuristic');

        $run = $this->optimizationService->optimize($period, $strategy, $user);

        return response()->json([
            'message' => 'Schedule optimization proposal generated.',
            'data' => $run,
        ]);
    }

    public function applyOptimization(Request $request, string $runId): JsonResponse
    {
        $user = $request->user();
        $run = HcmScheduleOptimizationRun::query()->where('tenant_id', $user->tenant_id)->findOrFail($runId);
        $applied = $this->optimizationService->applyProposedAssignments($run, $user);

        return response()->json([
            'message' => "Successfully applied {$applied} optimized assignment(s).",
            'applied_count' => $applied,
        ]);
    }

    public function estimateCost(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $period = RosterPeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $rate = (float) $request->input('hourly_rate', 20.00);

        $cost = $this->costService->estimatePeriodCost($period, $rate);

        return response()->json([
            'data' => $cost,
        ]);
    }

    public function requestSwap(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'requesting_assignment_id' => ['required', 'string'],
            'target_assignment_id' => ['required', 'string'],
            'reason' => ['required', 'string'],
        ]);

        $assignA = RosterAssignment::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('requesting_assignment_id'));
        $assignB = RosterAssignment::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('target_assignment_id'));

        $swap = $this->swapService->requestSwap(
            requestingAssignment: $assignA,
            targetAssignment: $assignB,
            requestingEmployee: $assignA->employee,
            targetEmployee: $assignB->employee,
            reason: $request->input('reason')
        );

        return response()->json([
            'message' => 'Swap request created successfully and awaiting peer response.',
            'data' => $swap,
        ], 201);
    }

    public function peerRespondSwap(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $swap = HcmShiftSwapRequest::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $accept = $request->boolean('accept');

        $updated = $this->swapService->peerRespond($swap, $accept);

        return response()->json([
            'message' => $accept ? 'Swap accepted by peer, forwarded to manager.' : 'Swap declined by peer.',
            'data' => $updated,
        ]);
    }

    public function managerApproveSwap(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $swap = HcmShiftSwapRequest::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $approve = $request->boolean('approve');

        $updated = $this->swapService->managerApprove($swap, $user, $approve);

        return response()->json([
            'message' => $approve ? 'Shift swap approved and assignments updated.' : 'Shift swap rejected.',
            'data' => $updated,
        ]);
    }

    public function createOpenShift(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'roster_period_id' => ['required', 'string'],
            'roster_date' => ['required', 'date'],
            'shift_definition_id' => ['required', 'string'],
            'slots_total' => ['nullable', 'integer', 'min:1'],
        ]);

        $period = RosterPeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('roster_period_id'));
        $shift = ShiftDefinition::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('shift_definition_id'));

        $openShift = $this->openShiftService->createOpenShift(
            period: $period,
            date: $request->input('roster_date'),
            shift: $shift,
            slots: (int) $request->input('slots_total', 1),
            requiredSkillId: $request->input('required_skill_id'),
            departmentId: $request->input('department_id')
        );

        return response()->json([
            'message' => 'Open shift created successfully.',
            'data' => $openShift,
        ], 201);
    }

    public function bidOpenShift(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $openShift = HcmOpenShift::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $employee = Employee::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('employee_id'));

        $bid = $this->openShiftService->submitBid($openShift, $employee);

        return response()->json([
            'message' => 'Bid submitted successfully.',
            'data' => $bid,
        ], 201);
    }

    public function awardOpenShift(Request $request, string $bidId): JsonResponse
    {
        $user = $request->user();
        $bid = HcmOpenShiftBid::query()->where('tenant_id', $user->tenant_id)->findOrFail($bidId);
        $assignment = $this->openShiftService->awardBid($bid, $user);

        return response()->json([
            'message' => 'Open shift awarded and assignment created.',
            'data' => $assignment,
        ]);
    }

    public function realTimeCoverage(Request $request): JsonResponse
    {
        $user = $request->user();
        $date = $request->input('date', now()->toDateString());
        $deptId = $request->input('department_id');

        $result = $this->realTimeService->evaluateRealTimeCoverage($user->tenant_id, $date, $deptId);

        return response()->json([
            'data' => $result,
        ]);
    }

    public function advisoryAi(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $period = RosterPeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);

        $insights = $this->aiService->generateAdvisoryInsights($period);

        return response()->json([
            'data' => $insights,
        ]);
    }

    public function storeAvailability(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'employee_id' => ['required', 'string'],
            'date' => ['required', 'date'],
            'availability_type' => ['required', 'string', 'in:available,unavailable,preferred,restricted'],
            'reason' => ['nullable', 'string'],
        ]);

        $avail = HcmEmployeeAvailability::query()->create([
            'tenant_id' => $user->tenant_id,
            'employee_id' => $request->input('employee_id'),
            'date' => $request->input('date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'availability_type' => $request->input('availability_type'),
            'reason' => $request->input('reason'),
            'is_recurring' => $request->boolean('is_recurring', false),
            'recurring_day_of_week' => $request->input('recurring_day_of_week'),
        ]);

        return response()->json([
            'message' => 'Availability saved successfully.',
            'data' => $avail,
        ], 201);
    }

    public function storePreference(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'employee_id' => ['required', 'string'],
            'preference_type' => ['required', 'string', 'in:preferred,avoid'],
        ]);

        $pref = HcmEmployeeShiftPreference::query()->create([
            'tenant_id' => $user->tenant_id,
            'employee_id' => $request->input('employee_id'),
            'shift_definition_id' => $request->input('shift_definition_id'),
            'day_of_week' => $request->input('day_of_week'),
            'preference_type' => $request->input('preference_type'),
            'priority' => $request->input('priority', 1),
        ]);

        return response()->json([
            'message' => 'Preference saved successfully.',
            'data' => $pref,
        ], 201);
    }
}
