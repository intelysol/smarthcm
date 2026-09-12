<?php

namespace App\Console\Commands;

use App\Domains\Absence\Services\AbsenceReconciliationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HcmAbsenceReconcileCommand extends Command
{
    protected $signature = 'hcm:absence-reconcile {tenant : Tenant UUID} {--start= : Start date (YYYY-MM-DD)} {--end= : End date (YYYY-MM-DD)}';
    protected $description = 'Perform cross-domain absence reconciliation across Leave, Attendance, and Schedules';

    public function handle(AbsenceReconciliationService $service): int
    {
        $tenantId = $this->argument('tenant');
        $start = $this->option('start') ? Carbon::parse($this->option('start')) : now()->startOfMonth();
        $end = $this->option('end') ? Carbon::parse($this->option('end')) : now()->endOfMonth();

        $this->info("Reconciling absence records for Tenant: {$tenantId} ({$start->toDateString()} to {$end->toDateString()})...");

        $rec = $service->reconcilePeriod($tenantId, $start, $end);
        $this->info("Reconciliation complete! Status: {$rec->status}, Discrepancies: {$rec->discrepant_records_count}.");

        return self::SUCCESS;
    }
}