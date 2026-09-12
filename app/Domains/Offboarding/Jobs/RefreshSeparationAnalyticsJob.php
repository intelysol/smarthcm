<?php

namespace App\Domains\Offboarding\Jobs;

use App\Domains\Offboarding\Services\SeparationAnalyticsService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshSeparationAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SeparationAnalyticsService $analyticsService): void
    {
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $analyticsService->getOffboardingMetrics($tenant->id);
        }
    }
}
