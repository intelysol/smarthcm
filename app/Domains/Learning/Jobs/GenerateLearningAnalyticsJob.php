<?php

namespace App\Domains\Learning\Jobs;

use App\Domains\Learning\Services\LearningAnalyticsService;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateLearningAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $tenantId) {}

    public function handle(LearningAnalyticsService $analyticsService, TenantContext $tenantContext): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) return;
        $tenantContext->set($tenant);

        $analyticsService->generateSnapshot($this->tenantId);
    }
}
