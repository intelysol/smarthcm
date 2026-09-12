<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Normalized Absence Events
        if (! Schema::hasTable('hcm_absence_events')) {
            Schema::create('hcm_absence_events', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->date('absence_date')->index();
                $table->string('start_time', 10)->nullable();
                $table->string('end_time', 10)->nullable();
                $table->decimal('duration_hours', 5, 2)->default(8.00);
                $table->string('absence_category', 50)->default('unplanned_sick'); // vacation, personal, family, unplanned_sick, bereavement, jury_duty, parental, training, administrative, other
                $table->string('source', 40)->default('employee_self_service'); // leave, attendance_exception, manager_report, employee_self_service, occupational_health
                $table->uuid('leave_application_id')->nullable()->index();
                $table->uuid('attendance_exception_id')->nullable()->index();
                $table->boolean('is_planned')->default(false);
                $table->string('status', 30)->default('reported')->index(); // reported, active, completed, cancelled
                $table->timestamp('reported_at')->useCurrent();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 2. Absence Periods (Continuous multi-day absence & Long-term tracking)
        if (! Schema::hasTable('hcm_absence_periods')) {
            Schema::create('hcm_absence_periods', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->date('start_date')->index();
                $table->date('end_date')->index();
                $table->unsignedInteger('working_days_count')->default(1);
                $table->unsignedInteger('calendar_days_count')->default(1);
                $table->decimal('total_absence_hours', 7, 2)->default(8.00);
                $table->string('absence_category', 50)->default('unplanned_sick');
                $table->boolean('is_long_term')->default(false)->index();
                $table->string('status', 30)->default('reported')->index(); // reported, active, extended, returned, closed, cancelled
                $table->date('expected_return_date')->nullable();
                $table->date('actual_return_date')->nullable();
                $table->uuid('leave_application_id')->nullable()->index();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 3. Operational Absence Impacts (Shift-level coverage & capacity deficit)
        if (! Schema::hasTable('hcm_absence_operational_impacts')) {
            Schema::create('hcm_absence_operational_impacts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('absence_event_id')->index();
                $table->uuid('employee_id')->index();
                $table->date('impact_date')->index();
                $table->uuid('affected_shift_id')->nullable()->index();
                $table->uuid('affected_roster_assignment_id')->nullable()->index();
                $table->decimal('scheduled_hours', 5, 2)->default(8.00);
                $table->decimal('lost_capacity_hours', 5, 2)->default(8.00);
                $table->uuid('department_id')->nullable()->index();
                $table->uuid('location_id')->nullable()->index();
                $table->string('coverage_status', 40)->default('uncovered')->index(); // uncovered, open_shift_created, reassigned, overtime_requested, covered
                $table->uuid('replacement_employee_id')->nullable()->index();
                $table->string('replacement_strategy', 50)->nullable(); // internal_allocation, open_shift, temporary_assignment, overtime, contractor
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('absence_event_id')->references('id')->on('hcm_absence_events')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('replacement_employee_id')->references('id')->on('employees')->nullOnDelete();
            });
        }

        // 4. Return-to-Work Plans (Phased capacity & operational restrictions)
        if (! Schema::hasTable('hcm_absence_return_to_work_plans')) {
            Schema::create('hcm_absence_return_to_work_plans', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('absence_period_id')->nullable()->index();
                $table->uuid('health_safety_case_id')->nullable()->index();
                $table->date('expected_return_date');
                $table->date('actual_return_date')->nullable();
                $table->string('return_phase', 40)->default('not_started'); // not_started, phased_return, modified_duties, full_duty
                $table->decimal('capacity_percentage', 5, 2)->default(100.00);
                $table->json('operational_restrictions')->nullable();
                $table->date('review_date')->nullable();
                $table->foreignId('responsible_manager_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('hr_owner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 30)->default('draft')->index(); // draft, active, review_due, completed, extended
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('absence_period_id')->references('id')->on('hcm_absence_periods')->nullOnDelete();
            });
        }

        // 5. Absence Cases (Long-term, pattern, or complex cases linked to HR cases)
        if (! Schema::hasTable('hcm_absence_cases')) {
            Schema::create('hcm_absence_cases', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('case_number', 60)->index();
                $table->uuid('employee_id')->index();
                $table->uuid('absence_period_id')->nullable()->index();
                $table->uuid('hr_case_id')->nullable()->index();
                $table->string('case_type', 50)->default('long_term_absence'); // long_term_absence, repeated_short_term, pattern_absence, complex_medical
                $table->string('severity', 30)->default('standard'); // standard, elevated, critical
                $table->string('status', 30)->default('opened')->index(); // opened, assessment, action_required, monitoring, return_planning, returned, closed
                $table->foreignId('assigned_hr_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('trigger_reason');
                $table->timestamp('opened_at')->useCurrent();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('absence_period_id')->references('id')->on('hcm_absence_periods')->nullOnDelete();
                $table->unique(['tenant_id', 'case_number']);
            });
        }

        // 6. Multi-Way Absence Reconciliations (Leave, Absence, Schedule, Attendance, Payroll)
        if (! Schema::hasTable('hcm_absence_reconciliations')) {
            Schema::create('hcm_absence_reconciliations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->date('reconciliation_date')->index();
                $table->date('period_start')->index();
                $table->date('period_end')->index();
                $table->string('status', 30)->default('balanced')->index(); // balanced, discrepancy_detected, reconciled
                $table->unsignedInteger('total_records_checked')->default(0);
                $table->unsignedInteger('discrepant_records_count')->default(0);
                $table->json('discrepancies')->nullable();
                $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reconciled_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 7. Absence Forecasts (Aggregated volume, rates, and capacity risk forecasts)
        if (! Schema::hasTable('hcm_absence_forecasts')) {
            Schema::create('hcm_absence_forecasts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->date('forecast_period_start')->index();
                $table->date('forecast_period_end')->index();
                $table->uuid('department_id')->nullable()->index();
                $table->decimal('projected_absence_hours', 10, 2)->default(0.00);
                $table->decimal('projected_absence_rate', 5, 2)->default(0.00);
                $table->decimal('confidence_score', 3, 2)->default(0.90);
                $table->string('forecast_method', 50)->default('seasonal_historical'); // seasonal_historical, moving_average, advisory_heuristic
                $table->json('assumptions')->nullable();
                $table->timestamp('generated_at')->useCurrent();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_absence_forecasts');
        Schema::dropIfExists('hcm_absence_reconciliations');
        Schema::dropIfExists('hcm_absence_cases');
        Schema::dropIfExists('hcm_absence_return_to_work_plans');
        Schema::dropIfExists('hcm_absence_operational_impacts');
        Schema::dropIfExists('hcm_absence_periods');
        Schema::dropIfExists('hcm_absence_events');
    }
};