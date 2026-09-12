<?php

namespace App\Console\Commands;

use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\ExpenseReimbursement;
use App\Domains\Expenses\Services\ExpenseReimbursementService;
use App\Domains\Payroll\Models\PayrollPeriod;
use Illuminate\Console\Command;

class ExpensesSyncCommand extends Command
{
    protected $signature = 'hcm:expenses-sync {--tenant= : Tenant UUID} {--payroll-period= : Payroll Period UUID}';

    protected $description = 'Synchronize approved employee expense reimbursements into active payroll periods';

    public function handle(ExpenseReimbursementService $reimbursementService): int
    {
        $tenantId = $this->option('tenant');
        $periodId = $this->option('payroll-period');

        $this->info("Starting Expenses & Travel Synchronization...");

        $query = ExpenseReimbursement::query()->where('status', 'approved')->where('reimbursement_method', 'payroll');
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $reimbursements = $query->get();
        $this->info("Found {$reimbursements->count()} approved payroll reimbursements.");

        $period = $periodId ? PayrollPeriod::find($periodId) : PayrollPeriod::where('status', 'open')->first();

        if (! $period) {
            $this->warn("No open payroll period found for sync.");
            return 0;
        }

        $synced = 0;
        foreach ($reimbursements as $reimb) {
            $reimbursementService->syncToPayrollPeriod($reimb, $period);
            $synced++;
        }

        $this->info("Successfully synced {$synced} reimbursements into Payroll Period {$period->period_name}.");

        return 0;
    }
}
