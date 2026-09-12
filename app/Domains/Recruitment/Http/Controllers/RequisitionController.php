<?php

namespace App\Domains\Recruitment\Http\Controllers;

use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\JobRequisitionService;
use App\Domains\Recruitment\Services\RecruitmentSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequisitionController extends Controller
{
    public function __construct(
        protected JobRequisitionService $requisitionService,
        protected RecruitmentSecurityService $securityService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $query = HcmRecruitmentRequisition::where('tenant_id', $tenantId)
            ->with(['position', 'department', 'template']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'position_id' => 'nullable|uuid',
            'job_template_id' => 'nullable|uuid',
            'department_id' => 'nullable|uuid',
            'employment_type' => 'nullable|string|max:30',
            'openings' => 'nullable|integer|min:1',
            'priority' => 'nullable|string|max:20',
            'reason' => 'nullable|string|max:40',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'budget_amount' => 'nullable|numeric|min:0',
            'target_start_date' => 'nullable|date',
            'workforce_plan_id' => 'nullable|uuid',
            'position_plan_id' => 'nullable|uuid',
            'hiring_plan_id' => 'nullable|uuid',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $requisition = $this->requisitionService->createRequisition($validated, $request->user()->id);

        return response()->json($requisition, 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $requisition = HcmRecruitmentRequisition::where('id', $id)->with(['position', 'department', 'approvals', 'postings'])->firstOrFail();
        $this->securityService->authorizeRequisitionAccess($request->user(), $requisition);

        return response()->json($requisition);
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        $requisition = HcmRecruitmentRequisition::where('id', $id)->firstOrFail();
        $this->securityService->authorizeRequisitionAccess($request->user(), $requisition);

        $submitted = $this->requisitionService->submitRequisition($requisition, $request->user()->id);
        return response()->json($submitted);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $requisition = HcmRecruitmentRequisition::where('id', $id)->firstOrFail();
        $this->securityService->authorizeRequisitionAccess($request->user(), $requisition);

        $approved = $this->requisitionService->approveRequisition($requisition, $request->user()->id, $request->input('comments'));
        return response()->json($approved);
    }
}
