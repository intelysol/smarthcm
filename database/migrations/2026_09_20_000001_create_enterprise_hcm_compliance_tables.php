<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Compliance Requirement Types
        Schema::create('hcm_compliance_requirement_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('default_renewal_required')->default(false);
            $table->integer('default_grace_period_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        // 2. Compliance Requirements
        Schema::create('hcm_compliance_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('requirement_type_id')->index();
            $table->string('name', 150);
            $table->string('code', 50);
            $table->text('description')->nullable();
            $table->string('country', 50)->nullable();
            $table->uuid('legal_entity_id')->nullable()->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->uuid('location_id')->nullable()->index();
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('job_id')->nullable()->index();
            $table->string('job_family', 100)->nullable();
            $table->uuid('position_id')->nullable()->index();
            $table->string('employee_type', 50)->nullable();
            $table->string('worker_type', 50)->nullable();
            $table->string('employment_type', 50)->nullable();
            $table->string('nationality_criteria', 50)->nullable(); // foreign, citizen, or specific country
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('renewal_required')->default(false);
            $table->boolean('expiry_required')->default(false);
            $table->integer('grace_period_days')->default(0);
            $table->boolean('verification_required')->default(true);
            $table->boolean('document_required')->default(false);
            $table->boolean('approval_required')->default(false);
            $table->string('responsible_role', 50)->default('employee');
            $table->json('escalation_policy')->nullable();
            $table->json('warning_periods')->nullable();
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('requirement_type_id')->references('id')->on('hcm_compliance_requirement_types')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code', 'version']);
        });

        // 3. Employee Assigned Compliance Requirements
        Schema::create('hcm_employee_compliance_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('requirement_id')->index();
            $table->string('status', 30)->default('required')->index(); // not_required, pending, required, in_progress, submitted, under_review, verified, compliant, expiring, expired, non_compliant, waived, exempt, suspended, cancelled
            $table->date('due_date')->nullable()->index();
            $table->timestamp('fulfilled_at')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('requirement_id')->references('id')->on('hcm_compliance_requirements')->cascadeOnDelete();
            $table->unique(['tenant_id', 'employee_id', 'requirement_id'], 'emp_req_unique');
        });

        // 4. Employee Work Permits
        Schema::create('hcm_employee_work_permits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('compliance_requirement_id')->nullable()->index();
            $table->string('permit_type', 80);
            $table->string('permit_number', 100);
            $table->string('issuing_authority', 150)->nullable();
            $table->string('country', 50);
            $table->date('issue_date')->nullable();
            $table->date('effective_from');
            $table->date('expiry_date')->index();
            $table->string('status', 30)->default('active')->index(); // pending, active, expiring, expired, suspended, cancelled, rejected
            $table->string('verification_status', 30)->default('unverified')->index();
            $table->string('sponsor', 150)->nullable();
            $table->string('job_restriction', 255)->nullable();
            $table->string('location_restriction', 255)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('document_id')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('compliance_requirement_id')->references('id')->on('hcm_compliance_requirements')->nullOnDelete();
        });

        // 5. Employee Visas & Residency Records
        Schema::create('hcm_employee_visa_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('compliance_requirement_id')->nullable()->index();
            $table->string('visa_type', 80);
            $table->string('visa_number', 100);
            $table->string('issuing_country', 50);
            $table->string('issuing_authority', 150)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('effective_from');
            $table->date('expiry_date')->index();
            $table->date('entry_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->boolean('is_multiple_entry')->default(false);
            $table->string('sponsor', 150)->nullable();
            $table->string('residency_status', 50)->nullable();
            $table->string('residency_number', 100)->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->string('verification_status', 30)->default('unverified')->index();
            $table->text('notes')->nullable();
            $table->uuid('document_id')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('compliance_requirement_id')->references('id')->on('hcm_compliance_requirements')->nullOnDelete();
        });

        // 6. Employee Professional & Occupational Licenses
        Schema::create('hcm_employee_licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('compliance_requirement_id')->nullable()->index();
            $table->string('license_type', 80);
            $table->string('license_name', 150);
            $table->string('license_number', 100);
            $table->string('issuing_authority', 150);
            $table->string('country', 50);
            $table->string('state_province', 100)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('effective_from');
            $table->date('expiry_date')->nullable()->index();
            $table->string('status', 30)->default('active')->index();
            $table->string('verification_status', 30)->default('unverified')->index();
            $table->string('renewal_status', 30)->default('not_started');
            $table->text('restrictions')->nullable();
            $table->uuid('document_id')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('compliance_requirement_id')->references('id')->on('hcm_compliance_requirements')->nullOnDelete();
        });

        // 7. Mandatory Government / Occupational Registrations
        Schema::create('hcm_employee_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('compliance_requirement_id')->nullable()->index();
            $table->string('registration_type', 80);
            $table->string('registration_number', 100);
            $table->string('authority_name', 150);
            $table->date('registration_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->string('verification_status', 30)->default('unverified');
            $table->uuid('document_id')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('compliance_requirement_id')->references('id')->on('hcm_compliance_requirements')->nullOnDelete();
        });

        // 8. Compliance Verifications
        Schema::create('hcm_compliance_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('verifiable_type', 100);
            $table->uuid('verifiable_id')->index();
            $table->string('verification_source', 50); // hr, compliance_officer, issuing_authority, document_verification, external_service, manual
            $table->string('status', 30)->default('pending'); // pending, verified, rejected
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_reference', 150)->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 9. Compliance Renewals
        Schema::create('hcm_compliance_renewals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('renewable_type', 100);
            $table->uuid('renewable_id')->index();
            $table->string('status', 30)->default('initiated'); // initiated, document_submitted, under_review, approved, completed, cancelled
            $table->date('old_expiry_date')->nullable();
            $table->date('new_expiry_date')->nullable();
            $table->timestamp('initiated_at');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 10. Compliance Exemptions / Waivers
        Schema::create('hcm_compliance_exemptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('requirement_id')->index();
            $table->string('reason', 255);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('requested'); // requested, approved, rejected, expired
            $table->date('effective_from');
            $table->date('expiry_date');
            $table->uuid('supporting_document_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('requirement_id')->references('id')->on('hcm_compliance_requirements')->cascadeOnDelete();
        });

        // 11. Employee Compliance Snapshots (Rebuildable Read Model)
        Schema::create('hcm_employee_compliance_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->unique();
            $table->string('overall_status', 30)->default('compliant')->index(); // compliant, at_risk, non_compliant, exempt
            $table->decimal('compliance_score', 5, 2)->default(100.00);
            $table->integer('total_requirements')->default(0);
            $table->integer('compliant_count')->default(0);
            $table->integer('expiring_count')->default(0);
            $table->integer('expired_count')->default(0);
            $table->integer('pending_count')->default(0);
            $table->integer('exempt_count')->default(0);
            $table->json('next_expiring_item')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 12. Compliance Tasks
        Schema::create('hcm_compliance_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('task_type', 50); // upload_document, verify_record, renew_item, complete_training
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending'); // pending, in_progress, completed, cancelled
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 13. Compliance Escalations
        Schema::create('hcm_compliance_escalations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('target_type', 100);
            $table->uuid('target_id')->index();
            $table->string('tier', 30); // 90_days, 60_days, 30_days, 14_days, 7_days, 1_day, expired
            $table->string('recipient_role', 50); // employee, manager, hr, compliance_officer
            $table->timestamp('triggered_at');
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 14. Compliance Audits
        Schema::create('hcm_compliance_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->nullable()->index();
            $table->string('action', 80)->index();
            $table->string('entity_type', 100);
            $table->uuid('entity_id')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_compliance_audits');
        Schema::dropIfExists('hcm_compliance_escalations');
        Schema::dropIfExists('hcm_compliance_tasks');
        Schema::dropIfExists('hcm_employee_compliance_snapshots');
        Schema::dropIfExists('hcm_compliance_exemptions');
        Schema::dropIfExists('hcm_compliance_renewals');
        Schema::dropIfExists('hcm_compliance_verifications');
        Schema::dropIfExists('hcm_employee_registrations');
        Schema::dropIfExists('hcm_employee_licenses');
        Schema::dropIfExists('hcm_employee_visa_records');
        Schema::dropIfExists('hcm_employee_work_permits');
        Schema::dropIfExists('hcm_employee_compliance_requirements');
        Schema::dropIfExists('hcm_compliance_requirements');
        Schema::dropIfExists('hcm_compliance_requirement_types');
    }
};
