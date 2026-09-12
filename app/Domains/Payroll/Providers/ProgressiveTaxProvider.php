<?php

namespace App\Domains\Payroll\Providers;

use App\Domains\Payroll\Contracts\TaxRuleProviderInterface;
use App\Domains\Payroll\Models\PayrollTaxRuleVersion;

class ProgressiveTaxProvider implements TaxRuleProviderInterface
{
    public function calculateTax(float $taxableIncome, PayrollTaxRuleVersion $version, array $exemptions = []): array
    {
        $standardExemption = (float) ($version->standard_exemption ?? 0);
        $totalExemptions = $standardExemption + array_sum($exemptions);

        $netTaxable = max(0, $taxableIncome - $totalExemptions);
        $brackets = $version->tax_brackets ?? [];

        $totalTax = 0.0;
        $breakdown = [];

        foreach ($brackets as $b) {
            $min = (float) ($b['min'] ?? 0);
            $max = isset($b['max']) && $b['max'] !== null ? (float) $b['max'] : null;
            $rate = (float) ($b['rate'] ?? 0);

            if ($netTaxable <= $min) {
                continue;
            }

            $taxableInBracket = $max !== null ? min($netTaxable - $min, $max - $min) : ($netTaxable - $min);
            $taxInBracket = $taxableInBracket * $rate;

            $totalTax += $taxInBracket;
            $breakdown[] = [
                'bracket_min' => $min,
                'bracket_max' => $max,
                'rate' => $rate,
                'taxable_in_bracket' => round($taxableInBracket, 4),
                'tax_in_bracket' => round($taxInBracket, 4),
            ];
        }

        return [
            'taxable_income' => round($taxableIncome, 4),
            'exemptions' => round($totalExemptions, 4),
            'tax_amount' => round($totalTax, 4),
            'rebate_amount' => 0.0,
            'net_tax' => round($totalTax, 4),
            'bracket_breakdown' => $breakdown,
        ];
    }
}
