<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $this->base($table);
            $table->uuid('company_id');
            $table->string('branch_code', 80);
            $table->string('branch_name');
            $table->string('region')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('working_days')->nullable();
            $table->string('status', 40)->default('active')->index();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique(['tenant_id', 'branch_code']);
        });

        Schema::create('business_units', function (Blueprint $table) {
            $this->base($table);
            $table->uuid('company_id');
            $table->uuid('branch_id')->nullable();
            $table->string('name');
            $table->string('code', 80);
            $table->text('description')->nullable();
            $table->foreignId('head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 40)->default('active')->index();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('departments', function (Blueprint $table) {
            $this->base($table);
            $table->uuid('business_unit_id');
            $table->uuid('parent_department_id')->nullable();
            $table->string('department_code', 80);
            $table->string('department_name');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('status', 40)->default('active')->index();
            $table->foreign('business_unit_id')->references('id')->on('business_units')->cascadeOnDelete();
            $table->foreign('parent_department_id')->references('id')->on('departments')->nullOnDelete();
            $table->unique(['tenant_id', 'department_code']);
        });

        Schema::create('sections', function (Blueprint $table) {
            $this->base($table);
            $table->uuid('department_id');
            $table->string('section_code', 80);
            $table->string('section_name');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            $table->unique(['tenant_id', 'section_code']);
        });

        Schema::create('teams', function (Blueprint $table) {
            $this->base($table);
            $table->uuid('section_id');
            $table->string('team_name');
            $table->foreignId('team_lead_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
            $table->unique(['tenant_id', 'section_id', 'team_name']);
        });

        Schema::create('cost_centers', function (Blueprint $table) {
            $this->base($table);
            $table->string('cost_center_code', 80);
            $table->string('name');
            $table->uuid('department_id')->nullable();
            $table->uuid('parent_cost_center_id')->nullable();
            $table->decimal('budget', 15, 2)->default(0);
            $table->string('status', 40)->default('active')->index();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('parent_cost_center_id')->references('id')->on('cost_centers')->nullOnDelete();
            $table->unique(['tenant_id', 'cost_center_code']);
        });

        Schema::create('job_grades', function (Blueprint $table) {
            $this->base($table);
            $table->string('grade_code', 80);
            $table->string('grade_name');
            $table->unsignedInteger('level')->index();
            $table->decimal('minimum_salary', 15, 2)->nullable();
            $table->decimal('maximum_salary', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->unique(['tenant_id', 'grade_code']);
        });

        Schema::create('designations', function (Blueprint $table) {
            $this->base($table);
            $table->string('designation_code', 80);
            $table->string('designation_name');
            $table->uuid('department_id')->nullable();
            $table->uuid('job_grade_id')->nullable();
            $table->text('description')->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('job_grade_id')->references('id')->on('job_grades')->nullOnDelete();
            $table->unique(['tenant_id', 'designation_code']);
        });

        Schema::create('job_categories', function (Blueprint $table) {
            $this->base($table);
            $table->string('category_name');
            $table->text('description')->nullable();
            $table->unique(['tenant_id', 'category_name']);
        });

        Schema::create('employment_types', function (Blueprint $table) {
            $this->base($table);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 40)->default('active')->index();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('shifts', function (Blueprint $table) {
            $this->base($table);
            $table->string('shift_name');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('grace_period_minutes')->default(0);
            $table->json('break_rules')->nullable();
            $table->json('overtime_rules')->nullable();
            $table->json('weekly_off')->nullable();
            $table->boolean('is_night_shift')->default(false)->index();
            $table->string('shift_type', 40)->default('fixed')->index();
            $table->string('status', 40)->default('active')->index();
            $table->unique(['tenant_id', 'shift_name']);
        });

        Schema::create('work_locations', function (Blueprint $table) {
            $this->base($table);
            $table->uuid('branch_id');
            $table->string('name');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->uuid('shift_id')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
            $table->unique(['tenant_id', 'branch_id', 'name']);
        });

        Schema::create('holiday_calendars', function (Blueprint $table) {
            $this->base($table);
            $table->uuid('company_id');
            $table->uuid('branch_id')->nullable();
            $table->string('name');
            $table->string('calendar_type', 40)->default('company')->index();
            $table->string('status', 40)->default('active')->index();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('holidays', function (Blueprint $table) {
            $this->base($table);
            $table->uuid('holiday_calendar_id');
            $table->string('name');
            $table->date('holiday_date');
            $table->boolean('is_recurring')->default(false)->index();
            $table->string('holiday_type', 40)->default('company')->index();
            $table->string('group_name')->nullable()->index();
            $table->text('description')->nullable();
            $table->foreign('holiday_calendar_id')->references('id')->on('holiday_calendars')->cascadeOnDelete();
            $table->unique(['tenant_id', 'holiday_calendar_id', 'holiday_date', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('holiday_calendars');
        Schema::dropIfExists('work_locations');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('employment_types');
        Schema::dropIfExists('job_categories');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('job_grades');
        Schema::dropIfExists('cost_centers');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('business_units');
        Schema::dropIfExists('branches');
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
};
