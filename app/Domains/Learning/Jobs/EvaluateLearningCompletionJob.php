<?php

namespace App\Domains\Learning\Jobs;

use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Services\LearningCompletionService;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluateLearningCompletionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $enrollmentId) {}

    public function handle(LearningCompletionService $completionService, TenantContext $tenantContext): void
    {
        $enrollment = LearningEnrollment::query()->find($this->enrollmentId);
        if (! $enrollment) return;

        $tenant = Tenant::query()->find($enrollment->tenant_id);
        if ($tenant) $tenantContext->set($tenant);

        $completionService->evaluateCourseCompletion($enrollment);
    }
}
