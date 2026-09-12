<?php

namespace App\Domains\EmployeeRelations\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeRelationEvidenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'evidence_number' => $this->evidence_number,
            'title' => $this->title,
            'description' => $this->description,
            'evidence_type' => $this->evidence_type,
            'source' => $this->source,
            'collected_by' => [
                'id' => $this->collector?->id,
                'name' => $this->collector?->name,
            ],
            'received_at' => $this->received_at?->toIso8601String(),
            'sha256_hash' => $this->sha256_hash,
            'confidentiality' => $this->confidentiality,
            'status' => $this->status,
            'is_relevant' => $this->is_relevant,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
