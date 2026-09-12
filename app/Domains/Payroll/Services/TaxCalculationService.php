<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Contracts\TaxRuleProviderInterface;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollTaxRule;
use App\Domains\Payroll\Models\PayrollTaxRuleVersion;
use App\Domains\Payroll\Providers\FlatRateTaxProvider;
use App\Domains\Payroll\Providers\ProgressiveTaxProvider;

class TaxCalculationService
{
    /**
     * Compute statutory income tax for an employee given their taxable income.
     *
     * @return array{
     *     tax_code: string,
     *     tax_name: string,
     *     tax_rule_version: ?string,
     *     taxable_income: float,
     *     exemptions: float,
     *     tax_amount: float,
     *     rebate_amount: float,
     *     net_tax_deducted: float,
     *     tax_bracket_breakdown: array
     * }
     */
    public function calculateTax(
        Employee $employee,
        PayrollPeriod $period,
        float $taxableIncome
    ): array {
        $tenantId = $employee->tenant_id;
        $periodEnd = $period->end_date->toDateString();

        // 1. Resolve active tax rule & version for period
        $taxRule = PayrollTaxRule::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();

        $version = null;
        if ($taxRule) {
            $version = PayrollTaxRuleVersion::query()
                ->where('tenant_id', $tenantId)
                ->where('payroll_tax_rule_id', $taxRule->id)
                ->where('is_active', true)
                ->whereDate('effective_from', '<=', $periodEnd)
                ->where(function ($q) use ($periodEnd) {
                    $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $periodEnd);
                })
                ->orderByDesc('effective_from')
                ->first();
        }

        if (! $version) {
            // Default 10% flat tax fallback if no specific tax rule version defined
            $taxAmount = round($taxableIncome * 0.10, 4);
            return [
                'tax_code' => 'TAX_DEFAULT',
                'tax_name' => 'Default Statutory Income Tax',
                'tax_rule_version' => 'DEFAULT_10PCT',
                'taxable_income' => $taxableIncome,
                'exemptions' => 0.0,
                'tax_amount' => $taxAmount,
                'rebate_amount' => 0.0,
                'net_tax_deducted' => $taxAmount,
                'tax_bracket_breakdown' => [
                    ['bracket_min' => 0, 'bracket_max' => null, 'rate' => 0.10, 'taxable_in_bracket' => $taxableIncome, 'tax_in_bracket' => $taxAmount],
                ],
            ];
        }

        /** @var TaxRuleProviderInterface $provider */
        $provider = $taxRule->calculation_mode === 'flat_rate'
            ? new FlatRateTaxProvider()
            : new ProgressiveTaxProvider();

        $result = $provider->calculateTax($taxableIncome, $version);

        return [
            'tax_code' => $taxRule->code,
            'tax_name' => $taxRule->name,
            'tax_rule_version' => $version->version_name,
            'taxable_income' => $result['taxable_income'],
            'exemptions' => $result['exemptions'],
            'tax_amount' => $result['tax_amount'],
            'rebate_amount' => $result['rebate_amount'],
            'net_tax_deducted' => $result['net_tax'],
            'tax_bracket_breakdown' => $result['bracket_breakdown'],
        ];
    }
}
