<?php

namespace App\Console\Commands;

use App\Domains\Benefits\Jobs\ProcessBenefitEnrollmentsJob;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Console\Command;

class BenefitsSyncCommand extends Command
{
    protected $signature = 'hcm:benefits-sync {--tenant= : Specific tenant UUID to sync}';

    protected $description = 'Synchronize and activate scheduled employee benefit enrollments and policies.';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        $query = Tenant::query();
        if ($tenantId) {
            $query->where('id', $tenantId);
        }

        $tenants = $query->get();
        $this->info("Starting benefits synchronization across {$tenants->count()} tenant(s)...");

        foreach ($tenants as $tenant) {
            ProcessBenefitEnrollmentsJob::dispatchSync($tenant->id);
            $this->line("  ✓ Synced enrollments for tenant: {$tenant->name} ({$tenant->id})");
        }

        $this->info('Benefits synchronization completed successfully.');
        return self::SUCCESS;
    }
}
