<?php

namespace App\Domains\Onboarding\Services;

use App\Domains\Onboarding\Enums\DocumentVerificationStatus;
use App\Domains\Onboarding\Models\HcmOnboardingDocumentRequirement;
use App\Domains\Onboarding\Models\HcmOnboardingDocumentReview;
use Illuminate\Support\Facades\DB;

class OnboardingDocumentService
{
    public function submitDocument(HcmOnboardingDocumentRequirement $requirement, string $filePath, string $fileName): HcmOnboardingDocumentRequirement
    {
        $requirement->update([
            'file_path' => $filePath,
            'file_name' => $fileName,
            'status' => DocumentVerificationStatus::SUBMITTED->value,
            'submitted_at' => now(),
        ]);

        return $requirement;
    }

    public function verifyDocument(HcmOnboardingDocumentRequirement $requirement, int $reviewerId, ?string $comments = null): HcmOnboardingDocumentRequirement
    {
        return DB::transaction(function () use ($requirement, $reviewerId, $comments) {
            $requirement->update([
                'status' => DocumentVerificationStatus::VERIFIED->value,
            ]);

            HcmOnboardingDocumentReview::create([
                'tenant_id' => $requirement->tenant_id,
                'document_requirement_id' => $requirement->id,
                'reviewer_id' => $reviewerId,
                'status' => DocumentVerificationStatus::VERIFIED->value,
                'comments' => $comments,
                'reviewed_at' => now(),
            ]);

            return $requirement;
        });
    }

    public function rejectDocument(HcmOnboardingDocumentRequirement $requirement, int $reviewerId, string $reason): HcmOnboardingDocumentRequirement
    {
        return DB::transaction(function () use ($requirement, $reviewerId, $reason) {
            $requirement->update([
                'status' => DocumentVerificationStatus::REJECTED->value,
            ]);

            HcmOnboardingDocumentReview::create([
                'tenant_id' => $requirement->tenant_id,
                'document_requirement_id' => $requirement->id,
                'reviewer_id' => $reviewerId,
                'status' => DocumentVerificationStatus::REJECTED->value,
                'comments' => $reason,
                'reviewed_at' => now(),
            ]);

            return $requirement;
        });
    }
}
