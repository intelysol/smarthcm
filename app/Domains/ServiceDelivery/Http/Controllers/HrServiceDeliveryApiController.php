<?php

namespace App\Domains\ServiceDelivery\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrKnowledgeCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\KnowledgeBaseService;
use App\Domains\SelfService\Services\RequestAssignmentService;
use App\Domains\SelfService\Services\RequestEscalationService;
use App\Domains\SelfService\Services\ServiceCatalogService;
use App\Domains\SelfService\Services\ServiceFeedbackService;
use App\Domains\SelfService\Services\ServiceRequestService;
use App\Domains\ServiceDelivery\Services\HrCaseOrchestrationService;
use App\Domains\ServiceDelivery\Services\HrServiceDeflectionService;
use App\Domains\ServiceDelivery\Services\HrServiceDeliveryCommandCenterService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrServiceDeliveryApiController extends Controller
{
    public function __construct(
        protected HrServiceDeliveryCommandCenterService $commandCenterService,
        protected HrCaseOrchestrationService $orchestrationService,
        protected ServiceRequestService $requestService,
        protected ServiceCatalogService $catalogService,
        protected KnowledgeBaseService $knowledgeService,
        protected ServiceFeedbackService $feedbackService,
        protected RequestAssignmentService $assignmentService,
        protected RequestEscalationService $escalationService,
        protected HrServiceDeflectionService $deflectionService
    ) {}

    protected function resolveTenantId(Request $request): string
    {
        return $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id ?? 'default';
    }

    public function dashboard(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $metrics = $this->commandCenterService->getCommandCenterMetrics($tenantId);

        return response()->json($metrics);
    }

    public function cases(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $filters = $request->only(['status', 'priority', 'queue_id', 'assigned_to', 'employee_id', 'search']);
        $perPage = (int) $request->query('per_page', 15);

        $cases = $this->orchestrationService->getFilteredCases($tenantId, $filters, $perPage);

        return response()->json($cases);
    }

    public function caseDetail(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $case = HrServiceRequest::where('tenant_id', $tenantId)->where('id', $id)->firstOrFail();

        // Check horizontal & sensitive case isolation
        $user = $request->user();
        if ($case->confidentiality_level === 'restricted' && !$user?->hasPermission('hr.er.view')) {
            abort(403, 'Unauthorized access to restricted HR case.');
        }

        $timeline = $this->orchestrationService->getCaseTimeline($case, true);

        return response()->json([
            'case' => $case->load(['employee', 'service', 'assignedQueue', 'assignedUser', 'slaInstance', 'comments', 'fields']),
            'timeline' => $timeline,
        ]);
    }

    public function assign(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $case = HrServiceRequest::where('tenant_id', $tenantId)->where('id', $id)->firstOrFail();

        $request->validate([
            'queue_id' => 'nullable|uuid',
            'user_id' => 'nullable|integer',
            'reason' => 'nullable|string',
        ]);

        $assignment = $this->assignmentService->assignTo(
            $case,
            $request->input('queue_id'),
            $request->input('user_id'),
            $request->user(),
            $request->input('reason')
        );

        return response()->json(['success' => true, 'assignment' => $assignment]);
    }

    public function escalate(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $case = HrServiceRequest::where('tenant_id', $tenantId)->where('id', $id)->firstOrFail();

        $reason = $request->input('reason', 'Manual operational escalation');
        $level = (int) $request->input('level', 2);

        $escalation = $this->escalationService->escalate($case, $level, $reason, $request->user());

        return response()->json(['success' => true, 'escalation' => $escalation]);
    }

    public function resolve(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $case = HrServiceRequest::where('tenant_id', $tenantId)->where('id', $id)->firstOrFail();

        $notes = $request->input('resolution_notes');
        $resolved = $this->orchestrationService->resolveCase($case, $request->user(), $notes);

        return response()->json(['success' => true, 'case' => $resolved]);
    }

    public function close(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $case = HrServiceRequest::where('tenant_id', $tenantId)->where('id', $id)->firstOrFail();

        $closed = $this->requestService->transitionStatus(
            $case,
            ServiceRequestStatus::CLOSED->value,
            $request->user(),
            $request->input('notes', 'Case formally closed.')
        );

        return response()->json(['success' => true, 'case' => $closed]);
    }

    public function addComment(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $case = HrServiceRequest::where('tenant_id', $tenantId)->where('id', $id)->firstOrFail();

        $request->validate([
            'message' => 'required|string',
            'comment_type' => 'nullable|in:public,internal',
        ]);

        $comment = $this->orchestrationService->addComment(
            $case,
            $request->input('message'),
            $request->input('comment_type', 'public'),
            $request->user()
        );

        return response()->json(['success' => true, 'comment' => $comment], 201);
    }

    public function aiSummary(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $case = HrServiceRequest::where('tenant_id', $tenantId)->where('id', $id)->firstOrFail();

        $summary = $this->orchestrationService->generateAiCaseSummary($case);

        return response()->json($summary);
    }

    public function catalog(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $categories = $this->catalogService->getCategories($tenantId);
        $popular = $this->catalogService->getPopularServices($tenantId, 10);

        return response()->json([
            'categories' => $categories,
            'popular_services' => $popular,
        ]);
    }

    public function submitRequest(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $employeeId = $request->header('X-Employee-ID') ?? $request->user()?->employee_id ?? $request->input('employee_id');

        $employee = Employee::where('tenant_id', $tenantId)->where('id', $employeeId)->firstOrFail();
        $service = HrServiceDefinition::where('tenant_id', $tenantId)->where('id', $request->input('service_id'))->firstOrFail();

        $created = $this->requestService->createRequest($employee, $service, $request->all(), $request->user());
        $submitted = $this->requestService->submitRequest($created, $request->user());

        return response()->json([
            'success' => true,
            'case_number' => $submitted->request_number,
            'case' => $submitted,
        ], 201);
    }

    public function submitFeedback(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $case = HrServiceRequest::where('tenant_id', $tenantId)->where('id', $id)->firstOrFail();
        $employee = $case->employee;

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
        ]);

        $feedback = $this->feedbackService->submitFeedback($case, $employee, $request->all());

        return response()->json(['success' => true, 'feedback' => $feedback], 201);
    }

    public function knowledge(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $query = $request->query('q');

        if ($query) {
            $articles = $this->knowledgeService->searchArticles($tenantId, $query);
        } else {
            $articles = HrKnowledgeArticle::where('tenant_id', $tenantId)->where('status', 'published')->get();
        }

        $categories = $this->knowledgeService->getCategories($tenantId);

        return response()->json([
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    public function queues(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $queues = HrServiceQueue::where('tenant_id', $tenantId)->with(['members'])->get();

        return response()->json(['queues' => $queues]);
    }

    public function analytics(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $metrics = $this->commandCenterService->getCommandCenterMetrics($tenantId);
        $feedbackSummary = $this->feedbackService->getFeedbackSummary($tenantId);
        $deflectionSummary = $this->deflectionService->getDeflectionMetrics($tenantId);

        return response()->json([
            'command_center' => $metrics,
            'csat' => $feedbackSummary,
            'deflection' => $deflectionSummary,
        ]);
    }
}
