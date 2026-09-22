<?php

use App\Domains\TenantAdmin\Http\Controllers\TenantAdminApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [TenantAdminApiController::class, 'dashboard']);
    Route::get('/onboarding', [TenantAdminApiController::class, 'onboarding']);
    Route::post('/onboarding/step', [TenantAdminApiController::class, 'onboardingStep']);
    Route::get('/configuration', [TenantAdminApiController::class, 'listConfigurations']);
    Route::post('/configuration', [TenantAdminApiController::class, 'saveConfiguration']);
    Route::get('/configuration/effective', [TenantAdminApiController::class, 'resolveEffectiveConfiguration']);
    Route::post('/configuration/rollback', [TenantAdminApiController::class, 'rollbackConfiguration']);
    Route::get('/features', [TenantAdminApiController::class, 'features']);
    Route::put('/features/{feature}', [TenantAdminApiController::class, 'updateFeature']);
    Route::get('/setup-health', [TenantAdminApiController::class, 'setupHealth']);
    Route::get('/branding', [TenantAdminApiController::class, 'branding']);
    Route::put('/branding', [TenantAdminApiController::class, 'updateBranding']);
    Route::get('/localization', [TenantAdminApiController::class, 'localization']);
    Route::put('/localization', [TenantAdminApiController::class, 'updateLocalization']);
    Route::get('/users', [TenantAdminApiController::class, 'users']);
    Route::post('/users/{id}/status', [TenantAdminApiController::class, 'updateUserStatus']);
    Route::get('/roles', [TenantAdminApiController::class, 'roleTemplates']);
    Route::get('/delegations', [TenantAdminApiController::class, 'delegations']);
    Route::post('/delegations', [TenantAdminApiController::class, 'createDelegation']);
    Route::get('/system-health', [TenantAdminApiController::class, 'systemHealth']);
    Route::post('/imports', [TenantAdminApiController::class, 'importData']);
    Route::post('/exports', [TenantAdminApiController::class, 'exportData']);
});
