<?php

namespace App\Domains\WorkforceIntelligence\DTOs;

class CrossDomainExplanationData
{
    public function __construct(
        public string $kpiOrEvent,
        public string $observedSummary,
        public array $observedFacts = [],
        public array $correlatedFactors = [],
        public array $inferredCauses = [],
        public array $recommendedActions = []
    ) {}

    public function toArray(): array
    {
        return [
            'kpi_or_event' => $this->kpiOrEvent,
            'observed_summary' => $this->observedSummary,
            'observed_facts' => $this->observedFacts,
            'correlated_factors' => $this->correlatedFactors,
            'inferred_causes' => $this->inferredCauses,
            'recommended_actions' => $this->recommendedActions,
        ];
    }
}
