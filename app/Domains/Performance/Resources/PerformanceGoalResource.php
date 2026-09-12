<?php

namespace App\Domains\Performance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceGoalResource extends JsonResource
{
    public function toArray(Request $request): array { return ['id' => $this->id, 'uuid' => $this->uuid, 'cycle_id' => $this->cycle_id, 'employee_id' => $this->employee_id, 'parent_goal_id' => $this->parent_goal_id, 'owner_type' => $this->owner_type, 'title' => $this->title, 'description' => $this->description, 'goal_type' => $this->goal_type, 'measurement_type' => $this->measurement_type, 'weight' => $this->weight, 'target_value' => $this->target_value, 'current_value' => $this->current_value, 'unit' => $this->unit, 'due_date' => $this->due_date?->toDateString(), 'status' => $this->status, 'progress_percentage' => $this->progress_percentage, 'priority' => $this->priority, 'version' => $this->version, 'milestones' => $this->whenLoaded('milestones'), 'progress_history' => $this->whenLoaded('progressRecords')]; }
}
