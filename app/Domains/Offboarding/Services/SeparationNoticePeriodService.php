<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Offboarding\Models\SeparationAudit;
use App\Domains\Offboarding\Models\SeparationNoticePeriod;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class SeparationNoticePeriodService
{
    public function initializeNoticePeriod(SeparationRequest $request): SeparationNoticePeriod
    {
        $noticeDays = $request->separationType->notice_days_default ?? 30;
        $startDate = $request->notice_start_date ? Carbon::parse($request->notice_start_date) : now();
        $calculatedLwd = $startDate->copy()->addDays($noticeDays);

        return SeparationNoticePeriod::create([
            'tenant_id' => $request->tenant_id,
            'separation_request_id' => $request->id,
            'notice_start_date' => $startDate->toDateString(),
            'required_days' => $noticeDays,
            'calculated_last_working_day' => $calculatedLwd->toDateString(),
            'agreed_days' => $noticeDays,
            'adjusted_last_working_day' => $request->proposed_last_working_day->toDateString(),
            'is_overridden' => false,
            'is_waived' => false,
            'is_buyout' => false,
            'buyout_amount' => 0.00,
        ]);
    }

    public function overrideNoticePeriod(SeparationNoticePeriod $notice, User $user, array $data): SeparationNoticePeriod
    {
        $canOverride = $user->is_platform_admin
            || (method_exists($user, 'hasPermission') && $user->hasPermission('separations.notice_override'));

        if (!$canOverride) {
            throw ValidationException::withMessages([
                'notice' => 'Unauthorized: Overriding notice period requires "separations.notice_override" permission.',
            ]);
        }

        $oldDays = $notice->agreed_days;
        $newDays = (int) $data['agreed_days'];
        $newLwd = Carbon::parse($notice->notice_start_date)->addDays($newDays);

        $notice->update([
            'agreed_days' => $newDays,
            'adjusted_last_working_day' => $newLwd->toDateString(),
            'is_overridden' => true,
            'override_reason' => $data['reason'] ?? 'Notice period shortened by management agreement',
            'overridden_by' => $user->id,
            'is_waived' => $data['is_waived'] ?? false,
            'is_buyout' => $data['is_buyout'] ?? false,
            'buyout_amount' => $data['buyout_amount'] ?? 0.00,
        ]);

        // Synchronize SeparationRequest proposed / approved LWD
        $notice->request->update([
            'approved_last_working_day' => $newLwd->toDateString(),
        ]);

        SeparationAudit::create([
            'tenant_id' => $notice->tenant_id,
            'separation_request_id' => $notice->separation_request_id,
            'actor_id' => $user->id,
            'event_name' => 'notice_overridden',
            'old_state' => ['agreed_days' => $oldDays],
            'new_state' => ['agreed_days' => $newDays, 'adjusted_lwd' => $newLwd->toDateString()],
            'reason' => $data['reason'] ?? 'Notice period adjusted',
        ]);

        return $notice;
    }
}
