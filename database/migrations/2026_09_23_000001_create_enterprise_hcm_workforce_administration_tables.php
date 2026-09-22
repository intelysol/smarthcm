<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. HR Operational Queues & Items
        Schema::create('hcm_ops_queues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 60)->index();
            $table->string('name', 150);
            $table->string('category', 60)->default('lifecycle'); // lifecycle, compliance, documents, payroll, benefits, exceptions, data_quality, integrations
            $table->text('description')->nullable();
            $table->string('default_priority', 30)->default('medium'); // critical, high, medium, low, informational
            $table->unsignedInteger('target_sla_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hcm_ops_queue_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('queue_id')->index();
            $table->string('item_number', 60)->unique();
            $table->string('title', 200);
            $table->string('entity_type', 100)->index();
            $table->uuid('entity_id')->index();
            $table->uuid('employee_id')->nullable()->index();
            $table->string('priority', 30)->default('medium');
            $table->string('status', 40)->default('pending'); // pending, assigned, in_progress, completed, escalated, cancelled
            $table->uuid('assigned_to')->nullable()->index();
            $table->string('assigned_team', 80)->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_sla_breached')->default(false);
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        // 2. Operational Exceptions & Events
        Schema::create('hcm_ops_exceptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('exception_number', 60)->unique();
            $table->string('exception_type', 80)->index();
            $table->string('severity', 30)->default('medium'); // critical, high, medium, low, informational
            $table->string('domain', 60)->index(); // core_hr, payroll, benefits, compliance, documents, expenses, lifecycle, integration
            $table->string('entity_type', 100);
            $table->uuid('entity_id')->index();
            $table->uuid('employee_id')->nullable()->index();
            $table->text('description');
            $table->string('status', 40)->default('detected'); // detected, assigned, investigating, action_required, resolved, verified, closed
            $table->uuid('owner_id')->nullable()->index();
            $table->string('assigned_team', 80)->nullable();
            $table->timestamp('detected_at')->useCurrent();
            $table->text('resolution_guidance')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->uuid('resolved_by')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('hcm_ops_exception_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('exception_id')->index();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_team', 80)->nullable();
            $table->uuid('assigned_by')->nullable()->index();
            $table->timestamp('assigned_at')->useCurrent();
            $table->text('notes')->nullable();
        });

        Schema::create('hcm_ops_exception_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('exception_id')->index();
            $table->string('event_type', 60); // status_change, assignment, note_added, resolution_attempt
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->text('comment')->nullable();
            $table->uuid('actor_id')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
        });

        // 3. Service Level Management (SLA)
        Schema::create('hcm_ops_sla_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 60)->index();
            $table->string('name', 150);
            $table->string('target_entity_type', 80); // queue_item, exception, task, personnel_action
            $table->string('priority', 30)->default('medium');
            $table->unsignedInteger('response_time_hours')->default(4);
            $table->unsignedInteger('resolution_time_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hcm_ops_sla_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('sla_policy_id')->index();
            $table->string('target_entity_type', 80);
            $table->uuid('target_entity_id')->index();
            $table->timestamp('started_at');
            $table->timestamp('response_due_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolution_due_at');
            $table->timestamp('resolved_at')->nullable();
            $table->string('status', 30)->default('running'); // running, fulfilled, breached, paused
            $table->boolean('is_breached')->default(false);
            $table->timestamps();
        });

        // 4. Governance Policies & Rules
        Schema::create('hcm_ops_governance_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 60)->index();
            $table->string('name', 150);
            $table->string('category', 60); // effective_dating, backdated_change, separation_of_duties, sensitive_field
            $table->text('description')->nullable();
            $table->boolean('is_enforced')->default(true);
            $table->timestamps();
        });

        Schema::create('hcm_ops_governance_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('policy_id')->index();
            $table->string('rule_key', 80);
            $table->string('rule_name', 150);
            $table->json('rule_parameters')->nullable();
            $table->string('severity_on_violation', 30)->default('warning'); // warning, error, block
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Operational Calendar & Deadlines
        Schema::create('hcm_ops_calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('event_type', 60); // new_hire_joining, termination_leaving, probation_end, contract_expiry, visa_expiry, payroll_cutoff, open_enrollment
            $table->string('title', 200);
            $table->date('event_date');
            $table->uuid('employee_id')->nullable()->index();
            $table->string('source_domain', 60); // core_hr, compliance, payroll, benefits, mobility
            $table->string('source_entity_type', 80);
            $table->uuid('source_entity_id')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('hcm_ops_deadlines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 150);
            $table->string('domain', 60);
            $table->date('cutoff_date');
            $table->unsignedInteger('lead_days_warning')->default(3);
            $table->string('status', 30)->default('upcoming'); // upcoming, due_soon, passed, closed
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        // 6. Operational Checklists
        Schema::create('hcm_ops_checklist_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 60)->index();
            $table->string('name', 150);
            $table->string('trigger_type', 60); // new_hire, transfer, promotion, separation, international_assignment
            $table->json('default_items')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hcm_ops_checklist_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('template_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('reference_number', 60)->unique();
            $table->string('status', 40)->default('open'); // open, in_progress, completed, cancelled
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->timestamps();
        });

        Schema::create('hcm_ops_checklist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('checklist_instance_id')->index();
            $table->string('category', 60); // equipment, payroll, benefits, compliance, documents, access
            $table->string('title', 180);
            $table->string('status', 30)->default('pending'); // pending, completed, waived
            $table->uuid('assigned_to')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->uuid('completed_by')->nullable()->index();
            $table->timestamps();
        });

        // 7. Bulk Operations Framework
        Schema::create('hcm_ops_bulk_operations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('operation_number', 60)->unique();
            $table->string('operation_type', 80); // bulk_department_update, bulk_location_update, bulk_manager_update, bulk_job_change, bulk_status_change
            $table->string('status', 40)->default('draft'); // draft, validating, validated, dry_run_ready, approved, executing, completed, failed
            $table->text('reason');
            $table->date('effective_date');
            $table->json('proposed_changes');
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('processed_records')->default(0);
            $table->unsignedInteger('successful_records')->default(0);
            $table->unsignedInteger('failed_records')->default(0);
            $table->uuid('created_by')->index();
            $table->uuid('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('hcm_ops_bulk_operation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('bulk_operation_id')->index();
            $table->uuid('employee_id')->index();
            $table->json('current_values')->nullable();
            $table->json('target_values')->nullable();
            $table->string('status', 30)->default('pending'); // pending, valid, error, executed, failed
            $table->text('execution_error')->nullable();
            $table->timestamps();
        });

        Schema::create('hcm_ops_bulk_operation_validations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('bulk_operation_id')->index();
            $table->unsignedInteger('valid_count')->default(0);
            $table->unsignedInteger('warning_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->json('impacted_domains')->nullable(); // payroll, benefits, compliance, etc.
            $table->json('validation_summary')->nullable();
            $table->timestamp('validated_at')->useCurrent();
        });

        Schema::create('hcm_ops_bulk_operation_errors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('bulk_operation_id')->index();
            $table->uuid('bulk_operation_item_id')->nullable()->index();
            $table->uuid('employee_id')->nullable()->index();
            $table->string('error_code', 80);
            $table->text('error_message');
            $table->timestamp('created_at')->useCurrent();
        });

        // 8. Data Quality & Reconciliation
        Schema::create('hcm_ops_data_quality_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('rule_code', 80)->index();
            $table->string('name', 150);
            $table->string('category', 40); // completeness, validity, consistency, uniqueness, freshness
            $table->string('domain', 60);
            $table->string('severity', 30)->default('warning'); // critical, error, warning, info
            $table->text('description')->nullable();
            $table->text('resolution_guidance')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hcm_ops_data_quality_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('run_number', 60)->unique();
            $table->decimal('overall_score', 5, 2)->default(100.00);
            $table->unsignedInteger('total_records_scanned')->default(0);
            $table->unsignedInteger('total_violations_found')->default(0);
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('hcm_ops_data_quality_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('run_id')->index();
            $table->uuid('rule_id')->index();
            $table->uuid('employee_id')->nullable()->index();
            $table->string('entity_type', 80);
            $table->uuid('entity_id')->index();
            $table->text('violation_message');
            $table->string('status', 30)->default('open'); // open, ignored, resolved
            $table->timestamps();
        });

        Schema::create('hcm_ops_reconciliation_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 60)->index();
            $table->string('name', 150);
            $table->string('source_domain', 60); // core_hr
            $table->string('target_domain', 60); // payroll, benefits, compliance, documents
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hcm_ops_reconciliation_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('rule_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('reconciliation_status', 30); // matched, missing, mismatch, duplicate
            $table->text('discrepancy_details')->nullable();
            $table->timestamp('checked_at')->useCurrent();
            $table->timestamps();
        });

        // 9. Configuration Health Checks
        Schema::create('hcm_ops_configuration_health_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('config_category', 60); // workflows, policies, integrations, templates, compensation, benefits
            $table->string('check_name', 150);
            $table->string('health_status', 30)->default('healthy'); // healthy, warning, error
            $table->text('diagnostic_message')->nullable();
            $table->timestamp('last_checked_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_ops_configuration_health_checks');
        Schema::dropIfExists('hcm_ops_reconciliation_results');
        Schema::dropIfExists('hcm_ops_reconciliation_rules');
        Schema::dropIfExists('hcm_ops_data_quality_results');
        Schema::dropIfExists('hcm_ops_data_quality_runs');
        Schema::dropIfExists('hcm_ops_data_quality_rules');
        Schema::dropIfExists('hcm_ops_bulk_operation_errors');
        Schema::dropIfExists('hcm_ops_bulk_operation_validations');
        Schema::dropIfExists('hcm_ops_bulk_operation_items');
        Schema::dropIfExists('hcm_ops_bulk_operations');
        Schema::dropIfExists('hcm_ops_checklist_items');
        Schema::dropIfExists('hcm_ops_checklist_instances');
        Schema::dropIfExists('hcm_ops_checklist_templates');
        Schema::dropIfExists('hcm_ops_deadlines');
        Schema::dropIfExists('hcm_ops_calendar_events');
        Schema::dropIfExists('hcm_ops_governance_rules');
        Schema::dropIfExists('hcm_ops_governance_policies');
        Schema::dropIfExists('hcm_ops_sla_instances');
        Schema::dropIfExists('hcm_ops_sla_policies');
        Schema::dropIfExists('hcm_ops_exception_events');
        Schema::dropIfExists('hcm_ops_exception_assignments');
        Schema::dropIfExists('hcm_ops_exceptions');
        Schema::dropIfExists('hcm_ops_queue_items');
        Schema::dropIfExists('hcm_ops_queues');
    }
};
