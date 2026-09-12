<?php

namespace App\Domains\WorkforceOptimization\DTOs;

class ScenarioComparisonData
{
    public function __construct(
        public readonly ?string $baselineScenarioCode = null,
        public readonly array $comparedScenarios = [],
        public readonly ?string $recommendedScenarioCode = null,
        public readonly ?string $tradeoffSummary = null,
        public readonly string $scenarioName = '',
        public readonly string $scenarioType = '',
        public readonly array $options = [],
        public readonly ?string $recommendedOptionLabel = null,
        public readonly array $tradeOffAnalysis = []
    ) {}

    public function toArray(): array
    {
        return [
            'baseline_scenario_code' => $this->baselineScenarioCode,
            'compared_scenarios' => $this->comparedScenarios,
            'recommended_scenario_code' => $this->recommendedScenarioCode,
            'tradeoff_summary' => $this->tradeoffSummary,
            'scenario_name' => $this->scenarioName,
            'scenario_type' => $this->scenarioType,
            'options' => $this->options,
            'recommended_option_label' => $this->recommendedOptionLabel ?? $this->recommendedScenarioCode,
            'trade_off_analysis' => $this->tradeOffAnalysis,
        ];
    }
}
