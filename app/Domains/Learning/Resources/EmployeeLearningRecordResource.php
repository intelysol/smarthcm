<?php

namespace App\Domains\Learning\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Learning\Models\EmployeeLearningRecord */
class EmployeeLearningRecordResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'course_id' => $this->course_id,
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course?->id,
                'title' => $this->course?->title,
                'code' => $this->course?->code,
                'delivery_type' => $this->course?->delivery_type,
            ]),
            'version_id' => $this->course_version_id,
            'provider_id' => $this->provider_id,
            'provider' => $this->whenLoaded('provider', fn () => [
                'id' => $this->provider?->id,
                'name' => $this->provider?->name,
            ]),
            'certificate_id' => $this->certificate_id,
            'certificate' => $this->whenLoaded('certificate'),
            'completion_date' => $this->completion_date?->toDateString(),
            'final_score' => $this->final_score !== null ? (float) $this->final_score : null,
            'credits_awarded' => (float) $this->credits_awarded,
            'learning_hours' => (float) $this->learning_hours,
            'status' => $this->status,
        ];
    }
}
