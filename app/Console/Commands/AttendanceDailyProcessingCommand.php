<?php

namespace App\Console\Commands;

use App\Domains\Attendance\Services\AttendanceProcessor;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Console\Command;

class AttendanceDailyProcessingCommand extends Command
{
    protected $signature = 'hcm:attendance-daily-process {--tenant= : Specific tenant UUID} {--date= : Specific date YYYY-MM-DD}';

    protected $description = 'Daily scheduled processing of attendance events, session calculation, and exception generation.';

    public function handle(AttendanceProcessor $processor): int
    {
        $date = $this->option('date') ?: now()->toDateString();
        $tenantId = $this->option('tenant');

        $this->info("Starting daily attendance processing for date: {$date}");

        $tenants = $tenantId
            ? Tenant::query()->where('id', $tenantId)->get()
            : Tenant::query()->get();

        foreach ($tenants as $tenant) {
            $this->line("Processing tenant: {$tenant->id}");
            $sessions = $processor->processTenantDate($tenant->id, $date);
            $this->info("Processed {$sessions->count()} employee sessions for tenant {$tenant->id}");
        }

        $this->info('Daily attendance processing complete.');

        return self::SUCCESS;
    }
}
