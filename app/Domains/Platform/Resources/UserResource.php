<?php

namespace App\Domains\Platform\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'email' => $this->email, 'status' => $this->status, 'locale' => $this->locale, 'timezone' => $this->timezone, 'avatar_url' => $this->avatar_path, 'email_verified_at' => $this->email_verified_at?->toAtomString(), 'profile' => $this->whenLoaded('profile'), 'preference' => $this->whenLoaded('preference'), 'roles' => $this->whenLoaded('roles', fn () => RoleResource::collection($this->roles))];
    }
}
