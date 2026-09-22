<?php

declare(strict_types=1);

use App\Domains\TenantAdmin\Http\Controllers\TenantAdminPortalWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'workspace:tenant_admin'])->prefix('admin')->group(function () {
    Route::get('/', [TenantAdminPortalWebController::class, 'dashboard'])->name('admin.index');
    Route::get('/dashboard', [TenantAdminPortalWebController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/departments', [TenantAdminPortalWebController::class, 'departments'])->name('admin.departments');
    Route::get('/positions', [TenantAdminPortalWebController::class, 'positions'])->name('admin.positions');
    Route::get('/users', [TenantAdminPortalWebController::class, 'users'])->name('admin.users');
    Route::get('/workflows', [TenantAdminPortalWebController::class, 'workflows'])->name('admin.workflows');
    Route::get('/operations', [TenantAdminPortalWebController::class, 'operations'])->name('admin.operations');
    Route::get('/data-lifecycle', [TenantAdminPortalWebController::class, 'dataLifecycle'])->name('admin.data-lifecycle');
    Route::get('/compliance-governance', [TenantAdminPortalWebController::class, 'complianceGovernance'])->name('admin.compliance-governance');
    Route::get('/settings', [TenantAdminPortalWebController::class, 'settings'])->name('admin.settings');
});
