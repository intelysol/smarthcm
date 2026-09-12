<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Optimization Models (Tenant-level configuration)
        if (! Schema::hasTable('hcm_workforce_optimization_models')) {
            Schema::create('hcm_workforce_optimization_models', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('name', 150);
                $table->string('model_type', 50)->default('balanced'); // cost_min, capacity_max, productivity_max, balanced
                $table->string('default_solver', 50)->default('weighted_scoring');
                $table->string('status', 30)->default('active');
                $table->unsignedInteger('current_version')->default(1);
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 2. Optimization Model Versions
        if (! Schema::hasTable('hcm_workforce_optimization_model_versions')) {
            Schema::create('hcm_workforce_optimization_model_versions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('model_id')->index();
                $table->unsignedInteger('version')->default(1);
                $table->json('configuration')->nullable();
                $table->date('effective_from')->index();
                $table->date('effective_to')->nullable()->index();
                $table->boolean('is_current')->default(true);
                $table->text('change_notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('model_id')->references('id')->on('hcm_workforce_optimization_models')->cascadeOnDelete();
                $table->unique(['model_id', 'version']);
            });
        }

        // 3. Optimization Objectives
        if (! Schema::hasTable('hcm_workforce_optimization_objectives')) {
            Schema::create('hcm_workforce_optimization_objectives', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('model_id')->index();
                $table->string('code', 64)->index();
                $table->string('name', 150);
                $table->string('direction', 20)->default('MINIMIZE'); // MINIMIZE, MAXIMIZE
                $table->string('target_metric', 64);
                $table->decimal('weight', 5, 2)->default(1.00);
                $table->unsignedInteger('priority')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('model_id')->references('id')->on('hcm_workforce_optimization_models')->cascadeOnDelete();
            });
        }

        // 4. Optimization Constraints
        if (! Schema::hasTable('hcm_workforce_optimization_constraints')) {
            Schema::create('hcm_workforce_optimization_constraints', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('model_id')->index();
                $table->string('constraint_type', 50); // working_hours, budget, certification, location, headcount
                $table->string('name', 150);
                $table->boolean('is_hard_constraint')->default(true);
                $table->json('parameters')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('model_id')->references('id')->on('hcm_workforce_optimization_models')->cascadeOnDelete();
            });
        }

        // 5. Optimization Runs
        if (! Schema::hasTable('hcm_workforce_optimization_runs')) {
            Schema::create('hcm_workforce_optimization_runs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('run_number', 64)->unique();
                $table->uuid('model_id')->index();
                $table->uuid('model_version_id')->nullable()->index();
                $table->string('scope_type', 50)->default('enterprise');
                $table->uuid('scope_id')->nullable()->index();
                $table->string('solver_type', 50)->default('weighted_scoring');
                $table->json('input_snapshot')->nullable();
                $table->unsignedInteger('opportunities_count')->default(0);
                $table->unsignedInteger('recommendations_count')->default(0);
                $table->unsignedInteger('execution_duration_ms')->default(0);
                $table->string('status', 30)->default('COMPLETED'); // QUEUED, RUNNING, COMPLETED, FAILED, CANCELLED
                $table->text('error_message')->nullable();
                $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('model_id')->references('id')->on('hcm_workforce_optimization_models')->cascadeOnDelete();
            });
        }

        // 6. Opportunities (Discovered workforce bottlenecks & potential)
        if (! Schema::hasTable('hcm_workforce_optimization_opportunities')) {
            Schema::create('hcm_workforce_optimization_opportunities', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('run_id')->nullable()->index();
                $table->string('opportunity_code', 64)->unique();
                $table->string('title', 180);
                $table->string('category', 50); // CAPACITY, COST, PRODUCTIVITY, SKILLS, OVERTIME, ABSENCE, VACANCY, SCHEDULING, WORKFORCE_RISK
                $table->string('severity', 30)->default('medium'); // low, medium, high, critical
                $table->uuid('department_id')->nullable()->index();
                $table->uuid('location_id')->nullable()->index();
                $table->string('role_or_skill', 120)->nullable();
                $table->decimal('estimated_impact_amount', 14, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->decimal('capacity_gap_hours', 10, 2)->default(0);
                $table->decimal('confidence_score', 4, 2)->default(0.85);
                $table->json('details')->nullable();
                $table->string('status', 30)->default('open'); // open, analyzing, addressed, dismissed
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('run_id')->references('id')->on('hcm_workforce_optimization_runs')->nullOnDelete();
            });
        }

        // 7. Recommendations
        if (! Schema::hasTable('hcm_workforce_optimization_recommendations')) {
            Schema::create('hcm_workforce_optimization_recommendations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('opportunity_id')->index();
                $table->string('recommendation_code', 64)->unique();
                $table->string('action_type', 50); // HIRE, REDEPLOY, RESKILL, TRAIN, CONTRACT, REDESIGN_SHIFT, REDUCE_OVERTIME, REALLOCATE_WORK, AUTOMATE, RELOCATE, REPLACE, DELAY_HIRING, ACCELERATE_HIRING
                $table->string('title', 180);
                $table->text('executive_summary');
                $table->uuid('source_department_id')->nullable()->index();
                $table->uuid('target_department_id')->nullable()->index();
                $table->uuid('candidate_employee_id')->nullable()->index();
                $table->decimal('decision_score', 5, 2)->default(85.00); // 0 - 100
                $table->decimal('cost_impact', 14, 4)->default(0);
                $table->decimal('capacity_impact_hours', 10, 2)->default(0);
                $table->decimal('productivity_impact_pct', 6, 2)->default(0);
                $table->unsignedInteger('time_to_realize_days')->default(30);
                $table->string('risk_level', 30)->default('low'); // low, medium, high
                $table->string('confidence', 30)->default('HIGH'); // HIGH, MEDIUM, LOW
                $table->string('lifecycle_status', 30)->default('GENERATED'); // DETECTED, ANALYZING, GENERATED, REVIEW, APPROVED, REJECTED, EXECUTING, COMPLETED, MEASURED, EXPIRED
                $table->string('authoritative_module', 64)->default('Core HR');
                $table->string('required_approval_role', 64)->default('HR Director');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('review_notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('opportunity_id')->references('id')->on('hcm_workforce_optimization_opportunities')->cascadeOnDelete();
                $table->foreign('candidate_employee_id')->references('id')->on('employees')->nullOnDelete();
            });
        }

        // 8. Recommendation Factors (Explainability & Decision scoring decomposition)
        if (! Schema::hasTable('hcm_workforce_optimization_recommendation_factors')) {
            Schema::create('hcm_workforce_optimization_recommendation_factors', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('recommendation_id')->index();
                $table->string('factor_type', 50); // objective_alignment, constraint_compliance, risk_indicator, trade_off, assumption
                $table->string('name', 150);
                $table->decimal('score', 5, 2)->default(0);
                $table->decimal('weight', 4, 2)->default(1.0);
                $table->text('details')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('recommendation_id')->references('id')->on('hcm_workforce_optimization_recommendations')->cascadeOnDelete();
            });
        }

        // 9. Optimization Scenarios (Alternative comparison sets)
        if (! Schema::hasTable('hcm_workforce_optimization_scenarios')) {
            Schema::create('hcm_workforce_optimization_scenarios', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('opportunity_id')->nullable()->index();
                $table->string('scenario_name', 150);
                $table->string('scenario_type', 50); // hire_vs_redeploy, reskill_vs_hire, contractor_vs_permanent, overtime_reduction
                $table->json('baseline_snapshot')->nullable();
                $table->string('status', 30)->default('evaluated');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('opportunity_id')->references('id')->on('hcm_workforce_optimization_opportunities')->nullOnDelete();
            });
        }

        // 10. Optimization Scenario Results
        if (! Schema::hasTable('hcm_workforce_optimization_scenario_results')) {
            Schema::create('hcm_workforce_optimization_scenario_results', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('scenario_id')->index();
                $table->string('option_label', 50); // Option A (Hire), Option B (Redeploy)
                $table->string('action_type', 50);
                $table->decimal('total_cost', 14, 4)->default(0);
                $table->decimal('cost_delta', 14, 4)->default(0);
                $table->decimal('capacity_gained_hours', 10, 2)->default(0);
                $table->decimal('productivity_impact_pct', 6, 2)->default(0);
                $table->unsignedInteger('time_to_capacity_days')->default(0);
                $table->decimal('payback_period_months', 6, 2)->nullable();
                $table->decimal('projected_roi_pct', 8, 2)->nullable();
                $table->boolean('is_recommended')->default(false);
                $table->unsignedInteger('pareto_rank')->default(1);
                $table->json('details')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('scenario_id')->references('id')->on('hcm_workforce_optimization_scenarios')->cascadeOnDelete();
            });
        }

        // 11. Approved Workforce Actions (Dispatched to Core HR, Recruitment, Learning, Scheduling)
        if (! Schema::hasTable('hcm_workforce_optimization_actions')) {
            Schema::create('hcm_workforce_optimization_actions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('recommendation_id')->index();
                $table->string('action_number', 64)->unique();
                $table->string('title', 180);
                $table->string('target_module', 64); // core_hr, recruitment, learning, scheduling
                $table->json('payload')->nullable();
                $table->string('status', 30)->default('pending'); // pending, dispatched, in_progress, completed, failed
                $table->timestamp('dispatched_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('recommendation_id')->references('id')->on('hcm_workforce_optimization_recommendations')->cascadeOnDelete();
            });
        }

        // 12. Realized Outcomes (Before vs After measurement)
        if (! Schema::hasTable('hcm_workforce_optimization_outcomes')) {
            Schema::create('hcm_workforce_optimization_outcomes', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('action_id')->nullable()->index();
                $table->uuid('recommendation_id')->nullable()->index();
                $table->date('measurement_date')->index();
                $table->string('metric_name', 64);
                $table->decimal('pre_action_value', 14, 4)->default(0);
                $table->decimal('post_action_value', 14, 4)->default(0);
                $table->decimal('variance_value', 14, 4)->default(0);
                $table->decimal('variance_pct', 6, 2)->default(0);
                $table->decimal('realized_financial_impact', 14, 4)->default(0);
                $table->string('causality_label', 30)->default('CORRELATION'); // CORRELATION, CAUSAL
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('action_id')->references('id')->on('hcm_workforce_optimization_actions')->nullOnDelete();
                $table->foreign('recommendation_id')->references('id')->on('hcm_workforce_optimization_recommendations')->nullOnDelete();
            });
        }

        // 13. Recommendation Feedback
        if (! Schema::hasTable('hcm_workforce_optimization_feedback')) {
            Schema::create('hcm_workforce_optimization_feedback', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('recommendation_id')->index();
                $table->string('feedback_type', 50); // APPROVED, REJECTED, NOT_FEASIBLE, ALREADY_IMPLEMENTED, INCORRECT_DATA, LOW_VALUE, HIGH_RISK
                $table->text('reason')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('recommendation_id')->references('id')->on('hcm_workforce_optimization_recommendations')->cascadeOnDelete();
            });
        }

        // 14. Audit Log
        if (! Schema::hasTable('hcm_workforce_optimization_audits')) {
            Schema::create('hcm_workforce_optimization_audits', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('action', 64);
                $table->string('target_type', 100);
                $table->uuid('target_id')->nullable()->index();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('changes')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_workforce_optimization_audits');
        Schema::dropIfExists('hcm_workforce_optimization_feedback');
        Schema::dropIfExists('hcm_workforce_optimization_outcomes');
        Schema::dropIfExists('hcm_workforce_optimization_actions');
        Schema::dropIfExists('hcm_workforce_optimization_scenario_results');
        Schema::dropIfExists('hcm_workforce_optimization_scenarios');
        Schema::dropIfExists('hcm_workforce_optimization_recommendation_factors');
        Schema::dropIfExists('hcm_workforce_optimization_recommendations');
        Schema::dropIfExists('hcm_workforce_optimization_opportunities');
        Schema::dropIfExists('hcm_workforce_optimization_runs');
        Schema::dropIfExists('hcm_workforce_optimization_constraints');
        Schema::dropIfExists('hcm_workforce_optimization_objectives');
        Schema::dropIfExists('hcm_workforce_optimization_model_versions');
        Schema::dropIfExists('hcm_workforce_optimization_models');
    }
};
