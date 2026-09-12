<?php

namespace App\Domains\EmployeeDocuments\Services;

use App\Domains\EmployeeDocuments\Enums\DocumentStatus;
use App\Domains\EmployeeDocuments\Enums\RequirementStatus;
use App\Domains\EmployeeDocuments\Enums\VerificationStatus;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentAudit;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentVerification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeDocumentVerificationService
{
    public function verify(EmployeeDocument $doc, User $reviewer, ?string $comments = null): EmployeeDocument
    {
        $canVerify = $reviewer->is_platform_admin
            || (method_exists($reviewer, 'hasPermission') && $reviewer->hasPermission('employee_documents.verify'));

        if (!$canVerify) {
            throw ValidationException::withMessages([
                'verification' => 'Unauthorized: Document verification requires "employee_documents.verify" permission.',
            ]);
        }

        return DB::transaction(function () use ($doc, $reviewer, $comments) {
            $currentVersion = $doc->sharedDocument?->current_version ?? 1;

            $doc->update([
                'status' => DocumentStatus::VERIFIED->value,
                'verification_status' => VerificationStatus::VERIFIED->value,
                'verified_by' => $reviewer->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            // Synchronize matching requirement
            if ($doc->requirement) {
                $doc->requirement->update(['status' => RequirementStatus::VERIFIED->value]);
            }

            EmployeeDocumentVerification::create([
                'tenant_id' => $doc->tenant_id,
                'employee_document_id' => $doc->id,
                'reviewer_id' => $reviewer->id,
                'decision' => 'verified',
                'version' => $currentVersion,
                'comments' => $comments,
            ]);

            EmployeeDocumentAudit::create([
                'tenant_id' => $doc->tenant_id,
                'employee_document_id' => $doc->id,
                'actor_id' => $reviewer->id,
                'event_name' => 'verified',
                'new_state' => ['status' => DocumentStatus::VERIFIED->value, 'verification_status' => VerificationStatus::VERIFIED->value],
                'reason' => $comments ?? 'Document verified by HR',
            ]);

            return $doc;
        });
    }

    public function reject(EmployeeDocument $doc, User $reviewer, string $reason, ?string $comments = null): EmployeeDocument
    {
        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'Rejection requires a mandatory, specific explanation reason.',
            ]);
        }

        $canReject = $reviewer->is_platform_admin
            || (method_exists($reviewer, 'hasPermission') && $reviewer->hasPermission('employee_documents.reject'));

        if (!$canReject) {
            throw ValidationException::withMessages([
                'verification' => 'Unauthorized: Rejecting documents requires "employee_documents.reject" permission.',
            ]);
        }

        return DB::transaction(function () use ($doc, $reviewer, $reason, $comments) {
            $currentVersion = $doc->sharedDocument?->current_version ?? 1;

            $doc->update([
                'status' => DocumentStatus::REJECTED->value,
                'verification_status' => VerificationStatus::REJECTED->value,
                'rejection_reason' => $reason,
            ]);

            // Synchronize matching requirement
            if ($doc->requirement) {
                $doc->requirement->update(['status' => RequirementStatus::REJECTED->value]);
            }

            EmployeeDocumentVerification::create([
                'tenant_id' => $doc->tenant_id,
                'employee_document_id' => $doc->id,
                'reviewer_id' => $reviewer->id,
                'decision' => 'rejected',
                'reason' => $reason,
                'version' => $currentVersion,
                'comments' => $comments,
            ]);

            EmployeeDocumentAudit::create([
                'tenant_id' => $doc->tenant_id,
                'employee_document_id' => $doc->id,
                'actor_id' => $reviewer->id,
                'event_name' => 'rejected',
                'new_state' => ['status' => DocumentStatus::REJECTED->value, 'rejection_reason' => $reason],
                'reason' => $reason,
            ]);

            return $doc;
        });
    }
}
