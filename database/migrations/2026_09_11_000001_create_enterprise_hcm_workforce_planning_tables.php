<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Workforce Plans & Versions
        Schema::create('hcm_workforce_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 80)->index();
            $table->string('name', 150);
            $table->string('planning_cycle', 50)->index(); // e.g. FY2027, Q1-2027
            $table->string('planning_type', 50)->default('annual'); // annual, multi_year, quarterly, strategic, project
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('draft')->index(); // draft, open, under_review, approved, locked, archived, rejected
            $table->unsignedInteger('current_version')->default(1);
            $table->string('currency', 10)->default('USD');
            $table->uuid('company_id')->nullable()->index();
            $table->uuid('business_unit_id')->nullable()->index();
            $table->uuid('department_id')->nullable()->index();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('hcm_workforce_plan_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->unsignedInteger('version_number')->default(1);
            $table->string('status', 30)->default('draft');
            $table->text('change_rationale')->nullable();
            $table->json('plan_payload')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
            $table->unique(['tenant_id', 'plan_id', 'version_number'], 'hcm_wf_plan_ver_plan_num_unique');
        });

        Schema::create('hcm_workforce_plan_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->string('period_name', 50); // Jan 2027, Q1 2027, FY 2027
            $table->unsignedSmallInteger('period_sequence')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
        });

        Schema::create('hcm_workforce_plan_assumptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->string('assumption_key', 80); // annual_salary_increase_pct, expected_attrition_pct, fill_rate_pct, hiring_lead_time_days, benefit_cost_pct, payroll_tax_pct
            $table->string('name', 150);
            $table->string('data_type', 30)->default('percentage'); // percentage, days, currency, integer, decimal
            $table->decimal('value', 15, 4);
            $table->text('notes')->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
        });

        // 2. Demand, Supply & Headcount Plans
        Schema::create('hcm_workforce_demand_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('job_grade_id')->nullable()->index();
            $table->string('job_family', 80)->nullable();
            $table->string('driver_name', 100)->default('business_growth'); // revenue, workload, customer_ratio, project
            $table->decimal('current_fte', 8, 2)->default(0.00);
            $table->decimal('required_fte', 8, 2)->default(0.00);
            $table->decimal('demand_gap_fte', 8, 2)->default(0.00);
            $table->json('driver_inputs')->nullable();
            $table->text('justification')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
        });

        Schema::create('hcm_workforce_supply_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->uuid('department_id')->nullable()->index();
            $table->unsignedInteger('current_headcount')->default(0);
            $table->unsignedInteger('expected_retirements')->default(0);
            $table->unsignedInteger('expected_attrition')->default(0);
            $table->unsignedInteger('expected_transfers_out')->default(0);
            $table->unsignedInteger('expected_transfers_in')->default(0);
            $table->unsignedInteger('expected_internal_hires')->default(0);
            $table->unsignedInteger('external_hiring_required')->default(0);
            $table->unsignedInteger('projected_closing_headcount')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
        });

        Schema::create('hcm_workforce_headcount_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->uuid('period_id')->index();
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('branch_id')->nullable()->index();
            
            // Deterministic balance: Closing = Opening + Hires + Transfers In - Exits - Transfers Out
            $table->unsignedInteger('opening_headcount')->default(0);
            $table->unsignedInteger('planned_hires')->default(0);
            $table->unsignedInteger('transfers_in')->default(0);
            $table->unsignedInteger('planned_exits')->default(0);
            $table->unsignedInteger('transfers_out')->default(0);
            $table->unsignedInteger('closing_headcount')->default(0);
            $table->decimal('planned_fte', 8, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
            $table->foreign('period_id')->references('id')->on('hcm_workforce_plan_periods')->cascadeOnDelete();
        });

        // 3. Position Planning & Budgets
        Schema::create('hcm_workforce_position_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->string('position_code', 80)->index();
            $table->string('title', 150);
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->uuid('job_grade_id')->nullable()->index();
            $table->uuid('designation_id')->nullable()->index();
            $table->uuid('cost_center_id')->nullable()->index();
            $table->string('status', 30)->default('planned')->index(); // planned, budgeted, open, occupied, frozen, cancelled, eliminated
            $table->boolean('is_budgeted')->default(true);
            $table->decimal('fte', 5, 2)->default(1.00);
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->uuid('current_employee_id')->nullable()->index();
            $table->uuid('actual_position_id')->nullable()->index(); // Link to Core HR position if mapped
            $table->text('elimination_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
        });

        Schema::create('hcm_workforce_position_budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('position_plan_id')->index();
            $table->decimal('base_salary_budget', 15, 2)->default(0.00);
            $table->decimal('bonus_budget', 15, 2)->default(0.00);
            $table->decimal('benefits_budget', 15, 2)->default(0.00);
            $table->decimal('employer_contributions_budget', 15, 2)->default(0.00);
            $table->decimal('payroll_tax_budget', 15, 2)->default(0.00);
            $table->decimal('recruitment_cost_budget', 15, 2)->default(0.00);
            $table->decimal('equipment_cost_budget', 15, 2)->default(0.00);
            $table->decimal('total_employment_cost', 15, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('position_plan_id')->references('id')->on('hcm_workforce_position_plans')->cascadeOnDelete();
        });

        // 4. Hiring Plans
        Schema::create('hcm_workforce_hiring_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->uuid('position_plan_id')->nullable()->index();
            $table->string('title', 150);
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('job_grade_id')->nullable()->index();
            $table->date('planned_start_date');
            $table->string('priority', 20)->default('medium'); // low, medium, high, critical
            $table->string('reason', 50)->default('growth'); // growth, replacement, new_capability, expansion, backfill
            $table->string('replacement_type', 30)->default('none'); // immediate, delayed, internal, external, none
            $table->uuid('replaces_employee_id')->nullable()->index();
            $table->string('status', 30)->default('planned'); // planned, approved, requisition_created, filled, cancelled
            $table->uuid('job_requisition_id')->nullable()->index(); // Link to Recruitment requisition upon handoff
            $table->foreignId('hiring_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
            $table->foreign('position_plan_id')->references('id')->on('hcm_workforce_position_plans')->nullOnDelete();
        });

        // 5. Cost Plans, Skills & Capacity
        Schema::create('hcm_workforce_cost_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->uuid('department_id')->nullable()->index();
            $table->string('cost_category', 50); // base_salary, overtime, bonus, commission, benefits, statutory_taxes, recruitment, training, relocation
            $table->decimal('budgeted_amount', 18, 2)->default(0.00);
            $table->decimal('forecast_amount', 18, 2)->default(0.00);
            $table->decimal('actual_amount', 18, 2)->default(0.00);
            $table->decimal('variance_amount', 18, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
        });

        Schema::create('hcm_workforce_skill_demand_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->string('skill_name', 100);
            $table->unsignedInteger('current_supply_count')->default(0);
            $table->unsignedInteger('required_count')->default(0);
            $table->integer('skill_gap_count')->default(0);
            $table->unsignedInteger('planned_hiring_count')->default(0);
            $table->unsignedInteger('planned_upskilling_count')->default(0);
            $table->unsignedInteger('planned_mobility_count')->default(0);
            $table->unsignedInteger('planned_contractor_count')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
        });

        Schema::create('hcm_workforce_capacity_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->string('capacity_metric_name', 100); // customers_per_employee, revenue_per_fte, support_tickets_per_agent
            $table->decimal('workload_volume', 18, 2)->default(0.00);
            $table->decimal('ratio_per_fte', 15, 2)->default(1.00);
            $table->decimal('calculated_required_fte', 8, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
        });

        // 6. Workforce Scenarios & Comparison
        Schema::create('hcm_workforce_scenarios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('base_plan_id')->index();
            $table->string('name', 150);
            $table->string('scenario_type', 50)->default('base'); // base, growth, cost_reduction, hiring_freeze, restructuring, expansion
            $table->text('description')->nullable();
            $table->string('status', 30)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('base_plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
        });

        Schema::create('hcm_workforce_scenario_assumptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('scenario_id')->index();
            $table->string('assumption_key', 80);
            $table->decimal('override_value', 15, 4);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('scenario_id')->references('id')->on('hcm_workforce_scenarios')->cascadeOnDelete();
        });

        Schema::create('hcm_workforce_scenario_positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('scenario_id')->index();
            $table->string('action_type', 30); // create_position, eliminate_position, freeze_position, move_position
            $table->uuid('position_plan_id')->nullable()->index();
            $table->string('title', 150)->nullable();
            $table->uuid('target_department_id')->nullable()->index();
            $table->decimal('cost_delta', 15, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('scenario_id')->references('id')->on('hcm_workforce_scenarios')->cascadeOnDelete();
        });

        Schema::create('hcm_workforce_scenario_headcount', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('scenario_id')->index();
            $table->string('period_name', 50);
            $table->unsignedInteger('projected_headcount')->default(0);
            $table->decimal('projected_fte', 8, 2)->default(0.00);
            $table->unsignedInteger('projected_hires')->default(0);
            $table->unsignedInteger('projected_exits')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('scenario_id')->references('id')->on('hcm_workforce_scenarios')->cascadeOnDelete();
        });

        Schema::create('hcm_workforce_scenario_costs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('scenario_id')->index();
            $table->string('cost_category', 50);
            $table->decimal('projected_cost', 18, 2)->default(0.00);
            $table->decimal('variance_vs_base', 18, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('scenario_id')->references('id')->on('hcm_workforce_scenarios')->cascadeOnDelete();
        });

        // 7. Approvals & Frozen Snapshots
        Schema::create('hcm_workforce_plan_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->string('approval_step', 50); // hr_manager, finance, business_unit_head, executive
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('pending'); // pending, approved, rejected
            $table->text('comments')->nullable();
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
        });

        Schema::create('hcm_workforce_plan_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->unsignedInteger('version_number');
            $table->json('full_plan_state');
            $table->timestamp('frozen_at');
            $table->foreignId('frozen_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('hcm_workforce_plans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_workforce_plan_snapshots');
        Schema::dropIfExists('hcm_workforce_plan_approvals');
        Schema::dropIfExists('hcm_workforce_scenario_costs');
        Schema::dropIfExists('hcm_workforce_scenario_headcount');
        Schema::dropIfExists('hcm_workforce_scenario_positions');
        Schema::dropIfExists('hcm_workforce_scenario_assumptions');
        Schema::dropIfExists('hcm_workforce_scenarios');
        Schema::dropIfExists('hcm_workforce_capacity_plans');
        Schema::dropIfExists('hcm_workforce_skill_demand_plans');
        Schema::dropIfExists('hcm_workforce_cost_plans');
        Schema::dropIfExists('hcm_workforce_hiring_plans');
        Schema::dropIfExists('hcm_workforce_position_budgets');
        Schema::dropIfExists('hcm_workforce_position_plans');
        Schema::dropIfExists('hcm_workforce_headcount_plans');
        Schema::dropIfExists('hcm_workforce_supply_plans');
        Schema::dropIfExists('hcm_workforce_demand_plans');
        Schema::dropIfExists('hcm_workforce_plan_assumptions');
        Schema::dropIfExists('hcm_workforce_plan_periods');
        Schema::dropIfExists('hcm_workforce_plan_versions');
        Schema::dropIfExists('hcm_workforce_plans');
    }
};
