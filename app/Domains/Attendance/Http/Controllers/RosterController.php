<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Requests\RosterAssignRequest;
use App\Domains\Attendance\Requests\RosterPeriodRequest;
use App\Domains\Attendance\Services\RosterService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    public function __construct(
        protected RosterService $rosterService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $periods = RosterPeriod::query()
            ->where('tenant_id', $user->tenant_id)
            ->withCount('assignments')
            ->latest('start_date')
            ->paginate(20);

        return response()->json($periods);
    }

    public function storePeriod(RosterPeriodRequest $request): JsonResponse
    {
        $user = $request->user();
        $period = $this->rosterService->createPeriod($user->tenant_id, $request->validated(), $user);

        return response()->json([
            'message' => 'Roster period created successfully.',
            'data' => $period,
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $period = RosterPeriod::query()
            ->where('tenant_id', $user->tenant_id)
            ->with(['assignments.employee', 'assignments.shift', 'assignments.conflicts'])
            ->findOrFail($id);

        return response()->json(['data' => $period]);
    }

    public function assign(RosterAssignRequest $request): JsonResponse
    {
        $user = $request->user();
        $period = RosterPeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('roster_period_id'));
        $employee = Employee::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('employee_id'));
        $shift = $request->filled('shift_definition_id')
            ? \App\Domains\Attendance\Models\ShiftDefinition::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('shift_definition_id'))
            : null;

        $assignment = $this->rosterService->assignShift(
            $period,
            $employee,
            $request->input('date'),
            $shift,
            $user,
            $request->input('notes')
        );

        return response()->json([
            'message' => 'Shift assigned successfully.',
            'data' => $assignment->load(['shift', 'conflicts']),
        ]);
    }

    public function publish(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $period = RosterPeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $published = $this->rosterService->publishRoster($period, $user);

        return response()->json([
            'message' => 'Roster published and notifications dispatched.',
            'data' => $published,
        ]);
    }

    public function swap(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'assignment_a_id' => ['required', 'string', 'uuid'],
            'assignment_b_id' => ['required', 'string', 'uuid'],
        ]);

        $assignmentA = RosterAssignment::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('assignment_a_id'));
        $assignmentB = RosterAssignment::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('assignment_b_id'));

        $this->rosterService->swapShifts($assignmentA, $assignmentB, $user);

        return response()->json(['message' => 'Shifts successfully swapped.']);
    }
}
