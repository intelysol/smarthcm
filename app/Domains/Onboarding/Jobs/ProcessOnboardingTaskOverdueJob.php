<?php

namespace App\Domains\Onboarding\Jobs;

use App\Domains\Onboarding\Enums\OnboardingTaskStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCaseTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOnboardingTaskOverdueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Query tasks that are past due and still incomplete
        HcmOnboardingCaseTask::whereIn('status', [OnboardingTaskStatus::PENDING->value, OnboardingTaskStatus::IN_PROGRESS->value])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->update([
                'notes' => 'Escalated: Task past designated due date.',
            ]);
    }
}
