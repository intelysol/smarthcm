<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\EmployeeCompensation;
use App\Domains\Payroll\Models\PayrollAdjustment;
use App\Domains\Payroll\Models\PayrollArrear;
use App\Domains\Payroll\Models\PayrollBonus;
use App\Domains\Payroll\Models\PayrollInput;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollPolicy;

class EarningsService
{
    /**
     * Calculate itemized earnings for an employee in a period.
     *
     * @return array<int, array{
     *     component_id: ?string,
     *     earning_code: string,
     *     earning_name: string,
     *     earning_type: string,
     *     rate: ?float,
     *     quantity: float,
     *     amount: float,
     *     currency: string,
     *     is_taxable: bool,
     *     is_pensionable: bool,
     *     calculation_source: string,
     *     source_reference_id: ?string
     * }>
     */
    public function calculateEarnings(
        Employee $employee,
        PayrollPeriod $period,
        ?EmployeeCompensation $compensation,
        ?PayrollInput $input,
        float $prorationFactor,
        ?PayrollPolicy $policy
    ): array {
        $earnings = [];
        $currency = $compensation?->currency ?? $period->currency;
        $baseSalary = (float) ($compensation?->base_salary ?? 0);
        $hourlyRate = $baseSalary > 0 ? ($baseSalary / 160.0) : 0.0; // Standard 160 hours monthly divisor

        // 1. Basic Salary (Prorated)
        if ($baseSalary > 0) {
            $proratedBase = round($baseSalary * $prorationFactor, 4);
            $earnings[] = [
                'component_id' => null,
                'earning_code' => 'BASIC',
                'earning_name' => 'Basic Salary',
                'earning_type' => 'basic',
                'rate' => $baseSalary,
                'quantity' => $prorationFactor,
                'amount' => $proratedBase,
                'currency' => $currency,
                'is_taxable' => true,
                'is_pensionable' => true,
                'calculation_source' => 'compensation_structure',
                'source_reference_id' => $compensation?->id,
            ];
        }

        // 2. Allowances from Employee Compensation
        if ($compensation && $compensation->components) {
            foreach ($compensation->components as $line) {
                $comp = $line->component;
                if (! $comp || ! $comp->isEarning() || $comp->code === 'BASIC') {
                    continue;
                }

                $amount = (float) $line->amount;
                if ($line->calculation_type === 'percentage_of_basic' && $line->percentage) {
                    $amount = $baseSalary * ($line->percentage / 100);
                }

                $proratedAmount = round($amount * $prorationFactor, 4);

                $earnings[] = [
                    'component_id' => $comp->id,
                    'earning_code' => $comp->code,
                    'earning_name' => $comp->name,
                    'earning_type' => $comp->component_type,
                    'rate' => $amount,
                    'quantity' => $prorationFactor,
                    'amount' => $proratedAmount,
                    'currency' => $currency,
                    'is_taxable' => $comp->is_taxable,
                    'is_pensionable' => $comp->is_pensionable,
                    'calculation_source' => 'compensation_structure',
                    'source_reference_id' => $compensation->id,
                ];
            }
        }

        // 3. Overtime Earnings (from Inputs)
        $otMultiplier = (float) ($policy?->overtime_rate_multiplier ?? 1.50);
        $otRate = round($hourlyRate * $otMultiplier, 4);

        if ($input && $input->lines) {
            $otLine = $input->lines->firstWhere('input_type', 'overtime_hours');
            if ($otLine && (float) $otLine->quantity > 0) {
                $otHours = (float) $otLine->quantity;
                $otAmount = round($otHours * $otRate, 4);

                $earnings[] = [
                    'component_id' => null,
                    'earning_code' => 'OVERTIME_REG',
                    'earning_name' => 'Regular Overtime',
                    'earning_type' => 'overtime',
                    'rate' => $otRate,
                    'quantity' => $otHours,
                    'amount' => $otAmount,
                    'currency' => $currency,
                    'is_taxable' => true,
                    'is_pensionable' => false,
                    'calculation_source' => 'attendance_timesheet',
                    'source_reference_id' => $otLine->source_entity_id,
                ];
            }

            // Bonuses from inputs
            $bonusLines = $input->lines->where('input_type', 'bonus');
            foreach ($bonusLines as $bLine) {
                $earnings[] = [
                    'component_id' => null,
                    'earning_code' => 'BONUS',
                    'earning_name' => $bLine->notes ?: 'Bonus',
                    'earning_type' => 'bonus',
                    'rate' => (float) $bLine->amount,
                    'quantity' => 1.0,
                    'amount' => (float) $bLine->amount,
                    'currency' => $currency,
                    'is_taxable' => true,
                    'is_pensionable' => false,
                    'calculation_source' => 'bonus_approval',
                    'source_reference_id' => $bLine->source_entity_id,
                ];
            }
        }

        // 4. Approved Arrears
        $arrears = PayrollArrear::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($period) {
                $q->where('payroll_period_id', $period->id)
                    ->orWhereNull('payroll_period_id');
            })
            ->get();

        foreach ($arrears as $arr) {
            $diff = (float) $arr->difference_amount;
            if ($diff > 0) {
                $earnings[] = [
                    'component_id' => null,
                    'earning_code' => 'ARREAR',
                    'earning_name' => "Arrear: {$arr->title}",
                    'earning_type' => 'arrear',
                    'rate' => $diff,
                    'quantity' => 1.0,
                    'amount' => $diff,
                    'currency' => $currency,
                    'is_taxable' => true,
                    'is_pensionable' => false,
                    'calculation_source' => 'retroactive_adjustment',
                    'source_reference_id' => $arr->id,
                ];
            }
        }

        // 5. Approved Manual Adjustments (Earnings)
        $adjustments = PayrollAdjustment::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('adjustment_type', 'earning')
            ->where(function ($q) use ($period) {
                $q->where('payroll_period_id', $period->id)
                    ->orWhere(function ($q2) use ($period) {
                        $q2->whereDate('effective_date', '>=', $period->start_date->toDateString())
                            ->whereDate('effective_date', '<=', $period->end_date->toDateString());
                    });
            })
            ->get();

        foreach ($adjustments as $adj) {
            $earnings[] = [
                'component_id' => null,
                'earning_code' => $adj->code,
                'earning_name' => $adj->title,
                'earning_type' => 'adjustment',
                'rate' => (float) $adj->amount,
                'quantity' => 1.0,
                'amount' => (float) $adj->amount,
                'currency' => $currency,
                'is_taxable' => true,
                'is_pensionable' => false,
                'calculation_source' => 'manual_adjustment',
                'source_reference_id' => $adj->id,
            ];
        }

        return $earnings;
    }
}
