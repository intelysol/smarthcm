<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Workforce Cost Models (Configuration per tenant)
        if (! Schema::hasTable('hcm_workforce_cost_models')) {
            Schema::create('hcm_workforce_cost_models', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('name', 120);
                $table->string('currency', 3)->default('USD');
                $table->decimal('standard_labor_burden_rate', 5, 2)->default(25.00); // % of direct wages
                $table->decimal('standard_working_hours_per_fte_month', 6, 2)->default(160.00);
                $table->string('default_allocation_method', 50)->default('percentage');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 2. Workforce Cost Snapshots (Immutable versioned snapshots)
        if (! Schema::hasTable('hcm_workforce_cost_snapshots')) {
            Schema::create('hcm_workforce_cost_snapshots', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('model_id')->nullable()->index();
                $table->string('snapshot_number', 64)->unique();
                $table->string('period_name', 50); // e.g. 2026-10, Q4-2026, 2026-FY
                $table->date('period_start')->index();
                $table->date('period_end')->index();
                $table->unsignedInteger('version')->default(1);
                $table->string('status', 30)->default('draft'); // draft, locked, published, superseded
                $table->string('currency', 3)->default('USD');
                $table->decimal('total_workforce_cost', 14, 4)->default(0);
                $table->decimal('total_direct_labor', 14, 4)->default(0);
                $table->decimal('total_indirect_labor', 14, 4)->default(0);
                $table->decimal('total_burden', 14, 4)->default(0);
                $table->decimal('total_overtime_cost', 14, 4)->default(0);
                $table->decimal('total_benefits_cost', 14, 4)->default(0);
                $table->decimal('total_contractor_cost', 14, 4)->default(0);
                $table->decimal('total_absence_cost', 14, 4)->default(0);
                $table->decimal('total_vacancy_cost', 14, 4)->default(0);
                $table->decimal('total_fte', 8, 2)->default(0);
                $table->unsignedInteger('total_headcount')->default(0);
                $table->decimal('total_labor_hours', 10, 2)->default(0);
                $table->string('idempotency_key', 128)->nullable()->index();
                $table->timestamp('locked_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('model_id')->references('id')->on('hcm_workforce_cost_models')->nullOnDelete();
            });
        }

        // 3. Workforce Cost Lines (Normalized cost components)
        if (! Schema::hasTable('hcm_workforce_cost_lines')) {
            Schema::create('hcm_workforce_cost_lines', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('snapshot_id')->nullable()->index();
                $table->uuid('employee_id')->nullable()->index();
                $table->uuid('department_id')->nullable()->index();
                $table->uuid('cost_center_id')->nullable()->index();
                $table->uuid('position_id')->nullable()->index();
                $table->uuid('job_id')->nullable()->index();
                $table->uuid('project_id')->nullable()->index();
                $table->string('worker_type', 30)->default('employee'); // employee, contractor, agency
                $table->string('cost_category', 50)->default('direct_labor'); // direct_labor, indirect_labor, burden, acquisition, development, mobility, contractor, opportunity
                $table->string('cost_nature', 30)->default('ACTUAL'); // ACTUAL, PLANNED, FORECAST, ESTIMATED, ALLOCATED, SCENARIO
                $table->string('component_type', 50)->default('BASE_PAY'); // BASE_PAY, OVERTIME, BONUS, BENEFITS, EMPLOYER_TAX, etc.
                $table->string('source_domain', 50)->default('payroll'); // payroll, compensation, benefits, attendance, scheduling, absence, expenses, recruitment, workforce_planning
                $table->string('source_record_id', 64)->nullable()->index();
                $table->date('cost_date')->index();
                $table->decimal('amount', 14, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->decimal('hours_worked', 8, 2)->default(0);
                $table->decimal('rate_per_hour', 10, 4)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('snapshot_id')->references('id')->on('hcm_workforce_cost_snapshots')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            });
        }

        // 4. Workforce Cost Allocation Rules
        if (! Schema::hasTable('hcm_workforce_cost_allocation_rules')) {
            Schema::create('hcm_workforce_cost_allocation_rules', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('rule_name', 150);
                $table->string('allocation_method', 50)->default('percentage'); // direct, percentage, hours_based, fte_based, cost_driver, activity_based, schedule_based
                $table->string('source_type', 50)->default('department'); // department, employee, cost_center, indirect_pool
                $table->uuid('source_id')->nullable()->index();
                $table->json('targets'); // array of {target_type, target_id, percentage}
                $table->unsignedSmallInteger('priority')->default(10);
                $table->date('effective_from')->index();
                $table->date('effective_to')->nullable()->index();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 5. Workforce Cost Allocations (Execution output)
        if (! Schema::hasTable('hcm_workforce_cost_allocations')) {
            Schema::create('hcm_workforce_cost_allocations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('cost_line_id')->index();
                $table->uuid('allocation_rule_id')->nullable()->index();
                $table->uuid('target_department_id')->nullable()->index();
                $table->uuid('target_cost_center_id')->nullable()->index();
                $table->uuid('target_project_id')->nullable()->index();
                $table->decimal('allocated_percentage', 6, 2)->default(100.00);
                $table->decimal('allocated_amount', 14, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->string('status', 30)->default('allocated'); // allocated, pending_review, disputed, reversed
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('cost_line_id')->references('id')->on('hcm_workforce_cost_lines')->cascadeOnDelete();
                $table->foreign('allocation_rule_id')->references('id')->on('hcm_workforce_cost_allocation_rules')->nullOnDelete();
            });
        }

        // 6. Workforce Cost Forecasts
        if (! Schema::hasTable('hcm_workforce_cost_forecasts')) {
            Schema::create('hcm_workforce_cost_forecasts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('department_id')->nullable()->index();
                $table->uuid('cost_center_id')->nullable()->index();
                $table->date('forecast_period_start')->index();
                $table->date('forecast_period_end')->index();
                $table->decimal('baseline_amount', 14, 4)->default(0);
                $table->decimal('headcount_impact_amount', 14, 4)->default(0);
                $table->decimal('salary_increase_impact_amount', 14, 4)->default(0);
                $table->decimal('overtime_impact_amount', 14, 4)->default(0);
                $table->decimal('contractor_impact_amount', 14, 4)->default(0);
                $table->decimal('forecast_total_cost', 14, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->json('assumptions')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 7. Workforce Cost Variances
        if (! Schema::hasTable('hcm_workforce_cost_variances')) {
            Schema::create('hcm_workforce_cost_variances', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('department_id')->nullable()->index();
                $table->uuid('cost_center_id')->nullable()->index();
                $table->date('period_start')->index();
                $table->date('period_end')->index();
                $table->string('comparison_type', 50)->default('plan_vs_actual'); // budget_vs_actual, plan_vs_actual, forecast_vs_actual, prior_period_vs_actual
                $table->decimal('planned_amount', 14, 4)->default(0);
                $table->decimal('actual_amount', 14, 4)->default(0);
                $table->decimal('variance_amount', 14, 4)->default(0);
                $table->decimal('variance_percentage', 6, 2)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->json('drivers')->nullable(); // breakdown: headcount %, rate %, overtime %, benefits %, contractor %, absence %
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 8. Workforce Cost Economics
        if (! Schema::hasTable('hcm_workforce_cost_economics')) {
            Schema::create('hcm_workforce_cost_economics', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('department_id')->nullable()->index();
                $table->date('period_start')->index();
                $table->date('period_end')->index();
                $table->decimal('cost_per_fte', 12, 2)->default(0);
                $table->decimal('cost_per_employee', 12, 2)->default(0);
                $table->decimal('cost_per_labor_hour', 10, 4)->default(0);
                $table->decimal('cost_per_productive_hour', 10, 4)->default(0);
                $table->decimal('overtime_cost_ratio', 6, 2)->default(0); // overtime cost / total labor cost %
                $table->decimal('contractor_ratio', 6, 2)->default(0); // contractor cost / total workforce cost %
                $table->decimal('absence_cost_total', 14, 4)->default(0);
                $table->decimal('vacancy_cost_total', 14, 4)->default(0);
                $table->decimal('cost_per_available_capacity_hour', 10, 4)->default(0);
                $table->decimal('cost_per_required_capacity_hour', 10, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 9. Workforce Cost Scenarios (What-if modeling)
        if (! Schema::hasTable('hcm_workforce_cost_scenarios')) {
            Schema::create('hcm_workforce_cost_scenarios', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('base_scenario_id')->nullable()->index();
                $table->string('name', 150);
                $table->string('scenario_type', 50); // hire, freeze, contractor_substitution, outsource, automate, reskill, relocate, salary_change, shift_redesign
                $table->decimal('current_cost', 14, 4)->default(0);
                $table->decimal('scenario_cost', 14, 4)->default(0);
                $table->decimal('cost_difference', 14, 4)->default(0);
                $table->decimal('headcount_difference', 6, 2)->default(0);
                $table->decimal('fte_difference', 6, 2)->default(0);
                $table->decimal('capacity_difference_hours', 10, 2)->default(0);
                $table->decimal('estimated_roi_percentage', 6, 2)->nullable();
                $table->decimal('payback_months', 5, 1)->nullable();
                $table->string('status', 30)->default('active'); // active, archived, adopted
                $table->string('currency', 3)->default('USD');
                $table->json('parameters')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 10. Workforce Cost Reconciliations
        if (! Schema::hasTable('hcm_workforce_cost_reconciliations')) {
            Schema::create('hcm_workforce_cost_reconciliations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('reconciliation_type', 50); // payroll_vs_workforce_cost, finance_vs_workforce_cost
                $table->string('source_period', 50);
                $table->decimal('source_total', 14, 4)->default(0);
                $table->decimal('workforce_cost_total', 14, 4)->default(0);
                $table->decimal('variance_amount', 14, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->string('status', 30)->default('MATCHED'); // MATCHED, MINOR_VARIANCE, MAJOR_VARIANCE, MISSING_SOURCE, MISSING_MAPPING, ERROR
                $table->json('discrepancy_details')->nullable();
                $table->timestamp('reconciled_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 11. Workforce Cost Audits
        if (! Schema::hasTable('hcm_workforce_cost_audits')) {
            Schema::create('hcm_workforce_cost_audits', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('action_type', 60); // snapshot_generated, cost_allocated, forecast_generated, rule_modified, reconciliation_executed
                $table->string('entity_type', 100);
                $table->uuid('entity_id')->index();
                $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('previous_state')->nullable();
                $table->json('new_state')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_workforce_cost_audits');
        Schema::dropIfExists('hcm_workforce_cost_reconciliations');
        Schema::dropIfExists('hcm_workforce_cost_scenarios');
        Schema::dropIfExists('hcm_workforce_cost_economics');
        Schema::dropIfExists('hcm_workforce_cost_variances');
        Schema::dropIfExists('hcm_workforce_cost_forecasts');
        Schema::dropIfExists('hcm_workforce_cost_allocations');
        Schema::dropIfExists('hcm_workforce_cost_allocation_rules');
        Schema::dropIfExists('hcm_workforce_cost_lines');
        Schema::dropIfExists('hcm_workforce_cost_snapshots');
        Schema::dropIfExists('hcm_workforce_cost_models');
    }
};