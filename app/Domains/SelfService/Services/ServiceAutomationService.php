<?php

namespace App\Domains\SelfService\Services;

use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceGeneratedDocument;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceTemplate;
use Illuminate\Support\Str;

class ServiceAutomationService
{
    public function __construct(
        protected RequestDocumentService $documentService
    ) {}

    /**
     * Attempt automated fulfillment for standard document or routine self-service requests.
     */
    public function autoFulfill(HrServiceRequest $request): ?HrServiceGeneratedDocument
    {
        $service = $request->service;
        $employee = $request->employee;

        if (!$service || !$employee) {
            return null;
        }

        // 1. Automated Document Generation (e.g. Employment Certificate, Salary Certificate)
        $template = HrServiceTemplate::where('tenant_id', $request->tenant_id)
            ->where(function ($q) use ($service) {
                $q->where('hr_service_definition_id', $service->id)
                    ->orWhere('code', 'LIKE', '%' . $service->service_code . '%');
            })
            ->where('is_active', true)
            ->first();

        if ($template) {
            $docNumber = 'DOC-' . strtoupper(Str::random(8));

            // Render placeholders
            $renderedBody = str_replace(
                ['{{employee.name}}', '{{employee.number}}', '{{employee.joining_date}}', '{{date}}'],
                ["{$employee->first_name} {$employee->last_name}", $employee->employee_number, $employee->joining_date?->format('Y-m-d') ?? now()->toDateString(), now()->format('F d, Y')],
                $template->template_body
            );

            $document = HrServiceGeneratedDocument::create([
                'tenant_id' => $request->tenant_id,
                'hr_service_request_id' => $request->id,
                'hr_service_template_id' => $template->id,
                'employee_id' => $employee->id,
                'document_number' => $docNumber,
                'title' => $template->name . ' - ' . $employee->first_name,
                'rendered_content' => $renderedBody,
                'status' => 'issued',
            ]);

            // Auto-resolve request
            $request->update([
                'status' => ServiceRequestStatus::RESOLVED->value,
                'resolved_at' => now(),
            ]);

            return $document;
        }

        return null;
    }
}
