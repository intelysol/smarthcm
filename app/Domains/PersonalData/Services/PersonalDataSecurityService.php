<?php

namespace App\Domains\PersonalData\Services;

use App\Models\User;

class PersonalDataSecurityService
{
    /**
     * Mask bank account number (show only last 4 digits).
     */
    public function maskBankAccount(?string $accountNumber): string
    {
        if (empty($accountNumber)) {
            return '';
        }

        $length = strlen($accountNumber);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($accountNumber, -4);
    }

    /**
     * Mask national identifier (e.g., CNIC 35202-1234567-1 -> 35202********1 or 13 digits 35202********1).
     */
    public function maskIdentifier(?string $idValue, string $type = 'national_id'): string
    {
        if (empty($idValue)) {
            return '';
        }

        $length = strlen($idValue);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        // Mask all except first 4 and last 1 or 2
        $prefixLen = min(4, intval($length / 3));
        $suffixLen = min(2, intval($length / 4));
        $maskLen = $length - $prefixLen - $suffixLen;

        return substr($idValue, 0, $prefixLen) . str_repeat('*', max(1, $maskLen)) . substr($idValue, -$suffixLen);
    }

    /**
     * Check if user is authorized to view unmasked sensitive personal data.
     */
    public function canViewSensitiveData(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->is_platform_admin ?? false) {
            return true;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('super-admin') || $user->hasRole('admin') || $user->hasRole('hr-admin'))) {
            return true;
        }

        if (method_exists($user, 'can') && $user->can('personal_data.view_sensitive')) {
            return true;
        }

        return false;
    }

    /**
     * Mask or reveal sensitive data according to user permissions.
     */
    public function formatMaskedIdentifier(?string $idValue, ?User $user, string $type = 'national_id'): string
    {
        if ($this->canViewSensitiveData($user)) {
            return $idValue ?? '';
        }

        return $this->maskIdentifier($idValue, $type);
    }

    /**
     * Mask or reveal bank account according to user permissions.
     */
    public function formatMaskedBankAccount(?string $accountNumber, ?User $user): string
    {
        if ($this->canViewSensitiveData($user)) {
            return $accountNumber ?? '';
        }

        return $this->maskBankAccount($accountNumber);
    }
}
