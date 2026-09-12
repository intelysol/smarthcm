<?php

namespace App\Domains\WorkforceIntelligence\DTOs;

class AiQueryResultData
{
    public function __construct(
        public string $naturalPrompt,
        public string $interpretedIntent,
        public array $citedKpis = [],
        public array $timeframe = [],
        public array $dimensions = [],
        public mixed $dataPayload = null,
        public string $narrativeAnswer = '',
        public array $limitationsAndDisclaimers = []
    ) {}

    public function toArray(): array
    {
        return [
            'natural_prompt' => $this->naturalPrompt,
            'interpreted_intent' => $this->interpretedIntent,
            'cited_kpis' => $this->citedKpis,
            'timeframe' => $this->timeframe,
            'dimensions' => $this->dimensions,
            'data_payload' => $this->dataPayload,
            'narrative_answer' => $this->narrativeAnswer,
            'limitations_and_disclaimers' => $this->limitationsAndDisclaimers,
        ];
    }
}
