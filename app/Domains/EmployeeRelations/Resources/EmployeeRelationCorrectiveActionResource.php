<?php

namespace App\Domains\EmployeeRelations\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeRelationCorrectiveActionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'decision_id' => $this->decision_id,
            'action_type' => $this->action_type,
            'description' => $this->description,
            'assigned_employee' => [
                'id' => $this->assignedEmployee?->id,
                'name' => $this->assignedEmployee ? ($this->assignedEmployee->first_name . ' ' . $this->assignedEmployee->last_name) : null,
            ],
            'start_date' => $this->start_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status,
            'employee_acknowledgement_status' => $this->employee_acknowledgement_status,
            'employee_acknowledged_at' => $this->employee_acknowledged_at?->toIso8601String(),
            'employee_comment' => $this->employee_comment,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'verified_at' => $this->verified_at?->toIso8601String(),
        ];
    }
}
