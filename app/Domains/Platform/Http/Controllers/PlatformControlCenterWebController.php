<?php

declare(strict_types=1);

namespace App\Domains\Platform\Http\Controllers;

use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Models\Tenant;
use App\Domains\Shared\Services\NavigationRegistry;
use App\Domains\Shared\Services\WorkspaceManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PlatformControlCenterWebController extends Controller
{
    public function __construct(
        protected WorkspaceManager $workspaceManager,
        protected NavigationRegistry $navigationRegistry
    ) {}

    public function index(Request $request): View
    {
        $tenantsCount = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();
        
        $totalUsers = DB::table('users')->count();
        $totalEmployees = Schema::hasTable('employees') ? DB::table('employees')->count() : 0;
        
        $subscriptionCount = Schema::hasTable('billing_subscriptions') ? DB::table('billing_subscriptions')->where('status', 'active')->count() : 0;
        $activePlans = Schema::hasTable('billing_plans') ? DB::table('billing_plans')->count() : 0;

        $recentTenants = Tenant::orderByDesc('created_at')->limit(5)->get();

        $metrics = [
            'tenants_count' => $tenantsCount,
            'active_tenants' => $activeTenants,
            'suspended_tenants' => $suspendedTenants,
            'total_users' => $totalUsers,
            'total_employees' => $totalEmployees,
            'active_subscriptions' => $subscriptionCount,
            'active_plans' => $activePlans,
            'platform_version' => '2.69-ENTERPRISE',
            'health_status' => 'OPERATIONAL',
            'uptime' => '99.98%',
        ];

        $currentWorkspace = WorkspaceType::PLATFORM_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('platform.control-center', compact(
            'metrics',
            'recentTenants',
            'currentWorkspace',
            'allowedWorkspaces',
            'navigation'
        ));
    }

    public function tenants(Request $request): View
    {
        $tenants = Tenant::orderByDesc('created_at')->paginate(15);
        $currentWorkspace = WorkspaceType::PLATFORM_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('platform.tenants', compact('tenants', 'currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function billing(Request $request): View
    {
        $currentWorkspace = WorkspaceType::PLATFORM_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('platform.billing', compact('currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function security(Request $request): View
    {
        $currentWorkspace = WorkspaceType::PLATFORM_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('platform.security', compact('currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function aiGovernance(Request $request): View
    {
        $currentWorkspace = WorkspaceType::PLATFORM_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('platform.ai-governance', compact('currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function settings(Request $request): View
    {
        $currentWorkspace = WorkspaceType::PLATFORM_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($request->user());
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $request->user());

        return view('platform.settings', compact('currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }
}
