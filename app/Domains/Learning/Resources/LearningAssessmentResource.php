<?php

namespace App\Domains\Learning\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Learning\Models\LearningAssessment */
class LearningAssessmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'assessment_type' => $this->assessment_type,
            'scoring_method' => $this->scoring_method,
            'total_points' => (float) $this->total_points,
            'passing_percentage' => (float) $this->passing_percentage,
            'time_limit_minutes' => $this->time_limit_minutes,
            'max_attempts' => $this->max_attempts,
            'randomize_questions' => (bool) $this->randomize_questions,
            'status' => $this->status,
            'questions_count' => $this->whenCounted('questions'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
