<?php

namespace App\Domains\EmployeeRelations\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeRelationInterviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'participant' => [
                'id' => $this->participant?->id,
                'name' => $this->participant?->name,
                'role_title' => $this->participant?->role_title,
            ],
            'investigator' => [
                'id' => $this->investigator?->id,
                'name' => $this->investigator?->name,
            ],
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'location' => $this->location,
            'format' => $this->format,
            'status' => $this->status,
        ];
    }
}
