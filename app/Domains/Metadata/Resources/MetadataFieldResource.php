<?php

namespace App\Domains\Metadata\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MetadataFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'key' => $this->key, 'label' => $this->label, 'field_type' => $this->field_type, 'sort_order' => $this->sort_order, 'configuration' => $this->configuration, 'validation_rules' => $this->validation_rules, 'visibility_expression' => $this->visibility_expression, 'formula' => $this->formula];
    }
}
