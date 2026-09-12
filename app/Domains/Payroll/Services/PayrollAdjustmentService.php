<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Enums\AdjustmentStatus;
use App\Domains\Payroll\Models\PayrollAdjustment;
use App\Domains\Payroll\Models\PayrollArrear;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Models\User;
use Carbon\CarbonImmutable;

class PayrollAdjustmentService
{
    public function requestAdjustment(Employee $employee, array $data, User $requester): PayrollAdjustment
    {
        return PayrollAdjustment::query()->create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'payroll_period_id' => $data['payroll_period_id'] ?? null,
            'adjustment_type' => $data['adjustment_type'] ?? 'earning',
            'code' => strtoupper($data['code'] ?? 'ADJ-MANUAL'),
            'title' => $data['title'],
            'amount' => (float) $data['amount'],
            'currency' => strtoupper($data['currency'] ?? 'USD'),
            'effective_date' => $data['effective_date'] ?? now()->toDateString(),
            'reason' => $data['reason'],
            'status' => AdjustmentStatus::PENDING->value,
            'requested_by' => $requester->id,
        ]);
    }

    public function approveAdjustment(PayrollAdjustment $adjustment, User $approver): PayrollAdjustment
    {
        $adjustment->update([
            'status' => AdjustmentStatus::APPROVED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $adjustment;
    }

    public function rejectAdjustment(PayrollAdjustment $adjustment, User $approver, string $reason): PayrollAdjustment
    {
        $adjustment->update([
            'status' => AdjustmentStatus::REJECTED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $adjustment;
    }

    /**
     * Create retroactive salary back-pay arrear.
     */
    public function createSalaryArrear(
        Employee $employee,
        string $title,
        string $originStart,
        string $originEnd,
        float $previousMonthlySalary,
        float $revisedMonthlySalary,
        ?PayrollPeriod $targetPeriod = null,
        ?User $actor = null
    ): PayrollArrear {
        $start = CarbonImmutable::parse($originStart);
        $end = CarbonImmutable::parse($originEnd);
        $months = max(1, (int) $start->diffInMonths($end) + 1);

        $monthlyDiff = max(0, $revisedMonthlySalary - $previousMonthlySalary);
        $totalDiff = $monthlyDiff * $months;

        return PayrollArrear::query()->create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'title' => $title,
            'origin_start_date' => $originStart,
            'origin_end_date' => $originEnd,
            'previous_amount' => $previousMonthlySalary * $months,
            'revised_amount' => $revisedMonthlySalary * $months,
            'difference_amount' => $totalDiff,
            'currency' => 'USD',
            'payroll_period_id' => $targetPeriod?->id,
            'status' => 'approved',
            'reason' => "Retroactive salary adjustment from {$originStart} to {$originEnd} ({$months} months).",
            'approved_by' => $actor?->id,
            'approved_at' => now(),
            'created_by' => $actor?->id,
        ]);
    }
}
