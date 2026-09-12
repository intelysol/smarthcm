<?php

namespace App\Domains\SelfService\Services;

use App\Domains\SelfService\Models\HrServiceGeneratedDocument;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RequestTemplateService
{
    public function generateDocumentFromTemplate(HrServiceRequest $request, HrServiceTemplate $template): HrServiceGeneratedDocument
    {
        return DB::transaction(function () use ($request, $template) {
            $employee = $request->employee;
            $docNumber = 'DOC-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));

            // Server-side authoritative attribute resolution
            $replacements = [
                '{{employee.name}}' => $employee ? "{$employee->first_name} {$employee->last_name}" : 'N/A',
                '{{employee.code}}' => $employee?->employee_code ?? ($employee?->employee_number ?? 'N/A'),
                '{{employee.position}}' => $employee?->designation?->designation_name ?? 'Associate',
                '{{employee.department}}' => $employee?->department?->department_name ?? 'Corporate',
                '{{employee.joining_date}}' => $employee?->joining_date?->format('F d, Y') ?? now()->format('F d, Y'),
                '{{employee.employment_status}}' => ucfirst($employee?->employment_status ?? 'Active'),
                '{{request.purpose}}' => $request->form_data['purpose'] ?? ($request->subject ?? 'Official Requirement'),
                '{{request.addressee}}' => $request->form_data['addressee_organization'] ?? 'To Whom It May Concern',
                '{{date}}' => now()->format('F d, Y'),
            ];

            $rendered = str_replace(array_keys($replacements), array_values($replacements), $template->template_body);

            $status = $template->requires_approval ? 'pending_approval' : 'approved';

            return HrServiceGeneratedDocument::create([
                'tenant_id' => $request->tenant_id,
                'hr_service_request_id' => $request->id,
                'hr_service_template_id' => $template->id,
                'employee_id' => $request->employee_id,
                'document_number' => $docNumber,
                'title' => $template->name,
                'rendered_content' => $rendered,
                'pdf_file_path' => "certificates/{$docNumber}.pdf",
                'status' => $status,
            ]);
        });
    }

    public function approveDocument(HrServiceGeneratedDocument $document, User $approver): HrServiceGeneratedDocument
    {
        $document->update([
            'status' => 'approved',
            'approved_by_user_id' => $approver->id,
            'approved_at' => now(),
        ]);

        return $document->fresh();
    }

    public function acknowledgeDocument(HrServiceGeneratedDocument $document, ?string $ip = null): HrServiceGeneratedDocument
    {
        $document->update([
            'acknowledged_at' => now(),
            'acknowledged_ip' => $ip ?? '127.0.0.1',
            'status' => 'issued',
        ]);

        return $document->fresh();
    }
}
