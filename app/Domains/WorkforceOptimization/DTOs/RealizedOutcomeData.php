<?php

namespace App\Domains\WorkforceOptimization\DTOs;

class RealizedOutcomeData
{
    public function __construct(
        public readonly string $metricName,
        public readonly float $preActionValue,
        public readonly float $postActionValue,
        public readonly float $varianceValue,
        public readonly float $variancePct,
        public readonly float $realizedFinancialImpact,
        public readonly string $causalityLabel = 'CORRELATION',
        public readonly ?string $notes = null
    ) {}

    public function toArray(): array
    {
        return [
            'metric_name' => $this->metricName,
            'pre_action_value' => $this->preActionValue,
            'post_action_value' => $this->postActionValue,
            'variance_value' => $this->varianceValue,
            'variance_pct' => $this->variancePct,
            'realized_financial_impact' => $this->realizedFinancialImpact,
            'causality_label' => $this->causalityLabel,
            'notes' => $this->notes,
        ];
    }
}
