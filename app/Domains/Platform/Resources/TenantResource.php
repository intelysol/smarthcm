<?php

namespace App\Domains\Platform\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'uuid' => $this->uuid ?? $this->id, 'code' => $this->tenant_code, 'name' => $this->name, 'legal_name' => $this->legal_name, 'slug' => $this->slug, 'status' => $this->status?->value ?? $this->status, 'timezone' => $this->timezone, 'locale' => $this->locale, 'currency' => $this->currency, 'country_code' => $this->country_code, 'logo' => $this->logo, 'activated_at' => $this->activated_at?->toAtomString(), 'suspended_at' => $this->suspended_at?->toAtomString(), 'created_at' => $this->created_at?->toAtomString()];
    }
}
