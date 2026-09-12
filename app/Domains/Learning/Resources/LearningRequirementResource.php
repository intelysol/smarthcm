<?php

namespace App\Domains\Learning\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Learning\Models\LearningRequirement */
class LearningRequirementResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'course_id' => $this->course_id,
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course?->id,
                'title' => $this->course?->title,
            ]),
            'title' => $this->title,
            'description' => $this->description,
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            'deadline_type' => $this->deadline_type,
            'due_date' => $this->due_date?->toDateString(),
            'days_offset' => $this->days_offset,
            'is_recurring' => (bool) $this->is_recurring,
            'recurrence_interval_months' => $this->recurrence_interval_months,
            'compliance_category' => $this->compliance_category,
            'status' => $this->status,
        ];
    }
}
