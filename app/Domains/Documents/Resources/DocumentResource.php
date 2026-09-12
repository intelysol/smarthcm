<?php
namespace App\Domains\Documents\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array { return ['id' => $this->id, 'title' => $this->title, 'description' => $this->description, 'tags' => $this->tags, 'module' => $this->module, 'related_type' => $this->related_type, 'related_id' => $this->related_id, 'status' => $this->status, 'classification' => $this->classification, 'current_version' => $this->current_version, 'owner_id' => $this->owner_id, 'created_at' => $this->created_at?->toAtomString(), 'versions' => $this->whenLoaded('versions')]; }
}
