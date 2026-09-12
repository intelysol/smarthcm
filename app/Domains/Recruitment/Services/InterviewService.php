<?php

namespace App\Domains\Recruitment\Services;

use App\Domains\Recruitment\Enums\InterviewRecommendation;
use App\Domains\Recruitment\Enums\InterviewType;
use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentInterview;
use App\Domains\Recruitment\Models\HcmRecruitmentInterviewEvaluation;
use App\Domains\Recruitment\Models\HcmRecruitmentInterviewParticipant;
use Illuminate\Support\Facades\DB;

class InterviewService
{
    public function scheduleInterview(HcmRecruitmentApplication $application, array $data, array $interviewerIds = []): HcmRecruitmentInterview
    {
        return DB::transaction(function () use ($application, $data, $interviewerIds) {
            $interview = HcmRecruitmentInterview::create([
                'tenant_id' => $application->tenant_id,
                'application_id' => $application->id,
                'title' => $data['title'],
                'interview_type' => $data['interview_type'] ?? InterviewType::TECHNICAL->value,
                'scheduled_at' => $data['scheduled_at'],
                'duration_minutes' => $data['duration_minutes'] ?? 45,
                'location' => $data['location'] ?? null,
                'meeting_url' => $data['meeting_url'] ?? null,
                'status' => 'scheduled',
                'internal_notes' => $data['internal_notes'] ?? null,
            ]);

            foreach ($interviewerIds as $id) {
                HcmRecruitmentInterviewParticipant::create([
                    'tenant_id' => $interview->tenant_id,
                    'interview_id' => $interview->id,
                    'interviewer_id' => $id,
                    'role' => 'interviewer',
                ]);
            }

            return $interview;
        });
    }

    public function submitEvaluation(HcmRecruitmentInterview $interview, int $evaluatorId, array $scorecard): HcmRecruitmentInterviewEvaluation
    {
        $tech = $scorecard['technical_rating'] ?? 0.0;
        $comm = $scorecard['communication_rating'] ?? 0.0;
        $problem = $scorecard['problem_solving_rating'] ?? 0.0;
        $overall = round(($tech + $comm + $problem) / 3, 1);

        return HcmRecruitmentInterviewEvaluation::updateOrCreate(
            [
                'interview_id' => $interview->id,
                'evaluator_id' => $evaluatorId,
            ],
            [
                'tenant_id' => $interview->tenant_id,
                'technical_rating' => $tech,
                'communication_rating' => $comm,
                'problem_solving_rating' => $problem,
                'overall_score' => $overall,
                'recommendation' => $scorecard['recommendation'] ?? InterviewRecommendation::YES->value,
                'strengths' => $scorecard['strengths'] ?? null,
                'concerns' => $scorecard['concerns'] ?? null,
                'confidential_notes' => $scorecard['confidential_notes'] ?? null,
                'submitted_at' => now(),
            ]
        );
    }

    public function getInterviewSummary(HcmRecruitmentInterview $interview): array
    {
        $evaluations = $interview->evaluations;
        $count = $evaluations->count();
        $avgScore = $count > 0 ? round($evaluations->avg('overall_score'), 1) : 0.0;

        $recommendations = $evaluations->pluck('recommendation')->countBy()->toArray();

        return [
            'interview_id' => $interview->id,
            'title' => $interview->title,
            'evaluations_count' => $count,
            'average_score' => $avgScore,
            'recommendations_breakdown' => $recommendations,
            'is_completed' => $count >= $interview->participants()->count(),
        ];
    }
}
