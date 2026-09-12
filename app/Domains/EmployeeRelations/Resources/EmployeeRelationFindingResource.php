<?php

namespace App\Domains\EmployeeRelations\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeRelationFindingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'allegation_id' => $this->allegation_id,
            'allegation_title' => $this->allegation?->title,
            'investigator' => [
                'id' => $this->investigator?->id,
                'name' => $this->investigator?->name,
            ],
            'finding' => $this->finding,
            'confidence' => $this->confidence,
            'rationale' => $this->rationale,
            'evidence_summary' => $this->evidence_summary,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
        ];
    }
}
