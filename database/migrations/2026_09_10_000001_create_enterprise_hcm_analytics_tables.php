<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. HCM Metrics Registry & Versions
        Schema::create('hcm_analytics_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('code', 80)->index();
            $table->string('name', 150);
            $table->string('category', 50)->index(); // workforce, turnover, recruitment, attendance, payroll, etc.
            $table->text('description')->nullable();
            $table->string('formula', 255)->nullable();
            $table->string('unit', 30)->default('count'); // count, percentage, currency, days, hours, score
            $table->string('aggregation', 30)->default('sum'); // sum, avg, count, min, max, median, percentile
            $table->string('sensitivity', 30)->default('public'); // public, sensitive, highly_sensitive, restricted
            $table->string('refresh_frequency', 30)->default('daily'); // real_time, hourly, daily, monthly
            $table->unsignedInteger('current_version')->default(1);
            $table->string('owner', 100)->default('HCM Analytics');
            $table->json('data_sources')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hcm_analytics_metric_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->uuid('hcm_analytics_metric_id')->index();
            $table->unsignedInteger('version_number')->default(1);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->json('calculation_definition')->nullable();
            $table->text('change_summary')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('hcm_analytics_metric_id')->references('id')->on('hcm_analytics_metrics')->cascadeOnDelete();
        });

        // 2. HCM Analytical Dimensions & Data Products
        Schema::create('hcm_analytics_dimensions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('dimension_type', 50)->index(); // department, branch, location, job, grade, cost_center, manager, status
            $table->string('dimension_key', 80)->index();
            $table->string('name', 150);
            $table->json('attributes')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hcm_analytics_data_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('code', 80)->index();
            $table->string('name', 150);
            $table->string('domain_module', 50); // core_hr, payroll, attendance, benefits, er, engagement
            $table->text('description')->nullable();
            $table->json('schema_fields')->nullable();
            $table->json('supported_dimensions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Workforce Snapshots & Snapshot Runs
        Schema::create('hcm_analytics_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('snapshot_type', 30)->default('daily'); // daily, monthly, quarterly, annual
            $table->date('snapshot_date')->index();
            
            // Aggregated Totals
            $table->unsignedInteger('headcount_total')->default(0);
            $table->unsignedInteger('headcount_active')->default(0);
            $table->unsignedInteger('headcount_inactive')->default(0);
            $table->decimal('fte_total', 10, 2)->default(0.00);
            $table->unsignedInteger('full_time_count')->default(0);
            $table->unsignedInteger('part_time_count')->default(0);
            $table->unsignedInteger('contractor_count')->default(0);
            
            // Movement Metrics
            $table->unsignedInteger('new_hires_count')->default(0);
            $table->unsignedInteger('transfers_in_count')->default(0);
            $table->unsignedInteger('transfers_out_count')->default(0);
            $table->unsignedInteger('promotions_count')->default(0);
            $table->unsignedInteger('terminations_count')->default(0);
            $table->unsignedInteger('voluntary_exits_count')->default(0);
            $table->unsignedInteger('involuntary_exits_count')->default(0);

            // Dimensional Slices & Precomputed Breakdowns
            $table->json('by_department')->nullable();
            $table->json('by_branch')->nullable();
            $table->json('by_job_grade')->nullable();
            $table->json('by_employment_type')->nullable();
            $table->json('by_tenure_band')->nullable();
            $table->json('by_gender')->nullable();
            $table->json('payload')->nullable();

            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(['tenant_id', 'snapshot_type', 'snapshot_date']);
        });

        Schema::create('hcm_analytics_snapshot_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('snapshot_type', 30);
            $table->date('snapshot_date');
            $table->string('status', 30)->default('completed'); // queued, running, completed, failed
            $table->unsignedInteger('records_processed')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('hcm_analytics_kpi_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hcm_analytics_metric_id')->index();
            $table->string('target_period', 30); // 2026-Q1, 2026-M09, 2026-FY
            $table->decimal('target_value', 15, 4);
            $table->decimal('warning_threshold', 15, 4)->nullable();
            $table->decimal('critical_threshold', 15, 4)->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('branch_id')->nullable();
            $table->timestamps();

            $table->foreign('hcm_analytics_metric_id')->references('id')->on('hcm_analytics_metrics')->cascadeOnDelete();
        });

        // 4. Dashboards & Saved Views
        Schema::create('hcm_analytics_dashboard_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 80)->index();
            $table->string('name', 150);
            $table->string('dashboard_type', 50)->default('standard'); // chro, hr_ops, manager, payroll, recruitment, talent, custom
            $table->text('description')->nullable();
            $table->json('layout_config')->nullable();
            $table->json('allowed_roles')->nullable();
            $table->boolean('is_system')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hcm_analytics_dashboard_widgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('dashboard_id')->index();
            $table->uuid('metric_id')->nullable()->index();
            $table->string('widget_type', 50); // kpi_card, line_chart, bar_chart, area_chart, pie_chart, table, funnel, heatmap
            $table->string('title', 150);
            $table->unsignedInteger('position_x')->default(0);
            $table->unsignedInteger('position_y')->default(0);
            $table->unsignedInteger('width')->default(4);
            $table->unsignedInteger('height')->default(3);
            $table->json('query_config')->nullable();
            $table->timestamps();

            $table->foreign('dashboard_id')->references('id')->on('hcm_analytics_dashboard_definitions')->cascadeOnDelete();
            $table->foreign('metric_id')->references('id')->on('hcm_analytics_metrics')->nullOnDelete();
        });

        Schema::create('hcm_analytics_saved_views', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 150);
            $table->string('view_scope', 30)->default('personal'); // personal, team, organization, system
            $table->string('page_context', 80); // workforce, payroll, attendance, reports
            $table->json('filters')->nullable();
            $table->json('dimensions')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Analytics Alerts & Subscriptions
        Schema::create('hcm_analytics_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hcm_analytics_metric_id')->index();
            $table->string('title', 150);
            $table->string('comparison_operator', 20)->default('>'); // >, <, >=, <=, ==, !=
            $table->decimal('threshold_value', 15, 4);
            $table->string('severity', 20)->default('warning'); // info, warning, critical
            $table->json('filters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('hcm_analytics_metric_id')->references('id')->on('hcm_analytics_metrics')->cascadeOnDelete();
        });

        Schema::create('hcm_analytics_alert_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hcm_analytics_alert_id')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('delivery_channel', 30)->default('in_app'); // in_app, email, webhook
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();

            $table->foreign('hcm_analytics_alert_id')->references('id')->on('hcm_analytics_alerts')->cascadeOnDelete();
        });

        // 6. Data Quality Checks & Results
        Schema::create('hcm_analytics_data_quality_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('code', 80)->index();
            $table->string('name', 150);
            $table->string('domain_module', 50);
            $table->text('description')->nullable();
            $table->string('severity', 20)->default('warning'); // info, warning, error
            $table->string('check_type', 50)->default('missing_foreign_key'); // missing_department, missing_manager, missing_salary, duplicate_employee
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hcm_analytics_data_quality_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('check_id')->index();
            $table->unsignedInteger('failed_records_count')->default(0);
            $table->unsignedInteger('total_records_evaluated')->default(0);
            $table->decimal('quality_score', 5, 2)->default(100.00); // 0.00 to 100.00%
            $table->json('sample_failing_records')->nullable();
            $table->timestamp('evaluated_at');
            $table->timestamps();

            $table->foreign('check_id')->references('id')->on('hcm_analytics_data_quality_checks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_analytics_data_quality_results');
        Schema::dropIfExists('hcm_analytics_data_quality_checks');
        Schema::dropIfExists('hcm_analytics_alert_subscriptions');
        Schema::dropIfExists('hcm_analytics_alerts');
        Schema::dropIfExists('hcm_analytics_saved_views');
        Schema::dropIfExists('hcm_analytics_dashboard_widgets');
        Schema::dropIfExists('hcm_analytics_dashboard_definitions');
        Schema::dropIfExists('hcm_analytics_kpi_targets');
        Schema::dropIfExists('hcm_analytics_snapshot_runs');
        Schema::dropIfExists('hcm_analytics_snapshots');
        Schema::dropIfExists('hcm_analytics_data_products');
        Schema::dropIfExists('hcm_analytics_dimensions');
        Schema::dropIfExists('hcm_analytics_metric_versions');
        Schema::dropIfExists('hcm_analytics_metrics');
    }
};
