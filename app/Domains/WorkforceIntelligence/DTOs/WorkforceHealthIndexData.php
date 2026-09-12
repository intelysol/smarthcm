<?php

namespace App\Domains\WorkforceIntelligence\DTOs;

class WorkforceHealthIndexData
{
    public function __construct(
        public float $compositeScore,
        public string $healthBand,
        public float $capacityDimensionScore,
        public float $productivityDimensionScore,
        public float $costDimensionScore,
        public float $retentionDimensionScore,
        public float $skillsDimensionScore,
        public float $complianceDimensionScore,
        public array $formulaWeights,
        public float $confidenceScore,
        public string $summaryDiagnosis,
        public array $contributingFactors = []
    ) {}

    public function toArray(): array
    {
        return [
            'composite_score' => $this->compositeScore,
            'health_band' => $this->healthBand,
            'dimensions' => [
                'capacity' => $this->capacityDimensionScore,
                'productivity' => $this->productivityDimensionScore,
                'cost' => $this->costDimensionScore,
                'retention' => $this->retentionDimensionScore,
                'skills' => $this->skillsDimensionScore,
                'compliance' => $this->complianceDimensionScore,
            ],
            'formula_weights' => $this->formulaWeights,
            'confidence_score' => $this->confidenceScore,
            'summary_diagnosis' => $this->summaryDiagnosis,
            'contributing_factors' => $this->contributingFactors,
        ];
    }
}
