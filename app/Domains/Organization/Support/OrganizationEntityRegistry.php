<?php

namespace App\Domains\Organization\Support;

use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\CostCenter;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\Organization\Models\EmploymentType;
use App\Domains\Organization\Models\Holiday;
use App\Domains\Organization\Models\HolidayCalendar;
use App\Domains\Organization\Models\JobCategory;
use App\Domains\Organization\Models\JobGrade;
use App\Domains\Organization\Models\Section;
use App\Domains\Organization\Models\Shift;
use App\Domains\Organization\Models\Team;
use App\Domains\Organization\Models\WorkLocation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrganizationEntityRegistry
{
    /**
     * @return OrganizationEntityDefinition
     */
    public function get(string $key): OrganizationEntityDefinition
    {
        $definitions = $this->all();

        if (! isset($definitions[$key])) {
            throw new ModelNotFoundException("Unsupported organization entity [{$key}].");
        }

        return $definitions[$key];
    }

    /**
     * @return array<string, OrganizationEntityDefinition>
     */
    public function all(): array
    {
        return [
            'companies' => new OrganizationEntityDefinition('companies', 'company', Company::class, [
                'name' => 'Company Name',
                'legal_name' => 'Legal Name',
                'registration_number' => 'Registration Number',
                'ntn' => 'NTN',
                'strn' => 'STRN',
                'industry' => 'Industry',
                'company_size' => 'Company Size',
                'website' => 'Website',
                'email' => 'Email',
                'phone' => 'Phone',
                'mobile' => 'Mobile',
                'fax' => 'Fax',
                'currency' => 'Currency',
                'timezone' => 'Timezone',
                'fiscal_year_start' => 'Fiscal Year Start',
                'address' => 'Address',
                'country' => 'Country',
                'state' => 'State',
                'city' => 'City',
                'postal_code' => 'Postal Code',
                'status' => 'Status',
            ], ['name', 'legal_name', 'registration_number', 'ntn', 'strn', 'email'], ['status', 'industry', 'country', 'city'], [
                'name' => ['required', 'string', 'max:255'],
                'legal_name' => ['nullable', 'string', 'max:255'],
                'registration_number' => ['nullable', 'string', 'max:100'],
                'ntn' => ['nullable', 'string', 'max:80'],
                'strn' => ['nullable', 'string', 'max:80'],
                'industry' => ['nullable', 'string', 'max:255'],
                'company_size' => ['nullable', 'string', 'max:80'],
                'website' => ['nullable', 'url', 'max:255'],
                'email' => ['nullable', 'email:rfc', 'max:255'],
                'phone' => ['nullable', 'string', 'max:40'],
                'mobile' => ['nullable', 'string', 'max:40'],
                'fax' => ['nullable', 'string', 'max:40'],
                'currency' => ['nullable', 'string', 'size:3'],
                'timezone' => ['nullable', 'timezone'],
                'fiscal_year_start' => ['nullable', 'date_format:m-d'],
                'address' => ['nullable', 'string', 'max:1000'],
                'country' => ['nullable', 'string', 'max:120'],
                'state' => ['nullable', 'string', 'max:120'],
                'city' => ['nullable', 'string', 'max:120'],
                'postal_code' => ['nullable', 'string', 'max:40'],
                'status' => ['nullable', 'in:active,inactive'],
            ], ['name']),
            'branches' => new OrganizationEntityDefinition('branches', 'branch', Branch::class, [
                'company_id' => 'Company',
                'branch_code' => 'Branch Code',
                'branch_name' => 'Branch Name',
                'region' => 'Region',
                'address' => 'Address',
                'contact_person' => 'Contact Person',
                'email' => 'Email',
                'phone' => 'Phone',
                'latitude' => 'Latitude',
                'longitude' => 'Longitude',
                'working_days' => 'Working Days',
                'status' => 'Status',
            ], ['branch_code', 'branch_name', 'region', 'contact_person', 'email'], ['company_id', 'region', 'status'], [
                'company_id' => ['required', 'uuid', 'exists:companies,id'],
                'branch_code' => ['required', 'string', 'max:80'],
                'branch_name' => ['required', 'string', 'max:255'],
                'region' => ['nullable', 'string', 'max:255'],
                'address' => ['nullable', 'string', 'max:1000'],
                'contact_person' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email:rfc', 'max:255'],
                'phone' => ['nullable', 'string', 'max:40'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'working_days' => ['nullable', 'array'],
                'status' => ['nullable', 'in:active,inactive'],
            ], ['branch_code']),
            'business-units' => new OrganizationEntityDefinition('business-units', 'business-unit', BusinessUnit::class, [
                'company_id' => 'Company',
                'branch_id' => 'Branch',
                'name' => 'Name',
                'code' => 'Code',
                'description' => 'Description',
                'head_id' => 'Head',
                'status' => 'Status',
            ], ['name', 'code', 'description'], ['company_id', 'branch_id', 'status'], [
                'company_id' => ['required', 'uuid', 'exists:companies,id'],
                'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
                'name' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:80'],
                'description' => ['nullable', 'string', 'max:1000'],
                'head_id' => ['nullable', 'integer', 'exists:users,id'],
                'status' => ['nullable', 'in:active,inactive'],
            ], ['code']),
            'departments' => new OrganizationEntityDefinition('departments', 'department', Department::class, [
                'business_unit_id' => 'Business Unit',
                'parent_department_id' => 'Parent Department',
                'department_code' => 'Department Code',
                'department_name' => 'Department Name',
                'manager_id' => 'Manager',
                'description' => 'Description',
                'status' => 'Status',
            ], ['department_code', 'department_name', 'description'], ['business_unit_id', 'parent_department_id', 'status'], [
                'business_unit_id' => ['required', 'uuid', 'exists:business_units,id'],
                'parent_department_id' => ['nullable', 'uuid', 'exists:departments,id'],
                'department_code' => ['required', 'string', 'max:80'],
                'department_name' => ['required', 'string', 'max:255'],
                'manager_id' => ['nullable', 'integer', 'exists:users,id'],
                'description' => ['nullable', 'string', 'max:1000'],
                'status' => ['nullable', 'in:active,inactive'],
            ], ['department_code']),
            'sections' => new OrganizationEntityDefinition('sections', 'section', Section::class, [
                'department_id' => 'Department',
                'section_code' => 'Section Code',
                'section_name' => 'Section Name',
                'supervisor_id' => 'Supervisor',
            ], ['section_code', 'section_name'], ['department_id'], [
                'department_id' => ['required', 'uuid', 'exists:departments,id'],
                'section_code' => ['required', 'string', 'max:80'],
                'section_name' => ['required', 'string', 'max:255'],
                'supervisor_id' => ['nullable', 'integer', 'exists:users,id'],
            ], ['section_code']),
            'teams' => new OrganizationEntityDefinition('teams', 'team', Team::class, [
                'section_id' => 'Section',
                'team_name' => 'Team Name',
                'team_lead_id' => 'Team Lead',
                'description' => 'Description',
            ], ['team_name', 'description'], ['section_id'], [
                'section_id' => ['required', 'uuid', 'exists:sections,id'],
                'team_name' => ['required', 'string', 'max:255'],
                'team_lead_id' => ['nullable', 'integer', 'exists:users,id'],
                'description' => ['nullable', 'string', 'max:1000'],
            ]),
            'cost-centers' => new OrganizationEntityDefinition('cost-centers', 'cost-center', CostCenter::class, [
                'cost_center_code' => 'Cost Center Code',
                'name' => 'Name',
                'department_id' => 'Department',
                'parent_cost_center_id' => 'Parent Cost Center',
                'budget' => 'Budget',
                'status' => 'Status',
            ], ['cost_center_code', 'name'], ['department_id', 'status'], [
                'cost_center_code' => ['required', 'string', 'max:80'],
                'name' => ['required', 'string', 'max:255'],
                'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
                'parent_cost_center_id' => ['nullable', 'uuid', 'exists:cost_centers,id'],
                'budget' => ['nullable', 'numeric', 'min:0'],
                'status' => ['nullable', 'in:active,inactive'],
            ], ['cost_center_code']),
            'job-grades' => new OrganizationEntityDefinition('job-grades', 'job-grade', JobGrade::class, [
                'grade_code' => 'Grade Code',
                'grade_name' => 'Grade Name',
                'level' => 'Level',
                'minimum_salary' => 'Minimum Salary',
                'maximum_salary' => 'Maximum Salary',
                'description' => 'Description',
            ], ['grade_code', 'grade_name'], ['level'], [
                'grade_code' => ['required', 'string', 'max:80'],
                'grade_name' => ['required', 'string', 'max:255'],
                'level' => ['required', 'integer', 'min:1'],
                'minimum_salary' => ['nullable', 'numeric', 'min:0'],
                'maximum_salary' => ['nullable', 'numeric', 'gte:minimum_salary'],
                'description' => ['nullable', 'string', 'max:1000'],
            ], ['grade_code']),
            'designations' => new OrganizationEntityDefinition('designations', 'designation', Designation::class, [
                'designation_code' => 'Designation Code',
                'designation_name' => 'Designation Name',
                'department_id' => 'Department',
                'job_grade_id' => 'Job Grade',
                'description' => 'Description',
            ], ['designation_code', 'designation_name'], ['department_id', 'job_grade_id'], [
                'designation_code' => ['required', 'string', 'max:80'],
                'designation_name' => ['required', 'string', 'max:255'],
                'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
                'job_grade_id' => ['nullable', 'uuid', 'exists:job_grades,id'],
                'description' => ['nullable', 'string', 'max:1000'],
            ], ['designation_code']),
            'job-categories' => new OrganizationEntityDefinition('job-categories', 'job-category', JobCategory::class, [
                'category_name' => 'Category Name',
                'description' => 'Description',
            ], ['category_name', 'description'], [], [
                'category_name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
            ], ['category_name']),
            'employment-types' => new OrganizationEntityDefinition('employment-types', 'employment-type', EmploymentType::class, [
                'name' => 'Name',
                'description' => 'Description',
                'status' => 'Status',
            ], ['name', 'description'], ['status'], [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
                'status' => ['nullable', 'in:active,inactive'],
            ], ['name']),
            'work-locations' => new OrganizationEntityDefinition('work-locations', 'work-location', WorkLocation::class, [
                'branch_id' => 'Branch',
                'name' => 'Name',
                'address' => 'Address',
                'latitude' => 'Latitude',
                'longitude' => 'Longitude',
                'shift_id' => 'Shift',
                'capacity' => 'Capacity',
            ], ['name', 'address'], ['branch_id', 'shift_id'], [
                'branch_id' => ['required', 'uuid', 'exists:branches,id'],
                'name' => ['required', 'string', 'max:255'],
                'address' => ['nullable', 'string', 'max:1000'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'shift_id' => ['nullable', 'uuid', 'exists:shifts,id'],
                'capacity' => ['nullable', 'integer', 'min:0'],
            ]),
            'shifts' => new OrganizationEntityDefinition('shifts', 'shift', Shift::class, [
                'shift_name' => 'Shift Name',
                'start_time' => 'Start Time',
                'end_time' => 'End Time',
                'grace_period_minutes' => 'Grace Period',
                'break_rules' => 'Break Rules',
                'overtime_rules' => 'Overtime Rules',
                'weekly_off' => 'Weekly Off',
                'is_night_shift' => 'Night Shift',
                'shift_type' => 'Shift Type',
                'status' => 'Status',
            ], ['shift_name', 'shift_type'], ['shift_type', 'status', 'is_night_shift'], [
                'shift_name' => ['required', 'string', 'max:255'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i'],
                'grace_period_minutes' => ['nullable', 'integer', 'min:0'],
                'break_rules' => ['nullable', 'array'],
                'overtime_rules' => ['nullable', 'array'],
                'weekly_off' => ['nullable', 'array'],
                'is_night_shift' => ['nullable', 'boolean'],
                'shift_type' => ['nullable', 'in:fixed,flexible,rotational,night,split'],
                'status' => ['nullable', 'in:active,inactive'],
            ], ['shift_name']),
            'holiday-calendars' => new OrganizationEntityDefinition('holiday-calendars', 'holiday-calendar', HolidayCalendar::class, [
                'company_id' => 'Company',
                'branch_id' => 'Branch',
                'name' => 'Name',
                'calendar_type' => 'Calendar Type',
                'status' => 'Status',
            ], ['name', 'calendar_type'], ['company_id', 'branch_id', 'calendar_type', 'status'], [
                'company_id' => ['required', 'uuid', 'exists:companies,id'],
                'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
                'name' => ['required', 'string', 'max:255'],
                'calendar_type' => ['nullable', 'in:company,regional,branch,religious'],
                'status' => ['nullable', 'in:active,inactive'],
            ], ['name']),
            'holidays' => new OrganizationEntityDefinition('holidays', 'holiday', Holiday::class, [
                'holiday_calendar_id' => 'Holiday Calendar',
                'name' => 'Name',
                'holiday_date' => 'Date',
                'is_recurring' => 'Recurring',
                'holiday_type' => 'Holiday Type',
                'group_name' => 'Group',
                'description' => 'Description',
            ], ['name', 'holiday_type', 'group_name'], ['holiday_calendar_id', 'holiday_type', 'group_name', 'is_recurring'], [
                'holiday_calendar_id' => ['required', 'uuid', 'exists:holiday_calendars,id'],
                'name' => ['required', 'string', 'max:255'],
                'holiday_date' => ['required', 'date'],
                'is_recurring' => ['nullable', 'boolean'],
                'holiday_type' => ['nullable', 'in:company,regional,branch,religious'],
                'group_name' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
            ]),
        ];
    }
}
