<?php

use App\Domains\Platform\Http\Controllers\AuthController;
use App\Domains\Platform\Http\Controllers\DashboardController;
use App\Domains\Platform\Http\Controllers\ExperienceController;
use App\Domains\Platform\Http\Controllers\PlatformManagementController;
use App\Domains\Platform\Http\Controllers\RoleController;
use App\Domains\Platform\Http\Controllers\TenantExperienceController;
use App\Domains\Platform\Http\Controllers\TenantManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/platform')->name('api.v1.platform.')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware(['web', 'throttle:login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:password-reset');

    Route::middleware(['web', 'auth', 'tenant'])->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [ExperienceController::class, 'updateProfile']);
        Route::put('me/password', [AuthController::class, 'changePassword']);
        Route::get('dashboard', DashboardController::class);
        Route::get('roles', [RoleController::class, 'index']);
        Route::post('roles', [RoleController::class, 'store']);
        Route::put('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);
        Route::post('roles/{role}/users', [RoleController::class, 'assign']);
        Route::get('notifications', [ExperienceController::class, 'notifications']);
        Route::put('notifications/{notification}/read', [ExperienceController::class, 'markRead']);
        Route::get('navigation/favorites', [ExperienceController::class, 'favorites']);
        Route::post('navigation/favorites', [ExperienceController::class, 'storeFavorite']);
        Route::post('navigation/recent-pages', [ExperienceController::class, 'recordRecent']);
        Route::get('audit/activities', [PlatformManagementController::class, 'activities']);
        Route::get('audit/login-history', [PlatformManagementController::class, 'loginHistory']);
        Route::get('feature-flags', [PlatformManagementController::class, 'flags']);
        Route::get('settings', [PlatformManagementController::class, 'settings']);
        Route::put('settings/{group}/{key}', [PlatformManagementController::class, 'updateSetting']);
    });
});

Route::prefix('v1/platform/tenants')->middleware(['web', 'auth'])->group(function (): void {
    Route::get('/', [TenantManagementController::class, 'index']);
    Route::post('/', [TenantManagementController::class, 'store']);
    Route::get('{tenant}', [TenantManagementController::class, 'show']);
    Route::patch('{tenant}', [TenantManagementController::class, 'update']);
    Route::post('{tenant}/suspend', [TenantManagementController::class, 'suspend']);
    Route::post('{tenant}/activate', [TenantManagementController::class, 'activate']);
    Route::get('{tenant}/usage', [TenantManagementController::class, 'usage']);
});

Route::prefix('v1')->middleware(['web', 'auth'])->group(function (): void {
    Route::get('me/tenants', [TenantExperienceController::class, 'tenants']);
    Route::post('me/tenants/switch', [TenantExperienceController::class, 'switch']);
});

Route::prefix('v1/tenant')->middleware(['web', 'auth', 'tenant'])->group(function (): void {
    Route::get('settings', [TenantExperienceController::class, 'settings']);
    Route::patch('settings', [TenantExperienceController::class, 'updateSettings']);
    Route::get('branding', [TenantExperienceController::class, 'branding']);
    Route::patch('branding', [TenantExperienceController::class, 'updateBranding']);
    Route::get('features', [TenantExperienceController::class, 'features']);
    Route::patch('features', [TenantExperienceController::class, 'updateFeatures']);
    Route::post('invitations', [TenantExperienceController::class, 'invite']);
    Route::get('invitations', [TenantExperienceController::class, 'invitations']);
    Route::post('invitations/{invitation}/resend', [TenantExperienceController::class, 'resend']);
    Route::delete('invitations/{invitation}', [TenantExperienceController::class, 'destroy']);
});
