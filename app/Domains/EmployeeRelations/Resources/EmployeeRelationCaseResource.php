<?php

namespace App\Domains\EmployeeRelations\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeRelationCaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'case_number' => $this->case_number,
            'case_type' => [
                'id' => $this->caseType?->id,
                'code' => $this->caseType?->code,
                'name' => $this->caseType?->name,
                'category' => $this->caseType?->category,
            ],
            'subject' => [
                'type' => $this->subject_type,
                'employee_id' => $this->subject_employee_id,
                'name' => $this->subjectEmployee ? ($this->subjectEmployee->first_name . ' ' . $this->subjectEmployee->last_name) : $this->subject_name,
            ],
            'title' => $this->title,
            'summary' => $this->summary,
            'priority' => $this->priority,
            'severity' => $this->severity,
            'status' => $this->status,
            'confidentiality_level' => $this->confidentiality_level,
            'incident_date' => $this->incident_date?->toDateString(),
            'incident_location' => $this->incident_location,
            'opened_at' => $this->opened_at?->toIso8601String(),
            'target_resolution_date' => $this->target_resolution_date?->toDateString(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'is_anonymous' => $this->is_anonymous,
            'is_locked' => $this->is_locked,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
