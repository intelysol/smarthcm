<?php

namespace App\Domains\Recruitment\Services;

use App\Domains\Recruitment\Enums\OfferStatus;
use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentOffer;
use App\Domains\Recruitment\Models\HcmRecruitmentOfferApproval;
use App\Domains\Recruitment\Models\HcmRecruitmentOfferVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfferManagementService
{
    public function createOffer(HcmRecruitmentApplication $application, array $data, int $actorId): HcmRecruitmentOffer
    {
        return DB::transaction(function () use ($application, $data, $actorId) {
            $offerNumber = 'OFR-' . strtoupper(bin2hex(random_bytes(4)));

            $offer = HcmRecruitmentOffer::create([
                'tenant_id' => $application->tenant_id,
                'offer_number' => $offerNumber,
                'application_id' => $application->id,
                'candidate_id' => $application->candidate_id,
                'requisition_id' => $application->requisition_id,
                'current_version' => 1,
                'base_salary' => $data['base_salary'],
                'bonus_amount' => $data['bonus_amount'] ?? 0.00,
                'currency' => $data['currency'] ?? 'USD',
                'start_date' => $data['start_date'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'employment_type' => $data['employment_type'] ?? 'full_time',
                'benefits_summary' => $data['benefits_summary'] ?? [],
                'status' => OfferStatus::DRAFT->value,
            ]);

            // Save initial version
            HcmRecruitmentOfferVersion::create([
                'tenant_id' => $offer->tenant_id,
                'offer_id' => $offer->id,
                'version_number' => 1,
                'base_salary' => $offer->base_salary,
                'bonus_amount' => $offer->bonus_amount,
                'start_date' => $offer->start_date,
                'expiry_date' => $offer->expiry_date,
                'change_rationale' => 'Initial approved offer drafted.',
                'created_by' => $actorId,
            ]);

            return $offer;
        });
    }

    public function createNewOfferVersion(HcmRecruitmentOffer $offer, array $newData, string $rationale, int $actorId): HcmRecruitmentOffer
    {
        if ($offer->status === OfferStatus::ACCEPTED->value) {
            throw ValidationException::withMessages(['offer' => 'Cannot modify an offer that has already been accepted.']);
        }

        return DB::transaction(function () use ($offer, $newData, $rationale, $actorId) {
            $nextVersion = $offer->current_version + 1;

            $offer->update([
                'current_version' => $nextVersion,
                'base_salary' => $newData['base_salary'] ?? $offer->base_salary,
                'bonus_amount' => $newData['bonus_amount'] ?? $offer->bonus_amount,
                'start_date' => $newData['start_date'] ?? $offer->start_date,
                'expiry_date' => $newData['expiry_date'] ?? $offer->expiry_date,
                'status' => OfferStatus::UNDER_REVIEW->value,
            ]);

            HcmRecruitmentOfferVersion::create([
                'tenant_id' => $offer->tenant_id,
                'offer_id' => $offer->id,
                'version_number' => $nextVersion,
                'base_salary' => $offer->base_salary,
                'bonus_amount' => $offer->bonus_amount,
                'start_date' => $offer->start_date,
                'expiry_date' => $offer->expiry_date,
                'change_rationale' => $rationale,
                'created_by' => $actorId,
            ]);

            return $offer;
        });
    }

    public function approveOffer(HcmRecruitmentOffer $offer, int $approverId, string $role = 'finance', ?string $comments = null): HcmRecruitmentOffer
    {
        HcmRecruitmentOfferApproval::create([
            'tenant_id' => $offer->tenant_id,
            'offer_id' => $offer->id,
            'approver_id' => $approverId,
            'role' => $role,
            'status' => 'approved',
            'comments' => $comments,
            'acted_at' => now(),
        ]);

        $offer->update([
            'status' => OfferStatus::APPROVED->value,
            'approved_at' => now(),
        ]);

        return $offer;
    }

    public function sendOfferToCandidate(HcmRecruitmentOffer $offer): HcmRecruitmentOffer
    {
        if ($offer->status !== OfferStatus::APPROVED->value) {
            throw ValidationException::withMessages(['status' => 'Offer must be approved before sending to candidate.']);
        }

        $offer->update([
            'status' => OfferStatus::SENT->value,
            'sent_at' => now(),
        ]);

        return $offer;
    }

    public function acceptOffer(HcmRecruitmentOffer $offer): HcmRecruitmentOffer
    {
        if ($offer->expiry_date && $offer->expiry_date->isPast()) {
            $offer->update(['status' => OfferStatus::EXPIRED->value]);
            throw ValidationException::withMessages(['expiry_date' => 'Offer has expired.']);
        }

        $offer->update([
            'status' => OfferStatus::ACCEPTED->value,
            'accepted_at' => now(),
        ]);

        return $offer;
    }
}
