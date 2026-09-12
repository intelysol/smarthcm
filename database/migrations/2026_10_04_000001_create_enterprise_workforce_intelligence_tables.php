<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Governed Semantic Metric Definitions
        Schema::create('hcm_command_center_kpis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('company_id')->nullable();
            $table->string('kpi_code')->unique();
            $table->string('name');
            $table->string('category'); // HEADCOUNT, CAPACITY, PRODUCTIVITY, COST, RETENTION, SKILLS, COMPLIANCE
            $table->text('description')->nullable();
            $table->string('unit')->default('count'); // count, currency, percentage, hours, ratio, index
            $table->string('target_direction')->default('HIGHER_IS_BETTER'); // HIGHER_IS_BETTER, LOWER_IS_BETTER, TARGET_RANGE
            $table->decimal('target_min', 15, 4)->nullable();
            $table->decimal('target_max', 15, 4)->nullable();
            $table->string('aggregation_method')->default('SUM'); // SUM, AVG, LAST, WEIGHTED_AVG
            $table->string('source_system')->default('CORE_HCM');
            $table->integer('freshness_ttl_seconds')->default(3600);
            $table->string('security_classification')->default('INTERNAL'); // PUBLIC, INTERNAL, CONFIDENTIAL, RESTRICTED
            $table->string('lifecycle_status')->default('ACTIVE'); // DRAFT, ACTIVE, DEPRECATED, ARCHIVED
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'lifecycle_status']);
        });

        // 2. Governed KPI Versions
        Schema::create('hcm_command_center_kpi_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('kpi_id');
            $table->integer('version_number')->default(1);
            $table->text('formula_expression');
            $table->json('formula_variables')->nullable();
            $table->json('dimension_bindings')->nullable();
            $table->string('effective_from_period')->nullable(); // e.g. 2026-Q1
            $table->string('effective_to_period')->nullable();
            $table->uuid('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('change_reason')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('kpi_id')->references('id')->on('hcm_command_center_kpis')->cascadeOnDelete();
            $table->unique(['kpi_id', 'version_number']);
        });

        // 3. Materialized KPI Values
        Schema::create('hcm_command_center_kpi_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('kpi_id');
            $table->uuid('department_id')->nullable();
            $table->string('period_type')->default('MONTH'); // DAY, WEEK, MONTH, QUARTER, YEAR
            $table->string('period_key'); // e.g. 2026-09
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('calculated_value', 18, 4);
            $table->decimal('target_value', 18, 4)->nullable();
            $table->decimal('benchmark_value', 18, 4)->nullable();
            $table->decimal('variance_value', 18, 4)->nullable();
            $table->decimal('variance_percentage', 8, 4)->nullable();
            $table->string('status_band')->default('ON_TRACK'); // CRITICAL, WARNING, ON_TRACK, EXCEEDING
            $table->timestamp('calculated_at');
            $table->json('calculation_context')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('kpi_id')->references('id')->on('hcm_command_center_kpis')->cascadeOnDelete();
            $table->index(['tenant_id', 'period_key', 'department_id']);
        });

        // 4. Executive Snapshots
        Schema::create('hcm_command_center_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('company_id')->nullable();
            $table->string('snapshot_code')->unique();
            $table->string('snapshot_name');
            $table->string('period_type')->default('MONTH');
            $table->string('period_key');
            $table->date('as_of_date');
            $table->integer('total_headcount')->default(0);
            $table->decimal('total_fte', 12, 2)->default(0);
            $table->decimal('total_workforce_cost', 18, 2)->default(0);
            $table->decimal('average_cost_per_fte', 15, 2)->default(0);
            $table->decimal('composite_health_score', 5, 2)->default(0);
            $table->decimal('overall_productivity_score', 5, 2)->default(0);
            $table->decimal('turnover_rate', 6, 2)->default(0);
            $table->decimal('absence_rate', 6, 2)->default(0);
            $table->integer('critical_risk_count')->default(0);
            $table->integer('open_decision_count')->default(0);
            $table->json('summary_metrics')->nullable();
            $table->json('dimension_breakdowns')->nullable();
            $table->uuid('generated_by_user_id')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'period_key']);
        });

        // 5. Composite Workforce Health Index
        Schema::create('hcm_command_center_health_indices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('department_id')->nullable();
            $table->string('period_key');
            $table->decimal('composite_score', 5, 2); // 0 - 100
            $table->string('health_band'); // CRITICAL, AT_RISK, STABLE, OPTIMAL
            $table->decimal('capacity_dimension_score', 5, 2)->default(0);
            $table->decimal('productivity_dimension_score', 5, 2)->default(0);
            $table->decimal('cost_dimension_score', 5, 2)->default(0);
            $table->decimal('retention_dimension_score', 5, 2)->default(0);
            $table->decimal('skills_dimension_score', 5, 2)->default(0);
            $table->decimal('compliance_dimension_score', 5, 2)->default(0);
            $table->json('formula_weights');
            $table->decimal('confidence_score', 5, 2)->default(100.00);
            $table->text('summary_diagnosis')->nullable();
            $table->json('contributing_factors')->nullable();
            $table->timestamp('evaluated_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'period_key', 'department_id']);
        });

        // 6. Cross-Domain Consolidated Risks
        Schema::create('hcm_command_center_risks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('department_id')->nullable();
            $table->string('risk_code')->unique();
            $table->string('title');
            $table->string('category'); // PEOPLE, CAPACITY, SKILLS, COST, PRODUCTIVITY, VACANCY, COMPLIANCE
            $table->string('severity')->default('MEDIUM'); // LOW, MEDIUM, HIGH, CRITICAL
            $table->decimal('impact_score', 5, 2)->default(0);
            $table->decimal('likelihood_score', 5, 2)->default(0);
            $table->decimal('exposure_value', 18, 2)->nullable();
            $table->text('description');
            $table->string('status')->default('IDENTIFIED'); // IDENTIFIED, MITIGATING, ACCEPTED, RESOLVED
            $table->string('attribution_level')->default('OBSERVED'); // OBSERVED, CORRELATED, INFERRED
            $table->json('causal_factors')->nullable();
            $table->text('recommended_mitigation')->nullable();
            $table->uuid('owner_user_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'category', 'severity']);
        });

        // 7. Prioritized Alerts
        Schema::create('hcm_command_center_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('department_id')->nullable();
            $table->string('alert_code')->unique();
            $table->string('severity')->default('INFO'); // INFO, WARNING, CRITICAL
            $table->string('category');
            $table->string('title');
            $table->text('message');
            $table->string('source_module'); // COST, CAPACITY, PRODUCTIVITY, OPTIMIZATION, CORE_HR
            $table->string('action_url')->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE, ACKNOWLEDGED, RESOLVED, SNOOZED
            $table->uuid('acknowledged_by_user_id')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'severity', 'status']);
        });

        // 8. Consolidated Decision Queue
        Schema::create('hcm_command_center_decision_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('department_id')->nullable();
            $table->string('decision_code')->unique();
            $table->string('source_domain'); // OPTIMIZATION, PLANNING, COST, HEADCOUNT, TALENT
            $table->string('source_reference_id')->nullable(); // UUID from upstream module
            $table->string('title');
            $table->text('summary');
            $table->string('urgency')->default('NORMAL'); // LOW, NORMAL, HIGH, IMMEDIATE
            $table->decimal('estimated_cost_impact', 18, 2)->nullable();
            $table->decimal('estimated_capacity_impact', 12, 2)->nullable();
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED, EXPIRED, CANCELLED
            $table->uuid('assigned_approver_id')->nullable();
            $table->uuid('actioned_by_user_id')->nullable();
            $table->text('action_notes')->nullable();
            $table->timestamp('actioned_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status', 'urgency']);
        });

        // 9. Configurable Dashboards
        Schema::create('hcm_command_center_dashboards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('dashboard_code')->unique();
            $table->string('name');
            $table->string('persona'); // EXECUTIVE, FINANCE, HR, MANAGER, WORKFORCE_PLANNER
            $table->text('description')->nullable();
            $table->boolean('is_system_default')->default(false);
            $table->json('layout_config')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'persona']);
        });

        // 10. Role-Aware Widgets
        Schema::create('hcm_command_center_widgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('dashboard_id');
            $table->string('widget_code');
            $table->string('title');
            $table->string('widget_type'); // KPI_CARD, CHART_TREND, HEALTH_RADAR, RISK_HEATMAP, DECISION_LIST, ALERT_BANNER
            $table->integer('grid_x')->default(0);
            $table->integer('grid_y')->default(0);
            $table->integer('grid_width')->default(4);
            $table->integer('grid_height')->default(3);
            $table->json('configuration')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('dashboard_id')->references('id')->on('hcm_command_center_dashboards')->cascadeOnDelete();
            $table->index(['dashboard_id', 'is_visible']);
        });

        // 11. Saved Views
        Schema::create('hcm_command_center_saved_views', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('user_id');
            $table->string('name');
            $table->string('persona')->default('EXECUTIVE');
            $table->json('filter_criteria');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'user_id']);
        });

        // 12. User Preferences
        Schema::create('hcm_command_center_user_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('user_id');
            $table->string('default_persona')->default('EXECUTIVE');
            $table->string('default_period_type')->default('MONTH');
            $table->json('pinned_widget_ids')->nullable();
            $table->json('custom_dashboard_layout')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'user_id']);
        });

        // 13. Audit & Compliance Trail
        Schema::create('hcm_command_center_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('user_id')->nullable();
            $table->string('action_type'); // KPI_EDIT, AI_QUERY, EXPORT, DECISION_ACTION, VIEW_RESTRICTED
            $table->string('entity_type')->nullable();
            $table->string('entity_id')->nullable();
            $table->text('summary');
            $table->json('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'action_type', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hcm_command_center_audits');
        Schema::dropIfExists('hcm_command_center_user_preferences');
        Schema::dropIfExists('hcm_command_center_saved_views');
        Schema::dropIfExists('hcm_command_center_widgets');
        Schema::dropIfExists('hcm_command_center_dashboards');
        Schema::dropIfExists('hcm_command_center_decision_items');
        Schema::dropIfExists('hcm_command_center_alerts');
        Schema::dropIfExists('hcm_command_center_risks');
        Schema::dropIfExists('hcm_command_center_health_indices');
        Schema::dropIfExists('hcm_command_center_snapshots');
        Schema::dropIfExists('hcm_command_center_kpi_values');
        Schema::dropIfExists('hcm_command_center_kpi_versions');
        Schema::dropIfExists('hcm_command_center_kpis');
    }
};
