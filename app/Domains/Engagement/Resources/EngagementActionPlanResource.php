<?php

namespace App\Domains\Engagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngagementActionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'title' => $this->title,
            'description' => $this->description,
            'scope_type' => $this->scope_type,
            'scope_id' => $this->scope_id,
            'owner_id' => $this->owner_id,
            'owner_name' => $this->owner ? "{$this->owner->first_name} {$this->owner->last_name}" : null,
            'due_date' => $this->due_date?->toDateString(),
            'priority' => $this->priority,
            'status' => $this->status,
            'items_count' => $this->whenCounted('items'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
