<?php

namespace App\Domains\Engagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngagementCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'survey_id' => $this->survey_id,
            'survey_title' => $this->survey?->title,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'timezone' => $this->timezone,
            'status' => $this->status,
            'minimum_response_threshold' => $this->minimum_response_threshold,
            'is_recurring' => $this->is_recurring,
            'recipients_count' => $this->whenCounted('recipients'),
            'responses_count' => $this->whenCounted('responses'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
