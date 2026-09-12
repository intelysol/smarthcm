<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Models\PayrollCalculationSnapshot;
use App\Domains\Payroll\Models\PayrollRun;
use Illuminate\Support\Facades\DB;

class PayrollAnalyticsService
{
    /**
     * Compute Executive Payroll KPI Summary for a run.
     *
     * @return array{
     *     total_employees: int,
     *     total_gross: float,
     *     total_net: float,
     *     total_tax: float,
     *     total_deductions: float,
     *     total_employer_cost: float,
     *     total_payroll_cost: float,
     *     average_gross_salary: float,
     *     department_breakdown: array,
     *     status: string
     * }
     */
    public function getRunSummary(PayrollRun $run): array
    {
        $tenantId = $run->tenant_id;
        $run->loadMissing(['calculationSnapshots.employee.department']);

        $totalEmployees = $run->calculationSnapshots->count();
        $totalGross = (float) $run->gross_total;
        $totalNet = (float) $run->net_total;
        $totalTax = (float) $run->tax_total;
        $totalDeductions = (float) $run->deduction_total;
        $totalEmployerCost = (float) $run->employer_cost_total;
        $totalPayrollCost = $totalGross + $totalEmployerCost;
        $avgGross = $totalEmployees > 0 ? round($totalGross / $totalEmployees, 2) : 0.0;

        $deptMap = [];
        foreach ($run->calculationSnapshots as $snap) {
            $deptName = $snap->employee?->department?->name ?? 'General';
            if (! isset($deptMap[$deptName])) {
                $deptMap[$deptName] = ['employees' => 0, 'gross' => 0.0, 'net' => 0.0];
            }
            $deptMap[$deptName]['employees']++;
            $deptMap[$deptName]['gross'] += (float) $snap->gross_pay;
            $deptMap[$deptName]['net'] += (float) $snap->net_pay;
        }

        return [
            'total_employees' => $totalEmployees,
            'total_gross' => $totalGross,
            'total_net' => $totalNet,
            'total_tax' => $totalTax,
            'total_deductions' => $totalDeductions,
            'total_employer_cost' => $totalEmployerCost,
            'total_payroll_cost' => $totalPayrollCost,
            'average_gross_salary' => $avgGross,
            'department_breakdown' => $deptMap,
            'status' => $run->status,
        ];
    }
}
