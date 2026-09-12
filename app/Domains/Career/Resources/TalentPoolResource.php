<?php

namespace App\Domains\Career\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TalentPoolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'criteria' => $this->criteria,
            'status' => $this->status,
            'members_count' => $this->members?->count() ?? 0,
        ];
    }
}
