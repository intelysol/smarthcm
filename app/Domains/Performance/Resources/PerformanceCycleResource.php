<?php

namespace App\Domains\Performance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceCycleResource extends JsonResource
{
    public function toArray(Request $request): array { return ['id' => $this->id, 'uuid' => $this->uuid, 'name' => $this->name, 'description' => $this->description, 'cycle_type' => $this->cycle_type, 'start_date' => $this->start_date?->toDateString(), 'end_date' => $this->end_date?->toDateString(), 'status' => $this->status, 'version' => $this->version, 'configuration' => $this->whenLoaded('configuration'), 'created_at' => $this->created_at?->toAtomString()]; }
}
