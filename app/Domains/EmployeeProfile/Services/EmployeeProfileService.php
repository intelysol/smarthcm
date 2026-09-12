<?php

namespace App\Domains\EmployeeProfile\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeProfile\Models\EmployeeProfileAudit;
use App\Domains\EmployeeProfile\Models\EmployeeProfilePreference;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EmployeeProfileService
{
    public function __construct(
        protected ?EmployeeProfileSecurityService $securityService = null,
        protected ?EmployeeTimelineService $timelineService = null
    ) {
        $this->securityService = $securityService ?? new EmployeeProfileSecurityService();
        $this->timelineService = $timelineService ?? new EmployeeTimelineService();
    }

    public function getProfileHeader(Employee $employee, User $viewer): array
    {
        $prefs = EmployeeProfilePreference::where('employee_id', $employee->id)->first();
        $isOwner = !empty($viewer->employee_id) && (string) $viewer->employee_id === (string) $employee->id;
        $isHr = $viewer->is_platform_admin || (method_exists($viewer, 'hasPermission') && $viewer->hasPermission('employee_profile.view_sensitive'));

        $personalEmail = ($isOwner || $isHr || ($prefs && $prefs->show_personal_email))
            ? $employee->personal_email
            : null;

        $personalPhone = ($isOwner || $isHr || ($prefs && $prefs->show_personal_phone))
            ? $employee->mobile
            : null;

        return [
            'id' => $employee->id,
            'employee_code' => $employee->employee_code,
            'employee_number' => $employee->employee_number,
            'full_name' => $employee->fullName(),
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'job_title' => $employee->designation?->name ?? 'Employee',
            'department' => $employee->department?->name ?? 'General',
            'branch' => $employee->branch?->name ?? null,
            'location' => $employee->workLocation?->name ?? null,
            'manager' => $employee->reportingManager ? [
                'id' => $employee->reportingManager->id,
                'name' => $employee->reportingManager->fullName(),
                'code' => $employee->reportingManager->employee_code,
            ] : null,
            'employment_status' => $employee->employment_status,
            'employment_type' => $employee->employmentType?->name ?? 'Permanent',
            'work_email' => $employee->official_email,
            'work_phone' => $employee->office_phone ?? $personalPhone,
            'personal_email' => $personalEmail,
            'photo_path' => $employee->photo_path,
            'joining_date' => $employee->joining_date?->toDateString(),
        ];
    }

    public function getProfileSummary(Employee $employee, User $viewer): array
    {
        $this->securityService->authorizeProfileAccess($viewer, $employee);

        $summary = [
            'header' => $this->getProfileHeader($employee, $viewer),
            'sections' => [],
        ];

        // 1. Employment & Job Details (Core HR)
        $summary['sections']['employment'] = [
            'status' => 'available',
            'data' => [
                'joining_date' => $employee->joining_date?->toDateString(),
                'confirmation_date' => $employee->confirmation_date?->toDateString(),
                'probation_end_date' => $employee->probation_end_date?->toDateString(),
                'notice_period_days' => $employee->notice_period_days,
                'job_grade' => $employee->jobGrade?->name ?? null,
            ],
        ];

        // 2. Documents Subsystem (Resilient Aggregation)
        try {
            $docCount = EmployeeDocument::where('employee_id', $employee->id)->count();
            $verifiedCount = EmployeeDocument::where('employee_id', $employee->id)->where('verification_status', 'verified')->count();
            $summary['sections']['documents'] = [
                'status' => 'available',
                'data' => [
                    'total_documents' => $docCount,
                    'verified_documents' => $verifiedCount,
                ],
            ];
        } catch (\Throwable $e) {
            Log::warning('Documents subsystem unavailable during profile aggregation', ['error' => $e->getMessage()]);
            $summary['sections']['documents'] = [
                'status' => 'unavailable',
                'message' => 'Document summary temporarily unavailable',
            ];
        }

        // 3. Learning & Certifications (Resilient Aggregation)
        try {
            $certCount = $employee->certifications()->count();
            $skillCount = $employee->skills()->count();
            $summary['sections']['learning'] = [
                'status' => 'available',
                'data' => [
                    'certifications_count' => $certCount,
                    'skills_count' => $skillCount,
                ],
            ];
        } catch (\Throwable $e) {
            $summary['sections']['learning'] = [
                'status' => 'unavailable',
                'message' => 'Learning summary temporarily unavailable',
            ];
        }

        // 4. Payroll Subsystem (Permission-checked & Resilient)
        if ($this->securityService->canViewCompensation($viewer, $employee)) {
            try {
                $salaries = $employee->salaries()->latest('effective_from')->first();
                $summary['sections']['payroll'] = [
                    'status' => 'available',
                    'data' => [
                        'salary_structure' => $salaries ? 'Standard Package' : 'Not configured',
                        'currency' => 'USD',
                        'last_revision_date' => $salaries?->effective_from?->toDateString(),
                    ],
                ];
            } catch (\Throwable $e) {
                $summary['sections']['payroll'] = [
                    'status' => 'unavailable',
                    'message' => 'Payroll summary temporarily unavailable',
                ];
            }
        } else {
            $summary['sections']['payroll'] = [
                'status' => 'restricted',
                'message' => 'Compensation details restricted to authorized HR administrators',
            ];
        }

        // 5. Timeline Events
        $summary['sections']['timeline'] = [
            'status' => 'available',
            'data' => $this->timelineService->getTimelineEvents($employee, $viewer),
        ];

        // Audit view
        EmployeeProfileAudit::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'actor_id' => $viewer->id,
            'event_name' => 'profile_viewed',
            'section' => 'summary',
            'ip_address' => request()->ip(),
        ]);

        return $summary;
    }
}
