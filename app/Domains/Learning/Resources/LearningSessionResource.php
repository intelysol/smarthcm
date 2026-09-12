<?php

namespace App\Domains\Learning\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Learning\Models\LearningSession */
class LearningSessionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course?->id,
                'title' => $this->course?->title,
            ]),
            'provider_id' => $this->provider_id,
            'provider' => $this->whenLoaded('provider', fn () => [
                'id' => $this->provider?->id,
                'name' => $this->provider?->name,
            ]),
            'instructor_id' => $this->instructor_id,
            'instructor' => $this->whenLoaded('instructor', fn () => [
                'id' => $this->instructor?->id,
                'name' => $this->instructor?->name,
            ]),
            'venue_id' => $this->venue_id,
            'venue' => $this->whenLoaded('venue', fn () => [
                'id' => $this->venue?->id,
                'name' => $this->venue?->name,
                'venue_type' => $this->venue?->venue_type,
            ]),
            'title' => $this->title,
            'start_datetime' => $this->start_datetime?->toIso8601String(),
            'end_datetime' => $this->end_datetime?->toIso8601String(),
            'capacity' => $this->capacity,
            'enrollment_deadline' => $this->enrollment_deadline?->toIso8601String(),
            'status' => $this->status,
        ];
    }
}
