<?php

namespace App\Domains\AiOperations\Http\Controllers;

use App\Domains\AiOperations\Contracts\AiOperationsInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiOperationsWebController extends Controller
{
    public function __construct(
        protected AiOperationsInterface $operationsService
    ) {}

    public function dashboard(Request $request): View
    {
        $tenantId = $request->query('tenant_id', $request->session()->get('tenant_id', 'default-tenant'));
        $dashboard = $this->operationsService->getOperationsDashboard($tenantId);
        $benchmarks = $this->operationsService->compareModelPerformance($tenantId);
        $budget = $this->operationsService->checkBudgetStatus($tenantId);

        return view('ai-operations.dashboard', compact('dashboard', 'benchmarks', 'budget'));
    }
}
