<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\AttendanceAdjustment;
use App\Domains\Attendance\Models\AttendanceException;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\OvertimeRequest;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeAttendancePortalController extends Controller
{
    public function myAttendance(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::query()->where('tenant_id', $user->tenant_id)->where('user_id', $user->id)->first();

        if (! $employee) {
            return response()->json(['data' => []]);
        }

        $sessions = AttendanceSession::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('employee_id', $employee->id)
            ->with(['shift', 'sessionBreaks', 'exceptions'])
            ->latest('session_date')
            ->limit(31)
            ->get();

        return response()->json(['data' => $sessions]);
    }

    public function myRoster(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::query()->where('tenant_id', $user->tenant_id)->where('user_id', $user->id)->first();

        if (! $employee) {
            return response()->json(['data' => []]);
        }

        $roster = RosterAssignment::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('is_published', true)
            ->with('shift.breaks')
            ->orderBy('roster_date')
            ->limit(31)
            ->get();

        return response()->json(['data' => $roster]);
    }

    public function myTimesheet(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::query()->where('tenant_id', $user->tenant_id)->where('user_id', $user->id)->first();

        if (! $employee) {
            return response()->json(['data' => null]);
        }

        $timesheet = Timesheet::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('employee_id', $employee->id)
            ->with('entries')
            ->latest('start_date')
            ->first();

        return response()->json(['data' => $timesheet]);
    }

    public function myExceptions(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::query()->where('tenant_id', $user->tenant_id)->where('user_id', $user->id)->first();

        if (! $employee) {
            return response()->json(['data' => []]);
        }

        $exceptions = AttendanceException::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('employee_id', $employee->id)
            ->with('session')
            ->latest('exception_date')
            ->get();

        return response()->json(['data' => $exceptions]);
    }
}
