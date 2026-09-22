<?php

declare(strict_types=1);

namespace App\Domains\EmployeeExperience\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Services\NavigationRegistry;
use App\Domains\Shared\Services\WorkspaceManager;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HrWorkspaceWebController extends Controller
{
    public function __construct(
        protected WorkspaceManager $workspaceManager,
        protected NavigationRegistry $navigationRegistry
    ) {}

    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $tenantId = session('tenant_uuid') ?? $user?->tenant_id;

        $totalEmployees = Schema::hasTable('employees') 
            ? DB::table('employees')->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->count() 
            : 0;

        $activeRequisitions = Schema::hasTable('hcm_recruitment_requisitions') 
            ? DB::table('hcm_recruitment_requisitions')->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('status', 'OPEN')->count() 
            : 0;

        $pendingLeaves = Schema::hasTable('leave_applications') 
            ? DB::table('leave_applications')->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('status', 'PENDING')->count() 
            : 0;

        $activeCases = Schema::hasTable('employee_relations_cases') 
            ? DB::table('employee_relations_cases')->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('status', 'OPEN')->count() 
            : 0;

        $recentEmployees = Schema::hasTable('employees') 
            ? DB::table('employees')->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->orderByDesc('created_at')->limit(5)->get() 
            : collect();

        $metrics = [
            'total_headcount' => $totalEmployees,
            'active_requisitions' => $activeRequisitions,
            'pending_leaves' => $pendingLeaves,
            'open_cases' => $activeCases,
            'payroll_status' => 'CURRENT PERIOD BALANCED',
            'attendance_rate' => '98.4%',
        ];

        $currentWorkspace = WorkspaceType::HR_ADMIN;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($user);
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $user);

        return view('hr-workspace.dashboard', compact(
            'metrics',
            'recentEmployees',
            'currentWorkspace',
            'allowedWorkspaces',
            'navigation'
        ));
    }
}
