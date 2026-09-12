<?php

namespace App\Domains\SelfService\Services;

use App\Domains\SelfService\Events\ServiceRequestDocumentAdded;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceRequestDocument;
use App\Models\User;

class RequestDocumentService
{
    public function attachDocument(HrServiceRequest $request, User $uploader, array $data): HrServiceRequestDocument
    {
        $doc = $request->documents()->create([
            'tenant_id' => $request->tenant_id,
            'uploaded_by_user_id' => $uploader->id,
            'document_title' => $data['document_title'] ?? $data['file_name'],
            'file_path' => $data['file_path'] ?? ('hr_requests/' . uniqid() . '.pdf'),
            'file_name' => $data['file_name'] ?? 'document.pdf',
            'file_type' => $data['file_type'] ?? 'application/pdf',
            'file_size' => $data['file_size'] ?? 1024,
            'is_confidential' => $data['is_confidential'] ?? false,
        ]);

        event(new ServiceRequestDocumentAdded($doc));

        return $doc;
    }
}
