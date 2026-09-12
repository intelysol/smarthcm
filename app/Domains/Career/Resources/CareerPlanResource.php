<?php

namespace App\Domains\Career\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareerPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'employee_id' => (string) $this->employee_id,
            'target_job' => $this->targetJob ? [
                'id' => (string) $this->targetJob->id,
                'title' => $this->targetJob->title,
                'code' => $this->targetJob->job_code,
            ] : null,
            'target_date' => $this->target_date?->toDateString(),
            'readiness_level' => $this->readiness_level,
            'readiness_score' => (float) $this->readiness_score,
            'visibility' => $this->visibility,
            'status' => $this->status,
            'actions' => $this->actions ? $this->actions->map(fn ($a) => [
                'id' => (string) $a->id,
                'action_type' => $a->action_type,
                'title' => $a->title,
                'due_date' => $a->due_date?->toDateString(),
                'status' => $a->status,
                'completion_percentage' => (float) $a->completion_percentage,
            ]) : [],
        ];
    }
}
