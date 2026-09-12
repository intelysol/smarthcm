<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Services;

use App\Domains\HealthSafety\Events\WorkplaceAccommodationRequested;
use App\Domains\HealthSafety\Models\HcmWorkplaceAccommodation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkplaceAccommodationService
{
    /**
     * Request a new workplace accommodation.
     */
    public function requestAccommodation(array $data, ?int $userId = null): HcmWorkplaceAccommodation
    {
        return DB::transaction(function () use ($data, $userId) {
            $reqNumber = $data['request_number'] ?? 'ACC-' . strtoupper(\Illuminate\Support\Str::random(8));

            $accommodation = HcmWorkplaceAccommodation::create([
                'tenant_id' => $data['tenant_id'],
                'employee_id' => $data['employee_id'],
                'request_number' => $reqNumber,
                'accommodation_type' => $data['accommodation_type'],
                'title' => $data['title'],
                'requested_adjustment' => $data['description'] ?? ($data['requested_adjustment'] ?? $data['title']),
                'decision' => HcmWorkplaceAccommodation::STATUS_REQUESTED,
                'review_date' => $data['review_date'] ?? null,
                'implemented_date' => $data['implemented_date'] ?? null,
                'cost_estimate' => $data['cost_estimate'] ?? null,
                'decision_notes' => $data['notes'] ?? null,
            ]);

            event(new WorkplaceAccommodationRequested($accommodation));

            return $accommodation;
        });
    }

    /**
     * Approve or reject an accommodation request.
     */
    public function assessAccommodation(
        HcmWorkplaceAccommodation $accommodation,
        bool $approved,
        array $details,
        int $approverId
    ): HcmWorkplaceAccommodation {
        $decision = $approved ? HcmWorkplaceAccommodation::STATUS_APPROVED : HcmWorkplaceAccommodation::STATUS_REJECTED;

        $accommodation->update([
            'decision' => $decision,
            'decided_by' => $approverId,
            'decided_at' => Carbon::now(),
            'decision_notes' => $details['notes'] ?? $accommodation->decision_notes,
            'review_date' => $details['review_date'] ?? $accommodation->review_date,
        ]);

        return $accommodation->fresh();
    }

    /**
     * Mark accommodation as implemented.
     */
    public function implementAccommodation(
        HcmWorkplaceAccommodation $accommodation,
        array $details,
        int $userId
    ): HcmWorkplaceAccommodation {
        $accommodation->update([
            'decision' => HcmWorkplaceAccommodation::STATUS_IMPLEMENTED,
            'implemented_date' => $details['start_date'] ?? ($details['implemented_date'] ?? Carbon::today()->toDateString()),
        ]);

        return $accommodation->fresh();
    }

    /**
     * Review or complete accommodation.
     */
    public function completeAccommodation(
        HcmWorkplaceAccommodation $accommodation,
        string $status,
        ?string $notes,
        int $userId
    ): HcmWorkplaceAccommodation {
        $accommodation->update([
            'decision' => $status,
            'decision_notes' => $notes ?? $accommodation->decision_notes,
        ]);

        return $accommodation->fresh();
    }
}

