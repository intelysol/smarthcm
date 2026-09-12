<?php

namespace App\Domains\EmployeeDocuments\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Enums\DocumentRequestStatus;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentAudit;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentRequest;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Models\User;

class EmployeeDocumentRequestService
{
    public function createRequest(User $requester, Employee $employee, HcmDocumentType $type, ?string $dueDate = null, ?string $instructions = null): EmployeeDocumentRequest
    {
        $request = EmployeeDocumentRequest::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'document_type_id' => $type->id,
            'requested_by' => $requester->id,
            'requested_at' => now(),
            'due_date' => $dueDate,
            'status' => DocumentRequestStatus::REQUESTED->value,
            'instructions' => $instructions,
        ]);

        EmployeeDocumentAudit::create([
            'tenant_id' => $employee->tenant_id,
            'actor_id' => $requester->id,
            'event_name' => 'document_requested',
            'reason' => "Document request created for {$type->name}",
        ]);

        return $request;
    }

    public function completeRequest(EmployeeDocumentRequest $request, EmployeeDocument $doc): EmployeeDocumentRequest
    {
        $request->update([
            'status' => DocumentRequestStatus::SUBMITTED->value,
            'completed_at' => now(),
            'employee_document_id' => $doc->id,
        ]);

        return $request;
    }

    public function cancelRequest(EmployeeDocumentRequest $request, User $actor, string $reason): EmployeeDocumentRequest
    {
        $request->update([
            'status' => DocumentRequestStatus::CANCELLED->value,
        ]);

        EmployeeDocumentAudit::create([
            'tenant_id' => $request->tenant_id,
            'actor_id' => $actor->id,
            'event_name' => 'request_cancelled',
            'reason' => $reason,
        ]);

        return $request;
    }
}
