<?php

namespace App\Domains\Career\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareerSkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'skill_type' => $this->skill_type,
            'category' => $this->category ? [
                'id' => (string) $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'assessment_interval_months' => $this->assessment_interval_months,
            'status' => $this->status,
        ];
    }
}
