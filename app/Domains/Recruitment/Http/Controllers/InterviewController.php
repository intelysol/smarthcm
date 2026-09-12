<?php

namespace App\Domains\Recruitment\Http\Controllers;

use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentInterview;
use App\Domains\Recruitment\Services\InterviewService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function __construct(protected InterviewService $interviewService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => 'required|uuid',
            'title' => 'required|string|max:150',
            'interview_type' => 'nullable|string|max:40',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:15|max:240',
            'location' => 'nullable|string|max:100',
            'meeting_url' => 'nullable|string|max:255',
            'interviewers' => 'nullable|array',
        ]);

        $application = HcmRecruitmentApplication::findOrFail($validated['application_id']);
        $interview = $this->interviewService->scheduleInterview(
            $application,
            $validated,
            $validated['interviewers'] ?? []
        );

        return response()->json($interview, 201);
    }

    public function evaluate(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'technical_rating' => 'required|numeric|min:0|max:5',
            'communication_rating' => 'required|numeric|min:0|max:5',
            'problem_solving_rating' => 'required|numeric|min:0|max:5',
            'recommendation' => 'required|string|in:strong_yes,yes,neutral,no,strong_no',
            'strengths' => 'nullable|string',
            'concerns' => 'nullable|string',
            'confidential_notes' => 'nullable|string',
        ]);

        $interview = HcmRecruitmentInterview::findOrFail($id);
        $evaluation = $this->interviewService->submitEvaluation(
            $interview,
            $request->user()->id,
            $validated
        );

        return response()->json($evaluation);
    }

    public function summary(string $id): JsonResponse
    {
        $interview = HcmRecruitmentInterview::with(['evaluations', 'participants'])->findOrFail($id);
        return response()->json($this->interviewService->getInterviewSummary($interview));
    }
}
