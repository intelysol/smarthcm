<?php

namespace App\Domains\TenantAdmin\Http\Controllers;

use App\Domains\TenantAdmin\Contracts\TenantAdministrationInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantAdminWebController extends Controller
{
    public function __construct(
        protected TenantAdministrationInterface $adminService
    ) {}

    public function dashboard(Request $request): View
    {
        $tenantId = $request->query('tenant_id', $request->session()->get('tenant_id', 'default-tenant'));
        $dashboard = $this->adminService->getAdminDashboard($tenantId);

        return view('admin.dashboard', compact('dashboard'));
    }
}
