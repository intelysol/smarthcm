<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Offboarding\Enums\ClearanceDepartment;
use App\Domains\Offboarding\Enums\ClearanceStatus;
use App\Domains\Offboarding\Models\SeparationAudit;
use App\Domains\Offboarding\Models\SeparationClearance;
use App\Domains\Offboarding\Models\SeparationClearanceItem;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SeparationClearanceService
{
    public function initializeClearance(SeparationRequest $request): void
    {
        $departments = [
            ClearanceDepartment::HR->value => [
                'Exit interview completed',
                'ID card & personnel records collected',
                'Experience letter verification',
            ],
            ClearanceDepartment::FINANCE->value => [
                'Outstanding loans & advances resolved',
                'Corporate credit card surrendered',
                'Travel & expense claims settled',
            ],
            ClearanceDepartment::IT->value => [
                'Company laptop & peripherals returned',
                'Email and cloud application access scheduled for revocation',
                'VPN & MFA credentials decommissioned',
            ],
            ClearanceDepartment::ASSETS->value => [
                'Office keys and physical building access fob returned',
                'Tools & specialized equipment accounted for',
            ],
        ];

        foreach ($departments as $dept => $items) {
            $clearance = SeparationClearance::create([
                'tenant_id' => $request->tenant_id,
                'separation_request_id' => $request->id,
                'department' => $dept,
                'status' => ClearanceStatus::PENDING->value,
            ]);

            foreach ($items as $itemTitle) {
                SeparationClearanceItem::create([
                    'tenant_id' => $request->tenant_id,
                    'separation_clearance_id' => $clearance->id,
                    'title' => $itemTitle,
                    'status' => ClearanceStatus::PENDING->value,
                    'is_blocking' => true,
                ]);
            }
        }
    }

    public function clearItem(SeparationClearanceItem $item, User $user, ?string $comments = null): SeparationClearanceItem
    {
        $item->update([
            'status' => ClearanceStatus::CLEARED->value,
            'comments' => $comments,
            'completed_at' => now(),
        ]);

        $clearance = $item->clearance;

        // If all items are cleared or waived, mark department clearance as CLEARED
        $hasPending = $clearance->items()->whereNotIn('status', [ClearanceStatus::CLEARED->value, ClearanceStatus::WAIVED->value])->exists();
        if (!$hasPending) {
            $clearance->update([
                'status' => ClearanceStatus::CLEARED->value,
                'cleared_by' => $user->id,
                'cleared_at' => now(),
            ]);
        }

        return $item;
    }

    public function waiveClearance(SeparationClearance $clearance, User $user, string $reason): SeparationClearance
    {
        $canWaive = $user->is_platform_admin
            || (method_exists($user, 'hasPermission') && $user->hasPermission('separations.clearance_waive'));

        if (!$canWaive) {
            throw ValidationException::withMessages([
                'clearance' => 'Unauthorized: Waiving department clearance requires "separations.clearance_waive" permission.',
            ]);
        }

        return DB::transaction(function () use ($clearance, $user, $reason) {
            $clearance->items()->update(['status' => ClearanceStatus::WAIVED->value]);

            $clearance->update([
                'status' => ClearanceStatus::WAIVED->value,
                'waived_by' => $user->id,
                'waiver_reason' => $reason,
                'cleared_at' => now(),
            ]);

            SeparationAudit::create([
                'tenant_id' => $clearance->tenant_id,
                'separation_request_id' => $clearance->separation_request_id,
                'actor_id' => $user->id,
                'event_name' => 'clearance_waived',
                'reason' => "Department '{$clearance->department}' clearance waived: {$reason}",
            ]);

            return $clearance;
        });
    }
}
