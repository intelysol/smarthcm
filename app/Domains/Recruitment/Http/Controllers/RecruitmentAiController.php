<?php

namespace App\Domains\Recruitment\Http\Controllers;

use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\RecruitmentAiService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecruitmentAiController extends Controller
{
    public function __construct(protected RecruitmentAiService $aiService)
    {
    }

    public function match(Request $request): JsonResponse
    {
        $request->validate([
            'candidate_id' => 'required|uuid',
            'requisition_id' => 'required|uuid',
        ]);

        $candidate = HcmRecruitmentCandidate::with('profile')->findOrFail($request->input('candidate_id'));
        $requisition = HcmRecruitmentRequisition::with('template')->findOrFail($request->input('requisition_id'));

        $matchResult = $this->aiService->matchCandidateToRequisition($candidate, $requisition);
        return response()->json($matchResult);
    }

    public function generateJobDescription(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'skills' => 'required|array',
            'department' => 'nullable|string|max:100',
        ]);

        $draft = $this->aiService->generateJobDescriptionDraft(
            $request->input('title'),
            $request->input('skills'),
            $request->input('department', 'General')
        );

        return response()->json($draft);
    }

    public function query(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|max:1000']);
        $tenantId = $request->user()->tenant_id;

        $answer = $this->aiService->answerRecruitmentInquiry($tenantId, $request->input('query'));
        return response()->json($answer);
    }
}
