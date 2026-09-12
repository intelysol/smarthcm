<?php

namespace App\Domains\Learning\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Learning\Models\LearningCourse */
class LearningCourseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'difficulty' => $this->difficulty,
            'delivery_type' => $this->delivery_type,
            'duration' => (float) $this->duration,
            'duration_unit' => $this->duration_unit,
            'language' => $this->language,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'provider_id' => $this->provider_id,
            'provider' => $this->whenLoaded('provider', fn () => [
                'id' => $this->provider?->id,
                'name' => $this->provider?->name,
                'type' => $this->provider?->type,
            ]),
            'current_version' => $this->current_version,
            'passing_score' => $this->passing_score !== null ? (float) $this->passing_score : null,
            'credit_points' => (float) $this->credit_points,
            'max_attempts' => $this->max_attempts,
            'requires_attendance' => (bool) $this->requires_attendance,
            'min_attendance_percentage' => $this->min_attendance_percentage !== null ? (float) $this->min_attendance_percentage : null,
            'versions' => $this->whenLoaded('versions'),
            'objectives' => $this->whenLoaded('objectives'),
            'prerequisites' => $this->whenLoaded('prerequisites'),
            'modules' => $this->whenLoaded('modules'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
