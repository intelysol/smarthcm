<?php

namespace App\Domains\EmployeeDocuments\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentAcknowledgement;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentAudit;

class EmployeeDocumentAcknowledgementService
{
    public function acknowledge(EmployeeDocument $doc, Employee $employee, ?string $ip = null, ?string $userAgent = null): EmployeeDocumentAcknowledgement
    {
        $currentVersion = $doc->sharedDocument?->current_version ?? 1;

        $ack = EmployeeDocumentAcknowledgement::create([
            'tenant_id' => $doc->tenant_id,
            'employee_document_id' => $doc->id,
            'employee_id' => $employee->id,
            'document_version' => $currentVersion,
            'acknowledged_at' => now(),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        EmployeeDocumentAudit::create([
            'tenant_id' => $doc->tenant_id,
            'employee_document_id' => $doc->id,
            'actor_id' => $employee->user_id,
            'event_name' => 'acknowledged',
            'new_state' => ['version' => $currentVersion],
            'reason' => 'Document formally acknowledged by employee',
            'ip_address' => $ip,
        ]);

        return $ack;
    }
}
