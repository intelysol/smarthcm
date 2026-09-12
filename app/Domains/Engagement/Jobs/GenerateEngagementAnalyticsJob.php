<?php

namespace App\Domains\Engagement\Jobs;

use App\Domains\Engagement\Services\EngagementAnalyticsService;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateEngagementAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(EngagementAnalyticsService $analyticsService): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if ($tenant) {
            app(TenantContext::class)->set($tenant);
        }

        $analyticsService->generateEngagementMetrics($this->tenantId);
    }
}
