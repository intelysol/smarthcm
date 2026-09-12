<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Project & Task Time Allocations (Chargeable vs Payable Time)
        if (! Schema::hasTable('hcm_time_allocations')) {
            Schema::create('hcm_time_allocations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('timesheet_id')->index();
                $table->uuid('timesheet_entry_id')->nullable()->index();
                $table->uuid('employee_id')->index();
                $table->date('allocation_date')->index();
                $table->uuid('project_id')->nullable()->index();
                $table->string('project_code', 60)->nullable();
                $table->uuid('task_id')->nullable()->index();
                $table->string('task_name', 150)->nullable();
                $table->uuid('cost_center_id')->nullable()->index();
                $table->uuid('client_id')->nullable()->index();
                $table->string('work_type', 40)->default('regular'); // regular, overtime, training, travel, meeting, project, on_call, standby
                $table->unsignedInteger('allocated_minutes')->default(0);
                $table->boolean('is_billable')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('timesheet_id')->references('id')->on('timesheets')->cascadeOnDelete();
                $table->foreign('timesheet_entry_id')->references('id')->on('timesheet_entries')->nullOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 2. Multi-Tier Overtime Records
        if (! Schema::hasTable('hcm_overtime_tier_records')) {
            Schema::create('hcm_overtime_tier_records', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('attendance_session_id')->nullable()->index();
                $table->uuid('timesheet_id')->nullable()->index();
                $table->date('overtime_date')->index();
                $table->unsignedInteger('tier_1_minutes')->default(0); // e.g. 1.5x up to 2 hours
                $table->unsignedInteger('tier_2_minutes')->default(0); // e.g. 2.0x between 2 and 4 hours
                $table->unsignedInteger('tier_3_minutes')->default(0); // e.g. 2.5x / 3.0x >4 hours or rest/holiday
                $table->unsignedInteger('total_overtime_minutes')->default(0);
                $table->string('overtime_category', 40)->default('daily_overtime'); // daily_overtime, weekly_overtime, rest_day_overtime, holiday_overtime
                $table->boolean('is_unauthorized')->default(false);
                $table->string('status', 30)->default('calculated')->index(); // calculated, pending_approval, approved, rejected
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->nullOnDelete();
                $table->foreign('timesheet_id')->references('id')->on('timesheets')->nullOnDelete();
            });
        }

        // 3. Batched Payroll Time Exports (Integration Hub Payload)
        if (! Schema::hasTable('hcm_payroll_time_exports')) {
            Schema::create('hcm_payroll_time_exports', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('export_reference', 60)->index();
                $table->uuid('attendance_period_id')->nullable()->index();
                $table->date('period_start')->index();
                $table->date('period_end')->index();
                $table->unsignedInteger('total_employees')->default(0);
                $table->decimal('total_regular_hours', 10, 2)->default(0.00);
                $table->decimal('total_overtime_hours', 10, 2)->default(0.00);
                $table->decimal('total_leave_hours', 10, 2)->default(0.00);
                $table->json('export_payload');
                $table->string('status', 30)->default('generated')->index(); // generated, dispatched, accepted, failed
                $table->foreignId('exported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('exported_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('attendance_period_id')->references('id')->on('attendance_periods')->nullOnDelete();
                $table->unique(['tenant_id', 'export_reference']);
            });
        }

        // 4. Payroll Time Reconciliations (Discrepancy Detection)
        if (! Schema::hasTable('hcm_payroll_time_reconciliations')) {
            Schema::create('hcm_payroll_time_reconciliations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_time_export_id')->index();
                $table->string('payroll_batch_id', 60)->nullable()->index();
                $table->string('status', 30)->default('balanced')->index(); // balanced, discrepancy_detected, reconciled
                $table->unsignedInteger('total_records_checked')->default(0);
                $table->unsignedInteger('discrepant_records_count')->default(0);
                $table->json('discrepancies')->nullable();
                $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reconciled_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_time_export_id')->references('id')->on('hcm_payroll_time_exports')->cascadeOnDelete();
            });
        }

        // 5. Labor Compliance Checks (Fatigue & Regulation Guard)
        if (! Schema::hasTable('hcm_labor_compliance_checks')) {
            Schema::create('hcm_labor_compliance_checks', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->date('check_date')->index();
                $table->uuid('attendance_session_id')->nullable()->index();
                $table->string('rule_code', 50)->index(); // MAX_DAILY_HOURS, MAX_WEEKLY_HOURS, MIN_REST_PERIOD, MANDATORY_MEAL_BREAK, CONSECUTIVE_WORK_DAYS
                $table->string('severity', 20)->default('warning'); // warning, critical, breach
                $table->unsignedInteger('threshold_value')->default(0); // in minutes or days
                $table->unsignedInteger('actual_value')->default(0); // in minutes or days
                $table->string('status', 30)->default('flagged')->index(); // flagged, reviewed, waived, resolved
                $table->text('waiver_reason')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->nullOnDelete();
            });
        }

        // 6. Attendance Correction Audits (Immutable History & Diffs)
        if (! Schema::hasTable('hcm_attendance_correction_audits')) {
            Schema::create('hcm_attendance_correction_audits', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('attendance_adjustment_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('action', 30)->default('requested'); // requested, reviewed, approved, rejected, recalculated
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('before_snapshot')->nullable();
                $table->json('after_snapshot')->nullable();
                $table->text('reason')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('attendance_adjustment_id')->references('id')->on('attendance_adjustments')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_attendance_correction_audits');
        Schema::dropIfExists('hcm_labor_compliance_checks');
        Schema::dropIfExists('hcm_payroll_time_reconciliations');
        Schema::dropIfExists('hcm_payroll_time_exports');
        Schema::dropIfExists('hcm_overtime_tier_records');
        Schema::dropIfExists('hcm_time_allocations');
    }
};