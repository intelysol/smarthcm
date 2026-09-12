<?php

namespace App\Domains\Lifecycle\Jobs;

use App\Domains\Lifecycle\Services\PersonnelActionAnalyticsService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshLifecycleAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(PersonnelActionAnalyticsService $analyticsService): void
    {
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $analyticsService->getLifecycleMetrics($tenant->id);
        }
    }
}
