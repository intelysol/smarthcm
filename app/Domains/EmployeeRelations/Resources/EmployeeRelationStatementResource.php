<?php

namespace App\Domains\EmployeeRelations\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeRelationStatementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'participant_id' => $this->participant_id,
            'participant_name' => $this->participant?->name,
            'statement_type' => $this->statement_type,
            'content' => $this->content,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'verified_at' => $this->verified_at?->toIso8601String(),
            'is_confidential' => $this->is_confidential,
            'current_version' => $this->current_version,
            'versions' => $this->whenLoaded('versions'),
        ];
    }
}
