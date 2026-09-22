<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Personnel Action Types
        Schema::create('personnel_action_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50); // PROMOTION, TRANSFER, JOB_CHANGE, COMPENSATION_CHANGE, etc.
            $table->string('name', 120);
            $table->string('category', 50)->default('general'); // promotion, transfer, job_change, compensation, assignment, probation
            $table->text('description')->nullable();
            $table->boolean('requires_approval')->default(true);
            $table->boolean('requires_acknowledgement')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 2. Personnel Action Requests (Aggregate Root)
        Schema::create('personnel_action_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('action_type_id')->index();
            $table->string('request_number', 50); // PA-2026-000001
            $table->string('status', 30)->default('draft'); // draft, submitted, under_review, pending_approval, approved, rejected, scheduled, processing, executed, failed, cancelled, reversed
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('requested_at');
            $table->date('effective_date');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executed_at')->nullable();
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 255)->nullable();
            $table->text('comments')->nullable();
            $table->string('source', 50)->default('manual'); // manual, bulk, workflow, api
            $table->string('priority', 20)->default('medium'); // low, medium, high, urgent
            $table->uuid('workflow_instance_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('action_type_id')->references('id')->on('personnel_action_types')->cascadeOnDelete();
            $table->unique(['tenant_id', 'request_number']);
            $table->index(['tenant_id', 'employee_id']);
            $table->index(['tenant_id', 'effective_date']);
            $table->index(['tenant_id', 'status']);
        });

        // 3. Normalized Field-Level Changes (Current vs Proposed State)
        Schema::create('personnel_action_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('personnel_action_request_id')->index('pa_chg_par_id_idx');
            $table->string('field_name', 80); // department_id, position_id, job_grade_id, base_salary, reporting_manager_id, etc.
            $table->string('entity_type', 80)->default('employee'); // employee, employment, assignment
            $table->string('entity_id', 80)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('old_value_label', 150)->nullable();
            $table->string('new_value_label', 150)->nullable();
            $table->string('change_type', 20)->default('update'); // add, remove, replace, update
            $table->date('effective_date')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('personnel_action_request_id')->references('id')->on('personnel_action_requests')->cascadeOnDelete();
        });

        // 4. Personnel Action Impact Analysis Results
        Schema::create('personnel_action_impacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('personnel_action_request_id')->index('pa_imp_par_id_idx');
            $table->string('domain', 50); // core_hr, payroll, benefits, attendance, learning, position, workflow
            $table->string('impact_type', 50); // salary_recalc, position_vacated, benefit_reeval, etc.
            $table->string('severity', 20)->default('info'); // info, warning, blocking
            $table->string('status', 30)->default('pending');
            $table->text('message');
            $table->string('source', 50)->default('system');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('personnel_action_request_id')->references('id')->on('personnel_action_requests')->cascadeOnDelete();
        });

        // 5. Employee Acknowledgement
        Schema::create('personnel_action_acknowledgements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('personnel_action_request_id')->index('pa_ack_par_id_idx');
            $table->uuid('employee_id')->index();
            $table->string('status', 30)->default('pending'); // not_required, pending, acknowledged, declined, expired
            $table->timestamp('acknowledged_at')->nullable();
            $table->text('comment')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('personnel_action_request_id', 'fk_pa_ack_par_id')->references('id')->on('personnel_action_requests')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['personnel_action_request_id', 'employee_id'], 'pa_ack_unique');
        });

        // 6. Temporary, Acting, Secondment & Deputation Assignments
        Schema::create('personnel_temporary_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('personnel_action_request_id')->nullable()->index('pta_par_id_idx');
            $table->string('assignment_type', 40); // temporary, acting, secondment, deputation
            $table->uuid('home_department_id')->nullable()->index();
            $table->uuid('temporary_department_id')->nullable()->index();
            $table->uuid('home_position_id')->nullable()->index();
            $table->uuid('temporary_position_id')->nullable()->index();
            $table->uuid('home_manager_id')->nullable()->index();
            $table->uuid('temporary_manager_id')->nullable()->index();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('active'); // active, completed, extended, reverted
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('personnel_action_request_id', 'fk_pta_par_id')->references('id')->on('personnel_action_requests')->nullOnDelete();
        });

        // 7. Personnel Action Generated Documents & Letters
        Schema::create('personnel_action_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('personnel_action_request_id')->index('pa_doc_par_id_idx');
            $table->string('document_type', 50); // promotion_letter, transfer_letter, salary_revision_letter
            $table->string('title', 150);
            $table->string('file_path');
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('personnel_action_request_id')->references('id')->on('personnel_action_requests')->cascadeOnDelete();
        });

        // 8. Reversals & Compensating Actions
        Schema::create('personnel_action_reversals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('original_action_id')->index();
            $table->uuid('reversal_action_id')->index();
            $table->text('reason');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('original_action_id')->references('id')->on('personnel_action_requests')->cascadeOnDelete();
            $table->foreign('reversal_action_id')->references('id')->on('personnel_action_requests')->cascadeOnDelete();
            $table->unique(['original_action_id', 'reversal_action_id'], 'pa_rev_orig_rev_unique');
        });

        // 9. Bulk Batches & Items
        Schema::create('personnel_action_bulk_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('action_type_id')->index();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('status', 30)->default('draft'); // draft, validating, validated, processing, completed, failed
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('valid_items')->default(0);
            $table->unsignedInteger('warning_items')->default(0);
            $table->unsignedInteger('error_items')->default(0);
            $table->unsignedInteger('processed_items')->default(0);
            $table->unsignedInteger('successful_items')->default(0);
            $table->unsignedInteger('failed_items')->default(0);
            $table->date('effective_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('action_type_id')->references('id')->on('personnel_action_types')->cascadeOnDelete();
        });

        Schema::create('personnel_action_bulk_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('batch_id')->index();
            $table->uuid('employee_id')->index();
            $table->json('payload');
            $table->string('validation_status', 20)->default('pending'); // pending, valid, warning, error
            $table->json('validation_errors')->nullable();
            $table->string('execution_status', 20)->default('pending'); // pending, successful, failed, skipped
            $table->uuid('personnel_action_id')->nullable()->index();
            $table->text('execution_error')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('batch_id')->references('id')->on('personnel_action_bulk_batches')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('personnel_action_id')->references('id')->on('personnel_action_requests')->nullOnDelete();
        });

        // 10. Immutable Audit Trail
        Schema::create('personnel_action_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('personnel_action_request_id')->index('pa_aud_par_id_idx');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_event', 50); // created, submitted, validated, approved, rejected, scheduled, executed, cancelled, reversed
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('reason', 255)->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('personnel_action_request_id')->references('id')->on('personnel_action_requests')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_action_audits');
        Schema::dropIfExists('personnel_action_bulk_items');
        Schema::dropIfExists('personnel_action_bulk_batches');
        Schema::dropIfExists('personnel_action_reversals');
        Schema::dropIfExists('personnel_action_documents');
        Schema::dropIfExists('personnel_temporary_assignments');
        Schema::dropIfExists('personnel_action_acknowledgements');
        Schema::dropIfExists('personnel_action_impacts');
        Schema::dropIfExists('personnel_action_changes');
        Schema::dropIfExists('personnel_action_requests');
        Schema::dropIfExists('personnel_action_types');
    }
};
