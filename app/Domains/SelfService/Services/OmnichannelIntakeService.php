<?php

namespace App\Domains\SelfService\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceOmnichannelMessage;
use App\Domains\SelfService\Models\HrServiceRequest;

class OmnichannelIntakeService
{
    public function __construct(
        protected ServiceRequestService $requestService
    ) {}

    /**
     * Ingest an omnichannel message into the unified HR Service Delivery model.
     */
    public function ingestMessage(string $tenantId, string $channel, array $payload): HrServiceRequest
    {
        $senderIdentifier = $payload['sender_email'] ?? $payload['sender_phone'] ?? $payload['sender_id'] ?? 'unknown';
        $senderName = $payload['sender_name'] ?? null;
        $subject = $payload['subject'] ?? 'Inbound HR Inquiry via ' . ucfirst($channel);
        $body = $payload['body'] ?? ($payload['message'] ?? '');

        // 1. Match Employee
        $employee = Employee::where('tenant_id', $tenantId)
            ->where(function ($q) use ($senderIdentifier) {
                $q->where('official_email', $senderIdentifier)
                    ->orWhere('personal_email', $senderIdentifier)
                    ->orWhere('mobile', $senderIdentifier);
            })->first();

        // Fallback employee if not found
        if (!$employee) {
            $employee = Employee::where('tenant_id', $tenantId)->first();
        }

        // 2. Match or fallback to General Service Definition
        $service = HrServiceDefinition::where('tenant_id', $tenantId)
            ->where(function ($q) use ($subject) {
                $q->where('name', 'LIKE', '%' . substr($subject, 0, 15) . '%')
                    ->orWhere('service_code', 'GENERAL_HR_INQUIRY');
            })->first();

        if (!$service) {
            $service = HrServiceDefinition::where('tenant_id', $tenantId)->first();
        }

        // 3. Create Service Request
        $request = $this->requestService->createRequest($employee, $service, [
            'subject' => $subject,
            'description' => $body,
            'source_domain_module' => 'omnichannel_' . $channel,
            'form_data' => ['inbound_channel' => $channel, 'sender' => $senderIdentifier],
        ]);

        // Submit request
        $submitted = $this->requestService->submitRequest($request);

        // 4. Log Omnichannel Inbound Record
        HrServiceOmnichannelMessage::create([
            'tenant_id' => $tenantId,
            'channel' => $channel,
            'external_message_id' => $payload['external_id'] ?? null,
            'sender_identifier' => $senderIdentifier,
            'sender_name' => $senderName,
            'matched_employee_id' => $employee->id,
            'hr_service_request_id' => $submitted->id,
            'subject' => $subject,
            'body' => $body,
            'raw_payload' => $payload,
            'processing_status' => 'converted_to_request',
        ]);

        return $submitted;
    }
}
