<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\AttendanceAdjustment;
use App\Domains\Attendance\Models\AttendanceException;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\OvertimeRequest;
use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerAttendancePortalController extends Controller
{
    public function teamAttendance(Request $request): JsonResponse
    {
        $user = $request->user();
        $manager = Employee::query()->where('tenant_id', $user->tenant_id)->where('user_id', $user->id)->first();

        if (! $manager) {
            return response()->json(['data' => []]);
        }

        $directReportIds = Employee::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('reporting_manager_id', $manager->id)
            ->pluck('id');

        $date = $request->query('date') ?: now()->toDateString();

        $sessions = AttendanceSession::query()
            ->where('tenant_id', $user->tenant_id)
            ->whereIn('employee_id', $directReportIds)
            ->where('session_date', $date)
            ->with(['employee', 'shift', 'exceptions'])
            ->get();

        return response()->json(['data' => $sessions]);
    }

    public function teamPendingApprovals(Request $request): JsonResponse
    {
        $user = $request->user();
        $manager = Employee::query()->where('tenant_id', $user->tenant_id)->where('user_id', $user->id)->first();

        if (! $manager) {
            return response()->json([
                'adjustments' => [],
                'overtimes' => [],
                'timesheets' => [],
            ]);
        }

        $directReportIds = Employee::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('reporting_manager_id', $manager->id)
            ->pluck('id');

        $adjustments = AttendanceAdjustment::query()
            ->where('tenant_id', $user->tenant_id)
            ->whereIn('employee_id', $directReportIds)
            ->where('status', 'pending')
            ->with('employee')
            ->get();

        $overtimes = OvertimeRequest::query()
            ->where('tenant_id', $user->tenant_id)
            ->whereIn('employee_id', $directReportIds)
            ->where('status', 'pending')
            ->with('employee')
            ->get();

        $timesheets = Timesheet::query()
            ->where('tenant_id', $user->tenant_id)
            ->whereIn('employee_id', $directReportIds)
            ->where('status', 'submitted')
            ->with('employee')
            ->get();

        return response()->json([
            'adjustments' => $adjustments,
            'overtimes' => $overtimes,
            'timesheets' => $timesheets,
        ]);
    }
}
