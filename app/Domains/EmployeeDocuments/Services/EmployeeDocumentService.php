<?php

namespace App\Domains\EmployeeDocuments\Services;

use App\Domains\Documents\Models\Document;
use App\Domains\Documents\Services\DocumentService;
use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Enums\DocumentStatus;
use App\Domains\EmployeeDocuments\Enums\RequirementStatus;
use App\Domains\EmployeeDocuments\Enums\VerificationStatus;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentAudit;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentRequirement;
use App\Domains\EmployeeDocuments\Models\HcmDocumentCategory;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class EmployeeDocumentService
{
    public function __construct(
        protected ?DocumentService $sharedDocService = null,
        protected ?EmployeeDocumentSecurityService $securityService = null
    ) {
        $this->sharedDocService = $sharedDocService ?? new DocumentService();
        $this->securityService = $securityService ?? new EmployeeDocumentSecurityService();
    }

    public function storeDocument(User $actor, Employee $employee, HcmDocumentType $docType, ?UploadedFile $file, array $data): EmployeeDocument
    {
        return DB::transaction(function () use ($actor, $employee, $docType, $file, $data) {
            // 1. Store file in shared Document Management service if provided
            $sharedDoc = null;
            if ($file) {
                $sharedDoc = $this->sharedDocService->store(
                    $employee->tenant_id,
                    $actor,
                    $file,
                    [
                        'title' => $data['title'] ?? $docType->name,
                        'description' => $data['description'] ?? null,
                        'module' => 'hcm_personnel_file',
                        'related_type' => 'App\\Domains\\Employee\\Models\\Employee',
                        'related_id' => $employee->id,
                        'classification' => $docType->confidentiality_level,
                    ]
                );
            } else {
                // System-generated or metadata-only document reference
                $sharedDoc = Document::create([
                    'tenant_id' => $employee->tenant_id,
                    'title' => $data['title'] ?? $docType->name,
                    'description' => $data['description'] ?? null,
                    'module' => 'hcm_personnel_file',
                    'related_type' => 'App\\Domains\\Employee\\Models\\Employee',
                    'related_id' => $employee->id,
                    'owner_id' => $actor->id,
                    'status' => 'active',
                    'current_version' => 1,
                    'classification' => $docType->confidentiality_level,
                ]);
            }

            // 2. Create EmployeeDocument association
            $employeeDoc = EmployeeDocument::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'document_type_id' => $docType->id,
                'document_id' => $sharedDoc->id,
                'title' => $data['title'] ?? $docType->name,
                'document_number' => $data['document_number'] ?? null,
                'issue_date' => $data['issue_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'status' => $data['status'] ?? DocumentStatus::SUBMITTED->value,
                'verification_status' => ($docType->requires_verification ?? true)
                    ? VerificationStatus::PENDING->value
                    : VerificationStatus::VERIFIED->value,
                'confidentiality_level' => $data['confidentiality_level'] ?? $docType->confidentiality_level ?? 'HR',
                'employee_visible' => $data['employee_visible'] ?? $docType->employee_visible ?? true,
                'manager_visible' => $data['manager_visible'] ?? $docType->manager_visible ?? true,
                'source' => $data['source'] ?? 'employee_upload',
                'related_type' => $data['related_type'] ?? null,
                'related_id' => $data['related_id'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);

            // 3. Link or update matching requirement
            $requirement = EmployeeDocumentRequirement::where('employee_id', $employee->id)
                ->where('document_type_id', $docType->id)
                ->first();

            if ($requirement) {
                $requirement->update([
                    'status' => $employeeDoc->verification_status === VerificationStatus::VERIFIED->value
                        ? RequirementStatus::VERIFIED->value
                        : RequirementStatus::SUBMITTED->value,
                    'employee_document_id' => $employeeDoc->id,
                ]);
            }

            // 4. Audit Trail
            EmployeeDocumentAudit::create([
                'tenant_id' => $employee->tenant_id,
                'employee_document_id' => $employeeDoc->id,
                'actor_id' => $actor->id,
                'event_name' => 'uploaded',
                'new_state' => ['title' => $employeeDoc->title, 'document_number' => $employeeDoc->document_number],
                'reason' => 'Document uploaded to digital personnel file',
            ]);

            return $employeeDoc->load(['documentType.category', 'sharedDocument.versions']);
        });
    }

    public function replaceDocument(EmployeeDocument $doc, User $actor, ?UploadedFile $file, array $data): EmployeeDocument
    {
        return DB::transaction(function () use ($doc, $actor, $file, $data) {
            $sharedDoc = $doc->sharedDocument;

            if ($file && $sharedDoc) {
                $this->sharedDocService->addVersion($sharedDoc, $actor, $file, $data['change_notes'] ?? 'Document replaced');
            }

            $oldStatus = $doc->status;
            $doc->update([
                'status' => DocumentStatus::SUBMITTED->value,
                'verification_status' => VerificationStatus::PENDING->value,
                'document_number' => $data['document_number'] ?? $doc->document_number,
                'issue_date' => $data['issue_date'] ?? $doc->issue_date,
                'expiry_date' => $data['expiry_date'] ?? $doc->expiry_date,
                'metadata' => $data['metadata'] ?? $doc->metadata,
                'rejection_reason' => null,
            ]);

            // Synchronize requirement status
            if ($doc->requirement) {
                $doc->requirement->update(['status' => RequirementStatus::SUBMITTED->value]);
            }

            EmployeeDocumentAudit::create([
                'tenant_id' => $doc->tenant_id,
                'employee_document_id' => $doc->id,
                'actor_id' => $actor->id,
                'event_name' => 'replaced',
                'old_state' => ['status' => $oldStatus],
                'new_state' => ['status' => DocumentStatus::SUBMITTED->value, 'verification_status' => VerificationStatus::PENDING->value],
                'reason' => $data['change_notes'] ?? 'Document replaced with new version',
            ]);

            return $doc->fresh(['documentType.category', 'sharedDocument.versions']);
        });
    }

    public function getPersonnelFile(Employee $employee, User $viewer): array
    {
        $categories = HcmDocumentCategory::where('tenant_id', $employee->tenant_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $documents = EmployeeDocument::where('employee_id', $employee->id)
            ->with(['documentType.category', 'sharedDocument.versions', 'verifications.reviewer'])
            ->get();

        $authorizedDocs = [];
        foreach ($documents as $doc) {
            if ($this->securityService->canView($viewer, $doc)) {
                $authorizedDocs[] = $doc;
            }
        }

        // Group by category code
        $grouped = [];
        foreach ($categories as $cat) {
            $grouped[$cat->code] = [
                'category' => $cat,
                'documents' => array_values(array_filter($authorizedDocs, fn ($d) => $d->documentType->category_id === $cat->id)),
            ];
        }

        return [
            'employee' => $employee,
            'categories' => $grouped,
            'total_authorized_documents' => count($authorizedDocs),
        ];
    }
}
