<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Offboarding\Models\SeparationDocument;
use App\Domains\Offboarding\Models\SeparationRequest;

class SeparationDocumentService
{
    public function generateDocument(SeparationRequest $request, string $docType, string $title, bool $employeeAccessible = true): SeparationDocument
    {
        $filePath = sprintf(
            'documents/offboarding/%s_%s.pdf',
            $request->request_number,
            strtolower($docType)
        );

        return SeparationDocument::create([
            'tenant_id' => $request->tenant_id,
            'separation_request_id' => $request->id,
            'document_type' => $docType,
            'title' => $title,
            'file_path' => $filePath,
            'employee_accessible' => $employeeAccessible,
            'generated_at' => now(),
        ]);
    }
}
