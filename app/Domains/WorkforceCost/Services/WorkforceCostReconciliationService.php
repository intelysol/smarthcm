<?php

namespace App\Domains\WorkforceCost\Services;

use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostLine;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostReconciliation;
use Illuminate\Support\Str;

class WorkforceCostReconciliationService
{
    /**
     * Reconcile Workforce Cost lines against authoritative PayrollRun total.
     */
    public function reconcileWithPayroll(string $tenantId, string $payrollRunId): HcmWorkforceCostReconciliation
    {
        $payrollRun = PayrollRun::where('tenant_id', $tenantId)->find($payrollRunId);
        $sourceTotal = $payrollRun ? ((float) $payrollRun->gross_total + (float) $payrollRun->employer_cost_total) : 0.00;

        $costLinesTotal = (float) HcmWorkforceCostLine::where('tenant_id', $tenantId)
            ->where('source_domain', 'payroll')
            ->where('source_record_id', $payrollRunId)
            ->sum('amount');

        $variance = round(abs($sourceTotal - $costLinesTotal), 4);
        $status = 'MATCHED';
        if ($sourceTotal <= 0 && $costLinesTotal <= 0) {
            $status = 'MISSING_SOURCE';
        } elseif ($variance > 10.0) {
            $status = 'MAJOR_VARIANCE';
        } elseif ($variance > 0.01) {
            $status = 'MINOR_VARIANCE';
        }

        return HcmWorkforceCostReconciliation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'reconciliation_type' => 'payroll_vs_workforce_cost',
            'source_period' => $payrollRun?->run_number ?? $payrollRunId,
            'source_total' => $sourceTotal,
            'workforce_cost_total' => $costLinesTotal,
            'variance_amount' => $variance,
            'currency' => $payrollRun?->currency ?? 'USD',
            'status' => $status,
            'discrepancy_details' => [
                'payroll_run_id' => $payrollRunId,
                'payroll_run_gross' => (float) ($payrollRun?->gross_total ?? 0),
                'payroll_employer_cost' => (float) ($payrollRun?->employer_cost_total ?? 0),
                'cost_lines_total' => $costLinesTotal,
                'variance' => $variance,
            ],
            'reconciled_at' => now(),
        ]);
    }

    /**
     * Reconcile analytical workforce cost with Finance GL total.
     */
    public function reconcileWithFinance(string $tenantId, string $sourcePeriod, float $financeGlTotal): HcmWorkforceCostReconciliation
    {
        $costTotal = (float) HcmWorkforceCostLine::where('tenant_id', $tenantId)->sum('amount');
        $variance = round(abs($financeGlTotal - $costTotal), 4);

        $status = $variance < 0.01 ? 'MATCHED' : ($variance > 500.0 ? 'MAJOR_VARIANCE' : 'MINOR_VARIANCE');

        return HcmWorkforceCostReconciliation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'reconciliation_type' => 'finance_vs_workforce_cost',
            'source_period' => $sourcePeriod,
            'source_total' => $financeGlTotal,
            'workforce_cost_total' => $costTotal,
            'variance_amount' => $variance,
            'currency' => 'USD',
            'status' => $status,
            'discrepancy_details' => [
                'finance_gl_total' => $financeGlTotal,
                'workforce_cost_total' => $costTotal,
                'variance' => $variance,
            ],
            'reconciled_at' => now(),
        ]);
    }
}