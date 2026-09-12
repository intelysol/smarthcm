<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\EmployeeCompensation;
use App\Domains\Payroll\Models\PayrollAdjustment;
use App\Domains\Payroll\Models\PayrollInput;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollPolicy;

class DeductionService
{
    /**
     * Calculate itemized deductions for an employee in a period.
     *
     * @return array<int, array{
     *     component_id: ?string,
     *     deduction_code: string,
     *     deduction_name: string,
     *     deduction_type: string,
     *     amount: float,
     *     currency: string,
     *     priority_order: int,
     *     calculation_source: string,
     *     source_reference_id: ?string
     * }>
     */
    public function calculateDeductions(
        Employee $employee,
        PayrollPeriod $period,
        ?EmployeeCompensation $compensation,
        ?PayrollInput $input,
        float $grossEarnings,
        ?PayrollPolicy $policy
    ): array {
        $deductions = [];
        $currency = $compensation?->currency ?? $period->currency;
        $baseSalary = (float) ($compensation?->base_salary ?? 0);

        // 1. Employee Deductions from Compensation Structure (e.g. Employee Pension)
        if ($compensation && $compensation->components) {
            foreach ($compensation->components as $line) {
                $comp = $line->component;
                if (! $comp || ! $comp->isDeduction() || $comp->component_type === 'tax') {
                    continue;
                }

                $amount = (float) $line->amount;
                if ($line->calculation_type === 'percentage_of_basic' && $line->percentage) {
                    $amount = $baseSalary * ($line->percentage / 100);
                } elseif ($line->calculation_type === 'percentage_of_gross' && $line->percentage) {
                    $amount = $grossEarnings * ($line->percentage / 100);
                }

                $deductions[] = [
                    'component_id' => $comp->id,
                    'deduction_code' => $comp->code,
                    'deduction_name' => $comp->name,
                    'deduction_type' => $comp->component_type,
                    'amount' => round($amount, 4),
                    'currency' => $currency,
                    'priority_order' => (int) $comp->priority_order,
                    'calculation_source' => 'compensation_structure',
                    'source_reference_id' => $compensation->id,
                ];
            }
        }

        // 2. Loan & Advance Deductions from Inputs
        if ($input && $input->lines) {
            $loanLines = $input->lines->where('input_type', 'loan_installment');
            foreach ($loanLines as $lLine) {
                $deductions[] = [
                    'component_id' => null,
                    'deduction_code' => 'LOAN_DED',
                    'deduction_name' => $lLine->notes ?: 'Loan Repayment',
                    'deduction_type' => 'loan',
                    'amount' => (float) $lLine->amount,
                    'currency' => $currency,
                    'priority_order' => 3,
                    'calculation_source' => 'loan_schedule',
                    'source_reference_id' => $lLine->source_entity_id,
                ];
            }

            $advanceLines = $input->lines->where('input_type', 'advance_repayment');
            foreach ($advanceLines as $aLine) {
                $deductions[] = [
                    'component_id' => null,
                    'deduction_code' => 'ADVANCE_DED',
                    'deduction_name' => $aLine->notes ?: 'Salary Advance Repayment',
                    'deduction_type' => 'advance',
                    'amount' => (float) $aLine->amount,
                    'currency' => $currency,
                    'priority_order' => 4,
                    'calculation_source' => 'advance_schedule',
                    'source_reference_id' => $aLine->source_entity_id,
                ];
            }
        }

        // 3. Manual Adjustments (Deductions)
        $adjustments = PayrollAdjustment::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('adjustment_type', 'deduction')
            ->where(function ($q) use ($period) {
                $q->where('payroll_period_id', $period->id)
                    ->orWhere(function ($q2) use ($period) {
                        $q2->whereDate('effective_date', '>=', $period->start_date->toDateString())
                            ->whereDate('effective_date', '<=', $period->end_date->toDateString());
                    });
            })
            ->get();

        foreach ($adjustments as $adj) {
            $deductions[] = [
                'component_id' => null,
                'deduction_code' => $adj->code,
                'deduction_name' => $adj->title,
                'deduction_type' => 'other',
                'amount' => (float) $adj->amount,
                'currency' => $currency,
                'priority_order' => 8,
                'calculation_source' => 'manual_adjustment',
                'source_reference_id' => $adj->id,
            ];
        }

        // Sort deductions by priority order
        usort($deductions, fn ($a, $b) => $a['priority_order'] <=> $b['priority_order']);

        return $deductions;
    }
}
