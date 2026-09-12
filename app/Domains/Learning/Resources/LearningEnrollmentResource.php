<?php

namespace App\Domains\Learning\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Learning\Models\LearningEnrollment */
class LearningEnrollmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'employee_id' => $this->employee_id,
            'course_id' => $this->course_id,
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course?->id,
                'title' => $this->course?->title,
                'code' => $this->course?->code,
                'delivery_type' => $this->course?->delivery_type,
                'duration' => (float) ($this->course?->duration ?? 0),
            ]),
            'session_id' => $this->session_id,
            'session' => $this->whenLoaded('session'),
            'enrollment_type' => $this->enrollment_type,
            'status' => $this->status,
            'progress_percentage' => (float) $this->progress_percentage,
            'enrolled_at' => $this->enrolled_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'due_date' => $this->due_date?->toDateString(),
            'score' => $this->score !== null ? (float) $this->score : null,
            'passed' => $this->passed,
            'progress_records' => $this->whenLoaded('progressRecords'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
