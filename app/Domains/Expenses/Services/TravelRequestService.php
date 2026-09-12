<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Enums\TravelStatus;
use App\Domains\Expenses\Enums\TravelType;
use App\Domains\Expenses\Models\TravelBooking;
use App\Domains\Expenses\Models\TravelRequest;
use App\Domains\Expenses\Models\TravelRequestSegment;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TravelRequestService
{
    protected AuditService $auditService;

    public function __construct(
        protected TravelAuthorizationService $authorizationService,
        ?AuditService $auditService = null
    ) {
        $this->auditService = $auditService ?? app(AuditService::class);
    }

    public function createTravelRequest(Employee $employee, array $data): TravelRequest
    {
        return DB::transaction(function () use ($employee, $data) {
            $requestNumber = 'TR-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));

            $travelRequest = TravelRequest::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'request_number' => $requestNumber,
                'travel_type' => $data['travel_type'] ?? TravelType::DOMESTIC->value,
                'destination' => $data['destination'],
                'purpose' => $data['purpose'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'estimated_cost' => $data['estimated_cost'] ?? 0.0000,
                'currency' => $data['currency'] ?? 'USD',
                'business_justification' => $data['business_justification'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'cost_center_id' => $data['cost_center_id'] ?? null,
                'per_diem' => $data['per_diem'] ?? 0.0000,
                'advance_required' => $data['advance_required'] ?? false,
                'status' => TravelStatus::DRAFT->value,
                'itinerary' => $data['itinerary'] ?? null,
            ]);

            // Add itinerary segments if provided
            if (!empty($data['segments']) && is_array($data['segments'])) {
                $order = 1;
                foreach ($data['segments'] as $seg) {
                    $travelRequest->segments()->create([
                        'tenant_id' => $travelRequest->tenant_id,
                        'segment_order' => $order++,
                        'origin' => $seg['origin'],
                        'destination' => $seg['destination'],
                        'departure_time' => $seg['departure_time'],
                        'arrival_time' => $seg['arrival_time'] ?? null,
                        'transport_type' => $seg['transport_type'] ?? 'flight',
                        'carrier_name' => $seg['carrier_name'] ?? null,
                        'booking_reference' => $seg['booking_reference'] ?? null,
                        'notes' => $seg['notes'] ?? null,
                    ]);
                }
            }

            $this->auditService->record(
                tenantId: $employee->tenant_id,
                eventType: 'travel_request.created',
                action: 'create',
                entityType: 'TravelRequest',
                entityId: $travelRequest->id,
                after: ['request_number' => $requestNumber, 'destination' => $travelRequest->destination, 'estimated_cost' => $travelRequest->estimated_cost]
            );

            return $travelRequest->fresh(['segments', 'employee']);
        });
    }

    public function submitTravelRequest(TravelRequest $travelRequest): TravelRequest
    {
        $oldStatus = $travelRequest->status;
        $travelRequest->update([
            'status' => TravelStatus::SUBMITTED->value,
        ]);

        $this->auditService->record(
            tenantId: $travelRequest->tenant_id,
            eventType: 'travel_request.submitted',
            action: 'update',
            entityType: 'TravelRequest',
            entityId: $travelRequest->id,
            before: ['status' => $oldStatus],
            after: ['status' => TravelStatus::SUBMITTED->value]
        );

        return $travelRequest->fresh();
    }

    public function approveTravelRequest(TravelRequest $travelRequest, User $approver, ?float $approvedBudget = null): TravelRequest
    {
        return DB::transaction(function () use ($travelRequest, $approver, $approvedBudget) {
            $budget = $approvedBudget !== null ? $approvedBudget : (float) $travelRequest->estimated_cost;
            $oldStatus = $travelRequest->status;

            $travelRequest->update([
                'status' => TravelStatus::APPROVED->value,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // Generate Travel Authorization
            $this->authorizationService->generateAuthorization($travelRequest, $budget, $approver);

            $this->auditService->record(
                tenantId: $travelRequest->tenant_id,
                eventType: 'travel_request.approved',
                action: 'update',
                entityType: 'TravelRequest',
                entityId: $travelRequest->id,
                actorId: is_numeric($approver->id) ? (int)$approver->id : null,
                before: ['status' => $oldStatus],
                after: ['status' => TravelStatus::APPROVED->value, 'budget' => $budget]
            );

            return $travelRequest->fresh(['authorization', 'segments']);
        });
    }

    public function rejectTravelRequest(TravelRequest $travelRequest, User $reviewer, ?string $reason = null): TravelRequest
    {
        $oldStatus = $travelRequest->status;
        $travelRequest->update([
            'status' => TravelStatus::REJECTED->value,
            'approved_by' => $reviewer->id,
            'approved_at' => now(),
            'business_justification' => $reason ? ($travelRequest->business_justification . ' [Rejected: ' . $reason . ']') : $travelRequest->business_justification,
        ]);

        $this->auditService->record(
            tenantId: $travelRequest->tenant_id,
            eventType: 'travel_request.rejected',
            action: 'update',
            entityType: 'TravelRequest',
            entityId: $travelRequest->id,
            actorId: is_numeric($reviewer->id) ? (int)$reviewer->id : null,
            before: ['status' => $oldStatus],
            after: ['status' => TravelStatus::REJECTED->value, 'reason' => $reason]
        );

        return $travelRequest->fresh();
    }

    public function addBooking(TravelRequest $travelRequest, array $bookingData): TravelBooking
    {
        $booking = $travelRequest->bookings()->create([
            'tenant_id' => $travelRequest->tenant_id,
            'booking_type' => $bookingData['booking_type'],
            'provider_name' => $bookingData['provider_name'],
            'booking_reference' => $bookingData['booking_reference'],
            'confirmation_number' => $bookingData['confirmation_number'] ?? null,
            'start_date' => $bookingData['start_date'],
            'end_date' => $bookingData['end_date'] ?? null,
            'cost' => $bookingData['cost'] ?? 0.0000,
            'currency' => $bookingData['currency'] ?? $travelRequest->currency,
            'status' => $bookingData['status'] ?? 'confirmed',
        ]);

        $this->auditService->record(
            tenantId: $travelRequest->tenant_id,
            eventType: 'travel_request.booking_added',
            action: 'create',
            entityType: 'TravelBooking',
            entityId: $booking->id,
            after: ['booking_type' => $booking->booking_type, 'provider' => $booking->provider_name, 'cost' => $booking->cost]
        );

        return $booking;
    }
}
