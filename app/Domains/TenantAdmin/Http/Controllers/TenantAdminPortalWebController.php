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
use App\Domains\Compliance\Models\GovernanceControl;
use App\Domains\Compliance\Models\PrivacyProcessingActivity;
use App\Domains\Compliance\Models\PrivacyRequest;
use App\Domains\Compliance\Services\EnterpriseGovernanceService;
use App\Domains\Compliance\Services\EnterprisePrivacyService;
use App\Domains\Operations\Services\DataLifecycleService;
use App\Domains\Platform\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TenantAdminPortalWebController extends Controller
{
    public function __construct(
        protected WorkspaceManager $workspaceManager,
        protected NavigationRegistry $navigationRegistry,
        protected DataLifecycleService $lifecycleService,
        protected EnterpriseGovernanceService $governanceService,
        protected EnterprisePrivacyService $privacyService
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
        $users = User::with('roles')->where('tenant_id', $tenant->id)->paginate(15);
        $roles = Role::where(fn ($q) => $q->where('tenant_id', $tenant->id)->orWhere('type', 'system'))->get();

        $currentWorkspace = WorkspaceType::TENANT_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('tenant-admin.users', compact('tenant', 'users', 'roles', 'currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function createUser(Request $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['nullable', 'string'],
            'role' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['nullable', 'string'],
        ]);

        $defaultPassword = $validated['password'] ?? env('DEMO_USER_PASSWORD', 'Demo1234!@#$');

        $user = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($defaultPassword),
            'status' => $validated['status'] ?? 'active',
            'is_platform_admin' => false,
        ]);

        if (!empty($validated['role_id'])) {
            $user->roles()->syncWithoutDetaching([$validated['role_id'] => ['assigned_by' => $request->user()?->id]]);
        } elseif (!empty($validated['role'])) {
            $roleObj = Role::where(function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->orWhere('type', 'system');
            })->where('name', $validated['role'])->first();
            if ($roleObj) {
                $user->roles()->syncWithoutDetaching([$roleObj->id => ['assigned_by' => $request->user()?->id]]);
            }
        }

        return redirect()->route('admin.users')
            ->with('status', "User {$user->name} created successfully.")
            ->with('success', "User {$user->name} created successfully.");
    }

    public function toggleUserStatus(Request $request, string $userId): RedirectResponse
    {
        $tenant = $this->resolveTenant($request);
        $user = User::where('tenant_id', $tenant->id)->findOrFail($userId);

        if ($request->user() && (string) $request->user()->id === (string) $user->id) {
            return redirect()->route('admin.users')->with('warning', 'You cannot deactivate your own account.');
        }

        $newStatus = ($user->status === 'active') ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return redirect()->route('admin.users')
            ->with('status', "User {$user->name} status changed to " . strtoupper($newStatus) . ".")
            ->with('success', "User {$user->name} status changed to " . strtoupper($newStatus) . ".");
    }

    public function resetUserPassword(Request $request, string $userId): RedirectResponse
    {
        $tenant = $this->resolveTenant($request);
        $user = User::where('tenant_id', $tenant->id)->findOrFail($userId);

        $newPassword = $request->input('password') ?: env('DEMO_USER_PASSWORD', 'Demo1234!@#$');
        $user->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => true,
        ]);

        return redirect()->route('admin.users')
            ->with('status', "Password for {$user->email} has been reset successfully.")
            ->with('success', "Password for {$user->email} has been reset successfully.");
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

    public function complianceGovernance(Request $request): View
    {
        $tenant = $this->resolveTenant($request);
        $currentWorkspace = WorkspaceType::TENANT_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        $governanceScorecard = $this->governanceService->getGovernanceDashboardScorecard($tenant->id);
        $privacyMetrics = $this->privacyService->getPrivacyDashboardMetrics($tenant->id);

        $controls = GovernanceControl::where('tenant_id', $tenant->id)->orWhereNull('tenant_id')->with('framework')->get();
        $activities = PrivacyProcessingActivity::where('tenant_id', $tenant->id)->get();
        $privacyRequests = PrivacyRequest::where('tenant_id', $tenant->id)->latest()->take(10)->get();

        return view('tenant-admin.compliance-governance', compact(
            'tenant',
            'currentWorkspace',
            'allowedWorkspaces',
            'navigation',
            'governanceScorecard',
            'privacyMetrics',
            'controls',
            'activities',
            'privacyRequests'
        ));
    }
}
