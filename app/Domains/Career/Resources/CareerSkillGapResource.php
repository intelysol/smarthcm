<?php

namespace App\Domains\Career\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareerSkillGapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'skill' => [
                'id' => (string) $this->skill_id,
                'name' => $this->skill?->name,
                'code' => $this->skill?->code,
            ],
            'current_level' => $this->current_level,
            'required_level' => $this->required_level,
            'gap' => $this->gap,
            'priority' => $this->priority,
            'status' => $this->status,
        ];
    }
}
