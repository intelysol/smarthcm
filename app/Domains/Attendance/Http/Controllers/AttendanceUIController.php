<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Services\AttendanceAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AttendanceUIController extends Controller
{
    public function __construct(
        protected AttendanceAnalyticsService $analyticsService
    ) {}

    public function dashboard(Request $request): View
    {
        $tenantId = $request->user()?->tenant_id ?? '';
        $metrics = $this->analyticsService->generateMetrics($tenantId);

        return view('attendance.dashboard', compact('metrics'));
    }

    public function rosterBoard(Request $request): View
    {
        return view('attendance.roster-board');
    }

    public function shifts(Request $request): View
    {
        return view('attendance.shifts');
    }

    public function calendars(Request $request): View
    {
        return view('attendance.calendars');
    }

    public function devices(Request $request): View
    {
        return view('attendance.devices');
    }

    public function exceptions(Request $request): View
    {
        return view('attendance.exceptions');
    }

    public function timesheets(Request $request): View
    {
        return view('attendance.timesheets');
    }

    public function periods(Request $request): View
    {
        return view('attendance.periods');
    }

    public function employeePortal(Request $request): View
    {
        return view('attendance.employee-portal');
    }

    public function managerPortal(Request $request): View
    {
        return view('attendance.manager-portal');
    }
}
