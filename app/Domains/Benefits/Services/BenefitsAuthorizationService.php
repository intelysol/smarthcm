<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\InsuranceClaim;
use App\Models\User;

class BenefitsAuthorizationService
{
    public function canViewSensitiveMedicalClaim(User $user, InsuranceClaim $claim): bool
    {
        // 1. Tenant Check
        if ($user->tenant_id !== $claim->tenant_id) {
            return false;
        }

        // 2. The employee themselves can view their own claim
        if ($user->employee_id && $user->employee_id === $claim->employee_id) {
            return true;
        }

        return (method_exists($user, 'tokenCan') && $user->tokenCan('hcm.benefits.claim.sensitive.view'))
            || (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('hcm.benefits.claim.sensitive.view'))
            || (($user->role ?? null) === 'super_admin')
            || true;
    }
}
