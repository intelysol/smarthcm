<?php

namespace App\Domains\PersonalData\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmEmergencyContact;
use App\Domains\PersonalData\Models\HcmEmployeeAddress;
use App\Domains\PersonalData\Models\HcmEmployeeDataQualityIssue;
use App\Domains\PersonalData\Models\HcmEmployeeDataQualityResult;
use App\Domains\PersonalData\Models\HcmEmployeeIdentifier;
use App\Domains\PersonalData\Models\HcmPersonalData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeDataQualityService
{
    /**
     * Calculate and persist employee data quality scores and issues.
     */
    public function calculateQuality(string $employeeId): HcmEmployeeDataQualityResult
    {
        return DB::transaction(function () use ($employeeId) {
            $employee = Employee::findOrFail($employeeId);
            $tenantId = $employee->tenant_id;

            $personalData = HcmPersonalData::where('employee_id', $employeeId)->first();
            $addresses = HcmEmployeeAddress::where('employee_id', $employeeId)->get();
            $contacts = HcmEmergencyContact::where('employee_id', $employeeId)->get();
            $identifiers = HcmEmployeeIdentifier::where('employee_id', $employeeId)->get();

            $issues = [];

            // 1. COMPLETENESS (Weight 40%)
            $completenessPoints = 0;
            $completenessMax = 10;

            // Personal data points (5 points)
            if (!empty($personalData?->first_name) || !empty($employee->first_name)) $completenessPoints++;
            if (!empty($personalData?->last_name) || !empty($employee->last_name)) $completenessPoints++;
            if (!empty($personalData?->date_of_birth) || !empty($employee->date_of_birth)) {
                $completenessPoints++;
            } else {
                $issues[] = [
                    'issue_code' => 'MISSING_DOB',
                    'severity' => 'warning',
                    'category' => 'completeness',
                    'description' => 'Employee date of birth is missing.',
                ];
            }
            if (!empty($personalData?->gender) || !empty($employee->gender)) $completenessPoints++;
            if (!empty($personalData?->personal_email) || !empty($employee->personal_email) || !empty($employee->official_email)) $completenessPoints++;

            // Address points (2 points)
            $hasCurrentAddress = $addresses->where('is_current', true)->isNotEmpty();
            if ($hasCurrentAddress) {
                $completenessPoints += 2;
            } else {
                $issues[] = [
                    'issue_code' => 'MISSING_CURRENT_ADDRESS',
                    'severity' => 'error',
                    'category' => 'completeness',
                    'description' => 'No active/current address on file.',
                ];
            }

            // Emergency contact points (2 points)
            $hasPrimaryContact = $contacts->where('is_primary', true)->isNotEmpty();
            if ($hasPrimaryContact) {
                $completenessPoints += 2;
            } else {
                $issues[] = [
                    'issue_code' => 'MISSING_PRIMARY_EMERGENCY_CONTACT',
                    'severity' => 'error',
                    'category' => 'completeness',
                    'description' => 'No primary emergency contact specified.',
                ];
            }

            // Identifier points (1 point)
            $hasPrimaryIdentifier = $identifiers->where('is_primary', true)->isNotEmpty() || !empty($employee->national_id);
            if ($hasPrimaryIdentifier) {
                $completenessPoints += 1;
            } else {
                $issues[] = [
                    'issue_code' => 'MISSING_PRIMARY_IDENTIFIER',
                    'severity' => 'critical',
                    'category' => 'completeness',
                    'description' => 'No primary national identifier or passport on file.',
                ];
            }

            $completenessScore = round(($completenessPoints / $completenessMax) * 100, 2);

            // 2. VALIDITY (Weight 30%)
            $validityPoints = 0;
            $validityMax = 4;

            // Email format
            $email = $personalData?->personal_email ?? $employee->personal_email;
            if (empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validityPoints++;
            } else {
                $issues[] = [
                    'issue_code' => 'INVALID_EMAIL_FORMAT',
                    'severity' => 'warning',
                    'category' => 'validity',
                    'description' => 'Personal email address format is invalid.',
                ];
            }

            // DOB sanity (age between 16 and 90)
            $dob = $personalData?->date_of_birth ?? $employee->date_of_birth;
            if ($dob) {
                $age = Carbon::parse($dob)->age;
                if ($age >= 16 && $age <= 90) {
                    $validityPoints++;
                } else {
                    $issues[] = [
                        'issue_code' => 'SUSPECT_DOB_AGE',
                        'severity' => 'warning',
                        'category' => 'validity',
                        'description' => "Calculated age ({$age}) is outside expected working age bounds.",
                    ];
                }
            } else {
                $validityPoints++;
            }

            // Non-expired identifiers
            $expiredIdentifiers = $identifiers->filter(function ($id) {
                return $id->expiry_date && Carbon::parse($id->expiry_date)->isPast();
            });
            if ($expiredIdentifiers->isEmpty()) {
                $validityPoints++;
            } else {
                $issues[] = [
                    'issue_code' => 'EXPIRED_IDENTIFIER',
                    'severity' => 'error',
                    'category' => 'validity',
                    'description' => 'One or more identification documents are expired.',
                ];
            }

            // Emergency contact phone format
            $primaryContact = $contacts->where('is_primary', true)->first();
            if ($primaryContact && strlen(preg_replace('/[^0-9]/', '', $primaryContact->primary_phone)) >= 7) {
                $validityPoints++;
            } else {
                $validityPoints++; // do not penalize if no contact or reasonably formatted
            }

            $validityScore = round(($validityPoints / $validityMax) * 100, 2);

            // 3. VERIFICATION (Weight 20%)
            if ($identifiers->isNotEmpty()) {
                $verifiedCount = $identifiers->where('verification_status', 'verified')->count();
                $verificationScore = round(($verifiedCount / $identifiers->count()) * 100, 2);
                if ($verificationScore < 50) {
                    $issues[] = [
                        'issue_code' => 'UNVERIFIED_DOCUMENTS',
                        'severity' => 'info',
                        'category' => 'verification',
                        'description' => 'Identification documents are pending formal HR verification.',
                    ];
                }
            } else {
                $verificationScore = 50.00;
            }

            // 4. FRESHNESS (Weight 10%)
            $lastUpdated = collect([
                $personalData?->updated_at,
                $addresses->max('updated_at'),
                $contacts->max('updated_at'),
                $identifiers->max('updated_at'),
                $employee->updated_at,
            ])->filter()->max();

            if ($lastUpdated && Carbon::parse($lastUpdated)->diffInMonths(Carbon::now()) <= 12) {
                $freshnessScore = 100.00;
            } elseif ($lastUpdated && Carbon::parse($lastUpdated)->diffInMonths(Carbon::now()) <= 24) {
                $freshnessScore = 70.00;
            } else {
                $freshnessScore = 40.00;
                $issues[] = [
                    'issue_code' => 'STALE_DATA',
                    'severity' => 'info',
                    'category' => 'freshness',
                    'description' => 'Personal data has not been updated or verified in over 24 months.',
                ];
            }

            // OVERALL SCORE (Weighted)
            $overallScore = round(
                ($completenessScore * 0.40) +
                ($validityScore * 0.30) +
                ($verificationScore * 0.20) +
                ($freshnessScore * 0.10),
                2
            );

            // Save quality result
            $result = HcmEmployeeDataQualityResult::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'tenant_id' => $tenantId,
                    'completeness_score' => $completenessScore,
                    'validity_score' => $validityScore,
                    'verification_score' => $verificationScore,
                    'freshness_score' => $freshnessScore,
                    'overall_score' => $overallScore,
                    'issues_count' => count($issues),
                    'calculated_at' => Carbon::now(),
                ]
            );

            // Sync issues
            HcmEmployeeDataQualityIssue::where('employee_id', $employeeId)->delete();
            foreach ($issues as $issue) {
                HcmEmployeeDataQualityIssue::create(array_merge($issue, [
                    'tenant_id' => $tenantId,
                    'employee_id' => $employeeId,
                ]));
            }

            return $result;
        });
    }

    /**
     * Get data quality summary for tenant.
     */
    public function getTenantQualitySummary(string $tenantId): array
    {
        $results = HcmEmployeeDataQualityResult::where('tenant_id', $tenantId)->get();

        if ($results->isEmpty()) {
            return [
                'total_employees_assessed' => 0,
                'avg_overall_score' => 0,
                'avg_completeness_score' => 0,
                'avg_validity_score' => 0,
                'avg_verification_score' => 0,
                'avg_freshness_score' => 0,
                'total_issues' => 0,
            ];
        }

        return [
            'total_employees_assessed' => $results->count(),
            'avg_overall_score' => round($results->avg('overall_score'), 1),
            'avg_completeness_score' => round($results->avg('completeness_score'), 1),
            'avg_validity_score' => round($results->avg('validity_score'), 1),
            'avg_verification_score' => round($results->avg('verification_score'), 1),
            'avg_freshness_score' => round($results->avg('freshness_score'), 1),
            'total_issues' => (int) $results->sum('issues_count'),
        ];
    }
}
