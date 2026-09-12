<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Separation Types (Configurable)
        Schema::create('separation_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50); // RESIGNATION, INVOLUNTARY_TERMINATION, RETIREMENT, CONTRACT_EXPIRY, REDUNDANCY, etc.
            $table->string('name', 120);
            $table->string('category', 50)->default('voluntary'); // voluntary, involuntary, retirement, expiry, mutual, other
            $table->unsignedInteger('notice_days_default')->default(30);
            $table->boolean('requires_clearance')->default(true);
            $table->boolean('requires_exit_interview')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 2. Separation Requests (Aggregate Root)
        Schema::create('separation_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('separation_type_id')->index();
            $table->string('request_number', 50); // SEP-2026-000001
            $table->string('status', 30)->default('draft'); // draft, submitted, under_review, pending_approval, approved, rejected, notice_period, offboarding, clearance, final_settlement, ready_for_exit, exited, cancelled, reversed, failed
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('requested_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('effective_date'); // Target separation effective date
            $table->date('notice_start_date')->nullable();
            $table->date('proposed_last_working_day');
            $table->date('approved_last_working_day')->nullable();
            $table->date('actual_last_working_day')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 255)->nullable();
            $table->text('comments')->nullable();
            $table->string('source', 50)->default('self_service'); // self_service, hr, manager, system, er
            $table->uuid('er_case_reference_id')->nullable()->index(); // Controlled reference to ER case without exposing details
            $table->boolean('is_garden_leave')->default(false);
            $table->date('garden_leave_start_date')->nullable();
            $table->date('garden_leave_end_date')->nullable();
            $table->uuid('workflow_instance_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('separation_type_id')->references('id')->on('separation_types')->cascadeOnDelete();
            $table->unique(['tenant_id', 'request_number']);
            $table->index(['tenant_id', 'employee_id']);
            $table->index(['tenant_id', 'effective_date']);
            $table->index(['tenant_id', 'status']);
        });

        // 3. Separation Impacts (Deterministic Read-Only Cross-Domain Analysis)
        Schema::create('separation_impacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('separation_request_id')->index();
            $table->string('domain', 50); // payroll, leave, benefits, assets, loans, expenses, er, access
            $table->string('impact_type', 50); // final_pay_calc, leave_encashment, unreturned_asset, open_loan, pending_expense, open_er_case
            $table->string('severity', 20)->default('info'); // info, warning, blocking
            $table->string('status', 30)->default('pending');
            $table->text('message');
            $table->string('source', 50)->default('system');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('separation_request_id')->references('id')->on('separation_requests')->cascadeOnDelete();
        });

        // 4. Separation Notice Periods (Tracking Calculation vs Overrides)
        Schema::create('separation_notice_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('separation_request_id')->index();
            $table->date('notice_start_date');
            $table->unsignedInteger('required_days');
            $table->date('calculated_last_working_day');
            $table->unsignedInteger('agreed_days')->nullable();
            $table->date('adjusted_last_working_day')->nullable();
            $table->boolean('is_overridden')->default(false);
            $table->string('override_reason')->nullable();
            $table->foreignId('overridden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_waived')->default(false);
            $table->boolean('is_buyout')->default(false);
            $table->decimal('buyout_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('separation_request_id')->references('id')->on('separation_requests')->cascadeOnDelete();
        });

        // 5. Separation Clearances (Multi-Department Clearance Containers)
        Schema::create('separation_clearances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('separation_request_id')->index();
            $table->string('department', 40); // hr, finance, it, assets, admin, security
            $table->string('status', 30)->default('pending'); // pending, cleared, rejected, waived, blocked
            $table->foreignId('cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cleared_at')->nullable();
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('waiver_reason')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('separation_request_id')->references('id')->on('separation_requests')->cascadeOnDelete();
            $table->unique(['separation_request_id', 'department']);
        });

        // 6. Separation Clearance Items (Individual Department Checklist Items)
        Schema::create('separation_clearance_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('separation_clearance_id')->index();
            $table->string('title', 150);
            $table->string('status', 30)->default('pending'); // pending, cleared, rejected, waived
            $table->boolean('is_blocking')->default(true);
            $table->text('comments')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('separation_clearance_id')->references('id')->on('separation_clearances')->cascadeOnDelete();
        });

        // 7. Separation Handover Records
        Schema::create('separation_handover_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('separation_request_id')->index();
            $table->uuid('successor_employee_id')->nullable()->index();
            $table->date('handover_date')->nullable();
            $table->string('status', 30)->default('pending'); // pending, in_progress, completed, verified
            $table->text('handover_notes')->nullable();
            $table->foreignId('manager_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_verified_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('separation_request_id')->references('id')->on('separation_requests')->cascadeOnDelete();
            $table->foreign('successor_employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        Schema::create('separation_handover_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('separation_handover_record_id')->index();
            $table->string('title', 150);
            $table->string('category', 50)->default('responsibility'); // responsibility, project, client, document, task
            $table->string('status', 30)->default('pending'); // pending, completed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('separation_handover_record_id')->references('id')->on('separation_handover_records')->cascadeOnDelete();
        });

        // 8. Separation Final Settlements (Orchestrator Snapshot)
        Schema::create('separation_final_settlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('separation_request_id')->index();
            $table->uuid('payroll_run_id')->nullable()->index();
            $table->decimal('gross_payable', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('net_payable', 15, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('settlement_status', 30)->default('pending'); // pending, calculated, approved, paid, hold
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('payment_date')->nullable();
            $table->json('snapshot_data')->nullable(); // Unpaid salary, leave settlement, deductions, loans
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('separation_request_id')->references('id')->on('separation_requests')->cascadeOnDelete();
        });

        // 9. Separation Exit Interviews
        Schema::create('separation_exit_interviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('separation_request_id')->index();
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('responses')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('overall_sentiment', 30)->nullable(); // positive, neutral, negative
            $table->string('primary_reason_category', 50)->nullable(); // career_growth, compensation, management, personal, relocation, other
            $table->text('confidential_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('separation_request_id')->references('id')->on('separation_requests')->cascadeOnDelete();
        });

        // 10. Separation Exit Documents
        Schema::create('separation_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('separation_request_id')->index();
            $table->string('document_type', 50); // relieving_letter, experience_certificate, clearance_certificate, termination_letter, service_certificate
            $table->string('title', 150);
            $table->string('file_path');
            $table->boolean('employee_accessible')->default(true);
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('separation_request_id')->references('id')->on('separation_requests')->cascadeOnDelete();
        });

        // 11. Separation Reversals & Reinstatements
        Schema::create('separation_reversals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('separation_request_id')->index();
            $table->string('action_type', 30)->default('reversal'); // reversal, reinstatement
            $table->text('reason');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('separation_request_id')->references('id')->on('separation_requests')->cascadeOnDelete();
        });

        // 12. Separation Audits (Immutable Historical Audit Trail)
        Schema::create('separation_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('separation_request_id')->index();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_name', 60); // created, submitted, notice_overridden, approved, clearance_cleared, clearance_waived, settlement_approved, executed, reversed, cancelled
            $table->json('old_state')->nullable();
            $table->json('new_state')->nullable();
            $table->string('reason', 255)->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('separation_request_id')->references('id')->on('separation_requests')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('separation_audits');
        Schema::dropIfExists('separation_reversals');
        Schema::dropIfExists('separation_documents');
        Schema::dropIfExists('separation_exit_interviews');
        Schema::dropIfExists('separation_final_settlements');
        Schema::dropIfExists('separation_handover_items');
        Schema::dropIfExists('separation_handover_records');
        Schema::dropIfExists('separation_clearance_items');
        Schema::dropIfExists('separation_clearances');
        Schema::dropIfExists('separation_notice_periods');
        Schema::dropIfExists('separation_impacts');
        Schema::dropIfExists('separation_requests');
        Schema::dropIfExists('separation_types');
    }
};
