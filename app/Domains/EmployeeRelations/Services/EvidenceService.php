<?php

namespace App\Domains\EmployeeRelations\Services;

use App\Domains\Documents\Services\DocumentService;
use App\Domains\EmployeeRelations\Enums\EvidenceStatus;
use App\Domains\EmployeeRelations\Events\EmployeeRelationEvidenceAdded;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationEvidence;
use App\Domains\EmployeeRelations\Models\EmployeeRelationEvidenceHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EvidenceService
{
    public function __construct(
        protected DocumentService $documentService
    ) {}

    /**
     * Ingest and record evidence
     */
    public function storeEvidence(EmployeeRelationCase $case, User $actor, array $data, ?UploadedFile $file = null): EmployeeRelationEvidence
    {
        return DB::transaction(function () use ($case, $actor, $data, $file) {
            $evidenceNumber = 'EV-' . date('Y') . '-' . str_pad((string) ($case->evidence()->count() + 1), 4, '0', STR_PAD_LEFT);
            $sha256 = null;
            $documentId = null;
            $filePath = null;

            if ($file) {
                $sha256 = hash_file('sha256', $file->getRealPath());
                // Store in Document Platform
                $document = $this->documentService->store($case->tenant_id, $actor, $file, [
                    'title' => $data['title'] ?? $file->getClientOriginalName(),
                    'description' => "ER Evidence for case {$case->case_number}",
                    'category' => 'employee_relations',
                ]);
                $documentId = $document->id;
                $filePath = $document->versions()->first()?->storage_path;
            }

            $evidence = EmployeeRelationEvidence::query()->create([
                'tenant_id' => $case->tenant_id,
                'case_id' => $case->id,
                'evidence_number' => $evidenceNumber,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'evidence_type' => $data['evidence_type'] ?? 'document',
                'source' => $data['source'] ?? 'Uploaded by Investigator',
                'collected_by' => $actor->id,
                'received_at' => now(),
                'file_path' => $filePath,
                'document_id' => $documentId,
                'sha256_hash' => $sha256,
                'confidentiality' => $data['confidentiality'] ?? $case->confidentiality_level,
                'status' => EvidenceStatus::RETAINED->value,
                'is_relevant' => true,
            ]);

            // Log upload history
            $this->logHistory($evidence, 'uploaded', $actor, [
                'sha256' => $sha256,
                'evidence_type' => $evidence->evidence_type,
            ]);

            event(new EmployeeRelationEvidenceAdded($case, $evidence));

            return $evidence;
        });
    }

    /**
     * Log access, view, download or change history
     */
    public function logHistory(EmployeeRelationEvidence $evidence, string $action, ?User $user = null, array $metadata = []): EmployeeRelationEvidenceHistory
    {
        return EmployeeRelationEvidenceHistory::query()->create([
            'tenant_id' => $evidence->tenant_id,
            'evidence_id' => $evidence->id,
            'case_id' => $evidence->case_id,
            'action' => $action,
            'user_id' => $user?->id,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    /**
     * Controlled evidence disposal
     */
    public function disposeEvidence(EmployeeRelationEvidence $evidence, User $actor, string $reason): EmployeeRelationEvidence
    {
        // Prevent disposal if case has active legal hold
        if ($evidence->case->hasActiveLegalHold()) {
            throw ValidationException::withMessages([
                'legal_hold' => 'Cannot dispose evidence while case has an active legal hold.',
            ]);
        }

        $evidence->update([
            'status' => EvidenceStatus::DISPOSED->value,
        ]);

        $this->logHistory($evidence, 'disposed', $actor, ['reason' => $reason]);

        return $evidence->fresh();
    }
}
