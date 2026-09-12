<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Offboarding\Enums\ClearanceStatus;
use App\Domains\Offboarding\Enums\SeparationCategory;
use App\Domains\Offboarding\Enums\SeparationStatus;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SeparationValidationService
{
    public function validateRequestCreation(User $user, Employee $employee, SeparationType $separationType, array $data): void
    {
        // 1. Involuntary termination / Redundancy requires specific authorization
        if (in_array($separationType->category, [SeparationCategory::INVOLUNTARY->value, 'redundancy'])) {
            $canTerminate = $user->is_platform_admin
                || (method_exists($user, 'hasPermission') && $user->hasPermission('separations.terminate'));

            if (!$canTerminate) {
                throw ValidationException::withMessages([
                    'separation_type' => 'Unauthorized: Involuntary termination requires "separations.terminate" permission.',
                ]);
            }
        }

        // 2. Self-service resignation scope: regular user can only resign themselves
        if ($data['source'] ?? '' === 'self_service') {
            if (!$user->is_platform_admin && $user->employee_id && (string) $user->employee_id !== (string) $employee->id) {
                throw ValidationException::withMessages([
                    'employee_id' => 'Unauthorized: Employees can only submit resignation for their own profile.',
                ]);
            }
        }
    }

    public function validateWithdrawal(SeparationRequest $request, User $user): void
    {
        if (in_array($request->status, [SeparationStatus::EXITED->value, SeparationStatus::CANCELLED->value])) {
            throw ValidationException::withMessages([
                'status' => 'Separation request is already finalized or cancelled.',
            ]);
        }

        // If already approved or in clearance, regular employee cannot withdraw without HR approval
        if (in_array($request->status, [SeparationStatus::APPROVED->value, SeparationStatus::CLEARANCE->value, SeparationStatus::FINAL_SETTLEMENT->value])) {
            $canManage = $user->is_platform_admin
                || (method_exists($user, 'hasPermission') && $user->hasPermission('separations.approve'));

            if (!$canManage) {
                throw ValidationException::withMessages([
                    'status' => 'Resignation has already been approved. Post-approval withdrawal requires HR authorization.',
                ]);
            }
        }
    }

    public function validateReadyForExit(SeparationRequest $request): void
    {
        // Check for any uncleared and unwaived blocking clearances
        $blockedClearance = $request->clearances()
            ->whereHas('items', function ($q) {
                $q->where('is_blocking', true)
                  ->whereNotIn('status', [ClearanceStatus::CLEARED->value, ClearanceStatus::WAIVED->value]);
            })
            ->whereNotIn('status', [ClearanceStatus::CLEARED->value, ClearanceStatus::WAIVED->value])
            ->first();

        if ($blockedClearance) {
            throw ValidationException::withMessages([
                'clearance' => "Exit blocked: Department '{$blockedClearance->department}' has unresolved blocking clearance items.",
            ]);
        }
    }
}
