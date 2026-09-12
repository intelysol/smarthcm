<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollCalculationSnapshot;
use App\Domains\Payroll\Models\PayrollPayslip;
use App\Domains\Payroll\Models\PayrollRun;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class PayslipService
{
    /**
     * Generate payslips for all calculated employees in an approved/calculated run.
     */
    public function generatePayslipsForRun(PayrollRun $run, ?User $actor = null): Collection
    {
        $tenantId = $run->tenant_id;
        $period = $run->period;
        $run->loadMissing(['calculationSnapshots.employee']);

        $currentYear = CarbonImmutable::parse($period->start_date)->year;

        foreach ($run->calculationSnapshots as $snapshot) {
            $emp = $snapshot->employee;

            // Compute YTD figures within this calendar year
            $ytdHistory = PayrollPayslip::query()
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $emp->id)
                ->whereYear('period_start', $currentYear)
                ->where('is_published', true)
                ->get();

            $ytdGross = $ytdHistory->sum('gross_pay') + (float) $snapshot->gross_pay;
            $ytdTax = $ytdHistory->sum('total_tax') + (float) $snapshot->total_tax;
            $ytdNet = $ytdHistory->sum('net_pay') + (float) $snapshot->net_pay;

            $payslipNum = 'PS-' . strtoupper(uniqid());

            PayrollPayslip::query()->updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $emp->id,
                ],
                [
                    'payroll_calculation_snapshot_id' => $snapshot->id,
                    'payslip_number' => $payslipNum,
                    'pay_date' => $period->payment_date ?? $period->end_date,
                    'period_start' => $period->start_date,
                    'period_end' => $period->end_date,
                    'gross_pay' => $snapshot->gross_pay,
                    'total_deductions' => $snapshot->total_deductions,
                    'total_tax' => $snapshot->total_tax,
                    'net_pay' => $snapshot->net_pay,
                    'ytd_gross' => $ytdGross,
                    'ytd_tax' => $ytdTax,
                    'ytd_net' => $ytdNet,
                    'currency' => $run->currency,
                    'masked_bank_account' => '**** **** ' . rand(1000, 9999),
                    'is_published' => false,
                    'created_by' => $actor?->id,
                ]
            );
        }

        return $run->payslips;
    }

    public function publishPayslipsForRun(PayrollRun $run, ?User $actor = null): int
    {
        return PayrollPayslip::query()
            ->where('tenant_id', $run->tenant_id)
            ->where('payroll_run_id', $run->id)
            ->update([
                'is_published' => true,
                'published_at' => now(),
            ]);
    }

    public function getEmployeePayslips(Employee $employee): Collection
    {
        return PayrollPayslip::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('is_published', true)
            ->with(['run.period', 'snapshot'])
            ->orderByDesc('period_start')
            ->get();
    }
}
