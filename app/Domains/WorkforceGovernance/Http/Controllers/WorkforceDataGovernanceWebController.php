<?php

namespace App\Domains\WorkforceGovernance\Http\Controllers;

use App\Domains\WorkforceGovernance\Contracts\WorkforceDataGovernanceInterface;
use App\Domains\WorkforceGovernance\Services\DataAssetCatalogService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkforceDataGovernanceWebController extends Controller
{
    public function __construct(
        protected WorkforceDataGovernanceInterface $governanceService,
        protected DataAssetCatalogService $catalogService
    ) {}

    public function dashboard(Request $request): View
    {
        $tenantId = $request->session()->get('tenant_id', 'default-tenant');

        $dashboard = $this->governanceService->getGovernanceDashboard($tenantId);
        $assets = $this->catalogService->listAssets($tenantId);

        return view('workforce-governance.dashboard', compact('dashboard', 'assets'));
    }
}
