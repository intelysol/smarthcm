<?php

namespace App\Domains\EmployeeRelations\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeRelationDecisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'decision_maker' => [
                'id' => $this->decisionMaker?->id,
                'name' => $this->decisionMaker?->name,
            ],
            'decision' => $this->decision,
            'reason' => $this->reason,
            'effective_date' => $this->effective_date?->toDateString(),
            'decided_at' => $this->decided_at?->toIso8601String(),
            'is_draft' => $this->is_draft,
            'corrective_actions' => EmployeeRelationCorrectiveActionResource::collection($this->whenLoaded('correctiveActions')),
        ];
    }
}
