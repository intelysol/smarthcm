<?php

namespace App\Domains\Recruitment\Jobs;

use App\Domains\Recruitment\Services\RecruitmentAnalyticsService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshRecruitmentAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(RecruitmentAnalyticsService $analyticsService): void
    {
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $analyticsService->getRecruitmentKpis($tenant->id);
            $analyticsService->getRecruitmentFunnel($tenant->id);
        }
    }
}
