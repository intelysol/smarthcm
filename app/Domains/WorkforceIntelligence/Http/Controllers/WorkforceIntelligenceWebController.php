<?php

namespace App\Domains\WorkforceIntelligence\Http\Controllers;

use App\Domains\WorkforceIntelligence\Contracts\WorkforceCommandCenterInterface;
use App\Domains\WorkforceIntelligence\Services\WorkforceCommandCenterService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkforceIntelligenceWebController extends Controller
{
    public function __construct(
        protected WorkforceCommandCenterService $commandCenterService,
        protected WorkforceCommandCenterInterface $commandCenter
    ) {}

    public function dashboard(Request $request): View
    {
        $tenantId = $request->session()->get('tenant_id', 'default-tenant');
        $persona = $request->query('persona', 'EXECUTIVE');

        $layout = $this->commandCenterService->getDashboardLayout($tenantId, $persona);
        $scorecard = $this->commandCenter->getExecutiveScorecard($tenantId);
        $healthIndex = $this->commandCenter->getWorkforceHealthIndex($tenantId);
        $pulse = $this->commandCenter->getWorkforcePulse($tenantId);
        $risks = $this->commandCenter->getConsolidatedRisks($tenantId);
        $alerts = $this->commandCenter->getPrioritizedAlerts($tenantId);
        $decisions = $this->commandCenter->getDecisionQueue($tenantId);

        return view('workforce-intelligence.dashboard', compact(
            'layout',
            'scorecard',
            'healthIndex',
            'pulse',
            'risks',
            'alerts',
            'decisions',
            'persona'
        ));
    }
}
