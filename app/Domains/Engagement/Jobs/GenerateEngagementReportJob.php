<?php

namespace App\Domains\Engagement\Jobs;

use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Services\EngagementResultService;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateEngagementReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $campaignId
    ) {}

    public function handle(EngagementResultService $resultService): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if ($tenant) {
            app(TenantContext::class)->set($tenant);
        }

        $campaign = EngagementCampaign::query()->find($this->campaignId);
        if ($campaign) {
            $resultService->getCampaignResults($campaign);
        }
    }
}
