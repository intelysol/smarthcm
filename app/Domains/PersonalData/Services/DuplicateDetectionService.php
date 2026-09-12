<?php

namespace App\Domains\PersonalData\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmEmployeeIdentifier;
use App\Domains\PersonalData\Models\HcmPersonalData;
use Illuminate\Support\Collection;

class DuplicateDetectionService
{
    /**
     * Find potential duplicate employee records within a tenant.
     * STRICT RULE: All results are ADVISORY. Automated merges are prohibited.
     */
    public function detectDuplicates(string $tenantId, ?string $employeeId = null): Collection
    {
        $query = Employee::where('tenant_id', $tenantId);
        if ($employeeId) {
            $query->where('id', $employeeId);
        }
        $employees = $query->get();

        $allEmployees = Employee::where('tenant_id', $tenantId)->get()->keyBy('id');
        $duplicates = collect();

        foreach ($employees as $emp) {
            $matches = $this->evaluateCandidates($emp, $allEmployees);
            foreach ($matches as $match) {
                $duplicates->push($match);
            }
        }

        return $duplicates;
    }

    /**
     * Evaluate candidates for an individual employee.
     */
    protected function evaluateCandidates(Employee $employee, Collection $allEmployees): Collection
    {
        $candidates = collect();

        foreach ($allEmployees as $other) {
            if ($other->id === $employee->id) {
                continue;
            }

            // Prevent duplicate pairs (only report A -> B where A < B or if evaluating single employee)
            $confidence = 0;
            $reasons = [];

            // 1. National ID / Identifiers Match
            if (!empty($employee->national_id) && !empty($other->national_id) && $employee->national_id === $other->national_id) {
                $confidence = max($confidence, 95);
                $reasons[] = 'Exact National ID match (' . $employee->national_id . ')';
            }

            // 2. Email Match
            if (!empty($employee->personal_email) && !empty($other->personal_email) && strtolower($employee->personal_email) === strtolower($other->personal_email)) {
                $confidence = max($confidence, 85);
                $reasons[] = 'Exact Personal Email match (' . $employee->personal_email . ')';
            }

            // 3. Mobile Phone Match
            if (!empty($employee->mobile) && !empty($other->mobile) && $employee->mobile === $other->mobile) {
                $confidence = max($confidence, 70);
                $reasons[] = 'Exact Mobile Phone match (' . $employee->mobile . ')';
            }

            // 4. Full Name + Date of Birth Match
            $fullNameA = strtolower(trim($employee->first_name . ' ' . $employee->last_name));
            $fullNameB = strtolower(trim($other->first_name . ' ' . $other->last_name));

            if (!empty($fullNameA) && $fullNameA === $fullNameB && !empty($employee->date_of_birth) && $employee->date_of_birth->toDateString() === $other->date_of_birth?->toDateString()) {
                $confidence = max($confidence, 80);
                $reasons[] = 'Full Name and Date of Birth match (' . $employee->first_name . ' ' . $employee->last_name . ' - ' . $employee->date_of_birth->toDateString() . ')';
            }

            if ($confidence > 0) {
                $candidates->push([
                    'source_employee_id' => $employee->id,
                    'source_employee_code' => $employee->employee_code,
                    'source_employee_name' => $employee->fullName(),
                    'candidate_employee_id' => $other->id,
                    'candidate_employee_code' => $other->employee_code,
                    'candidate_employee_name' => $other->fullName(),
                    'confidence_score' => $confidence,
                    'match_reasons' => $reasons,
                    'is_advisory' => true,
                    'automated_merge_allowed' => false,
                ]);
            }
        }

        return $candidates;
    }
}
