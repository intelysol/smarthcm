<?php

namespace App\Domains\ResponsibleAi\Http\Controllers;

use App\Domains\ResponsibleAi\Contracts\ResponsibleAiGovernanceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResponsibleAiGovernanceWebController extends Controller
{
    public function __construct(
        protected ResponsibleAiGovernanceInterface $governanceService
    ) {}

    public function dashboard(Request $request): View
    {
        $tenantId = $request->query('tenant_id', $request->session()->get('tenant_id', 'default-tenant'));
        $dashboard = $this->governanceService->getGovernanceDashboard($tenantId);

        return view('responsible-ai.dashboard', compact('dashboard'));
    }
}
