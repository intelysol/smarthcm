<?php

namespace App\Domains\Recruitment\Http\Controllers;

use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentApplicationStage;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\ApplicationService;
use App\Domains\Recruitment\Services\HiringHandoffService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationService $applicationService,
        protected HiringHandoffService $hiringHandoffService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $query = HcmRecruitmentApplication::where('tenant_id', $tenantId)
            ->with(['candidate', 'requisition', 'stage']);

        if ($request->filled('requisition_id')) {
            $query->where('requisition_id', $request->input('requisition_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'candidate_id' => 'required|uuid',
            'requisition_id' => 'required|uuid',
            'cover_letter' => 'nullable|string',
        ]);

        $candidate = HcmRecruitmentCandidate::findOrFail($validated['candidate_id']);
        $requisition = HcmRecruitmentRequisition::findOrFail($validated['requisition_id']);

        $application = $this->applicationService->apply($candidate, $requisition, array_merge($validated, ['actor_id' => $request->user()->id]));
        return response()->json($application, 201);
    }

    public function show(string $id): JsonResponse
    {
        $application = HcmRecruitmentApplication::where('id', $id)
            ->with(['candidate.profile', 'requisition', 'stage', 'activities', 'screenings', 'interviews.evaluations', 'offer', 'backgroundChecks', 'hiringDecision'])
            ->firstOrFail();

        return response()->json($application);
    }

    public function changeStage(Request $request, string $id): JsonResponse
    {
        $request->validate(['stage_id' => 'required|uuid']);
        $application = HcmRecruitmentApplication::findOrFail($id);
        $stage = HcmRecruitmentApplicationStage::findOrFail($request->input('stage_id'));

        $updated = $this->applicationService->transitionStage($application, $stage, $request->user()->id, $request->input('notes'));
        return response()->json($updated);
    }

    public function screen(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'skills_match' => 'boolean',
            'experience_match' => 'boolean',
            'education_match' => 'boolean',
            'salary_match' => 'boolean',
            'result' => 'required|string|in:passed,failed,on_hold',
            'feedback' => 'nullable|string',
        ]);

        $application = HcmRecruitmentApplication::findOrFail($id);
        $screening = $this->applicationService->screenApplication($application, $validated, $request->user()->id);

        return response()->json($screening);
    }

    public function hire(Request $request, string $id): JsonResponse
    {
        $application = HcmRecruitmentApplication::findOrFail($id);
        $decision = $this->hiringHandoffService->processHire(
            $application,
            $request->user()->id,
            $request->input('rationale')
        );

        return response()->json($decision);
    }
}
