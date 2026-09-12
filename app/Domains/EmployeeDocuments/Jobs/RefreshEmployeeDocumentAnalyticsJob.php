<?php

namespace App\Domains\EmployeeDocuments\Jobs;

use App\Domains\EmployeeDocuments\Services\EmployeeDocumentAnalyticsService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshEmployeeDocumentAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(EmployeeDocumentAnalyticsService $service): void
    {
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $service->getMetrics($tenant->id);
        }
    }
}
