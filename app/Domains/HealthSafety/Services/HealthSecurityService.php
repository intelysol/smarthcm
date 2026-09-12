<?php

namespace App\Domains\HealthSafety\Services;

use App\Domains\HealthSafety\Models\HcmMedicalRestriction;
use App\Models\User;

class HealthSecurityService
{
    /**
     * Check if user is an authorized medical / occupational health officer.
     */
    public function isMedicalOfficer(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->is_platform_admin ?? false) {
            return true;
        }

        $hasMedicalRole = $user->roles()->whereIn('name', ['medical_officer', 'occupational_health_officer', 'occupational_health'])->exists();

        return $hasMedicalRole
            || $user->hasPermission('hcm.health.view_clinical_data')
            || $user->hasPermission('health.view.medical')
            || $user->permissions()->whereIn('name', ['hcm.health.view_clinical_data', 'health.view.medical'])->exists();
    }

    /**
     * Check if user has safety management permissions.
     */
    public function isSafetyOfficer(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->is_platform_admin ?? false) {
            return true;
        }

        $hasSafetyRole = $user->roles()->whereIn('name', ['safety_officer', 'safety_manager'])->exists();

        return $hasSafetyRole
            || $user->hasPermission('hcm.safety.report_incident')
            || $user->permissions()->whereIn('name', ['hcm.safety.report_incident', 'health.incident.investigate'])->exists();
    }

    /**
     * Return masked medical restriction object or array for viewer.
     */
    public function maskMedicalRestriction(HcmMedicalRestriction $restriction, ?User $user): HcmMedicalRestriction
    {
        $cloned = clone $restriction;

        if (!$this->isMedicalOfficer($user)) {
            $cloned->medical_rationale_restricted = '[RESTRICTED - MEDICAL ACCESS ONLY]';
        }

        return $cloned;
    }

    /**
     * Filter medical restriction details according to viewer role.
     * Managers & HR only see operational accommodations, NEVER clinical diagnoses or rationale.
     */
    public function filterRestrictionForViewer(HcmMedicalRestriction $restriction, ?User $user): array
    {
        $canSeeMedical = $this->isMedicalOfficer($user);

        return [
            'id' => $restriction->id,
            'employee_id' => $restriction->employee_id,
            'restriction_type' => $restriction->restriction_type,
            'title' => $restriction->title,
            'operational_description' => $restriction->operational_description,
            'effective_from' => $restriction->effective_from?->toDateString(),
            'effective_to' => $restriction->effective_to?->toDateString(),
            'is_permanent' => (bool) $restriction->is_permanent,
            'status' => $restriction->status,
            'medical_rationale_restricted' => $canSeeMedical ? $restriction->medical_rationale_restricted : '[RESTRICTED - MEDICAL ACCESS ONLY]',
            'has_restricted_access' => $canSeeMedical,
        ];
    }
}
