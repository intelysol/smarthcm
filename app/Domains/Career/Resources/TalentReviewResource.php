<?php

namespace App\Domains\Career\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TalentReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'scope_type' => $this->scope_type,
            'review_date' => $this->review_date?->toDateString(),
            'status' => $this->status,
            'records_count' => $this->records?->count() ?? 0,
        ];
    }
}
