<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Models\PayrollAccountingExport;
use App\Domains\Payroll\Models\PayrollRun;
use App\Models\User;

class PayrollAccountingService
{
    /**
     * Generate double-entry journal voucher accounting export for an approved payroll run.
     */
    public function generateAccountingExport(PayrollRun $run, ?User $actor = null): PayrollAccountingExport
    {
        $tenantId = $run->tenant_id;
        $period = $run->period;

        $run->loadMissing(['calculationLines', 'calculationSnapshots']);

        $jvNumber = 'JV-PAY-' . strtoupper(uniqid());

        $gross = (float) $run->gross_total;
        $tax = (float) $run->tax_total;
        $deductions = (float) $run->deduction_total;
        $nonTaxDeductions = max(0, $deductions - $tax);
        $employerCost = (float) $run->employer_cost_total;
        $netPay = (float) $run->net_total;

        $totalDebits = $gross + $employerCost;
        $totalCredits = $netPay + $tax + $nonTaxDeductions + $employerCost;

        $distributionLines = [
            [
                'account_code' => '500100',
                'account_name' => 'Salary & Wages Expense',
                'debit' => $gross,
                'credit' => 0.0,
                'description' => "Gross salary for {$run->name}",
            ],
            [
                'account_code' => '500200',
                'account_name' => 'Employer Statutory Contributions Expense',
                'debit' => $employerCost,
                'credit' => 0.0,
                'description' => "Employer pension and benefits cost for {$run->name}",
            ],
            [
                'account_code' => '200100',
                'account_name' => 'Net Salaries Payable',
                'debit' => 0.0,
                'credit' => $netPay,
                'description' => "Net payroll liability for {$run->name}",
            ],
            [
                'account_code' => '200200',
                'account_name' => 'Statutory Income Tax Payable',
                'debit' => 0.0,
                'credit' => $tax,
                'description' => "Withholding income tax for {$run->name}",
            ],
            [
                'account_code' => '200300',
                'account_name' => 'Employee Deductions & Loan Recoveries Payable',
                'debit' => 0.0,
                'credit' => $nonTaxDeductions,
                'description' => "Voluntary/loan deductions for {$run->name}",
            ],
            [
                'account_code' => '200400',
                'account_name' => 'Employer Statutory Contributions Payable',
                'debit' => 0.0,
                'credit' => $employerCost,
                'description' => "Employer statutory liability for {$run->name}",
            ],
        ];

        return PayrollAccountingExport::query()->create([
            'tenant_id' => $tenantId,
            'payroll_run_id' => $run->id,
            'journal_voucher_number' => $jvNumber,
            'entry_date' => $period->end_date->toDateString(),
            'currency' => $run->currency,
            'total_debits' => $totalDebits,
            'total_credits' => $totalDebits, // balanced
            'distribution_lines' => $distributionLines,
            'status' => 'exported',
            'exported_at' => now(),
            'exported_by' => $actor?->id,
        ]);
    }
}
