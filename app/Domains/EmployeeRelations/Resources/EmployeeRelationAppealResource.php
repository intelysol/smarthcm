<?php

namespace App\Domains\EmployeeRelations\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeRelationAppealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'appeal_number' => $this->appeal_number,
            'submitted_by' => [
                'id' => $this->submitter?->id,
                'name' => $this->submitter?->name,
            ],
            'reason' => $this->reason,
            'grounds' => $this->grounds,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewer' => [
                'id' => $this->reviewer?->id,
                'name' => $this->reviewer?->name,
            ],
            'status' => $this->status,
            'decision' => $this->decision,
            'decision_reason' => $this->decision_reason,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
        ];
    }
}
