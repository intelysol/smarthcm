<?php

namespace App\Domains\Learning\Services;

use App\Domains\Learning\Models\LearningEvidence;
use App\Domains\Learning\Models\LearningExternalRecord;
use Illuminate\Support\Facades\DB;

class LearningExternalService
{
    public function submitExternalRecord(array $data, ?array $evidenceFiles = null): LearningExternalRecord
    {
        return DB::transaction(function () use ($data, $evidenceFiles) {
            $record = LearningExternalRecord::create([
                'tenant_id' => $data['tenant_id'],
                'employee_id' => $data['employee_id'],
                'provider_name' => $data['provider_name'],
                'course_title' => $data['course_title'],
                'completion_date' => $data['completion_date'],
                'duration_hours' => $data['duration_hours'] ?? 0,
                'credits_earned' => $data['credits_earned'] ?? 0.00,
                'credential_id' => $data['credential_id'] ?? null,
                'status' => 'pending_verification',
            ]);

            if (!empty($evidenceFiles)) {
                foreach ($evidenceFiles as $file) {
                    LearningEvidence::create([
                        'tenant_id' => $record->tenant_id,
                        'external_record_id' => $record->id,
                        'evidence_type' => $file['evidence_type'] ?? 'certificate',
                        'file_path' => $file['file_path'],
                        'file_name' => $file['file_name'],
                        'mime_type' => $file['mime_type'] ?? 'application/pdf',
                        'file_size' => $file['file_size'] ?? 1024,
                    ]);
                }
            }

            return $record;
        });
    }

    public function verifyExternalRecord(LearningExternalRecord $record, int $verifiedByUserId, bool $approved, ?string $notes = null): LearningExternalRecord
    {
        $record->update([
            'status' => $approved ? 'verified' : 'rejected',
            'verified_by' => $verifiedByUserId,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);

        return $record;
    }
}
