<?php

namespace App\Domains\Rules\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessRuleResource extends JsonResource
{
    public function toArray(Request $request): array { return ['id' => $this->id, 'key' => $this->key, 'name' => $this->name, 'description' => $this->description, 'domain' => $this->domain, 'category' => $this->category, 'trigger' => $this->trigger, 'priority' => $this->priority, 'version' => $this->version, 'status' => $this->status, 'conditions' => $this->conditions, 'actions' => $this->actions, 'tags' => $this->tags, 'effective_from' => $this->effective_from?->toAtomString(), 'effective_to' => $this->effective_to?->toAtomString()]; }
}
