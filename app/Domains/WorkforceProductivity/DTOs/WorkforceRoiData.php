<?php

namespace App\Domains\WorkforceProductivity\DTOs;

class WorkforceRoiData
{
    public function __construct(
        public readonly string $investmentName,
        public readonly string $investmentType,
        public readonly float $investmentCost,
        public readonly float $operationalBenefit,
        public readonly float $netBenefit,
        public readonly float $roiPercentage,
        public readonly ?float $paybackPeriodMonths,
        public readonly string $causalityLabel,
        public readonly ?float $preInvestmentRate = null,
        public readonly ?float $postInvestmentRate = null,
        public readonly array $details = [],
    ) {}

    public function toArray(): array
    {
        return [
            'investment_name' => $this->investmentName,
            'investment_type' => $this->investmentType,
            'investment_cost' => $this->investmentCost,
            'operational_benefit' => $this->operationalBenefit,
            'net_benefit' => $this->netBenefit,
            'roi_percentage' => $this->roiPercentage,
            'payback_period_months' => $this->paybackPeriodMonths,
            'causality_label' => $this->causalityLabel,
            'pre_investment_rate' => $this->preInvestmentRate,
            'post_investment_rate' => $this->postInvestmentRate,
            'details' => $this->details,
        ];
    }
}
