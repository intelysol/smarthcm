<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Services\AttendanceAuthorizationService;
use App\Domains\Attendance\Services\AttendanceProcessor;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceSessionController extends Controller
{
    public function __construct(
        protected AttendanceProcessor $processor,
        protected AttendanceAuthorizationService $authService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $query = AttendanceSession::query()
            ->where('tenant_id', $tenantId)
            ->with(['employee', 'shift', 'sessionBreaks', 'exceptions']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->query('employee_id'));
        }

        if ($request->filled('date')) {
            $query->where('session_date', $request->query('date'));
        } elseif ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('session_date', [$request->query('from'), $request->query('to')]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $sessions = $query->latest('session_date')->paginate(30);

        return response()->json($sessions);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $session = AttendanceSession::query()
            ->where('tenant_id', $user->tenant_id)
            ->with(['employee', 'shift.breaks', 'sessionBreaks', 'exceptions', 'adjustments', 'overtimeRequests'])
            ->findOrFail($id);

        if (! $this->authService->canViewSession($user, $session)) {
            return response()->json(['message' => 'Unauthorized to view this attendance session.'], 403);
        }

        return response()->json(['data' => $session]);
    }

    public function process(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'employee_id' => ['required', 'string', 'uuid'],
            'date' => ['required', 'date'],
        ]);

        $employee = Employee::query()
            ->where('tenant_id', $user->tenant_id)
            ->findOrFail($request->input('employee_id'));

        $session = $this->processor->processEmployeeDate($employee, $request->input('date'));

        return response()->json([
            'message' => 'Attendance session processed successfully.',
            'data' => $session->load(['shift', 'sessionBreaks', 'exceptions']),
        ]);
    }
}
