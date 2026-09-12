<?php

namespace App\Domains\EmployeeDocuments\Jobs;

use App\Domains\EmployeeDocuments\Services\EmployeeDocumentExpirationService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MonitorEmployeeDocumentExpirationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(EmployeeDocumentExpirationService $service): array
    {
        $tenants = Tenant::all();
        $totalResults = [];

        foreach ($tenants as $tenant) {
            $totalResults[$tenant->id] = $service->checkExpirations($tenant->id);
        }

        return $totalResults;
    }
}
