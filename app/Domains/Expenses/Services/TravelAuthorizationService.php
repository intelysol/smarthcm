<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Models\TravelAuthorization;
use App\Domains\Expenses\Models\TravelRequest;
use App\Models\User;

class TravelAuthorizationService
{
    public function generateAuthorization(TravelRequest $travelRequest, float $approvedBudget, User $approver): TravelAuthorization
    {
        $authNumber = 'TA-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));

        return TravelAuthorization::updateOrCreate(
            [
                'tenant_id' => $travelRequest->tenant_id,
                'travel_request_id' => $travelRequest->id,
            ],
            [
                'authorization_number' => $authNumber,
                'approved_budget' => $approvedBudget,
                'currency' => $travelRequest->currency,
                'valid_from' => $travelRequest->start_date,
                'valid_to' => $travelRequest->end_date,
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'status' => 'active',
                'notes' => "Authorized for {$travelRequest->purpose} to {$travelRequest->destination}",
            ]
        );
    }
}
