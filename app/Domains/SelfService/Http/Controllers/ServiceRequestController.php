<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Requests\CreateServiceRequestRequest;
use App\Domains\SelfService\Requests\ReopenServiceRequestRequest;
use App\Domains\SelfService\Services\ServiceAuthorizationService;
use App\Domains\SelfService\Services\ServiceRequestService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    public function __construct(
        protected ServiceRequestService $requestService,
        protected ServiceAuthorizationService $authService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $tenantId = $user->tenant_id ?? 'default';

        $query = HrServiceRequest::query()
            ->where('tenant_id', $tenantId)
            ->with(['service.category', 'employee', 'assignedQueue', 'assignedUser', 'slaInstance'])
            ->orderBy('created_at', 'desc');

        if ($user->employee_id && ($user->role ?? null) !== 'hr_agent' && ($user->role ?? null) !== 'super_admin') {
            $query->where('employee_id', $user->employee_id);
        }

        $requests = $query->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($requests);
        }

        return view('self-service.requests.index', compact('requests'));
    }

    public function show(Request $request, HrServiceRequest $hrServiceRequest): View|JsonResponse
    {
        $user = $request->user();
        $hrServiceRequest->load([
            'service.category',
            'employee.department',
            'assignedQueue',
            'assignedUser',
            'slaInstance.events',
            'statusHistory.changedBy',
            'documents',
            'generatedDocuments',
        ]);

        $visibleComments = $hrServiceRequest->comments()
            ->with(['user', 'employee'])
            ->when($user->employee_id && $user->employee_id === $hrServiceRequest->employee_id && ($user->role ?? null) !== 'hr_agent' && ($user->role ?? null) !== 'super_admin', function ($q) {
                $q->where('comment_type', 'public');
            })
            ->orderBy('created_at', 'asc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'request' => $hrServiceRequest,
                'comments' => $visibleComments,
            ]);
        }

        return view('self-service.requests.show', [
            'request' => $hrServiceRequest,
            'comments' => $visibleComments,
        ]);
    }

    public function store(CreateServiceRequestRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('tenant_id', $user->tenant_id)->firstOrFail();
        $service = HrServiceDefinition::findOrFail($request->input('hr_service_definition_id'));

        $req = $this->requestService->createRequest($employee, $service, $request->validated(), $user);
        $submitted = $this->requestService->submitRequest($req, $user);

        return response()->json([
            'message' => 'HR Service Request submitted successfully.',
            'data' => $submitted,
        ], 201);
    }

    public function reopen(ReopenServiceRequestRequest $request, HrServiceRequest $hrServiceRequest): JsonResponse
    {
        $user = $request->user();
        $reopened = $this->requestService->reopenRequest($hrServiceRequest, $user, $request->input('reason'));

        return response()->json([
            'message' => 'Service request reopened.',
            'data' => $reopened,
        ]);
    }

    public function resolve(Request $request, HrServiceRequest $hrServiceRequest): JsonResponse
    {
        $user = $request->user();
        $comment = $request->input('resolution_notes', 'Request resolved by HR Agent.');

        $resolved = $this->requestService->transitionStatus($hrServiceRequest, 'resolved', $user, $comment);

        return response()->json([
            'message' => 'Service request resolved.',
            'data' => $resolved,
        ]);
    }

    public function close(Request $request, HrServiceRequest $hrServiceRequest): JsonResponse
    {
        $user = $request->user();
        $closed = $this->requestService->transitionStatus($hrServiceRequest, 'closed', $user, 'Request closed.');

        return response()->json([
            'message' => 'Service request closed.',
            'data' => $closed,
        ]);
    }
}
