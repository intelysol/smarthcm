<?php

namespace App\Domains\Lifecycle\Http\Controllers;

use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Services\PersonnelActionSecurityService;
use App\Domains\Lifecycle\Services\PersonnelActionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelActionController extends Controller
{
    public function __construct(
        protected PersonnelActionService $actionService,
        protected PersonnelActionSecurityService $securityService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $query = PersonnelActionRequest::where('tenant_id', $tenantId)
            ->with(['employee.department', 'actionType']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->whereHas('actionType', fn ($q) => $q->where('category', $request->input('category')));
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'action_type_id' => 'required|uuid',
            'effective_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'changes' => 'required|array|min:1',
            'changes.*.field_name' => 'required|string',
            'changes.*.entity_type' => 'nullable|string',
            'changes.*.old_value' => 'nullable',
            'changes.*.new_value' => 'nullable',
            'changes.*.old_value_label' => 'nullable|string',
            'changes.*.new_value_label' => 'nullable|string',
        ]);

        $action = $this->actionService->createRequest($request->user(), $validated);

        return response()->json($action, 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $action = PersonnelActionRequest::where('id', $id)
            ->with(['employee.department', 'actionType', 'changes', 'impacts', 'acknowledgement', 'documents', 'audits'])
            ->firstOrFail();

        $this->securityService->authorizeRequestAccess($request->user(), $action);

        return response()->json($action);
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        $action = PersonnelActionRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $action);

        $submitted = $this->actionService->submitRequest($action, $request->user());
        return response()->json($submitted);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $action = PersonnelActionRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $action);

        $approved = $this->actionService->approveRequest($action, $request->user(), $request->input('comments'));
        return response()->json($approved);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $action = PersonnelActionRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $action);

        $rejected = $this->actionService->rejectRequest($action, $request->user(), $request->input('reason'));
        return response()->json($rejected);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $action = PersonnelActionRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $action);

        $cancelled = $this->actionService->cancelRequest($action, $request->user(), $request->input('reason'));
        return response()->json($cancelled);
    }
}
