<?php

namespace App\Domains\Platform\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'label' => $this->label, 'description' => $this->description, 'is_system' => $this->is_system, 'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->map(fn ($permission) => ['id' => $permission->id, 'name' => $permission->name, 'label' => $permission->label])), 'created_at' => $this->created_at?->toAtomString()];
    }
}
