<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/portal');
});

// Production Health Check & Operational Endpoints
Route::get('/health', [\App\Domains\Platform\Http\Controllers\HealthCheckController::class, 'health']);
Route::get('/health/live', [\App\Domains\Platform\Http\Controllers\HealthCheckController::class, 'live']);
Route::get('/health/ready', [\App\Domains\Platform\Http\Controllers\HealthCheckController::class, 'ready']);
Route::get('/health/dependencies', [\App\Domains\Platform\Http\Controllers\HealthCheckController::class, 'dependencies']);
Route::get('/health/services', [\App\Domains\Platform\Http\Controllers\HealthCheckController::class, 'services']);
Route::get('/operations/system-health', [\App\Domains\Platform\Http\Controllers\SystemHealthWebController::class, 'index'])->name('operations.system-health');

// Authentication & Dashboard Routes
Route::get('/login', [\App\Domains\Platform\Http\Controllers\LoginWebController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Domains\Platform\Http\Controllers\LoginWebController::class, 'login'])->name('login.submit');
Route::post('/logout', [\App\Domains\Platform\Http\Controllers\LoginWebController::class, 'logout'])->name('logout');
Route::get('/dashboard', function () {
    return redirect('/portal');
})->name('dashboard');

// Workspace Context & Switching
Route::middleware(['web', 'auth'])->prefix('workspace')->group(function () {
    Route::post('/switch', [\App\Domains\Shared\Http\Controllers\WorkspaceSwitcherController::class, 'switch'])->name('workspace.switch');
    Route::get('/status', [\App\Domains\Shared\Http\Controllers\WorkspaceSwitcherController::class, 'status'])->name('workspace.status');
});

// 1. Platform / Super Admin Control Center
Route::middleware(['web', 'auth', 'workspace:platform'])->prefix('platform')->group(function () {
    Route::get('/', [\App\Domains\Platform\Http\Controllers\PlatformControlCenterWebController::class, 'index'])->name('platform.index');
    Route::get('/control-center', [\App\Domains\Platform\Http\Controllers\PlatformControlCenterWebController::class, 'index'])->name('platform.control-center');
    Route::get('/tenants', [\App\Domains\Platform\Http\Controllers\PlatformControlCenterWebController::class, 'tenants'])->name('platform.tenants');
    Route::get('/billing', [\App\Domains\Platform\Http\Controllers\PlatformControlCenterWebController::class, 'billing'])->name('platform.billing');
    Route::get('/integrations', [\App\Domains\Platform\Http\Controllers\PlatformControlCenterWebController::class, 'index'])->name('platform.integrations');
    Route::get('/security', [\App\Domains\Platform\Http\Controllers\PlatformControlCenterWebController::class, 'security'])->name('platform.security');
    Route::get('/ai-governance', [\App\Domains\Platform\Http\Controllers\PlatformControlCenterWebController::class, 'aiGovernance'])->name('platform.ai-governance');
    Route::get('/settings', [\App\Domains\Platform\Http\Controllers\PlatformControlCenterWebController::class, 'settings'])->name('platform.settings');
});

// 3. HR Operations Workspace
Route::middleware(['web', 'auth', 'workspace:hr'])->prefix('hr')->group(function () {
    Route::get('/', [\App\Domains\EmployeeExperience\Http\Controllers\HrWorkspaceWebController::class, 'dashboard'])->name('hr.index');
    Route::get('/dashboard', [\App\Domains\EmployeeExperience\Http\Controllers\HrWorkspaceWebController::class, 'dashboard'])->name('hr.dashboard');
});

// 4. Manager Workspace
Route::middleware(['web', 'auth', 'workspace:manager'])->prefix('manager')->group(function () {
    Route::get('/', [\App\Domains\EmployeeExperience\Http\Controllers\ManagerWorkspaceWebController::class, 'workbench'])->name('manager.index');
    Route::get('/workbench', [\App\Domains\EmployeeExperience\Http\Controllers\ManagerWorkspaceWebController::class, 'workbench'])->name('manager.workbench');
    Route::get('/members', [\App\Domains\EmployeeExperience\Http\Controllers\ManagerWorkspaceWebController::class, 'members'])->name('manager.members');
    Route::get('/performance', [\App\Domains\EmployeeExperience\Http\Controllers\ManagerWorkspaceWebController::class, 'performance'])->name('manager.performance');
    Route::get('/analytics', [\App\Domains\EmployeeExperience\Http\Controllers\ManagerWorkspaceWebController::class, 'analytics'])->name('manager.analytics');
});

