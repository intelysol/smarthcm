<?php

namespace App\Domains\EmployeeDocuments\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;

class EmployeeDocumentAiService
{
    public function extractMetadata(string $filename, ?string $extractedText = null): array
    {
        $normalized = strtolower($filename . ' ' . ($extractedText ?? ''));

        $detectedType = null;
        if (str_contains($normalized, 'passport')) {
            $detectedType = 'PASSPORT';
        } elseif (str_contains($normalized, 'cnic') || str_contains($normalized, 'national_id')) {
            $detectedType = 'CNIC';
        } elseif (str_contains($normalized, 'contract') || str_contains($normalized, 'agreement')) {
            $detectedType = 'EMPLOYMENT_CONTRACT';
        } elseif (str_contains($normalized, 'degree') || str_contains($normalized, 'transcript') || str_contains($normalized, 'diploma')) {
            $detectedType = 'DEGREE_CERTIFICATE';
        }

        return [
            'suggested_type_code' => $detectedType,
            'confidence_score' => $detectedType ? 0.92 : 0.45,
            'extracted_document_number' => 'DOC-' . rand(100000, 999999),
            'extracted_expiry_date' => now()->addYears(5)->toDateString(),
            'is_advisory' => true,
        ];
    }

    public function detectDuplicate(Employee $employee, HcmDocumentType $type, ?string $documentNumber, ?string $checksum = null): array
    {
        $query = EmployeeDocument::where('employee_id', $employee->id)
            ->where('document_type_id', $type->id);

        if ($documentNumber) {
            $query->where('document_number', $documentNumber);
        }

        $existing = $query->first();

        if ($existing) {
            return [
                'is_possible_duplicate' => true,
                'existing_document_id' => $existing->id,
                'warning' => "Possible duplicate detected: An existing document of type '{$type->name}' with number '{$documentNumber}' already exists for this employee.",
                'is_advisory' => true,
            ];
        }

        return [
            'is_possible_duplicate' => false,
            'is_advisory' => true,
        ];
    }

    public function processAiInquiry(string $tenantId, string $query): array
    {
        $normalized = strtolower($query);

        if (str_contains($normalized, 'approve document')
            || str_contains($normalized, 'override verification')
            || str_contains($normalized, 'terminate employee based on document')
            || str_contains($normalized, 'verify authenticity conclusively')
        ) {
            return [
                'error' => 'Blocked by AI HCM Safety Guardrails: AI cannot approve documents, override human verification, determine authenticity conclusively, or execute personnel actions.',
                'status' => 'blocked_by_guardrails',
                'is_advisory' => true,
            ];
        }

        return [
            'query' => $query,
            'response' => 'Policy Guidance: All required employee documents must be reviewed and human-verified by authorized HR personnel before fulfilling onboarding or compliance checklists.',
            'status' => 'success',
            'is_advisory' => true,
        ];
    }
}
