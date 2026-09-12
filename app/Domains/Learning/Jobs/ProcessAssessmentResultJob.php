<?php

namespace App\Domains\Learning\Jobs;

use App\Domains\Learning\Models\LearningAssessmentAttempt;
use App\Domains\Learning\Services\LearningAssessmentService;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAssessmentResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $attemptId) {}

    public function handle(LearningAssessmentService $assessmentService, TenantContext $tenantContext): void
    {
        $attempt = LearningAssessmentAttempt::query()->find($this->attemptId);
        if (! $attempt) return;

        $tenant = Tenant::query()->find($attempt->tenant_id);
        if ($tenant) $tenantContext->set($tenant);

        if ($attempt->enrollment_id) {
            EvaluateLearningCompletionJob::dispatch($attempt->enrollment_id);
        }
    }
}
