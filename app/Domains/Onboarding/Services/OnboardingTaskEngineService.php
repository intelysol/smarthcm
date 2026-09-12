<?php

namespace App\Domains\Onboarding\Services;

use App\Domains\Onboarding\Enums\OnboardingTaskStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCaseTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OnboardingTaskEngineService
{
    public function __construct(protected ?OnboardingCaseService $caseService = null)
    {
        $this->caseService = $caseService ?? new OnboardingCaseService();
    }

    public function addDependency(HcmOnboardingCaseTask $task, HcmOnboardingCaseTask $prerequisite): void
    {
        if ($task->id === $prerequisite->id) {
            throw ValidationException::withMessages(['dependency' => 'A task cannot depend on itself.']);
        }

        $task->prerequisites()->syncWithoutDetaching([
            $prerequisite->id => ['tenant_id' => $task->tenant_id],
        ]);

        if ($prerequisite->status !== OnboardingTaskStatus::COMPLETED->value) {
            $task->update(['status' => OnboardingTaskStatus::BLOCKED->value]);
        }
    }

    public function completeTask(HcmOnboardingCaseTask $task, ?int $completedBy = null, ?string $notes = null): HcmOnboardingCaseTask
    {
        // 1. Verify all prerequisite tasks are satisfied
        $pendingPrereqs = $task->prerequisites()
            ->where('status', '!=', OnboardingTaskStatus::COMPLETED->value)
            ->count();

        if ($pendingPrereqs > 0) {
            throw ValidationException::withMessages([
                'dependencies' => 'Cannot complete task: Prerequisite onboarding requirements have not been completed.',
            ]);
        }

        return DB::transaction(function () use ($task, $completedBy, $notes) {
            $task->update([
                'status' => OnboardingTaskStatus::COMPLETED->value,
                'completed_at' => now(),
                'completed_by' => $completedBy,
                'notes' => $notes ?? $task->notes,
            ]);

            // 2. Cascade unblocking to downstream dependents
            foreach ($task->dependents as $dependent) {
                $hasOtherPendingPrereqs = $dependent->prerequisites()
                    ->where('hcm_onboarding_case_tasks.id', '!=', $task->id)
                    ->where('status', '!=', OnboardingTaskStatus::COMPLETED->value)
                    ->exists();

                if (!$hasOtherPendingPrereqs && $dependent->status === OnboardingTaskStatus::BLOCKED->value) {
                    $dependent->update(['status' => OnboardingTaskStatus::PENDING->value]);
                }
            }

            // 3. Recalculate case completion percentage
            $this->caseService->recalculateProgress($task->case);

            return $task;
        });
    }

    public function blockTask(HcmOnboardingCaseTask $task, string $reason): HcmOnboardingCaseTask
    {
        $task->update([
            'status' => OnboardingTaskStatus::BLOCKED->value,
            'notes' => $reason,
        ]);

        return $task;
    }
}
