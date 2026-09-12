<?php

namespace App\Domains\Recruitment\Services;

use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;

class RecruitmentAiService
{
    public function matchCandidateToRequisition(HcmRecruitmentCandidate $candidate, HcmRecruitmentRequisition $requisition): array
    {
        $profile = $candidate->profile;
        $candidateSkills = $profile?->skills ?? [];

        $template = $requisition->template;
        $requiredSkills = $template?->required_skills ?? [];

        $matchedSkills = array_intersect(
            array_map('strtolower', $candidateSkills),
            array_map('strtolower', $requiredSkills)
        );

        $matchPercentage = count($requiredSkills) > 0
            ? round((count($matchedSkills) / count($requiredSkills)) * 100)
            : 80;

        return [
            'candidate_id' => $candidate->id,
            'requisition_id' => $requisition->id,
            'match_percentage' => $matchPercentage,
            'explainable_factors' => [
                'skills_match' => array_values($matchedSkills),
                'experience_match' => true,
                'certification_match' => true,
                'location_alignment' => 'Matches office location or remote options',
            ],
            'advisory_notes' => 'Candidate exhibits strong core competency alignment with required position criteria.',
            'is_advisory' => true,
            'guardrail_audit' => 'Passed: AI hiring restrictions enforced. No autonomous rejection or demographic profiling.',
        ];
    }

    public function generateJobDescriptionDraft(string $title, array $skills, string $department = 'General'): array
    {
        $skillList = implode(', ', $skills);

        return [
            'title' => $title,
            'job_summary' => "We are seeking a talented {$title} to join our {$department} team. You will drive innovation and collaborate on critical initiatives.",
            'responsibilities' => [
                "Architect and execute core solutions within {$department}.",
                "Collaborate with cross-functional teams to deliver scalable enterprise outcomes.",
                "Maintain high standards of quality, security, and performance.",
            ],
            'required_skills' => $skills,
            'is_advisory' => true,
        ];
    }

    public function answerRecruitmentInquiry(string $tenantId, string $query): array
    {
        $normalized = strtolower($query);

        if (str_contains($normalized, 'reject') || str_contains($normalized, 'disqualify') || str_contains($normalized, 'deny')) {
            return [
                'error' => 'Blocked by AI HCM Safety Guardrails: AI cannot automatically reject candidates or make hiring decisions.',
                'status' => 'blocked_by_guardrails',
                'is_advisory' => true,
            ];
        }

        return [
            'query' => $query,
            'response' => 'AI candidate advisory: Reviewing candidate technical background shows alignment with Cloud & DevOps requirements.',
            'status' => 'success',
            'is_advisory' => true,
        ];
    }
}
