<?php

namespace App\Domains\Career\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuccessionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'scope_type' => $this->scope_type,
            'review_date' => $this->review_date?->toDateString(),
            'status' => $this->status,
            'positions_count' => $this->positions?->count() ?? 0,
        ];
    }
}
