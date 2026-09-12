<?php

use App\Domains\Attendance\Http\Controllers\AttendanceAdjustmentController;
use App\Domains\Attendance\Http\Controllers\AttendanceDeviceController;
use App\Domains\Attendance\Http\Controllers\AttendanceExceptionController;
use App\Domains\Attendance\Http\Controllers\AttendancePeriodController;
use App\Domains\Attendance\Http\Controllers\AttendancePunchController;
use App\Domains\Attendance\Http\Controllers\AttendanceReportController;
use App\Domains\Attendance\Http\Controllers\AttendanceSessionController;
use App\Domains\Attendance\Http\Controllers\CalendarController;
use App\Domains\Attendance\Http\Controllers\EmployeeAttendancePortalController;
use App\Domains\Attendance\Http\Controllers\ManagerAttendancePortalController;
use App\Domains\Attendance\Http\Controllers\OvertimeController;
use App\Domains\Attendance\Http\Controllers\RosterController;
use App\Domains\Attendance\Http\Controllers\ShiftController;
use App\Domains\Attendance\Http\Controllers\TimesheetController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('v1/hcm')->group(function () {

    // Employee Portal Routes
    Route::prefix('me/attendance')->group(function () {
        Route::get('/', [EmployeeAttendancePortalController::class, 'myAttendance'])->name('api.attendance.me.index');
        Route::get('roster', [EmployeeAttendancePortalController::class, 'myRoster'])->name('api.attendance.me.roster');
        Route::get('timesheet', [EmployeeAttendancePortalController::class, 'myTimesheet'])->name('api.attendance.me.timesheet');
        Route::get('exceptions', [EmployeeAttendancePortalController::class, 'myExceptions'])->name('api.attendance.me.exceptions');
    });

    // Manager Portal Routes
    Route::prefix('manager/attendance')->group(function () {
        Route::get('team', [ManagerAttendancePortalController::class, 'teamAttendance'])->name('api.attendance.manager.team');
        Route::get('pending-approvals', [ManagerAttendancePortalController::class, 'teamPendingApprovals'])->name('api.attendance.manager.pending');
    });

    // Core Attendance & Workforce Scheduling Routes
    Route::prefix('attendance')->group(function () {

        // Sessions & Processing
        Route::get('sessions', [AttendanceSessionController::class, 'index'])->name('api.attendance.sessions.index');
        Route::get('sessions/{id}', [AttendanceSessionController::class, 'show'])->name('api.attendance.sessions.show');
        Route::post('process', [AttendanceSessionController::class, 'process'])->name('api.attendance.process');
        Route::post('punch', [AttendancePunchController::class, 'punch'])->name('api.attendance.punch');

        // Exceptions
        Route::get('exceptions', [AttendanceExceptionController::class, 'index'])->name('api.attendance.exceptions.index');
        Route::post('exceptions/{id}/resolve', [AttendanceExceptionController::class, 'resolve'])->name('api.attendance.exceptions.resolve');

        // Adjustments / Regularizations
        Route::get('adjustments', [AttendanceAdjustmentController::class, 'index'])->name('api.attendance.adjustments.index');
        Route::post('adjustments', [AttendanceAdjustmentController::class, 'store'])->name('api.attendance.adjustments.store');
        Route::post('adjustments/{id}/approve', [AttendanceAdjustmentController::class, 'approve'])->name('api.attendance.adjustments.approve');
        Route::post('adjustments/{id}/reject', [AttendanceAdjustmentController::class, 'reject'])->name('api.attendance.adjustments.reject');

        // Overtime
        Route::get('overtime', [OvertimeController::class, 'index'])->name('api.attendance.overtime.index');
        Route::post('overtime', [OvertimeController::class, 'store'])->name('api.attendance.overtime.store');
        Route::post('overtime/{id}/approve', [OvertimeController::class, 'approve'])->name('api.attendance.overtime.approve');
        Route::post('overtime/{id}/reject', [OvertimeController::class, 'reject'])->name('api.attendance.overtime.reject');

        // Timesheets
        Route::get('timesheets', [TimesheetController::class, 'index'])->name('api.attendance.timesheets.index');
        Route::post('timesheets/generate', [TimesheetController::class, 'generate'])->name('api.attendance.timesheets.generate');
        Route::get('timesheets/{id}', [TimesheetController::class, 'show'])->name('api.attendance.timesheets.show');
        Route::post('timesheets/{id}/submit', [TimesheetController::class, 'submit'])->name('api.attendance.timesheets.submit');
        Route::post('timesheets/{id}/approve', [TimesheetController::class, 'approve'])->name('api.attendance.timesheets.approve');
        Route::post('timesheets/{id}/reject', [TimesheetController::class, 'reject'])->name('api.attendance.timesheets.reject');

        // Periods (Cutoff & Locking)
        Route::get('periods', [AttendancePeriodController::class, 'index'])->name('api.attendance.periods.index');
        Route::post('periods', [AttendancePeriodController::class, 'store'])->name('api.attendance.periods.store');
        Route::post('periods/{id}/lock', [AttendancePeriodController::class, 'lock'])->name('api.attendance.periods.lock');
        Route::post('periods/{id}/reopen', [AttendancePeriodController::class, 'reopen'])->name('api.attendance.periods.reopen');

        // Devices
        Route::get('devices', [AttendanceDeviceController::class, 'index'])->name('api.attendance.devices.index');
        Route::post('devices', [AttendanceDeviceController::class, 'store'])->name('api.attendance.devices.store');
        Route::get('devices/{id}', [AttendanceDeviceController::class, 'show'])->name('api.attendance.devices.show');
        Route::post('devices/{id}/sync', [AttendanceDeviceController::class, 'sync'])->name('api.attendance.devices.sync');
        Route::post('devices/{id}/test-connection', [AttendanceDeviceController::class, 'testConnection'])->name('api.attendance.devices.test_connection');

        // Calendars
        Route::get('calendars/work', [CalendarController::class, 'workCalendars'])->name('api.attendance.calendars.work');
        Route::get('calendars/holiday', [CalendarController::class, 'holidayCalendars'])->name('api.attendance.calendars.holiday');

        // Reports & Payroll Export Contract
        Route::get('reports/dashboard', [AttendanceReportController::class, 'dashboard'])->name('api.attendance.reports.dashboard');
        Route::get('reports/payroll-export', [AttendanceReportController::class, 'payrollExport'])->name('api.attendance.reports.payroll_export');
        Route::get('reports/export-csv', [AttendanceReportController::class, 'exportCsv'])->name('api.attendance.reports.export_csv');
    });

    // Shifts & Rosters
    Route::prefix('shifts')->group(function () {
        Route::get('/', [ShiftController::class, 'index'])->name('api.shifts.index');
        Route::post('/', [ShiftController::class, 'store'])->name('api.shifts.store');
        Route::get('patterns', [ShiftController::class, 'patterns'])->name('api.shifts.patterns');
        Route::get('{id}', [ShiftController::class, 'show'])->name('api.shifts.show');
        Route::patch('{id}', [ShiftController::class, 'update'])->name('api.shifts.update');
    });

    Route::prefix('rosters')->group(function () {
        Route::get('/', [RosterController::class, 'index'])->name('api.rosters.index');
        Route::post('periods', [RosterController::class, 'storePeriod'])->name('api.rosters.periods.store');
        Route::get('periods/{id}', [RosterController::class, 'show'])->name('api.rosters.periods.show');
        Route::post('assign', [RosterController::class, 'assign'])->name('api.rosters.assign');
        Route::post('periods/{id}/publish', [RosterController::class, 'publish'])->name('api.rosters.publish');
        Route::post('swap', [RosterController::class, 'swap'])->name('api.rosters.swap');
    });

    // Epic 2.46 Enterprise Workforce Scheduling & Rostering
    Route::prefix('scheduling')->group(function () {
        Route::post('periods/{id}/validate', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'validatePeriod'])->name('api.scheduling.validate');
        Route::post('periods/{id}/publish', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'publishPeriod'])->name('api.scheduling.publish');
        Route::post('periods/{id}/lock', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'lockPeriod'])->name('api.scheduling.lock');
        Route::get('periods/{id}/coverage', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'coverageMatrix'])->name('api.scheduling.coverage');
        Route::post('periods/{id}/coverage/ingest', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'ingestCoverage'])->name('api.scheduling.coverage.ingest');
        Route::post('periods/{id}/optimize', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'optimize'])->name('api.scheduling.optimize');
        Route::post('optimization-runs/{runId}/apply', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'applyOptimization'])->name('api.scheduling.optimize.apply');
        Route::get('periods/{id}/cost', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'estimateCost'])->name('api.scheduling.cost');
        Route::get('periods/{id}/ai-insights', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'advisoryAi'])->name('api.scheduling.ai_insights');

        Route::post('swaps', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'requestSwap'])->name('api.scheduling.swaps.request');
        Route::post('swaps/{id}/peer-respond', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'peerRespondSwap'])->name('api.scheduling.swaps.peer_respond');
        Route::post('swaps/{id}/manager-approve', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'managerApproveSwap'])->name('api.scheduling.swaps.manager_approve');

        Route::post('open-shifts', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'createOpenShift'])->name('api.scheduling.open_shifts.create');
        Route::post('open-shifts/{id}/bid', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'bidOpenShift'])->name('api.scheduling.open_shifts.bid');
        Route::post('open-shifts/bids/{bidId}/award', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'awardOpenShift'])->name('api.scheduling.open_shifts.award');

        Route::get('realtime-coverage', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'realTimeCoverage'])->name('api.scheduling.realtime_coverage');
        Route::post('availabilities', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'storeAvailability'])->name('api.scheduling.availabilities.store');
        Route::post('preferences', [\App\Domains\Attendance\Http\Controllers\WorkforceSchedulingController::class, 'storePreference'])->name('api.scheduling.preferences.store');
    });

    // Epic 2.47 Enterprise Workforce Time, Attendance, Overtime & Labor Compliance Intelligence
    Route::prefix('time')->group(function () {
        Route::post('punch', [\App\Domains\Attendance\Http\Controllers\TimePunchController::class, 'punch'])->name('api.time.punch');

        // Corrections
        Route::get('corrections', [\App\Domains\Attendance\Http\Controllers\AttendanceCorrectionController::class, 'index'])->name('api.time.corrections.index');
        Route::post('corrections', [\App\Domains\Attendance\Http\Controllers\AttendanceCorrectionController::class, 'store'])->name('api.time.corrections.store');
        Route::post('corrections/{id}/approve', [\App\Domains\Attendance\Http\Controllers\AttendanceCorrectionController::class, 'approve'])->name('api.time.corrections.approve');
        Route::post('corrections/{id}/reject', [\App\Domains\Attendance\Http\Controllers\AttendanceCorrectionController::class, 'reject'])->name('api.time.corrections.reject');

        // Timesheet Allocations
        Route::post('allocations', [\App\Domains\Attendance\Http\Controllers\TimesheetAllocationController::class, 'store'])->name('api.time.allocations.store');
        Route::get('timesheets/{id}/allocations/summary', [\App\Domains\Attendance\Http\Controllers\TimesheetAllocationController::class, 'summary'])->name('api.time.allocations.summary');

        // Overtime Tiers
        Route::get('overtime-tiers', [\App\Domains\Attendance\Http\Controllers\OvertimeTierController::class, 'index'])->name('api.time.overtime_tiers.index');
        Route::post('overtime-tiers/calculate', [\App\Domains\Attendance\Http\Controllers\OvertimeTierController::class, 'calculate'])->name('api.time.overtime_tiers.calculate');
        Route::post('overtime-tiers/{id}/approve', [\App\Domains\Attendance\Http\Controllers\OvertimeTierController::class, 'approve'])->name('api.time.overtime_tiers.approve');
        Route::post('overtime-tiers/{id}/reject', [\App\Domains\Attendance\Http\Controllers\OvertimeTierController::class, 'reject'])->name('api.time.overtime_tiers.reject');

        // Labor Compliance Intelligence
        Route::get('compliance-checks', [\App\Domains\Attendance\Http\Controllers\LaborComplianceController::class, 'index'])->name('api.time.compliance.index');
        Route::post('compliance-checks/scan/{sessionId}', [\App\Domains\Attendance\Http\Controllers\LaborComplianceController::class, 'scanSession'])->name('api.time.compliance.scan');
        Route::post('compliance-checks/{id}/waive', [\App\Domains\Attendance\Http\Controllers\LaborComplianceController::class, 'waive'])->name('api.time.compliance.waive');

        // Payroll Exports & Reconciliations
        Route::get('payroll-exports', [\App\Domains\Attendance\Http\Controllers\PayrollExportController::class, 'index'])->name('api.time.payroll_exports.index');
        Route::post('payroll-exports', [\App\Domains\Attendance\Http\Controllers\PayrollExportController::class, 'store'])->name('api.time.payroll_exports.store');
        Route::post('payroll-exports/{id}/dispatch', [\App\Domains\Attendance\Http\Controllers\PayrollExportController::class, 'dispatchExport'])->name('api.time.payroll_exports.dispatch');
        Route::get('payroll-reconciliations', [\App\Domains\Attendance\Http\Controllers\PayrollReconciliationController::class, 'index'])->name('api.time.payroll_reconciliations.index');
        Route::post('payroll-reconciliations', [\App\Domains\Attendance\Http\Controllers\PayrollReconciliationController::class, 'store'])->name('api.time.payroll_reconciliations.store');

        // Advisory AI
        Route::get('ai/anomalies', [\App\Domains\Attendance\Http\Controllers\AdvisoryTimeAiController::class, 'anomalies'])->name('api.time.ai.anomalies');
        Route::get('ai/timesheet-variance/{timesheetId}', [\App\Domains\Attendance\Http\Controllers\AdvisoryTimeAiController::class, 'timesheetVariance'])->name('api.time.ai.timesheet_variance');
    });
});
