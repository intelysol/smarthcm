<?php

namespace App\Domains\Onboarding\Jobs;

use App\Domains\Onboarding\Enums\ProbationStatus;
use App\Domains\Onboarding\Models\HcmOnboardingProbation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckProbationDueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Detect probations ending within 14 days and mark as 'due'
        HcmOnboardingProbation::where('status', ProbationStatus::IN_PROGRESS->value)
            ->where('probation_end_date', '<=', now()->addDays(14)->toDateString())
            ->update([
                'status' => ProbationStatus::DUE->value,
            ]);
    }
}
