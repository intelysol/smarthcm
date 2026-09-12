<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceRequirement;
use App\Domains\Employee\Models\Employee;
use Illuminate\Support\Collection;

class ComplianceApplicabilityService
{
    /**
     * Determine all compliance requirements applicable to an employee.
     */
    public function getApplicableRequirements(Employee $employee): Collection
    {
        $allRequirements = HcmComplianceRequirement::where('tenant_id', $employee->tenant_id)
            ->where('is_active', true)
            ->get();

        return $allRequirements->filter(function (HcmComplianceRequirement $req) use ($employee) {
            return $this->isApplicable($req, $employee);
        });
    }

    /**
     * Test whether a single requirement applies to an employee.
     */
    public function isApplicable(HcmComplianceRequirement $req, Employee $employee): bool
    {
        // 1. Legal Entity / Company filter
        if ($req->legal_entity_id && $req->legal_entity_id !== $employee->company_id) {
            return false;
        }

        // 2. Branch filter
        if ($req->branch_id && $req->branch_id !== $employee->branch_id) {
            return false;
        }

        // 3. Work Location filter
        if ($req->location_id && $req->location_id !== $employee->work_location_id) {
            return false;
        }

        // 4. Department filter
        if ($req->department_id && $req->department_id !== $employee->department_id) {
            return false;
        }

        // 5. Position / Designation filter
        if ($req->position_id && $req->position_id !== $employee->designation_id) {
            return false;
        }

        // 6. Country filter (checks employee country or workplace country)
        $workCountry = $employee->country;
        if ($req->country && !empty($workCountry) && strcasecmp($req->country, $workCountry) !== 0) {
            return false;
        }

        // 7. Nationality Criteria
        if (!empty($req->nationality_criteria)) {
            $nationality = $employee->nationality;
            $criteria = strtolower($req->nationality_criteria);

            if ($criteria === 'foreign') {
                // Must not be citizen of the employment country
                if (!empty($workCountry) && !empty($nationality) && strcasecmp($workCountry, $nationality) === 0) {
                    return false;
                }
            } elseif ($criteria === 'citizen') {
                // Must be citizen of the employment country
                if (empty($workCountry) || empty($nationality) || strcasecmp($workCountry, $nationality) !== 0) {
                    return false;
                }
            } else {
                // Explicit nationality requirement
                if (empty($nationality) || strcasecmp($criteria, $nationality) !== 0) {
                    return false;
                }
            }
        }

        return true;
    }
}
