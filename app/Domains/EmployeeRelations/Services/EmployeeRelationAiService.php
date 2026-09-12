<?php

namespace App\Domains\EmployeeRelations\Services;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class EmployeeRelationAiService
{
    /**
     * Generate an AI-assisted summary of the case timeline and facts for authorized HR
     */
    public function generateCaseSummary(EmployeeRelationCase $case, User $user, CaseAuthorizationService $auth): array
    {
        if (! $auth->canViewCase($user, $case)) {
            throw ValidationException::withMessages([
                'authorization' => 'Unauthorized to generate AI summary for this case.',
            ]);
        }

        // Prepare sanitized, isolated context strictly limited to this specific case
        $caseInfo = [
            'case_number' => $case->case_number,
            'title' => $case->title,
            'summary' => $case->summary,
            'status' => $case->status,
            'priority' => $case->priority,
            'severity' => $case->severity,
            'opened_at' => $case->opened_at->toFormattedDateString(),
            'allegations_count' => $case->allegations()->count(),
            'evidence_items_count' => $case->evidence()->count(),
            'interviews_count' => $case->interviews()->count(),
        ];

        // Guardrail: AI is decision-support only
        $narrative = "Case {$case->case_number} ('{$case->title}') is currently in '{$case->status}' status. "
            . "It was opened on {$caseInfo['opened_at']} with {$caseInfo['allegations_count']} allegation(s), "
            . "{$caseInfo['evidence_items_count']} evidence item(s) logged, and {$caseInfo['interviews_count']} interview(s) recorded. "
            . "Summary: {$case->summary}";

        return [
            'case_number' => $case->case_number,
            'generated_at' => now()->toIso8601String(),
            'summary' => $narrative,
            'safety_notice' => 'AI is configured as a timeline and synthesis tool only. Formal findings, guilt, innocence, disciplinary actions, and legal outcomes must be determined solely by authorized human investigators and decision-makers.',
        ];
    }
}
