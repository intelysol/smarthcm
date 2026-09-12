<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend roster_periods with versioning, lock, validation, and cost estimates
        if (Schema::hasTable('roster_periods')) {
            Schema::table('roster_periods', function (Blueprint $table): void {
                if (! Schema::hasColumn('roster_periods', 'version')) {
                    $table->unsignedSmallInteger('version')->default(1)->after('status');
                }
                if (! Schema::hasColumn('roster_periods', 'is_locked')) {
                    $table->boolean('is_locked')->default(false)->after('version');
                }
                if (! Schema::hasColumn('roster_periods', 'locked_at')) {
                    $table->timestamp('locked_at')->nullable()->after('is_locked');
                }
                if (! Schema::hasColumn('roster_periods', 'locked_by')) {
                    $table->foreignId('locked_by')->nullable()->after('locked_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('roster_periods', 'validation_status')) {
                    $table->string('validation_status', 40)->default('unvalidated')->after('locked_by');
                }
                if (! Schema::hasColumn('roster_periods', 'validation_summary')) {
                    $table->json('validation_summary')->nullable()->after('validation_status');
                }
                if (! Schema::hasColumn('roster_periods', 'schedule_quality_score')) {
                    $table->decimal('schedule_quality_score', 5, 2)->nullable()->after('validation_summary');
                }
                if (! Schema::hasColumn('roster_periods', 'estimated_labor_cost')) {
                    $table->decimal('estimated_labor_cost', 15, 2)->default(0.00)->after('schedule_quality_score');
                }
            });
        }

        // 2. Coverage Requirements (Ingesting Epic 2.45 Capacity & Shift Requirements)
        if (! Schema::hasTable('hcm_schedule_coverage_requirements')) {
            Schema::create('hcm_schedule_coverage_requirements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('roster_period_id')->index();
                $table->date('requirement_date')->index();
                $table->uuid('shift_definition_id')->nullable()->index();
                $table->string('start_time', 10)->nullable();
                $table->string('end_time', 10)->nullable();
                $table->unsignedInteger('required_headcount')->default(1);
                $table->uuid('department_id')->nullable()->index();
                $table->uuid('location_id')->nullable()->index();
                $table->uuid('position_id')->nullable()->index();
                $table->uuid('required_skill_id')->nullable()->index();
                $table->unsignedTinyInteger('min_proficiency_level')->default(1);
                $table->uuid('capacity_calculation_id')->nullable()->index();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('roster_period_id')->references('id')->on('roster_periods')->cascadeOnDelete();
                $table->foreign('shift_definition_id')->references('id')->on('shift_definitions')->nullOnDelete();
            });
        }

        // 3. Employee Availability Blocks
        if (! Schema::hasTable('hcm_employee_availabilities')) {
            Schema::create('hcm_employee_availabilities', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->date('date')->index();
                $table->string('start_time', 10)->nullable();
                $table->string('end_time', 10)->nullable();
                $table->string('availability_type', 30)->default('available'); // available, unavailable, preferred, restricted
                $table->string('reason', 255)->nullable();
                $table->boolean('is_recurring')->default(false);
                $table->unsignedTinyInteger('recurring_day_of_week')->nullable(); // 0 (Sun) to 6 (Sat)
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 4. Employee Shift Preferences
        if (! Schema::hasTable('hcm_employee_shift_preferences')) {
            Schema::create('hcm_employee_shift_preferences', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('shift_definition_id')->nullable()->index();
                $table->unsignedTinyInteger('day_of_week')->nullable(); // 0 to 6
                $table->string('preference_type', 20)->default('preferred'); // preferred, avoid
                $table->unsignedTinyInteger('priority')->default(1); // 1 = low, 2 = medium, 3 = high
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('shift_definition_id')->references('id')->on('shift_definitions')->nullOnDelete();
            });
        }

        // 5. Shift Swap Requests
        if (! Schema::hasTable('hcm_shift_swap_requests')) {
            Schema::create('hcm_shift_swap_requests', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('requesting_assignment_id')->index();
                $table->uuid('target_assignment_id')->nullable()->index();
                $table->uuid('requesting_employee_id')->index();
                $table->uuid('target_employee_id')->nullable()->index();
                $table->string('status', 30)->default('pending_peer'); // pending_peer, pending_manager, approved, rejected, cancelled
                $table->json('validation_payload')->nullable();
                $table->timestamp('peer_response_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('requesting_assignment_id')->references('id')->on('roster_assignments')->cascadeOnDelete();
                $table->foreign('target_assignment_id')->references('id')->on('roster_assignments')->nullOnDelete();
                $table->foreign('requesting_employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('target_employee_id')->references('id')->on('employees')->nullOnDelete();
            });
        }

        // 6. Open Shifts & Bidding
        if (! Schema::hasTable('hcm_open_shifts')) {
            Schema::create('hcm_open_shifts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('roster_period_id')->index();
                $table->date('roster_date')->index();
                $table->uuid('shift_definition_id')->index();
                $table->uuid('department_id')->nullable()->index();
                $table->uuid('location_id')->nullable()->index();
                $table->uuid('required_skill_id')->nullable()->index();
                $table->unsignedInteger('slots_total')->default(1);
                $table->unsignedInteger('slots_filled')->default(0);
                $table->string('status', 30)->default('open'); // open, filled, cancelled, expired
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('roster_period_id')->references('id')->on('roster_periods')->cascadeOnDelete();
                $table->foreign('shift_definition_id')->references('id')->on('shift_definitions')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('hcm_open_shift_bids')) {
            Schema::create('hcm_open_shift_bids', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('open_shift_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('bid_status', 30)->default('submitted'); // submitted, selected, declined
                $table->decimal('eligibility_score', 5, 2)->default(100.00);
                $table->json('eligibility_details')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('open_shift_id')->references('id')->on('hcm_open_shifts')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 7. Schedule Exceptions & Real-Time Operational Deviations
        if (! Schema::hasTable('hcm_schedule_exceptions')) {
            Schema::create('hcm_schedule_exceptions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('roster_assignment_id')->nullable()->index();
                $table->uuid('employee_id')->nullable()->index();
                $table->string('exception_type', 50); // no_show, late_arrival, missing_staff, skill_shortage, undercoverage, overcoverage, excessive_overtime, leave_conflict
                $table->string('severity', 20)->default('warning'); // info, warning, critical
                $table->string('status', 30)->default('detected'); // detected, acknowledged, assigned, resolved, closed
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->text('resolution_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('roster_assignment_id')->references('id')->on('roster_assignments')->nullOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            });
        }

        // 8. Schedule Acknowledgements
        if (! Schema::hasTable('hcm_schedule_acknowledgements')) {
            Schema::create('hcm_schedule_acknowledgements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('roster_period_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('status', 30)->default('acknowledged'); // acknowledged, declined, disputed
                $table->text('dispute_reason')->nullable();
                $table->timestamp('acknowledged_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('roster_period_id')->references('id')->on('roster_periods')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->unique(['roster_period_id', 'employee_id']);
            });
        }

        // 9. Schedule Change Logs
        if (! Schema::hasTable('hcm_schedule_change_logs')) {
            Schema::create('hcm_schedule_change_logs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('roster_assignment_id')->index();
                $table->uuid('previous_shift_id')->nullable();
                $table->uuid('new_shift_id')->nullable();
                $table->date('previous_date')->nullable();
                $table->date('new_date')->nullable();
                $table->string('reason', 255);
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('roster_assignment_id')->references('id')->on('roster_assignments')->cascadeOnDelete();
            });
        }

        // 10. Schedule Optimization Runs
        if (! Schema::hasTable('hcm_schedule_optimization_runs')) {
            Schema::create('hcm_schedule_optimization_runs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('roster_period_id')->index();
                $table->string('strategy', 50)->default('rule_based_heuristic'); // rule_based_heuristic, greedy_coverage, cost_minimizer, fairness_balancer
                $table->string('status', 30)->default('pending'); // pending, running, completed, failed
                $table->json('metrics_before')->nullable();
                $table->json('metrics_after')->nullable();
                $table->json('proposed_assignments')->nullable();
                $table->decimal('quality_score', 5, 2)->default(0.00);
                $table->foreignId('ran_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('roster_period_id')->references('id')->on('roster_periods')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_schedule_optimization_runs');
        Schema::dropIfExists('hcm_schedule_change_logs');
        Schema::dropIfExists('hcm_schedule_acknowledgements');
        Schema::dropIfExists('hcm_schedule_exceptions');
        Schema::dropIfExists('hcm_open_shift_bids');
        Schema::dropIfExists('hcm_open_shifts');
        Schema::dropIfExists('hcm_shift_swap_requests');
        Schema::dropIfExists('hcm_employee_shift_preferences');
        Schema::dropIfExists('hcm_employee_availabilities');
        Schema::dropIfExists('hcm_schedule_coverage_requirements');

        if (Schema::hasTable('roster_periods')) {
            Schema::table('roster_periods', function (Blueprint $table): void {
                $table->dropForeign(['locked_by']);
                $table->dropColumn([
                    'version',
                    'is_locked',
                    'locked_at',
                    'locked_by',
                    'validation_status',
                    'validation_summary',
                    'schedule_quality_score',
                    'estimated_labor_cost',
                ]);
            });
        }
    }
};
