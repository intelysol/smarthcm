<?php

namespace App\Domains\Learning\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Learning\Models\LearningCertificate */
class LearningCertificateResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'certificate_number' => $this->certificate_number,
            'title' => $this->title,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee?->id,
                'first_name' => $this->employee?->first_name,
                'last_name' => $this->employee?->last_name,
            ]),
            'course_id' => $this->course_id,
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course?->id,
                'title' => $this->course?->title,
            ]),
            'issued_at' => $this->issued_at?->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'status' => $this->status,
            'is_external' => (bool) $this->is_external,
            'issuing_body' => $this->issuing_body,
            'document_id' => $this->document_id,
            'verification_code' => $this->verification_code,
            'verified_at' => $this->verified_at?->toIso8601String(),
        ];
    }
}
