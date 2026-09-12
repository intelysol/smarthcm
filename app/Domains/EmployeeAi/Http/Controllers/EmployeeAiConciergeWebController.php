<?php

namespace App\Domains\EmployeeAi\Http\Controllers;

use App\Domains\EmployeeAi\Contracts\EmployeeAiConciergeInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeAiConciergeWebController extends Controller
{
    public function __construct(
        protected EmployeeAiConciergeInterface $conciergeService
    ) {}

    public function concierge(Request $request): View
    {
        $tenantId = $request->session()->get('tenant_id', 'default-tenant');
        $employeeId = $request->session()->get('employee_id', 'default-emp');

        $summary = $this->conciergeService->getMyHrSummary($tenantId, $employeeId);
        $suggestions = $this->conciergeService->getProactiveSuggestions($tenantId, $employeeId);

        return view('employee-ai.concierge', compact('summary', 'suggestions'));
    }
}
