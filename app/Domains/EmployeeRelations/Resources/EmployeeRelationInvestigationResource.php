<?php

namespace App\Domains\EmployeeRelations\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeRelationInvestigationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'investigator' => [
                'id' => $this->investigator?->id,
                'name' => $this->investigator?->name,
                'email' => $this->investigator?->email,
            ],
            'scope' => $this->scope,
            'start_date' => $this->start_date?->toDateString(),
            'target_completion_date' => $this->target_completion_date?->toDateString(),
            'status' => $this->status,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'summary_findings' => $this->summary_findings,
            'steps' => $this->whenLoaded('steps'),
            'questions' => $this->whenLoaded('questions'),
        ];
    }
}
