<?php

namespace App\Domains\Career\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'skill' => [
                'id' => (string) $this->skill_id,
                'code' => $this->skill?->code,
                'name' => $this->skill?->name,
                'skill_type' => $this->skill?->skill_type,
            ],
            'current_level' => $this->current_level,
            'target_level' => $this->target_level,
            'source' => $this->source,
            'verification_status' => $this->verification_status,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'last_assessed_at' => $this->last_assessed_at?->toDateString(),
            'next_assessment_at' => $this->next_assessment_at?->toDateString(),
            'evidence_count' => $this->evidence?->count() ?? 0,
            'notes' => $this->notes,
        ];
    }
}
