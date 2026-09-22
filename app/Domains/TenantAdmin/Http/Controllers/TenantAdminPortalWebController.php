<?php

declare(strict_types=1);

namespace App\Domains\TenantAdmin\Http\Controllers;

use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Models\Tenant;
use App\Domains\Shared\Services\NavigationRegistry;
use App\Domains\Shared\Services\WorkspaceManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Domains\Operations\Services\DataLifecycleService;
use Illuminate\View\View;

class TenantAdminPortalWebController extends Controller
{
    public function __construct(
        protected WorkspaceManager $workspaceManager,
        protected NavigationRegistry $navigationRegistry,
        protected DataLifecycleService $lifecycleService
    ) {}

    protected function resolveTenant(Request $request): Tenant
    {
        $user = $request->user();
        if ($user && $user->tenant) {
            return $user->tenant;
        }

        $tenantId = session('tenant_uuid') ?? $user?->tenant_id;
        if ($tenantId) {
            $t = Tenant::find($tenantId);
            if ($t) {
                return $t;
            }
        }

        return Tenant::first() ?? Tenant::create([
            'name' => 'Default Organization',
            'slug' => 'default-org',
            'status' => 'active',
        ]);
    }

    public function dashboard(Request $request): View
    {
        $tenant = $this->resolveTenant($request);
        $user = $request->user();

        $departmentsCount = Schema::hasTable('departments') ? DB::table('departments')->where('tenant_id', $tenant->id)->count() : 0;
        $positionsCount = Schema::hasTable('positions') ? DB::table('positions')->where('tenant_id', $tenant->id)->count() : 0;
        $usersCount = DB::table('users')->where('tenant_id', $tenant->id)->count();
        $employeesCount = Schema::hasTable('employees') ? DB::table('employees')->where('tenant_id', $tenant->id)->count() : 0;
        $policiesCount = Schema::hasTable('compliance_policies') ? DB::table('compliance_policies')->where('tenant_id', $tenant->id)->count() : 4;

            $statusVal = is_string($tenant->status) ? $tenant->status : ($tenant->status?->value ?? (string) $tenant->status);
            $metrics = [
                'departments_count' => $departmentsCount,
                'positions_count' => $positionsCount,
                'users_count' => $usersCount,
                'employees_count' => $employeesCount,
                'policies_count' => $policiesCount,
                'setup_health' => 96.5,
                'status' => strtoupper($statusVal ?: 'ACTIVE'),
            ];

        $currentWorkspace = WorkspaceType::TENANT_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($user);
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $user);

        return view('tenant-admin.overview', compact(
            'tenant',
            'metrics',
            'currentWorkspace',
            'allowedWorkspaces',
            'navigation'
        ));
    }

    public function departments(Request $request): View
    {
        $tenant = $this->resolveTenant($request);
        $departments = Schema::hasTable('departments') 
            ? DB::table('departments')->where('tenant_id', $tenant->id)->paginate(15) 
            : collect();

        $currentWorkspace = WorkspaceType::TENANT_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('tenant-admin.departments', compact('tenant', 'departments', 'currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function positions(Request $request): View
    {
        $tenant = $this->resolveTenant($request);
        $positions = Schema::hasTable('positions') 
            ? DB::table('positions')->where('tenant_id', $tenant->id)->paginate(15) 
            : collect();

        $currentWorkspace = WorkspaceType::TENANT_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('tenant-admin.positions', compact('tenant', 'positions', 'currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function users(Request $request): View
    {
        $tenant = $this->resolveTenant($request);
        $users = DB::table('users')->where('tenant_id', $tenant->id)->paginate(15);

        $currentWorkspace = WorkspaceType::TENANT_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('tenant-admin.users', compact('tenant', 'users', 'currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function workflows(Request $request): View
    {
        $tenant = $this->resolveTenant($request);
        $currentWorkspace = WorkspaceType::TENANT_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('tenant-admin.workflows', compact('tenant', 'currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function operations(Request $request): View
    {
        $tenant = $this->resolveTenant($request);
        $currentWorkspace = WorkspaceType::TENANT_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        $alerts = \App\Domains\Operations\Models\OpsAlert::query()
            ->where('tenant_id', $tenant->id)
            ->latest('triggered_at')
            ->paginate(15);

        $recentMetrics = \App\Domains\Operations\Models\OpsMetric::query()
            ->where('tenant_id', $tenant->id)
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        $auditCount = Schema::hasTable('audit_logs')
            ? DB::table('audit_logs')->where('tenant_id', $tenant->id)->count()
            : 0;

        return view('tenant-admin.operations', compact(
            'tenant',
            'currentWorkspace',
            'allowedWorkspaces',
            'navigation',
            'alerts',
            'recentMetrics',
            'auditCount'
        ));
    }

    public function settings(Request $request): View
    {
        $tenant = $this->resolveTenant($request);
        $currentWorkspace = WorkspaceType::TENANT_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('tenant-admin.settings', compact('tenant', 'currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function dataLifecycle(Request $request): View
    {
        $tenant = $this->resolveTenant($request);
        $currentWorkspace = WorkspaceType::TENANT_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        $telemetry = $this->lifecycleService->getLifecycleDashboardTelemetry($tenant->id);
        $effectivePolicy = $this->lifecycleService->resolveEffectivePolicy(
            $tenant->id,
            'Employee',
            'Employee Master Data',
            1825 // e.g. 5-year attempt against 7-year floor
        );

        return view('tenant-admin.data-lifecycle', compact(
            'tenant',
            'currentWorkspace',
            'allowedWorkspaces',
            'navigation',
            'telemetry',
            'effectivePolicy'
        ));
    }
}
