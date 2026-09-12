<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $this->base($table);
            $table->string('employee_number', 40);
            $table->string('employee_code', 80)->nullable();
            $table->uuid('company_id');
            $table->uuid('branch_id')->nullable();
            $table->uuid('business_unit_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('section_id')->nullable();
            $table->uuid('team_id')->nullable();
            $table->uuid('cost_center_id')->nullable();
            $table->uuid('designation_id')->nullable();
            $table->uuid('job_grade_id')->nullable();
            $table->uuid('reporting_manager_id')->nullable();
            $table->uuid('employment_type_id')->nullable();
            $table->uuid('work_location_id')->nullable();
            $table->uuid('shift_id')->nullable();
            $table->uuid('holiday_calendar_id')->nullable();
            $table->string('payroll_group')->nullable();
            $table->string('employment_status', 40)->default('active')->index();
            $table->date('joining_date');
            $table->date('confirmation_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable()->index();
            $table->unsignedInteger('notice_period_days')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('preferred_name')->nullable();
            $table->string('gender', 40)->nullable()->index();
            $table->date('date_of_birth')->nullable()->index();
            $table->string('marital_status', 40)->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('nationality', 120)->nullable();
            $table->string('religion', 120)->nullable();
            $table->string('national_id', 120)->nullable()->index();
            $table->string('passport_number', 120)->nullable()->index();
            $table->date('passport_expiry')->nullable()->index();
            $table->string('visa_number', 120)->nullable();
            $table->date('visa_expiry')->nullable()->index();
            $table->string('driving_license_number', 120)->nullable();
            $table->date('driving_license_expiry')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('personal_email')->nullable()->index();
            $table->string('official_email')->nullable()->index();
            $table->string('mobile', 40)->nullable()->index();
            $table->string('alternate_mobile', 40)->nullable();
            $table->string('office_phone', 40)->nullable();
            $table->string('emergency_phone', 40)->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('country', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('postal_code', 40)->nullable();
            $table->json('tags')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('business_unit_id')->references('id')->on('business_units')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->nullOnDelete();
            $table->foreign('designation_id')->references('id')->on('designations')->nullOnDelete();
            $table->foreign('job_grade_id')->references('id')->on('job_grades')->nullOnDelete();
            $table->foreign('reporting_manager_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('employment_type_id')->references('id')->on('employment_types')->nullOnDelete();
            $table->foreign('work_location_id')->references('id')->on('work_locations')->nullOnDelete();
            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
            $table->foreign('holiday_calendar_id')->references('id')->on('holiday_calendars')->nullOnDelete();
            $table->unique(['tenant_id', 'employee_number']);
            $table->unique(['tenant_id', 'employee_code']);
            $table->index(['tenant_id', 'first_name', 'last_name']);
        });

        Schema::create('employee_emergency_contacts', fn (Blueprint $table) => $this->child($table, ['name', 'relationship', 'phone', 'mobile', 'email', 'address', 'priority']));
        Schema::create('employee_family_members', fn (Blueprint $table) => $this->child($table, ['name', 'date_of_birth', 'relationship', 'occupation']));
        Schema::create('employee_educations', fn (Blueprint $table) => $this->child($table, ['degree', 'institution', 'board_university', 'major', 'start_date', 'end_date', 'grade', 'certificate_path']));
        Schema::create('employee_work_experiences', fn (Blueprint $table) => $this->child($table, ['company', 'position', 'industry', 'start_date', 'end_date', 'responsibilities', 'salary', 'reason_for_leaving']));
        Schema::create('employee_profile_skills', fn (Blueprint $table) => $this->child($table, ['skill_name', 'skill_type', 'skill_level', 'years_of_experience', 'certification_id']));
        Schema::create('employee_certifications', fn (Blueprint $table) => $this->child($table, ['certification_name', 'issuing_organization', 'issue_date', 'expiry_date', 'credential_number', 'attachment_path']));
        Schema::create('employee_languages', fn (Blueprint $table) => $this->child($table, ['language', 'reading_level', 'writing_level', 'speaking_level', 'is_native']));
        Schema::create('employee_bank_accounts', fn (Blueprint $table) => $this->child($table, ['bank', 'branch', 'iban', 'account_number', 'account_title', 'is_primary']));
        Schema::create('employee_salaries', fn (Blueprint $table) => $this->child($table, ['salary_structure', 'basic_salary', 'gross_salary', 'currency', 'payroll_group']));
        Schema::create('employee_documents', fn (Blueprint $table) => $this->child($table, ['document_type', 'title', 'file_path', 'mime_type', 'file_size', 'version', 'expires_at', 'metadata']));

        Schema::create('employee_custom_field_definitions', function (Blueprint $table) {
            $this->base($table);
            $table->string('field_key', 80);
            $table->string('label');
            $table->string('field_type', 40);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unique(['tenant_id', 'field_key']);
        });

        Schema::create('employee_custom_field_values', function (Blueprint $table) {
            $this->base($table);
            $table->uuid('employee_id');
            $table->uuid('field_definition_id');
            $table->json('value')->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('field_definition_id')->references('id')->on('employee_custom_field_definitions')->cascadeOnDelete();
            $table->unique(['employee_id', 'field_definition_id'], 'employee_custom_values_employee_field_unique');
        });

        Schema::create('employee_timelines', function (Blueprint $table) {
            $this->base($table);
            $table->uuid('employee_id');
            $table->string('event_type', 80)->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        foreach ([
            'employee_timelines',
            'employee_custom_field_values',
            'employee_custom_field_definitions',
            'employee_documents',
            'employee_salaries',
            'employee_bank_accounts',
            'employee_languages',
            'employee_certifications',
            'employee_profile_skills',
            'employee_work_experiences',
            'employee_educations',
            'employee_family_members',
            'employee_emergency_contacts',
            'employees',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function base(Blueprint $table): void
    {
        $table->uuid('id')->primary();
        $table->uuid('tenant_id')->index();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();
        $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
    }

    /**
     * @param  list<string>  $columns
     */
    private function child(Blueprint $table, array $columns): void
    {
        $this->base($table);
        $table->uuid('employee_id');

        foreach ($columns as $column) {
            match ($column) {
                'priority', 'version', 'file_size', 'years_of_experience' => $table->unsignedInteger($column)->nullable(),
                'date_of_birth', 'start_date', 'end_date', 'issue_date', 'expiry_date', 'expires_at' => $table->date($column)->nullable(),
                'salary', 'basic_salary', 'gross_salary' => $table->decimal($column, 15, 2)->nullable(),
                'is_native', 'is_primary' => $table->boolean($column)->default(false),
                'metadata' => $table->json($column)->nullable(),
                default => $table->text($column)->nullable(),
            };
        }

        $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        $table->index(['tenant_id', 'employee_id']);
    }
};
