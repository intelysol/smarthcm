<?php

namespace App\Domains\ServiceDelivery\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\KnowledgeBaseService;
use App\Domains\SelfService\Services\ServiceCatalogService;
use App\Domains\ServiceDelivery\Services\HrCaseOrchestrationService;
use App\Domains\ServiceDelivery\Services\HrServiceDeflectionService;
use App\Domains\ServiceDelivery\Services\HrServiceDeliveryCommandCenterService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrServiceDeliveryWebController extends Controller
{
    public function __construct(
        protected HrServiceDeliveryCommandCenterService $commandCenterService,
        protected HrCaseOrchestrationService $orchestrationService,
        protected ServiceCatalogService $catalogService,
        protected KnowledgeBaseService $knowledgeService,
        protected HrServiceDeflectionService $deflectionService
    ) {}

    protected function resolveEmployee(Request $request): Employee
    {
        $user = $request->user();
        $tenantId = $request->query('tenant_id') ?? $request->header('X-Tenant-ID') ?? $user?->tenant_id;
        $employeeId = $request->query('employee_id') ?? $request->header('X-Employee-ID');

        if ($employeeId && $tenantId) {
            $emp = Employee::where('tenant_id', $tenantId)->where('id', $employeeId)->first();
            if ($emp) return $emp;
        }

        if ($user) {
            if ($user->employee) return $user->employee;
            $emp = Employee::where('user_id', $user->id)->first();
            if ($emp) return $emp;
        }

        return Employee::first() ?? abort(404, 'No employee record found.');
    }

    public function commandCenter(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;

        $metrics = $this->commandCenterService->getCommandCenterMetrics($tenantId);

        return view('portal.hr_services.command_center', compact('employee', 'metrics'));
    }

    public function cases(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;

        $filters = $request->only(['status', 'priority', 'queue_id', 'search']);
        $cases = $this->orchestrationService->getFilteredCases($tenantId, $filters, 15);
        $queues = HrServiceQueue::where('tenant_id', $tenantId)->get();

        return view('portal.hr_services.cases', compact('employee', 'cases', 'queues', 'filters'));
    }

    public function caseDetail(Request $request, string $id): View
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;

        $case = HrServiceRequest::where('tenant_id', $tenantId)->where('id', $id)->firstOrFail();
        $timeline = $this->orchestrationService->getCaseTimeline($case, true);
        $aiSummary = $this->orchestrationService->generateAiCaseSummary($case);
        $queues = HrServiceQueue::where('tenant_id', $tenantId)->get();
        $agents = \App\Models\User::where('tenant_id', $tenantId)->get();

        return view('portal.hr_services.case_detail', compact('employee', 'case', 'timeline', 'aiSummary', 'queues', 'agents'));
    }

    public function catalog(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;

        $catalog = HrServiceDefinition::where('tenant_id', $tenantId)->where('status', 'active')->with('category')->get();
        $categories = $this->catalogService->getCategories($tenantId);

        return view('portal.hr_services.catalog', compact('employee', 'catalog', 'categories'));
    }

    public function knowledge(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;

        $articles = HrKnowledgeArticle::where('tenant_id', $tenantId)->where('status', 'published')->paginate(12);
        $categories = $this->knowledgeService->getCategories($tenantId);
        $deflection = $this->deflectionService->getDeflectionAnalytics($tenantId);

        return view('portal.hr_services.knowledge', compact('employee', 'articles', 'categories', 'deflection'));
    }
}
