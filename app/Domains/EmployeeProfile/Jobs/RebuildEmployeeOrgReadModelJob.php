<?php

namespace App\Domains\EmployeeProfile\Jobs;

use App\Domains\EmployeeProfile\Services\EmployeeOrgProjectionService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RebuildEmployeeOrgReadModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $tenantId = null)
    {
    }

    public function handle(EmployeeOrgProjectionService $service): array
    {
        $tenants = $this->tenantId ? [$this->tenantId] : Tenant::pluck('id')->toArray();
        $results = [];

        foreach ($tenants as $id) {
            $results[$id] = $service->rebuildForTenant($id);
        }

        return $results;
    }
}
