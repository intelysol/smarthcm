<?php

namespace App\Domains\Engagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CultureInitiativeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'owner_id' => $this->owner_id,
            'owner_name' => $this->owner ? "{$this->owner->first_name} {$this->owner->last_name}" : null,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->status,
            'employees_reached' => $this->employees_reached,
            'actions_count' => $this->whenCounted('actions'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
