<?php

namespace App\Domains\WorkforceIntelligence\DTOs;

class ExecutiveScorecardData
{
    public function __construct(
        public int $totalHeadcount,
        public float $totalFte,
        public float $totalWorkforceCost,
        public float $averageCostPerFte,
        public float $compositeHealthScore,
        public string $healthBand,
        public float $overallProductivityScore,
        public float $turnoverRate,
        public float $absenceRate,
        public int $criticalRiskCount,
        public int $openDecisionCount,
        public string $periodKey,
        public array $topHighlights = [],
        public array $kpiMetrics = []
    ) {}

    public function toArray(): array
    {
        return [
            'total_headcount' => $this->totalHeadcount,
            'total_fte' => $this->totalFte,
            'total_workforce_cost' => $this->totalWorkforceCost,
            'average_cost_per_fte' => $this->averageCostPerFte,
            'composite_health_score' => $this->compositeHealthScore,
            'health_band' => $this->healthBand,
            'overall_productivity_score' => $this->overallProductivityScore,
            'turnover_rate' => $this->turnoverRate,
            'absence_rate' => $this->absenceRate,
            'critical_risk_count' => $this->criticalRiskCount,
            'open_decision_count' => $this->openDecisionCount,
            'period_key' => $this->periodKey,
            'top_highlights' => $this->topHighlights,
            'kpi_metrics' => $this->kpiMetrics,
        ];
    }
}
