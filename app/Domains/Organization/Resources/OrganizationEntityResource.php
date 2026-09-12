<?php

namespace App\Domains\Organization\Resources;

use App\Domains\Shared\Resources\BaseResource;
use Illuminate\Http\Request;

class OrganizationEntityResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'type' => $this->resource->getTable(),
            'attributes' => $this->resource->attributesToArray(),
        ];
    }
}
