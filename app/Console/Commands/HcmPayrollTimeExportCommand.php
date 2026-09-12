<?php

namespace App\Console\Commands;

use App\Domains\Attendance\Services\PayrollTimeExportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HcmPayrollTimeExportCommand extends Command
{
    protected $signature = 'hcm:payroll-time-export {tenant : Tenant UUID} {start : Start date (YYYY-MM-DD)} {end : End date (YYYY-MM-DD)}';
    protected $description = 'Generate and export approved time records for payroll integration';

    public function handle(PayrollTimeExportService $service): int
    {
        $tenantId = $this->argument('tenant');
        $start = Carbon::parse($this->argument('start'));
        $end = Carbon::parse($this->argument('end'));

        $this->info("Generating payroll time export for Tenant: {$tenantId} ({$start->toDateString()} to {$end->toDateString()})...");

        $export = $service->generatePayrollExport($tenantId, $start, $end);
        $this->info("Export successfully generated! Reference: {$export->export_reference}, Total Employees: {$export->total_employees}, Regular Hours: {$export->total_regular_hours}h, OT Hours: {$export->total_overtime_hours}h.");

        return self::SUCCESS;
    }
}