// 5. Employee Self-Service Application
Route::middleware(['web', 'auth', 'workspace:employee'])->prefix('employee')->group(function () {
    Route::get('/', [\App\Domains\EmployeeExperience\Http\Controllers\EmployeeWorkspaceWebController::class, 'home'])->name('employee.index');
    Route::get('/home', [\App\Domains\EmployeeExperience\Http\Controllers\EmployeeWorkspaceWebController::class, 'home'])->name('employee.home');
});

// 6. Executive Workspace
Route::middleware(['web', 'auth', 'workspace:executive'])->prefix('executive')->group(function () {
    Route::get('/', [\App\Domains\Analytics\Http\Controllers\ExecutiveWorkspaceWebController::class, 'overview'])->name('executive.index');
    Route::get('/overview', [\App\Domains\Analytics\Http\Controllers\ExecutiveWorkspaceWebController::class, 'overview'])->name('executive.overview');
    Route::get('/costs', [\App\Domains\Analytics\Http\Controllers\ExecutiveWorkspaceWebController::class, 'costs'])->name('executive.costs');
});

// 7. Operations Workspace
Route::middleware(['web', 'auth', 'workspace:operations'])->prefix('operations')->group(function () {
    Route::get('/', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'dashboard'])->name('operations.index');
    Route::get('/dashboard', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'dashboard'])->name('operations.dashboard');
    Route::get('/incidents', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'incidents'])->name('operations.incidents');
    Route::get('/alerts', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'alerts'])->name('operations.alerts');
    Route::get('/queues', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'queues'])->name('operations.queues');
    Route::get('/backups', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'backups'])->name('operations.backups');
    Route::get('/recovery', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'recovery'])->name('operations.recovery');
    Route::get('/performance', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'performance'])->name('operations.performance');
    Route::get('/capacity', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'capacity'])->name('operations.capacity');
    Route::get('/data-lifecycle', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'dataLifecycle'])->name('operations.data-lifecycle');
    Route::get('/compliance', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'compliance'])->name('operations.compliance');
    Route::get('/logs', [\App\Domains\Platform\Http\Controllers\OperationalWorkspaceWebController::class, 'logs'])->name('operations.logs');
});


require base_path('app/Domains/Learning/Routes/web.php');
require base_path('app/Domains/Career/Routes/web.php');
require base_path('app/Domains/Engagement/Routes/web.php');
require base_path('app/Domains/EmployeeRelations/Routes/web.php');
require base_path('app/Domains/Attendance/Routes/web.php');
require base_path('app/Domains/Payroll/Routes/web.php');
require base_path('app/Domains/Benefits/Routes/web.php');
require base_path('app/Domains/Expenses/Routes/web.php');
require base_path('app/Domains/SelfService/Routes/web.php');
require base_path('app/Domains/Analytics/Routes/web.php');
require base_path('app/Domains/WorkforcePlanning/Routes/web.php');
require base_path('app/Domains/Recruitment/Routes/web.php');
require base_path('app/Domains/Onboarding/Routes/web.php');
require base_path('app/Domains/Lifecycle/Routes/web.php');
require base_path('app/Domains/Offboarding/Routes/web.php');
require base_path('app/Domains/EmployeeDocuments/Routes/web.php');
require base_path('app/Domains/EmployeeProfile/Routes/web.php');
require base_path('app/Domains/PersonalData/Routes/web.php');
require base_path('app/Domains/Compliance/Routes/web.php');
require base_path('app/Domains/HealthSafety/Routes/web.php');
require base_path('app/Domains/Performance/Routes/web.php');
require base_path('app/Domains/Mobility/Routes/web.php');
require base_path('app/Domains/WorkforceAdmin/Routes/web.php');
require base_path('app/Domains/WorkforceProductivity/Routes/web.php');
require base_path('app/Domains/WorkforceOptimization/Routes/web.php');
require base_path('app/Domains/WorkforceIntelligence/Routes/web.php');
require base_path('app/Domains/WorkforceGovernance/Routes/web.php');
require base_path('app/Domains/EmployeeAi/Routes/web.php');
require base_path('app/Domains/ResponsibleAi/Routes/web.php');
require base_path('app/Domains/AiOperations/Routes/web.php');
require base_path('app/Domains/TenantAdmin/Routes/web.php');
require base_path('app/Domains/EmployeeExperience/Routes/web.php');
require base_path('app/Domains/ServiceDelivery/Routes/web.php');
require base_path('app/Domains/Integration/Routes/web.php');
require base_path('routes/billing_web.php');
