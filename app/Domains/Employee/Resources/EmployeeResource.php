<?php

namespace App\Domains\Employee\Resources;

use App\Domains\Shared\Resources\BaseResource;
use Illuminate\Http\Request;

class EmployeeResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $canViewPersonal = $request->user()?->hasPermission('employee.personal') === true;

        return [
            'id' => $this->id,
            'employee_number' => $this->employee_number,
            'employee_code' => $this->employee_code,
            'full_name' => $this->fullName(),
            'employment_status' => $this->employment_status,
            'employment' => [
                'company_id' => $this->company_id,
                'branch_id' => $this->branch_id,
                'business_unit_id' => $this->business_unit_id,
                'department_id' => $this->department_id,
                'section_id' => $this->section_id,
                'team_id' => $this->team_id,
                'designation_id' => $this->designation_id,
                'job_grade_id' => $this->job_grade_id,
                'reporting_manager_id' => $this->reporting_manager_id,
                'joining_date' => $this->joining_date?->toDateString(),
            ],
            'personal' => [
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'preferred_name' => $this->preferred_name,
                'gender' => $this->gender,
                'date_of_birth' => $this->date_of_birth?->toDateString(),
                'national_id' => $canViewPersonal ? $this->national_id : null,
                'passport_number' => $canViewPersonal ? $this->passport_number : null,
            ],
            'contact' => [
                'personal_email' => $this->personal_email,
                'official_email' => $this->official_email,
                'mobile' => $this->mobile,
                'office_phone' => $this->office_phone,
            ],
            'relations' => [
                'department' => $this->whenLoaded('department'),
                'designation' => $this->whenLoaded('designation'),
                'branch' => $this->whenLoaded('branch'),
                'manager' => $this->whenLoaded('reportingManager'),
                'emergency_contacts' => $this->whenLoaded('emergencyContacts'),
                'family_members' => $this->whenLoaded('familyMembers'),
                'educations' => $this->whenLoaded('educations'),
                'work_experiences' => $this->whenLoaded('workExperiences'),
                'skills' => $this->whenLoaded('skills'),
                'certifications' => $this->whenLoaded('certifications'),
                'languages' => $this->whenLoaded('languages'),
                'bank_accounts' => $request->user()?->hasPermission('employee.bank') ? $this->whenLoaded('bankAccounts') : null,
                'salaries' => $request->user()?->hasPermission('employee.salary') ? $this->whenLoaded('salaries') : null,
                'documents' => $request->user()?->hasPermission('employee.documents') ? $this->whenLoaded('documents') : null,
                'timeline' => $request->user()?->hasPermission('employee.timeline') ? $this->whenLoaded('timelines') : null,
            ],
        ];
    }
}
