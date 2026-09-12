<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Attendance\Services\TimesheetService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function __construct(
        protected TimesheetService $timesheetService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Timesheet::query()
            ->where('tenant_id', $user->tenant_id)
            ->with(['employee', 'period', 'approver']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->query('employee_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $timesheets = $query->latest('start_date')->paginate(30);

        return response()->json($timesheets);
    }

    public function generate(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'employee_id' => ['required', 'string', 'uuid'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'period_type' => ['nullable', 'string', 'in:daily,weekly,biweekly,monthly,pay_period'],
        ]);

        $employee = Employee::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('employee_id'));

        $timesheet = $this->timesheetService->generateTimesheet(
            $employee,
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('period_type', 'monthly'),
            $user
        );

        return response()->json([
            'message' => 'Timesheet generated successfully.',
            'data' => $timesheet->load(['entries', 'employee']),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $timesheet = Timesheet::query()
            ->where('tenant_id', $user->tenant_id)
            ->with(['employee', 'entries', 'approver'])
            ->findOrFail($id);

        return response()->json(['data' => $timesheet]);
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $timesheet = Timesheet::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $submitted = $this->timesheetService->submitTimesheet($timesheet);

        return response()->json([
            'message' => 'Timesheet submitted for approval.',
            'data' => $submitted,
        ]);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $timesheet = Timesheet::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $approved = $this->timesheetService->approveTimesheet($timesheet, $user);

        return response()->json([
            'message' => 'Timesheet approved.',
            'data' => $approved,
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $timesheet = Timesheet::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $rejected = $this->timesheetService->rejectTimesheet($timesheet, $user, $request->input('reason'));

        return response()->json([
            'message' => 'Timesheet rejected.',
            'data' => $rejected,
        ]);
    }
}
