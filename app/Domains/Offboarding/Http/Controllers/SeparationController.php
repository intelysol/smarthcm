<?php

namespace App\Domains\Offboarding\Http\Controllers;

use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Services\SeparationExecutionService;
use App\Domains\Offboarding\Services\SeparationNoticePeriodService;
use App\Domains\Offboarding\Services\SeparationReversalService;
use App\Domains\Offboarding\Services\SeparationSecurityService;
use App\Domains\Offboarding\Services\SeparationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeparationController extends Controller
{
    public function __construct(
        protected SeparationService $separationService,
        protected SeparationNoticePeriodService $noticeService,
        protected SeparationExecutionService $executionService,
        protected SeparationReversalService $reversalService,
        protected SeparationSecurityService $securityService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $query = SeparationRequest::where('tenant_id', $tenantId)
            ->with(['employee.department', 'separationType']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->whereHas('separationType', fn ($q) => $q->where('category', $request->input('category')));
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'separation_type_id' => 'required|uuid',
            'proposed_last_working_day' => 'required|date',
            'effective_date' => 'nullable|date',
            'notice_start_date' => 'nullable|date',
            'reason' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
            'source' => 'nullable|string|in:self_service,hr,manager,system,er',
            'er_case_reference_id' => 'nullable|string',
            'is_garden_leave' => 'nullable|boolean',
            'garden_leave_start_date' => 'nullable|date',
            'garden_leave_end_date' => 'nullable|date',
        ]);

        $separation = $this->separationService->createRequest($request->user(), $validated);

        return response()->json($separation, 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $separation = SeparationRequest::where('id', $id)
            ->with(['employee.department', 'separationType', 'noticePeriod', 'clearances.items', 'handoverRecord.items', 'finalSettlement', 'exitInterview', 'documents', 'audits'])
            ->firstOrFail();

        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        // Mask ER case details if user is not authorized
        $separation->er_case_reference_id = $this->securityService->maskErCaseIfUnauthorized(
            $request->user(),
            $separation->er_case_reference_id
        );

        return response()->json($separation);
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        $separation = SeparationRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        $submitted = $this->separationService->submitRequest($separation, $request->user());
        return response()->json($submitted);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $separation = SeparationRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        $approved = $this->separationService->approveRequest(
            $separation,
            $request->user(),
            $request->input('approved_last_working_day'),
            $request->input('comments')
        );

        return response()->json($approved);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $separation = SeparationRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        $rejected = $this->separationService->rejectRequest($separation, $request->user(), $request->input('reason'));
        return response()->json($rejected);
    }

    public function withdraw(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $separation = SeparationRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        $withdrawn = $this->separationService->withdrawRequest($separation, $request->user(), $request->input('reason'));
        return response()->json($withdrawn);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $separation = SeparationRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        $cancelled = $this->separationService->cancelRequest($separation, $request->user(), $request->input('reason'));
        return response()->json($cancelled);
    }

    public function execute(Request $request, string $id): JsonResponse
    {
        $separation = SeparationRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        $executed = $this->executionService->execute($separation, $request->user());
        return response()->json($executed);
    }

    public function reverse(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $separation = SeparationRequest::findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        $reversal = $this->reversalService->reverseSeparation(
            $separation,
            $request->user(),
            $request->input('reason'),
            $request->input('action_type', 'reversal')
        );

        return response()->json($reversal, 201);
    }

    public function overrideNotice(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'agreed_days' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
            'is_waived' => 'nullable|boolean',
            'is_buyout' => 'nullable|boolean',
            'buyout_amount' => 'nullable|numeric|min:0',
        ]);

        $separation = SeparationRequest::with('noticePeriod')->findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $separation);

        $overridden = $this->noticeService->overrideNoticePeriod(
            $separation->noticePeriod,
            $request->user(),
            $validated
        );

        return response()->json($overridden);
    }
}
