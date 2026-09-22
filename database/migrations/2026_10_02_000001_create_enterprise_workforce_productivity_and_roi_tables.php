<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Metric Definitions
        if (! Schema::hasTable('hcm_productivity_metric_definitions')) {
            Schema::create('hcm_productivity_metric_definitions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 64)->index();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('metric_type', 50)->default('volume'); // volume, time, quality, economic
                $table->string('unit', 50)->default('units_per_hour');
                $table->unsignedInteger('current_version')->default(1);
                $table->string('status', 30)->default('active'); // draft, active, archived, deprecated
                $table->json('dimensions')->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 2. Metric Versions (Immutable versioned formulas)
        if (! Schema::hasTable('hcm_productivity_metric_versions')) {
            Schema::create('hcm_productivity_metric_versions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('metric_definition_id')->index();
                $table->unsignedInteger('version')->default(1);
                $table->string('formula_name', 120);
                $table->string('numerator_code', 64);
                $table->string('denominator_code', 64);
                $table->text('calculation_expression')->nullable();
                $table->date('effective_from')->index();
                $table->date('effective_to')->nullable()->index();
                $table->boolean('is_current')->default(true);
                $table->text('change_notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('metric_definition_id')->references('id')->on('hcm_productivity_metric_definitions')->cascadeOnDelete();
                $table->unique(['metric_definition_id', 'version'], 'hcm_prod_met_ver_def_ver_unique');
            });
        }

        // 3. Operational Output Records
        if (! Schema::hasTable('hcm_productivity_output_records')) {
            Schema::create('hcm_productivity_output_records', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->date('output_date')->index();
                $table->uuid('metric_definition_id')->nullable()->index();
                $table->uuid('employee_id')->nullable()->index();
                $table->uuid('department_id')->nullable()->index();
                $table->uuid('cost_center_id')->nullable()->index();
                $table->uuid('location_id')->nullable()->index();
                $table->uuid('project_id')->nullable()->index();
                $table->uuid('shift_id')->nullable()->index();
                $table->string('output_type', 64)->default('units');
                $table->decimal('units_completed', 12, 4)->default(0);
                $table->decimal('units_defective', 12, 4)->default(0);
                $table->decimal('rework_count', 12, 4)->default(0);
                $table->decimal('revenue_generated', 14, 4)->default(0);
                $table->decimal('quality_score', 5, 2)->nullable();
                $table->string('source_domain', 50)->default('operations');
                $table->string('source_record_id', 64)->nullable()->index();
                $table->string('nature', 30)->default('ACTUAL'); // ACTUAL, ESTIMATED
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            });
        }

        // 4. Normalized Productive Time Records
        if (! Schema::hasTable('hcm_productivity_time_records')) {
            Schema::create('hcm_productivity_time_records', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->date('record_date')->index();
                $table->uuid('employee_id')->nullable()->index();
                $table->uuid('department_id')->nullable()->index();
                $table->uuid('shift_id')->nullable()->index();
                $table->string('category', 50)->default('PRODUCTIVE'); // PRODUCTIVE, NON_PRODUCTIVE, TRAINING, MEETING, ADMINISTRATION, WAITING, IDLE, BREAK, ABSENCE, OVERTIME_PRODUCTIVE, OVERTIME_NON_PRODUCTIVE
                $table->string('nature', 30)->default('ACTUAL'); // ACTUAL, ESTIMATED
                $table->unsignedInteger('minutes')->default(0);
                $table->decimal('hours', 8, 2)->default(0);
                $table->string('source_domain', 50)->default('attendance');
                $table->string('source_record_id', 64)->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            });
        }

        // 5. Measurements (Aggregate & Period-level calculations)
        if (! Schema::hasTable('hcm_productivity_measurements')) {
            Schema::create('hcm_productivity_measurements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('metric_definition_id')->index();
                $table->uuid('metric_version_id')->nullable()->index();
                $table->uuid('department_id')->nullable()->index();
                $table->uuid('location_id')->nullable()->index();
                $table->uuid('shift_id')->nullable()->index();
                $table->uuid('employee_id')->nullable()->index();
                $table->string('period_type', 30)->default('monthly'); // daily, weekly, monthly, quarterly, annual
                $table->string('period_name', 50);
                $table->date('period_start')->index();
                $table->date('period_end')->index();
                $table->decimal('output_volume', 14, 4)->default(0);
                $table->decimal('labor_hours', 10, 2)->default(0);
                $table->decimal('productive_hours', 10, 2)->default(0);
                $table->decimal('scheduled_hours', 10, 2)->default(0);
                $table->decimal('available_hours', 10, 2)->default(0);
                $table->decimal('overtime_hours', 10, 2)->default(0);
                $table->decimal('idle_hours', 10, 2)->default(0);
                $table->decimal('productivity_rate', 12, 4)->nullable(); // output / productive_hours
                $table->decimal('utilization_rate', 6, 2)->nullable(); // productive_hours / available_hours * 100
                $table->decimal('quality_rate', 6, 2)->nullable();
                $table->decimal('labor_cost', 14, 4)->default(0);
                $table->decimal('cost_per_unit', 12, 4)->nullable(); // labor_cost / output_volume
                $table->decimal('cost_per_productive_hour', 12, 4)->nullable();
                $table->decimal('output_per_dollar', 12, 4)->nullable();
                $table->string('data_quality_status', 30)->default('VALID');
                $table->json('source_provenance')->nullable();
                $table->string('idempotency_key', 128)->nullable()->index();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('metric_definition_id')->references('id')->on('hcm_productivity_metric_definitions')->cascadeOnDelete();
                $table->foreign('metric_version_id')->references('id')->on('hcm_productivity_metric_versions')->nullOnDelete();
            });
        }

        // 6. Measurement Drill-Down Lines
        if (! Schema::hasTable('hcm_productivity_measurement_lines')) {
            Schema::create('hcm_productivity_measurement_lines', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('measurement_id')->index();
                $table->string('dimension_type', 50); // department, position, job_family, shift, project, client
                $table->uuid('dimension_id')->nullable()->index();
                $table->string('dimension_name', 120)->nullable();
                $table->decimal('output_contribution', 12, 4)->default(0);
                $table->decimal('hours_contribution', 10, 2)->default(0);
                $table->decimal('cost_contribution', 14, 4)->default(0);
                $table->decimal('productivity_rate', 12, 4)->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('measurement_id')->references('id')->on('hcm_productivity_measurements')->cascadeOnDelete();
            });
        }

        // 7. Multi-Dimensional Scorecards
        if (! Schema::hasTable('hcm_productivity_scorecards')) {
            Schema::create('hcm_productivity_scorecards', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('scorecard_level', 50)->default('department'); // enterprise, business_unit, department, location, team, shift
                $table->uuid('entity_id')->nullable()->index();
                $table->string('entity_name', 150);
                $table->date('period_start')->index();
                $table->date('period_end')->index();
                $table->decimal('productivity_score', 6, 2)->default(100.00);
                $table->decimal('utilization_rate', 6, 2)->default(0);
                $table->decimal('quality_rate', 6, 2)->default(0);
                $table->decimal('overtime_ratio', 6, 2)->default(0);
                $table->decimal('cost_per_unit', 12, 4)->nullable();
                $table->decimal('capacity_gap_pct', 6, 2)->default(0);
                $table->string('status_label', 30)->default('optimal'); // optimal, monitor, bottleneck, critical
                $table->json('component_details')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 8. Snapshots (Immutable periodic rollups)
        if (! Schema::hasTable('hcm_productivity_snapshots')) {
            Schema::create('hcm_productivity_snapshots', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('snapshot_number', 64)->unique();
                $table->string('period_type', 30)->default('monthly');
                $table->string('period_name', 50);
                $table->date('period_start')->index();
                $table->date('period_end')->index();
                $table->unsignedInteger('version')->default(1);
                $table->string('status', 30)->default('draft'); // draft, locked, published, superseded
                $table->decimal('total_output', 14, 4)->default(0);
                $table->decimal('total_labor_hours', 12, 2)->default(0);
                $table->decimal('total_productive_hours', 12, 2)->default(0);
                $table->decimal('total_labor_cost', 14, 4)->default(0);
                $table->decimal('average_productivity_rate', 12, 4)->nullable();
                $table->decimal('average_utilization_rate', 6, 2)->nullable();
                $table->decimal('average_cost_per_unit', 12, 4)->nullable();
                $table->decimal('total_fte', 8, 2)->default(0);
                $table->unsignedInteger('headcount')->default(0);
                $table->string('idempotency_key', 128)->nullable()->index();
                $table->timestamp('locked_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 9. Forecasts
        if (! Schema::hasTable('hcm_productivity_forecasts')) {
            Schema::create('hcm_productivity_forecasts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('forecast_number', 64)->unique();
                $table->uuid('department_id')->nullable()->index();
                $table->date('forecast_start')->index();
                $table->date('forecast_end')->index();
                $table->decimal('projected_output', 14, 4)->default(0);
                $table->decimal('projected_labor_hours', 12, 2)->default(0);
                $table->decimal('projected_productive_hours', 12, 2)->default(0);
                $table->decimal('projected_utilization_rate', 6, 2)->nullable();
                $table->decimal('projected_labor_cost', 14, 4)->default(0);
                $table->decimal('projected_cost_per_unit', 12, 4)->nullable();
                $table->json('assumptions')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 10. Benchmarks & Normalized Index
        if (! Schema::hasTable('hcm_productivity_benchmarks')) {
            Schema::create('hcm_productivity_benchmarks', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('benchmark_name', 120);
                $table->string('benchmark_type', 50)->default('department'); // department, location, shift, period
                $table->date('baseline_period_start');
                $table->date('baseline_period_end');
                $table->decimal('baseline_productivity_rate', 12, 4)->default(0);
                $table->date('comparison_period_start');
                $table->date('comparison_period_end');
                $table->decimal('comparison_productivity_rate', 12, 4)->default(0);
                $table->decimal('normalized_index', 8, 2)->default(100.00); // 100 = baseline
                $table->decimal('variance_pct', 6, 2)->default(0);
                $table->json('benchmark_data')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 11. ROI Models
        if (! Schema::hasTable('hcm_productivity_roi_models')) {
            Schema::create('hcm_productivity_roi_models', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('model_name', 120);
                $table->string('investment_type', 50)->default('training'); // hiring, training, automation, technology, relocation, reskilling, scheduling_redesign, process_improvement
                $table->string('currency', 3)->default('USD');
                $table->string('evaluation_methodology', 50)->default('NET_BENEFIT');
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 12. ROI Calculations
        if (! Schema::hasTable('hcm_productivity_roi_calculations')) {
            Schema::create('hcm_productivity_roi_calculations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('roi_model_id')->index();
                $table->uuid('department_id')->nullable()->index();
                $table->string('investment_name', 150);
                $table->decimal('investment_cost', 14, 4)->default(0);
                $table->decimal('operational_benefit', 14, 4)->default(0);
                $table->decimal('net_benefit', 14, 4)->default(0);
                $table->decimal('roi_percentage', 8, 2)->default(0);
                $table->decimal('payback_period_months', 6, 2)->nullable();
                $table->string('causality_label', 30)->default('CORRELATION'); // CORRELATION, CAUSAL
                $table->decimal('pre_investment_output_rate', 12, 4)->nullable();
                $table->decimal('post_investment_output_rate', 12, 4)->nullable();
                $table->json('calculation_details')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('roi_model_id')->references('id')->on('hcm_productivity_roi_models')->cascadeOnDelete();
            });
        }

        // 13. What-If Scenarios
        if (! Schema::hasTable('hcm_productivity_scenarios')) {
            Schema::create('hcm_productivity_scenarios', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('scenario_name', 120);
                $table->string('scenario_type', 50)->default('headcount_change'); // headcount_change, overtime_substitution, automation, training, schedule_change
                $table->uuid('baseline_snapshot_id')->nullable()->index();
                $table->integer('headcount_delta')->default(0);
                $table->decimal('fte_delta', 8, 2)->default(0);
                $table->decimal('capacity_hours_delta', 10, 2)->default(0);
                $table->decimal('expected_output_delta', 12, 4)->default(0);
                $table->decimal('cost_delta', 14, 4)->default(0);
                $table->decimal('projected_cost_per_unit', 12, 4)->nullable();
                $table->decimal('projected_roi_pct', 8, 2)->nullable();
                $table->json('assumptions')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('baseline_snapshot_id')->references('id')->on('hcm_productivity_snapshots')->nullOnDelete();
            });
        }

        // 14. Anomalies & Bottlenecks
        if (! Schema::hasTable('hcm_productivity_anomalies')) {
            Schema::create('hcm_productivity_anomalies', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('anomaly_type', 64); // bottleneck, zero_output, drop_spike, quality_divergence, excessive_waiting
                $table->string('severity', 30)->default('medium'); // low, medium, high, critical
                $table->uuid('department_id')->nullable()->index();
                $table->uuid('location_id')->nullable()->index();
                $table->uuid('shift_id')->nullable()->index();
                $table->date('detected_date')->index();
                $table->decimal('observed_value', 12, 4)->nullable();
                $table->decimal('expected_baseline', 12, 4)->nullable();
                $table->decimal('variance_pct', 6, 2)->default(0);
                $table->json('possible_drivers')->nullable();
                $table->string('status', 30)->default('open'); // open, investigating, resolved, dismissed
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 15. Audit Log
        if (! Schema::hasTable('hcm_productivity_audits')) {
            Schema::create('hcm_productivity_audits', function (Blueprint $table): void {
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
        Schema::dropIfExists('hcm_productivity_audits');
        Schema::dropIfExists('hcm_productivity_anomalies');
        Schema::dropIfExists('hcm_productivity_scenarios');
        Schema::dropIfExists('hcm_productivity_roi_calculations');
        Schema::dropIfExists('hcm_productivity_roi_models');
        Schema::dropIfExists('hcm_productivity_benchmarks');
        Schema::dropIfExists('hcm_productivity_forecasts');
        Schema::dropIfExists('hcm_productivity_snapshots');
        Schema::dropIfExists('hcm_productivity_scorecards');
        Schema::dropIfExists('hcm_productivity_measurement_lines');
        Schema::dropIfExists('hcm_productivity_measurements');
        Schema::dropIfExists('hcm_productivity_time_records');
        Schema::dropIfExists('hcm_productivity_output_records');
        Schema::dropIfExists('hcm_productivity_metric_versions');
        Schema::dropIfExists('hcm_productivity_metric_definitions');
    }
};
