<?php

namespace App\Domains\Lifecycle\Services;

use App\Domains\Lifecycle\Models\PersonnelActionDocument;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;

class PersonnelActionDocumentService
{
    public function generateLetter(PersonnelActionRequest $request, string $docType, string $title): PersonnelActionDocument
    {
        $filePath = sprintf(
            'documents/lifecycle/%s_%s.pdf',
            $request->request_number,
            strtolower($docType)
        );

        return PersonnelActionDocument::create([
            'tenant_id' => $request->tenant_id,
            'personnel_action_request_id' => $request->id,
            'document_type' => $docType,
            'title' => $title,
            'file_path' => $filePath,
            'generated_at' => now(),
        ]);
    }
}
