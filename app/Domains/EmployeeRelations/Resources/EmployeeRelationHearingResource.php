<?php

namespace App\Domains\EmployeeRelations\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeRelationHearingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'title' => $this->title,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'location' => $this->location,
            'chairperson' => [
                'id' => $this->chairperson?->id,
                'name' => $this->chairperson?->name,
            ],
            'status' => $this->status,
            'notes' => $this->notes,
            'outcome' => $this->whenLoaded('outcome'),
        ];
    }
}
