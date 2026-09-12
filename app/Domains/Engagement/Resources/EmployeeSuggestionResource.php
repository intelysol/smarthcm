<?php

namespace App\Domains\Engagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_anonymous' => $this->is_anonymous,
            'author_name' => (! $this->is_anonymous && $this->employee)
                ? "{$this->employee->first_name} {$this->employee->last_name}"
                : 'Anonymous Employee',
            'category' => $this->category,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'votes_count' => $this->votes_count,
            'review_notes' => $this->review_notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
