<?php

namespace App\Console\Commands;

use App\Domains\WorkforceCost\Services\WorkforceCostReconciliationService;
use Illuminate\Console\Command;

class HcmWorkforceCostReconcileCommand extends Command
{
    protected $signature = 'hcm:cost-reconcile {--tenant-id= : Tenant UUID} {--payroll-run-id= : Payroll Run UUID}';
    protected $description = 'Reconcile workforce cost lines against authoritative payroll run total';

    public function handle(WorkforceCostReconciliationService $service): int
    {
        $tenantId = $this->option('tenant-id');
        $payrollRunId = $this->option('payroll-run-id');

        if (! $tenantId || ! $payrollRunId) {
            $this->error('Both --tenant-id and --payroll-run-id are required.');
            return self::FAILURE;
        }

        $this->info("Reconciling workforce cost for payroll run {$payrollRunId}...");
        $reconciliation = $service->reconcileWithPayroll($tenantId, $payrollRunId);

        $this->info("Reconciliation status: {$reconciliation->status} (Variance: {$reconciliation->variance_amount} {$reconciliation->currency})");
        return self::SUCCESS;
    }
}