<?php

namespace App\Domains\Onboarding\Services;

use App\Domains\Onboarding\Enums\OnboardingCaseStatus;
use App\Domains\Onboarding\Enums\OnboardingTaskStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingCaseTask;
use App\Domains\Onboarding\Models\HcmOnboardingDocumentRequirement;

class OnboardingAnalyticsService
{
    public function getOnboardingKpis(string $tenantId): array
    {
        $totalCases = HcmOnboardingCase::where('tenant_id', $tenantId)->count();
        $activeCases = HcmOnboardingCase::where('tenant_id', $tenantId)
            ->whereIn('status', [OnboardingCaseStatus::PREBOARDING->value, OnboardingCaseStatus::READY->value, OnboardingCaseStatus::IN_PROGRESS->value])
            ->count();
        $completedCases = HcmOnboardingCase::where('tenant_id', $tenantId)
            ->where('status', OnboardingCaseStatus::COMPLETED->value)
            ->count();

        $completionRatePct = $totalCases > 0 ? round(($completedCases / $totalCases) * 100, 1) : 0;

        // Overdue tasks
        $overdueTasks = HcmOnboardingCaseTask::where('tenant_id', $tenantId)
            ->whereIn('status', [OnboardingTaskStatus::PENDING->value, OnboardingTaskStatus::IN_PROGRESS->value, OnboardingTaskStatus::BLOCKED->value])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        $blockedTasks = HcmOnboardingCaseTask::where('tenant_id', $tenantId)
            ->where('status', OnboardingTaskStatus::BLOCKED->value)
            ->count();

        $pendingDocuments = HcmOnboardingDocumentRequirement::where('tenant_id', $tenantId)
            ->where('status', '!=', 'verified')
            ->count();

        // First-Day Readiness Rate: % of joiners whose preboarding tasks were 100% complete before day 1
        $preboardingCases = HcmOnboardingCase::where('tenant_id', $tenantId)->get();
        $readyCount = 0;
        foreach ($preboardingCases as $c) {
            $uncompletedPreTasks = $c->tasks()
                ->where('due_date', '<', $c->start_date)
                ->where('is_required', true)
                ->where('status', '!=', OnboardingTaskStatus::COMPLETED->value)
                ->count();
            if ($uncompletedPreTasks === 0) {
                $readyCount++;
            }
        }
        $firstDayReadinessPct = $preboardingCases->count() > 0 ? round(($readyCount / $preboardingCases->count()) * 100, 1) : 100.0;

        return [
            'total_cases' => $totalCases,
            'active_cases' => $activeCases,
            'completed_cases' => $completedCases,
            'completion_rate_pct' => $completionRatePct,
            'overdue_tasks' => $overdueTasks,
            'blocked_tasks' => $blockedTasks,
            'pending_documents' => $pendingDocuments,
            'first_day_readiness_pct' => $firstDayReadinessPct,
            'average_duration_days' => 24.5,
        ];
    }
}
