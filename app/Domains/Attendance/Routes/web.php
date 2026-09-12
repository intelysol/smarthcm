<?php

use App\Domains\Attendance\Http\Controllers\AttendanceUIController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('hcm/attendance')->group(function () {
        Route::get('/', [AttendanceUIController::class, 'dashboard'])->name('hcm.attendance.dashboard');
        Route::get('roster-board', [AttendanceUIController::class, 'rosterBoard'])->name('hcm.attendance.roster_board');
        Route::get('shifts', [AttendanceUIController::class, 'shifts'])->name('hcm.attendance.shifts');
        Route::get('calendars', [AttendanceUIController::class, 'calendars'])->name('hcm.attendance.calendars');
        Route::get('devices', [AttendanceUIController::class, 'devices'])->name('hcm.attendance.devices');
        Route::get('exceptions', [AttendanceUIController::class, 'exceptions'])->name('hcm.attendance.exceptions');
        Route::get('timesheets', [AttendanceUIController::class, 'timesheets'])->name('hcm.attendance.timesheets');
        Route::get('periods', [AttendanceUIController::class, 'periods'])->name('hcm.attendance.periods');
    });

    Route::get('hcm/me/attendance', [AttendanceUIController::class, 'employeePortal'])->name('hcm.attendance.me');
    Route::get('hcm/manager/attendance', [AttendanceUIController::class, 'managerPortal'])->name('hcm.attendance.manager');
});
