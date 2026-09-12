<?php

namespace App\Domains\Learning\Services;

use App\Domains\Learning\Models\LearningCertificate;

class LearningPublicVerificationService
{
    public function verifyByCode(string $code): array
    {
        $certificate = LearningCertificate::where('verification_code', $code)
            ->with(['course', 'employee'])
            ->first();

        if (!$certificate) {
            return [
                'valid' => false,
                'message' => 'Certificate not found or verification code is invalid.',
            ];
        }

        $isExpired = $certificate->expiry_date && $certificate->expiry_date->isPast();
        $status = $isExpired ? 'expired' : $certificate->status;

        // Sanitized public verification payload (Zero exposure of personal/salary/contact data)
        return [
            'valid' => $status === 'active',
            'certificate_number' => $certificate->certificate_number,
            'title' => $certificate->title,
            'recipient_name' => $certificate->employee ? ($certificate->employee->first_name . ' ' . substr($certificate->employee->last_name ?? '', 0, 1) . '.') : 'Authorized Learner',
            'course_title' => $certificate->course?->title ?? $certificate->title,
            'issued_at' => $certificate->issued_at?->toDateString(),
            'expiry_date' => $certificate->expiry_date?->toDateString(),
            'issuing_body' => $certificate->issuing_body ?? 'Flow HCM Academy',
            'status' => $status,
            'verification_timestamp' => now()->toIso8601String(),
        ];
    }
}
