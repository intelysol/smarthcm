<?php

namespace App\Domains\Metadata\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MetadataEntityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'key' => $this->key, 'label' => $this->label, 'module' => $this->module, 'entity_type' => $this->entity_type, 'category' => $this->category, 'icon' => $this->icon, 'color' => $this->color, 'description' => $this->description, 'status' => $this->status, 'version' => $this->version, 'settings' => $this->settings, 'fields_count' => $this->whenCounted('fields'), 'fields' => MetadataFieldResource::collection($this->whenLoaded('fields')), 'forms' => $this->whenLoaded('forms'), 'updated_at' => $this->updated_at?->toAtomString()];
    }
}
