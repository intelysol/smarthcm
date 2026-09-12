<?php

namespace App\Domains\EmployeeRelations\Services;

use App\Domains\EmployeeRelations\Enums\CaseStatus;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationLegalHold;
use App\Domains\EmployeeRelations\Models\EmployeeRelationRetentionPolicy;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RetentionAndLegalHoldService
{
    /**
     * Place a legal hold on an ER case
     */
    public function placeLegalHold(EmployeeRelationCase $case, User $placedBy, string $reason, ?string $reference = null): EmployeeRelationLegalHold
    {
        return DB::transaction(function () use ($case, $placedBy, $reason, $reference) {
            $holdReference = $reference ?? ('LH-' . $case->case_number . '-' . ($case->legalHolds()->count() + 1));

            $hold = EmployeeRelationLegalHold::query()->create([
                'tenant_id' => $case->tenant_id,
                'case_id' => $case->id,
                'hold_reference' => $holdReference,
                'reason' => $reason,
                'placed_by' => $placedBy->id,
                'placed_at' => now(),
                'status' => 'active',
            ]);

            $case->update(['is_locked' => true]);

            return $hold;
        });
    }

    /**
     * Release a legal hold
     */
    public function releaseLegalHold(EmployeeRelationLegalHold $hold, User $releasedBy, string $releaseReason): EmployeeRelationLegalHold
    {
        return DB::transaction(function () use ($hold, $releasedBy, $releaseReason) {
            $hold->update([
                'status' => 'released',
                'released_by' => $releasedBy->id,
                'released_at' => now(),
                'release_reason' => $releaseReason,
            ]);

            $case = $hold->case;
            if (! $case->hasActiveLegalHold()) {
                $case->update(['is_locked' => false]);
            }

            return $hold->fresh();
        });
    }

    /**
     * Controlled Case Disposal
     */
    public function disposeCase(EmployeeRelationCase $case, User $actor, string $reason): void
    {
        if ($case->hasActiveLegalHold()) {
            throw ValidationException::withMessages([
                'legal_hold' => 'Cannot dispose case because an active legal hold is currently placed.',
            ]);
        }

        DB::transaction(function () use ($case, $actor, $reason) {
            // Soft delete the case and mark archived
            $case->update([
                'status' => CaseStatus::ARCHIVED->value,
            ]);
            $case->delete();
        });
    }
}
