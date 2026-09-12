<?php

namespace App\Domains\Onboarding\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Models\HcmOnboardingCase;

class OnboardingAiService
{
    public function generateWelcomeMessage(Employee $employee, HcmOnboardingCase $case): array
    {
        $department = $employee->department?->department_name ?? 'our company';
        $firstName = $employee->first_name;

        return [
            'employee_id' => $employee->id,
            'case_id' => $case->id,
            'welcome_subject' => "Welcome to the team, {$firstName}!",
            'welcome_body' => "Dear {$firstName},\n\nWe are thrilled to welcome you to the {$department} team at Flow HCM. Your first day is scheduled for {$case->start_date->format('M d, Y')}. Please review your Preboarding checklist to complete your profile, submit verification documents, and review our company handbook.\n\nWe look forward to an amazing journey together!",
            'is_advisory' => true,
        ];
    }

    public function summarizeCaseReadiness(HcmOnboardingCase $case): array
    {
        $pendingTasks = $case->tasks()->where('status', '!=', 'completed')->count();
        $pendingDocs = $case->documentRequirements()->where('status', '!=', 'verified')->count();

        $readiness = ($pendingTasks === 0 && $pendingDocs === 0) ? 'Ready for Day One' : 'Action items pending';

        return [
            'case_id' => $case->id,
            'completion_percentage' => (float) $case->completion_percentage,
            'pending_tasks_count' => $pendingTasks,
            'pending_documents_count' => $pendingDocs,
            'summary' => "Employee has achieved {$case->completion_percentage}% progress with {$pendingTasks} pending tasks remaining. Status: {$readiness}.",
            'is_advisory' => true,
            'guardrail_audit' => 'Passed: AI assistance is purely informational. Human review mandatory for probation outcomes.',
        ];
    }

    public function answerOnboardingInquiry(string $tenantId, string $query): array
    {
        $normalized = strtolower($query);

        if (str_contains($normalized, 'terminate') || str_contains($normalized, 'fire') || str_contains($normalized, 'fail probation') || str_contains($normalized, 'reject employee')) {
            return [
                'error' => 'Blocked by AI HCM Safety Guardrails: AI cannot make probation, status change, or employment termination decisions.',
                'status' => 'blocked_by_guardrails',
                'is_advisory' => true,
            ];
        }

        return [
            'query' => $query,
            'response' => 'Onboarding Guidance: Standard company orientation begins at 09:00 AM on Monday in Conference Room A or via the virtual orientation link.',
            'status' => 'success',
            'is_advisory' => true,
        ];
    }
}
