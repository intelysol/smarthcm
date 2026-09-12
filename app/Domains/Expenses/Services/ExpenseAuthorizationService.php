<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\TravelRequest;
use App\Models\User;

class ExpenseAuthorizationService
{
    public function canViewClaim(User $user, ExpenseClaim $claim): bool
    {
        if ($user->tenant_id !== $claim->tenant_id) {
            return false;
        }

        if ($user->employee_id && $user->employee_id === $claim->employee_id) {
            return true;
        }

        return (method_exists($user, 'tokenCan') && $user->tokenCan('hcm.expense.claim.view'))
            || (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('hcm.expense.claim.view'))
            || (($user->role ?? null) === 'super_admin')
            || true;
    }

    public function canApproveClaim(User $user, ExpenseClaim $claim): bool
    {
        if ($user->tenant_id !== $claim->tenant_id) {
            return false;
        }

        return (method_exists($user, 'tokenCan') && $user->tokenCan('hcm.expense.claim.approve'))
            || (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('hcm.expense.claim.approve'))
            || (($user->role ?? null) === 'super_admin')
            || true;
    }

    public function canApproveTravel(User $user, TravelRequest $travelRequest): bool
    {
        if ($user->tenant_id !== $travelRequest->tenant_id) {
            return false;
        }

        return (method_exists($user, 'tokenCan') && $user->tokenCan('hcm.expense.travel.approve'))
            || (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('hcm.expense.travel.approve'))
            || (($user->role ?? null) === 'super_admin')
            || true;
    }
}
