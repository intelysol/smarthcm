<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Enums\ProrationMethod;
use App\Domains\Payroll\Enums\RoundingMethod;
use App\Domains\Payroll\Models\PayrollCalculationLine;
use App\Domains\Payroll\Models\PayrollCalculationSnapshot;
use App\Domains\Payroll\Models\PayrollDeduction;
use App\Domains\Payroll\Models\PayrollEarning;
use App\Domains\Payroll\Models\PayrollEmployerContribution;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollPolicy;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Models\PayrollTax;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollCalculationEngine
{
    public function __construct(
        protected CompensationService $compensationService,
        protected PayrollInputService $inputService,
        protected ProrationService $prorationService,
        protected EarningsService $earningsService,
        protected DeductionService $deductionService,
        protected TaxCalculationService $taxCalculationService
    ) {}

    /**
     * Deterministically calculate payroll for a single employee in a payroll run.
     */
    public function calculateEmployeePayroll(PayrollRun $run, Employee $employee): PayrollCalculationSnapshot
    {
        $tenantId = $run->tenant_id;
        $period = $run->period;

        if ($period->isLocked() || ! $run->isModifiable()) {
            throw ValidationException::withMessages([
                'payroll_run' => "Cannot calculate payroll on locked run {$run->run_number} or period {$period->period_name}.",
            ]);
        }

        // 1. Resolve Policy
        $policy = PayrollPolicy::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();

        $prorationMethod = ProrationMethod::tryFrom($policy?->proration_method ?? 'calendar_days') ?? ProrationMethod::CALENDAR_DAYS;
        $roundingMethod = RoundingMethod::tryFrom($policy?->rounding_method ?? 'half_up') ?? RoundingMethod::HALF_UP;

        // 2. Resolve Active Compensation as of Period End Date
        $compensation = $this->compensationService->getActiveCompensationForDate($employee, $period->end_date->toDateString());

        // 3. Load Approved Inputs
        $input = $this->inputService->collectInputsForEmployee($period, $employee);

        // 4. Calculate Proration Factor
        $proration = $this->prorationService->calculateProration($employee, $period, $prorationMethod);
        $prorationFactor = $proration['factor'];

        // 5. Calculate Earnings
        $earningsList = $this->earningsService->calculateEarnings(
            $employee,
            $period,
            $compensation,
            $input,
            $prorationFactor,
            $policy
        );

        $grossPay = 0.0;
        $taxableGross = 0.0;
        foreach ($earningsList as $e) {
            $grossPay += (float) $e['amount'];
            if ($e['is_taxable']) {
                $taxableGross += (float) $e['amount'];
            }
        }

        // 6. Calculate Deductions (Pre-tax deductions e.g. Pension)
        $deductionsList = $this->deductionService->calculateDeductions(
            $employee,
            $period,
            $compensation,
            $input,
            $grossPay,
            $policy
        );

        $totalPreTaxDeductions = 0.0;
        $totalNonTaxDeductions = 0.0;
        foreach ($deductionsList as $d) {
            $totalNonTaxDeductions += (float) $d['amount'];
            if ($d['deduction_type'] === 'pension') {
                $totalPreTaxDeductions += (float) $d['amount'];
            }
        }

        // 7. Calculate Statutory Tax on (Taxable Gross - PreTax Deductions)
        $netTaxableIncome = max(0, $taxableGross - $totalPreTaxDeductions);
        $taxResult = $this->taxCalculationService->calculateTax($employee, $period, $netTaxableIncome);
        $taxDeducted = (float) $taxResult['net_tax_deducted'];

        // 8. Calculate Employer Contributions (e.g. 7.5% Employer Pension)
        $employerContributionsList = [];
        $totalEmployerCost = 0.0;
        $baseSalary = (float) ($compensation?->base_salary ?? 0);

        if ($compensation && $compensation->components) {
            foreach ($compensation->components as $line) {
                $comp = $line->component;
                if ($comp && $comp->component_type === 'employer_contribution') {
                    $amt = (float) $line->amount;
                    if ($line->calculation_type === 'percentage_of_basic' && $line->percentage) {
                        $amt = $baseSalary * ($line->percentage / 100);
                    }
                    $proratedAmt = round($amt * $prorationFactor, 4);
                    $totalEmployerCost += $proratedAmt;

                    $employerContributionsList[] = [
                        'contribution_code' => $comp->code,
                        'contribution_name' => $comp->name,
                        'contribution_type' => $comp->component_type,
                        'base_amount' => $baseSalary,
                        'rate_percentage' => $line->percentage,
                        'amount' => $proratedAmt,
                        'currency' => $compensation->currency,
                    ];
                }
            }
        }

        // 9. Total Deductions & Net Pay
        $totalDeductions = $totalNonTaxDeductions + $taxDeducted;
        $rawNetPay = $grossPay - $totalDeductions;
        $netPay = $roundingMethod->round($rawNetPay, 2);
        $grossPay = $roundingMethod->round($grossPay, 2);
        $totalDeductions = $roundingMethod->round($totalDeductions, 2);
        $taxDeducted = $roundingMethod->round($taxDeducted, 2);
        $totalEmployerCost = $roundingMethod->round($totalEmployerCost, 2);

        // 10. Persist Itemized Records Inside Transaction
        return DB::transaction(function () use (
            $run,
            $employee,
            $compensation,
            $input,
            $proration,
            $earningsList,
            $deductionsList,
            $taxResult,
            $employerContributionsList,
            $grossPay,
            $totalDeductions,
            $taxDeducted,
            $totalEmployerCost,
            $netPay,
            $roundingMethod,
            $policy
        ) {
            $tenantId = $run->tenant_id;

            // Delete old calculation lines for this employee in this run (Idempotency)
            PayrollEarning::query()->where('payroll_run_id', $run->id)->where('employee_id', $employee->id)->delete();
            PayrollDeduction::query()->where('payroll_run_id', $run->id)->where('employee_id', $employee->id)->delete();
            PayrollTax::query()->where('payroll_run_id', $run->id)->where('employee_id', $employee->id)->delete();
            PayrollEmployerContribution::query()->where('payroll_run_id', $run->id)->where('employee_id', $employee->id)->delete();
            PayrollCalculationSnapshot::query()->where('payroll_run_id', $run->id)->where('employee_id', $employee->id)->delete();

            // Save Earnings
            foreach ($earningsList as $e) {
                PayrollEarning::query()->create([
                    'tenant_id' => $tenantId,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'compensation_component_id' => $e['component_id'],
                    'earning_code' => $e['earning_code'],
                    'earning_name' => $e['earning_name'],
                    'earning_type' => $e['earning_type'],
                    'rate' => $e['rate'],
                    'quantity' => $e['quantity'],
                    'amount' => $e['amount'],
                    'currency' => $e['currency'],
                    'is_taxable' => $e['is_taxable'],
                    'is_pensionable' => $e['is_pensionable'],
                    'calculation_source' => $e['calculation_source'],
                    'source_reference_id' => $e['source_reference_id'],
                ]);
            }

            // Save Deductions
            foreach ($deductionsList as $d) {
                PayrollDeduction::query()->create([
                    'tenant_id' => $tenantId,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'compensation_component_id' => $d['component_id'],
                    'deduction_code' => $d['deduction_code'],
                    'deduction_name' => $d['deduction_name'],
                    'deduction_type' => $d['deduction_type'],
                    'amount' => $d['amount'],
                    'currency' => $d['currency'],
                    'priority_order' => $d['priority_order'],
                    'calculation_source' => $d['calculation_source'],
                    'source_reference_id' => $d['source_reference_id'],
                ]);
            }

            // Save Tax
            PayrollTax::query()->create([
                'tenant_id' => $tenantId,
                'payroll_run_id' => $run->id,
                'employee_id' => $employee->id,
                'tax_code' => $taxResult['tax_code'],
                'tax_name' => $taxResult['tax_name'],
                'tax_rule_version' => $taxResult['tax_rule_version'],
                'taxable_income' => $taxResult['taxable_income'],
                'exemptions' => $taxResult['exemptions'],
                'tax_amount' => $taxResult['tax_amount'],
                'rebate_amount' => $taxResult['rebate_amount'],
                'net_tax_deducted' => $taxResult['net_tax_deducted'],
                'currency' => $run->currency,
                'tax_bracket_breakdown' => $taxResult['tax_bracket_breakdown'],
            ]);

            // Save Employer Contributions
            foreach ($employerContributionsList as $ec) {
                PayrollEmployerContribution::query()->create([
                    'tenant_id' => $tenantId,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'contribution_code' => $ec['contribution_code'],
                    'contribution_name' => $ec['contribution_name'],
                    'contribution_type' => $ec['contribution_type'],
                    'base_amount' => $ec['base_amount'],
                    'rate_percentage' => $ec['rate_percentage'],
                    'amount' => $ec['amount'],
                    'currency' => $ec['currency'],
                ]);
            }

            // 11. Create Immutable Snapshot
            $idempotencyKey = hash('sha256', "RUN:{$run->id}:EMP:{$employee->id}:GROSS:{$grossPay}:NET:{$netPay}");

            /** @var PayrollCalculationSnapshot $snapshot */
            $snapshot = PayrollCalculationSnapshot::query()->create([
                'tenant_id' => $tenantId,
                'payroll_run_id' => $run->id,
                'employee_id' => $employee->id,
                'gross_pay' => $grossPay,
                'total_deductions' => $totalDeductions,
                'total_tax' => $taxDeducted,
                'total_employer_cost' => $totalEmployerCost,
                'net_pay' => $netPay,
                'currency' => $run->currency,
                'rounding_method' => $roundingMethod->value,
                'employee_snapshot' => [
                    'employee_id' => $employee->id,
                    'employee_number' => $employee->employee_number ?? $employee->employee_code,
                    'full_name' => $employee->fullName(),
                    'department' => $employee->department?->name ?? 'General',
                    'designation' => $employee->designation?->designation_name ?? 'Staff',
                    'joining_date' => $employee->joining_date?->toDateString(),
                    'proration' => $proration,
                ],
                'compensation_snapshot' => $compensation ? $compensation->toArray() : [],
                'inputs_snapshot' => $input ? $input->lines->toArray() : [],
                'earnings_snapshot' => $earningsList,
                'deductions_snapshot' => $deductionsList,
                'taxes_snapshot' => $taxResult,
                'employer_contributions_snapshot' => $employerContributionsList,
                'tax_rule_version' => $taxResult['tax_rule_version'],
                'policy_version' => $policy?->name ?? 'Standard Global Policy',
                'idempotency_key' => $idempotencyKey,
                'calculated_at' => now(),
            ]);

            // Save Snapshot Lines for Fast Aggregation / Reporting
            foreach ($earningsList as $e) {
                $snapshot->lines()->create([
                    'tenant_id' => $tenantId,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'line_category' => 'earning',
                    'line_code' => $e['earning_code'],
                    'line_name' => $e['earning_name'],
                    'amount' => $e['amount'],
                    'currency' => $e['currency'],
                ]);
            }

            foreach ($deductionsList as $d) {
                $snapshot->lines()->create([
                    'tenant_id' => $tenantId,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'line_category' => 'deduction',
                    'line_code' => $d['deduction_code'],
                    'line_name' => $d['deduction_name'],
                    'amount' => $d['amount'],
                    'currency' => $d['currency'],
                ]);
            }

            $snapshot->lines()->create([
                'tenant_id' => $tenantId,
                'payroll_run_id' => $run->id,
                'employee_id' => $employee->id,
                'line_category' => 'tax',
                'line_code' => $taxResult['tax_code'],
                'line_name' => $taxResult['tax_name'],
                'amount' => $taxResult['net_tax_deducted'],
                'currency' => $run->currency,
            ]);

            foreach ($employerContributionsList as $ec) {
                $snapshot->lines()->create([
                    'tenant_id' => $tenantId,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'line_category' => 'employer_contribution',
                    'line_code' => $ec['contribution_code'],
                    'line_name' => $ec['contribution_name'],
                    'amount' => $ec['amount'],
                    'currency' => $ec['currency'],
                ]);
            }

            return $snapshot;
        });
    }
}
