<?php

namespace App\Domains\Engagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngagementSurveyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'survey_type' => $this->survey_type,
            'instructions' => $this->instructions,
            'confidentiality_type' => $this->confidentiality_type,
            'allow_multiple_responses' => $this->allow_multiple_responses,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->status,
            'version' => $this->version,
            'sections_count' => $this->whenCounted('sections'),
            'questions_count' => $this->whenCounted('questions'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
