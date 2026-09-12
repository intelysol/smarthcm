<?php

namespace App\Domains\Payroll\Contracts;

use App\Domains\Payroll\Models\PayrollTaxRuleVersion;

interface TaxRuleProviderInterface
{
    /**
     * Calculate tax on taxable income based on the rule version.
     *
     * @return array{
     *     taxable_income: float,
     *     exemptions: float,
     *     tax_amount: float,
     *     rebate_amount: float,
     *     net_tax: float,
     *     bracket_breakdown: array<int, array{bracket_min: float, bracket_max: ?float, rate: float, taxable_in_bracket: float, tax_in_bracket: float}>
     * }
     */
    public function calculateTax(float $taxableIncome, PayrollTaxRuleVersion $version, array $exemptions = []): array;
}
