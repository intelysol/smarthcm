<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Onboarding Templates
        Schema::create('hcm_onboarding_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 120);
            $table->string('code', 50);
            $table->text('description')->nullable();
            $table->string('department_id')->nullable()->index();
            $table->string('location_id')->nullable()->index();
            $table->string('employment_type', 40)->nullable(); // full_time, contractor, intern
            $table->string('worker_type', 40)->nullable(); // office, remote, hybrid
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 2. Onboarding Template Versions
        Schema::create('hcm_onboarding_template_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('template_id')->index();
            $table->unsignedInteger('version_number')->default(1);
            $table->string('status', 30)->default('published'); // draft, published, archived
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('template_id')->references('id')->on('hcm_onboarding_templates')->cascadeOnDelete();
            $table->unique(['template_id', 'version_number'], 'hcm_onb_tmpl_ver_tmpl_num_unique');
        });

        // 3. Template Tasks Definition
        Schema::create('hcm_onboarding_template_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('template_version_id')->index();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('task_type', 40)->default('general'); // document, training, form, it_provisioning, equipment, manager_check, policy, buddy_meet, general
            $table->string('owner_role', 40)->default('employee'); // employee, manager, hr, it, finance, security, facilities
            $table->integer('due_offset_days')->default(0); // negative = before start date (preboarding), 0 = on start date, positive = after start date
            $table->unsignedInteger('sla_hours')->default(24);
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('display_order')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('template_version_id')->references('id')->on('hcm_onboarding_template_versions')->cascadeOnDelete();
        });

        // 4. Onboarding Cases (One per new hire transition)
        Schema::create('hcm_onboarding_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('case_number', 50);
            $table->uuid('employee_id')->index(); // Core HR Employee
            $table->uuid('recruitment_application_id')->nullable()->index();
            $table->uuid('offer_id')->nullable()->index();
            $table->uuid('template_version_id')->index();
            $table->date('start_date');
            $table->date('target_completion_date')->nullable();
            $table->string('status', 30)->default('preboarding'); // draft, preboarding, ready, in_progress, blocked, completed, cancelled
            $table->decimal('completion_percentage', 5, 2)->default(0.00);
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete(); // HR case owner
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('template_version_id')->references('id')->on('hcm_onboarding_template_versions')->cascadeOnDelete();
            $table->unique(['tenant_id', 'case_number']);
        });

        // 5. Onboarding Concrete Tasks
        Schema::create('hcm_onboarding_case_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->uuid('template_task_id')->nullable()->index();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('task_type', 40)->default('general');
            $table->string('owner_role', 40)->default('employee');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('assigned_to_employee_id')->nullable()->index();
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('pending'); // pending, in_progress, blocked, completed, skipped, cancelled
            $table->boolean('is_required')->default(true);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('hcm_onboarding_cases')->cascadeOnDelete();
            $table->foreign('assigned_to_employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 6. Task Dependencies
        Schema::create('hcm_onboarding_task_dependencies', function (Blueprint $table) {
            $table->uuid('tenant_id')->index();
            $table->uuid('task_id')->index();
            $table->uuid('depends_on_task_id')->index();
            $table->timestamps();

            $table->primary(['task_id', 'depends_on_task_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('hcm_onboarding_case_tasks')->cascadeOnDelete();
            $table->foreign('depends_on_task_id')->references('id')->on('hcm_onboarding_case_tasks')->cascadeOnDelete();
        });

        // 7. Document Requirements & Collection
        Schema::create('hcm_onboarding_document_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('document_type', 50); // id_proof, address_proof, education_cert, tax_form, signed_offer
            $table->string('title', 150);
            $table->boolean('is_mandatory')->default(true);
            $table->string('status', 30)->default('pending'); // pending, submitted, verified, rejected, requires_correction
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('hcm_onboarding_cases')->cascadeOnDelete();
        });

        Schema::create('hcm_onboarding_document_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('document_requirement_id')->index();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('verified'); // verified, rejected, requires_correction
            $table->text('comments')->nullable();
            $table->timestamp('reviewed_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('document_requirement_id')->references('id')->on('hcm_onboarding_document_requirements')->cascadeOnDelete();
        });

        // 8. Digital Forms & Submissions
        Schema::create('hcm_onboarding_forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 100);
            $table->string('code', 50);
            $table->json('schema_definition');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('hcm_onboarding_form_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->uuid('form_id')->index();
            $table->json('form_data');
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('hcm_onboarding_cases')->cascadeOnDelete();
            $table->foreign('form_id')->references('id')->on('hcm_onboarding_forms')->cascadeOnDelete();
            $table->unique(['case_id', 'form_id']);
        });

        // 9. Policy Acknowledgements
        Schema::create('hcm_onboarding_policy_acknowledgements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('policy_code', 50);
            $table->string('policy_title', 150);
            $table->string('policy_version', 20)->default('v1.0');
            $table->timestamp('acknowledged_at');
            $table->string('ip_address', 50)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('hcm_onboarding_cases')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['case_id', 'policy_code', 'policy_version'], 'hcm_onb_pol_ack_case_pol_ver_unique');
        });

        // 10. Provisioning Requests (IT Hardware, System Access, Facilities)
        Schema::create('hcm_onboarding_provisioning_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('request_type', 40); // it_access, laptop_equipment, desk_facilities, access_card
            $table->string('title', 150);
            $table->json('specifications')->nullable();
            $table->string('status', 30)->default('pending'); // pending, in_progress, provisioned, rejected
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('hcm_onboarding_cases')->cascadeOnDelete();
        });

        // 11. Buddy / Mentor Assignment
        Schema::create('hcm_onboarding_buddy_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('buddy_employee_id')->index(); // Peer Employee
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('active'); // active, completed, cancelled
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('hcm_onboarding_cases')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('buddy_employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 12. Probation Tracking & Reviews
        Schema::create('hcm_onboarding_probations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->uuid('employee_id')->index();
            $table->date('probation_start_date');
            $table->date('probation_end_date');
            $table->date('extended_to_date')->nullable();
            $table->string('status', 30)->default('in_progress'); // not_started, in_progress, due, completed, extended, passed, failed
            $table->string('outcome', 30)->nullable(); // confirmed, extended, terminated
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('hcm_onboarding_cases')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['case_id', 'employee_id']);
        });

        Schema::create('hcm_onboarding_probation_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('probation_id')->index();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('performance_rating', 3, 1)->default(0.0);
            $table->string('recommendation', 30)->default('pass'); // pass, extend, fail
            $table->text('comments')->nullable();
            $table->timestamp('reviewed_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('probation_id')->references('id')->on('hcm_onboarding_probations')->cascadeOnDelete();
        });

        // 13. Onboarding Surveys & Feedback
        Schema::create('hcm_onboarding_surveys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->uuid('employee_id')->index();
            $table->unsignedTinyInteger('experience_rating'); // 1 to 5
            $table->unsignedTinyInteger('it_readiness_rating'); // 1 to 5
            $table->unsignedTinyInteger('manager_support_rating'); // 1 to 5
            $table->text('feedback_comments')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('hcm_onboarding_cases')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['case_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_onboarding_surveys');
        Schema::dropIfExists('hcm_onboarding_probation_reviews');
        Schema::dropIfExists('hcm_onboarding_probations');
        Schema::dropIfExists('hcm_onboarding_buddy_assignments');
        Schema::dropIfExists('hcm_onboarding_provisioning_requests');
        Schema::dropIfExists('hcm_onboarding_policy_acknowledgements');
        Schema::dropIfExists('hcm_onboarding_form_submissions');
        Schema::dropIfExists('hcm_onboarding_forms');
        Schema::dropIfExists('hcm_onboarding_document_reviews');
        Schema::dropIfExists('hcm_onboarding_document_requirements');
        Schema::dropIfExists('hcm_onboarding_task_dependencies');
        Schema::dropIfExists('hcm_onboarding_case_tasks');
        Schema::dropIfExists('hcm_onboarding_cases');
        Schema::dropIfExists('hcm_onboarding_template_tasks');
        Schema::dropIfExists('hcm_onboarding_template_versions');
        Schema::dropIfExists('hcm_onboarding_templates');
    }
};
