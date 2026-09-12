<?php

namespace App\Domains\OrganizationDesign\Services;

use App\Domains\OrganizationDesign\Models\JobProfile;

class AdvisoryJobAiService
{
    /**
     * Provide advisory job profile content drafting and suggestions.
     * Guardrail: Always advisory only; never autonomously mutates employment records or live data.
     */
    public function generateProfileDraftSuggestions(string $title, string $familyName): array
    {
        $normalizedTitle = strtolower($title);

        $suggestedSkills = ['Communication', 'Problem Solving', 'Project Coordination'];
        $suggestedCompetencies = ['Collaboration', 'Accountability', 'Adaptability'];

        if (str_contains($normalizedTitle, 'software') || str_contains($normalizedTitle, 'engineer') || str_contains($normalizedTitle, 'developer')) {
            $suggestedSkills = ['Object-Oriented Programming', 'Git Version Control', 'API Architecture', 'Unit Testing', 'Database Optimization'];
            $suggestedCompetencies = ['Technical Excellence', 'Systemic Thinking', 'Code Quality Standards'];
        } elseif (str_contains($normalizedTitle, 'finance') || str_contains($normalizedTitle, 'account')) {
            $suggestedSkills = ['Financial Modeling', 'Variance Analysis', 'Tax Compliance', 'Ledger Reconciliation'];
            $suggestedCompetencies = ['Fiduciary Integrity', 'Attention to Detail', 'Quantitative Reasoning'];
        } elseif (str_contains($normalizedTitle, 'hr') || str_contains($normalizedTitle, 'people') || str_contains($normalizedTitle, 'talent')) {
            $suggestedSkills = ['Employee Relations', 'Talent Acquisition', 'HRIS Administration', 'Labor Law Knowledge'];
            $suggestedCompetencies = ['Empathy', 'Confidentiality', 'Stakeholder Management'];
        }

        return [
            'is_advisory_only' => true,
            'title' => $title,
            'job_family' => $familyName,
            'suggested_summary' => "The {$title} role within {$familyName} is responsible for driving standard operational execution, cross-functional collaboration, and domain quality standards.",
            'suggested_responsibilities' => [
                "Execute day-to-day {$familyName} deliverables according to company standards.",
                "Collaborate with cross-functional stakeholders to align on strategic objectives.",
                "Continuously improve workflow procedures and maintain documentation.",
            ],
            'suggested_skills' => $suggestedSkills,
            'suggested_competencies' => $suggestedCompetencies,
            'recommendation_note' => 'Review and refine draft before submitting to formal job architecture governance approval.',
        ];
    }

    /**
     * Identify potential job title standardization candidates.
     */
    public function suggestTitleStandardization(string $title): array
    {
        $variations = [
            'sr.' => 'Senior',
            'sr' => 'Senior',
            'snr' => 'Senior',
            'jr.' => 'Junior',
            'jr' => 'Junior',
            'mgr' => 'Manager',
            'dir' => 'Director',
            'eng' => 'Engineer',
            'dev' => 'Developer',
        ];

        $words = explode(' ', $title);
        $standardizedWords = [];
        $changed = false;

        foreach ($words as $word) {
            $lower = strtolower($word);
            if (isset($variations[$lower])) {
                $standardizedWords[] = $variations[$lower];
                $changed = true;
            } else {
                $standardizedWords[] = ucfirst($word);
            }
        }

        $standardized = implode(' ', $standardizedWords);

        return [
            'is_advisory_only' => true,
            'original_title' => $title,
            'suggested_standard_title' => $standardized,
            'requires_standardization' => $changed,
            'confidence' => $changed ? 0.95 : 1.0,
        ];
    }
}
