<?php

namespace App\Domains\Employee\Models;

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
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends EmployeeModel
{
    protected $fillable = [
        'tenant_id', 'person_id', 'user_id', 'employee_number', 'employee_code', 'current_employment_id', 'current_position_id', 'current_manager_employee_id', 'original_hire_date', 'termination_date', 'portal_access', 'version', 'company_id', 'branch_id', 'business_unit_id',
        'department_id', 'section_id', 'team_id', 'cost_center_id', 'designation_id', 'job_grade_id',
        'reporting_manager_id', 'employment_type_id', 'employment_status', 'joining_date',
        'confirmation_date', 'probation_end_date', 'contract_start_date', 'contract_end_date',
        'notice_period_days', 'work_location_id', 'shift_id', 'payroll_group', 'holiday_calendar_id',
        'first_name', 'middle_name', 'last_name', 'preferred_name', 'gender', 'date_of_birth',
        'marital_status', 'blood_group', 'nationality', 'religion', 'national_id', 'passport_number',
        'passport_expiry', 'visa_number', 'visa_expiry', 'driving_license_number', 'driving_license_expiry',
        'photo_path', 'signature_path', 'personal_email', 'official_email', 'mobile', 'alternate_mobile',
        'office_phone', 'emergency_phone', 'present_address', 'permanent_address', 'country', 'state',
        'city', 'postal_code', 'tags', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'confirmation_date' => 'date',
        'probation_end_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'date_of_birth' => 'date',
        'passport_expiry' => 'date',
        'visa_expiry' => 'date',
        'driving_license_expiry' => 'date',
        'notice_period_days' => 'integer',
        'original_hire_date' => 'date',
        'termination_date' => 'date',
        'portal_access' => 'boolean',
        'version' => 'integer',
        'tags' => 'array',
    ];

    public function fullName(): string
    {
        return trim(collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->implode(' '));
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reporting_manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(self::class, 'reporting_manager_id');
    }

    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class);
    }

    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function holidayCalendar(): BelongsTo
    {
        return $this->belongsTo(HolidayCalendar::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(EmployeeFamilyMember::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(EmployeeEducation::class);
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(EmployeeWorkExperience::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(EmployeeCertification::class);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(EmployeeLanguage::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(EmployeeBankAccount::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(EmployeeCustomFieldValue::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(EmployeeTimeline::class);
    }
}
