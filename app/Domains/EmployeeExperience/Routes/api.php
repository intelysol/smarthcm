<?php

use App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceApiController;
use App\Domains\EmployeeExperience\Http\Controllers\ManagerWorkbenchApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('me')->middleware(['api'])->group(function () {
    Route::get('/dashboard', [EmployeeExperienceApiController::class, 'dashboard'])->name('api.me.dashboard');
    Route::get('/tasks', [EmployeeExperienceApiController::class, 'tasks'])->name('api.me.tasks');
    Route::get('/requests', [EmployeeExperienceApiController::class, 'requests'])->name('api.me.requests');
    Route::get('/quick-actions', [EmployeeExperienceApiController::class, 'quickActions'])->name('api.me.quick-actions');
    Route::get('/attendance', [EmployeeExperienceApiController::class, 'attendance'])->name('api.me.attendance');
    Route::post('/attendance/clock', [EmployeeExperienceApiController::class, 'clock'])->name('api.me.attendance.clock');
    Route::get('/leave', [EmployeeExperienceApiController::class, 'leave'])->name('api.me.leave');
    Route::post('/leave/apply', [EmployeeExperienceApiController::class, 'applyLeave'])->name('api.me.leave.apply');
    Route::get('/profile', [EmployeeExperienceApiController::class, 'profile'])->name('api.me.profile');
    Route::get('/pay', [EmployeeExperienceApiController::class, 'pay'])->name('api.me.pay');
    Route::get('/pay/payslips/{id}', [EmployeeExperienceApiController::class, 'payslipDetail'])->name('api.me.pay.payslip');
    Route::get('/documents', [EmployeeExperienceApiController::class, 'documents'])->name('api.me.documents');
    Route::post('/documents/{id}/acknowledge', [EmployeeExperienceApiController::class, 'acknowledgeDocument'])->name('api.me.documents.acknowledge');
    Route::get('/directory', [EmployeeExperienceApiController::class, 'directory'])->name('api.me.directory');
    Route::post('/ai/chat', [EmployeeExperienceApiController::class, 'aiChat'])->name('api.me.ai.chat');
});

Route::prefix('manager')->middleware(['api'])->group(function () {
    Route::get('/dashboard', [ManagerWorkbenchApiController::class, 'dashboard'])->name('api.manager.dashboard');
    Route::get('/team', [ManagerWorkbenchApiController::class, 'team'])->name('api.manager.team');
    Route::get('/approvals', [ManagerWorkbenchApiController::class, 'approvals'])->name('api.manager.approvals');
    Route::post('/approvals/{type}/{id}/action', [ManagerWorkbenchApiController::class, 'actOnApproval'])->name('api.manager.approvals.action');
    Route::get('/alerts', [ManagerWorkbenchApiController::class, 'alerts'])->name('api.manager.alerts');
    Route::get('/capacity', [ManagerWorkbenchApiController::class, 'capacity'])->name('api.manager.capacity');
});
