<?php

namespace App\Domains\Payroll\Providers;

use App\Domains\Payroll\Contracts\TaxRuleProviderInterface;
use App\Domains\Payroll\Models\PayrollTaxRuleVersion;

class FlatRateTaxProvider implements TaxRuleProviderInterface
{
    public function calculateTax(float $taxableIncome, PayrollTaxRuleVersion $version, array $exemptions = []): array
    {
        $standardExemption = (float) ($version->standard_exemption ?? 0);
        $totalExemptions = $standardExemption + array_sum($exemptions);

        $netTaxable = max(0, $taxableIncome - $totalExemptions);
        $rate = (float) ($version->taxRule?->flat_rate_percentage ?? 0.10);
        $tax = $netTaxable * $rate;

        return [
            'taxable_income' => round($taxableIncome, 4),
            'exemptions' => round($totalExemptions, 4),
            'tax_amount' => round($tax, 4),
            'rebate_amount' => 0.0,
            'net_tax' => round($tax, 4),
            'bracket_breakdown' => [
                [
                    'bracket_min' => 0.0,
                    'bracket_max' => null,
                    'rate' => $rate,
                    'taxable_in_bracket' => round($netTaxable, 4),
                    'tax_in_bracket' => round($tax, 4),
                ],
            ],
        ];
    }
}
