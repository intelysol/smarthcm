<?php

namespace App\Domains\Employee\Requests;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\CostCenter;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\Organization\Models\EmploymentType;
use App\Domains\Organization\Models\HolidayCalendar;
use App\Domains\Organization\Models\JobGrade;
use App\Domains\Organization\Models\Section;
use App\Domains\Organization\Models\Shift;
use App\Domains\Organization\Models\Team;
use App\Domains\Organization\Models\WorkLocation;
use App\Domains\Shared\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Validation\Validator;

class EmployeeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $action = $this->isMethod('post') ? 'create' : 'update';

        return $this->user()?->hasPermission("employee.{$action}") === true;
    }

    public function rules(): array
    {
        $rules = [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'employee_code' => ['nullable', 'string', 'max:80'],
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'business_unit_id' => ['nullable', 'uuid', 'exists:business_units,id'],
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'section_id' => ['nullable', 'uuid', 'exists:sections,id'],
            'team_id' => ['nullable', 'uuid', 'exists:teams,id'],
            'cost_center_id' => ['nullable', 'uuid', 'exists:cost_centers,id'],
            'designation_id' => ['nullable', 'uuid', 'exists:designations,id'],
            'job_grade_id' => ['nullable', 'uuid', 'exists:job_grades,id'],
            'reporting_manager_id' => ['nullable', 'uuid', 'exists:employees,id'],
            'employment_type_id' => ['nullable', 'uuid', 'exists:employment_types,id'],
            'work_location_id' => ['nullable', 'uuid', 'exists:work_locations,id'],
            'shift_id' => ['nullable', 'uuid', 'exists:shifts,id'],
            'holiday_calendar_id' => ['nullable', 'uuid', 'exists:holiday_calendars,id'],
            'payroll_group' => ['nullable', 'string', 'max:120'],
            'employment_status' => ['required', 'in:active,probation,confirmed,suspended,on_leave,resigned,terminated,retired'],
            'joining_date' => ['required', 'date'],
            'confirmation_date' => ['nullable', 'date', 'after_or_equal:joining_date'],
            'probation_end_date' => ['nullable', 'date', 'after_or_equal:joining_date'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:contract_start_date'],
            'notice_period_days' => ['nullable', 'integer', 'min:0'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'preferred_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female,non_binary,not_disclosed'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'marital_status' => ['nullable', 'string', 'max:40'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'religion' => ['nullable', 'string', 'max:120'],
            'national_id' => ['nullable', 'string', 'max:120'],
            'passport_number' => ['nullable', 'string', 'max:120'],
            'passport_expiry' => ['nullable', 'date'],
            'visa_number' => ['nullable', 'string', 'max:120'],
            'visa_expiry' => ['nullable', 'date'],
            'driving_license_number' => ['nullable', 'string', 'max:120'],
            'driving_license_expiry' => ['nullable', 'date'],
            'photo_path' => ['nullable', 'string', 'max:255'],
            'signature_path' => ['nullable', 'string', 'max:255'],
            'personal_email' => ['nullable', 'email:rfc', 'max:255'],
            'official_email' => ['nullable', 'email:rfc', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'alternate_mobile' => ['nullable', 'string', 'max:40'],
            'office_phone' => ['nullable', 'string', 'max:40'],
            'emergency_phone' => ['nullable', 'string', 'max:40'],
            'present_address' => ['nullable', 'string', 'max:1000'],
            'permanent_address' => ['nullable', 'string', 'max:1000'],
            'country' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:40'],
            'tags' => ['nullable', 'array'],
        ];

        if (! $this->isMethod('post')) {
            foreach ($rules as $field => $fieldRules) {
                array_unshift($fieldRules, 'sometimes');
                $rules[$field] = $fieldRules;
            }
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $tenantId = $this->user()?->tenant_id;

            if ($tenantId === null) {
                return;
            }

            foreach ([
                'user_id' => User::class,
                'company_id' => Company::class,
                'branch_id' => Branch::class,
                'business_unit_id' => BusinessUnit::class,
                'department_id' => Department::class,
                'section_id' => Section::class,
                'team_id' => Team::class,
                'cost_center_id' => CostCenter::class,
                'designation_id' => Designation::class,
                'job_grade_id' => JobGrade::class,
                'reporting_manager_id' => Employee::class,
                'employment_type_id' => EmploymentType::class,
                'work_location_id' => WorkLocation::class,
                'shift_id' => Shift::class,
                'holiday_calendar_id' => HolidayCalendar::class,
            ] as $field => $model) {
                $value = $this->input($field);

                if ($value !== null && ! $model::query()->where('tenant_id', $tenantId)->whereKey($value)->exists()) {
                    $validator->errors()->add($field, 'The selected record must belong to your tenant.');
                }
            }
        }];
    }
}
