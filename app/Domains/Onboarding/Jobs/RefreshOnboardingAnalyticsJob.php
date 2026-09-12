<?php

namespace App\Domains\Onboarding\Jobs;

use App\Domains\Onboarding\Services\OnboardingAnalyticsService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshOnboardingAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(OnboardingAnalyticsService $analyticsService): void
    {
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $analyticsService->getOnboardingKpis($tenant->id);
        }
    }
}
