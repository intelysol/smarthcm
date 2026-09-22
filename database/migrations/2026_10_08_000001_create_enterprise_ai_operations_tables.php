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
        // 1. Governed AI Interaction Telemetry
        Schema::create('hcm_ai_interaction_telemetry', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('session_id')->nullable();
            $table->uuid('message_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('employee_id')->nullable();
            $table->string('use_case_code');
            $table->string('model_code');
            $table->string('provider');
            $table->string('prompt_version')->nullable();
            $table->string('policy_version')->nullable();
            $table->string('lifecycle_status')->default('DELIVERED'); // REQUESTED, AUTHORIZED, POLICY_CHECKED, PROCESSING, TOOLS_EXECUTED, GROUNDING_VALIDATED, DELIVERED, POLICY_BLOCKED, etc.
            $table->string('failure_reason')->nullable();
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->integer('cached_tokens')->default(0);
            $table->integer('latency_ms')->default(0);
            $table->decimal('cost_estimate', 10, 6)->default(0.000000);
            $table->json('tool_calls')->nullable();
            $table->json('retrieval_citations')->nullable();
            $table->decimal('grounding_score', 5, 2)->nullable();
            $table->boolean('is_sensitive_redacted')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'use_case_code']);
            $table->index(['tenant_id', 'lifecycle_status']);
        });

        // 2. Evaluation Datasets
        Schema::create('hcm_ai_eval_datasets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('dataset_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('version')->default('1.0');
            $table->string('dataset_type')->default('GOLDEN_QUESTIONS'); // GOLDEN_QUESTIONS, GOLDEN_WORKFLOWS, SAFETY_SCENARIOS, GROUNDING_SCENARIOS, REGRESSION_BENCHMARK
            $table->integer('total_cases')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'dataset_type']);
        });

        // 3. Evaluation Test Cases
        Schema::create('hcm_ai_eval_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('dataset_id');
            $table->string('case_code');
            $table->string('use_case_code');
            $table->text('prompt_input');
            $table->text('expected_behavior');
            $table->json('expected_tools')->nullable();
            $table->json('expected_sources')->nullable();
            $table->string('expected_policy')->nullable();
            $table->string('risk_level')->default('LOW');
            $table->json('rubric_criteria')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('dataset_id')->references('id')->on('hcm_ai_eval_datasets')->cascadeOnDelete();
            $table->index(['tenant_id', 'dataset_id']);
        });

        // 4. Evaluation Runs
        Schema::create('hcm_ai_eval_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('dataset_id');
            $table->string('run_code')->unique();
            $table->string('model_code');
            $table->string('provider');
            $table->string('prompt_version')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, RUNNING, COMPLETED, FAILED
            $table->decimal('accuracy_score', 5, 2)->default(0.00);
            $table->decimal('grounding_score', 5, 2)->default(0.00);
            $table->decimal('citation_score', 5, 2)->default(0.00);
            $table->decimal('tool_correctness_score', 5, 2)->default(0.00);
            $table->decimal('policy_compliance_score', 5, 2)->default(0.00);
            $table->decimal('safety_score', 5, 2)->default(0.00);
            $table->decimal('overall_quality_score', 5, 2)->default(0.00);
            $table->integer('total_cases_evaluated')->default(0);
            $table->integer('passed_cases')->default(0);
            $table->integer('failed_cases')->default(0);
            $table->integer('avg_latency_ms')->default(0);
            $table->decimal('total_cost', 10, 4)->default(0.0000);
            $table->foreignId('executed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('dataset_id')->references('id')->on('hcm_ai_eval_datasets')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        // 5. AI Regression Detection
        Schema::create('hcm_ai_regressions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('eval_run_id')->nullable();
            $table->string('regression_code')->unique();
            $table->string('use_case_code');
            $table->string('model_code');
            $table->string('metric_name'); // ACCURACY, GROUNDING, LATENCY, TOOL_SUCCESS, POLICY_COMPLIANCE
            $table->decimal('baseline_value', 8, 2);
            $table->decimal('current_value', 8, 2);
            $table->decimal('variance_pct', 8, 2);
            $table->string('status')->default('DETECTED'); // DETECTED, INVESTIGATING, MITIGATED, ACCEPTED
            $table->uuid('incident_id')->nullable();
            $table->text('mitigation_notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        // 6. User Feedback
        Schema::create('hcm_ai_feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('telemetry_id')->nullable();
            $table->uuid('message_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('rating')->default(5); // 1-5
            $table->boolean('is_positive')->default(true);
            $table->string('feedback_category')->nullable(); // INCORRECT, OUTDATED, NOT_RELEVANT, UNSAFE, MISSING_INFORMATION, WRONG_SOURCE, WRONG_ACTION, TOO_SLOW, OTHER
            $table->boolean('task_completed')->default(true);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('telemetry_id')->references('id')->on('hcm_ai_interaction_telemetry')->nullOnDelete();
            $table->index(['tenant_id', 'is_positive']);
        });

        // 7. Continuous Improvement Items
        Schema::create('hcm_ai_improvement_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('item_code')->unique();
            $table->text('problem');
            $table->string('source')->default('FEEDBACK'); // FEEDBACK, TELEMETRY, EVALUATION, REGRESSION, INCIDENT
            $table->string('severity')->default('MEDIUM'); // LOW, MEDIUM, HIGH, CRITICAL
            $table->integer('frequency_count')->default(1);
            $table->json('affected_use_cases')->nullable();
            $table->text('evidence_summary')->nullable();
            $table->text('recommended_action');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('IDENTIFIED'); // IDENTIFIED, TRIAGED, ASSIGNED, IN_PROGRESS, VALIDATING, COMPLETED, REJECTED
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status', 'severity']);
        });

        // 8. Operational AI Budgets & Policies
        Schema::create('hcm_ai_budget_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('scope')->default('TENANT'); // TENANT, USE_CASE, MODEL, DEPARTMENT
            $table->string('target_identifier')->default('ALL');
            $table->string('budget_period')->default('MONTHLY'); // MONTHLY, WEEKLY, DAILY
            $table->decimal('budget_limit_usd', 10, 2)->default(500.00);
            $table->decimal('current_spend_usd', 10, 2)->default(0.00);
            $table->integer('threshold_alert_pct')->default(80);
            $table->string('enforcement_action')->default('WARN'); // NOTIFY, WARN, REQUIRE_APPROVAL, RATE_LIMIT
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'scope']);
        });

        // 9. AI Production Readiness Score
        Schema::create('hcm_ai_production_readiness', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('use_case_code');
            $table->decimal('governance_score', 5, 2)->default(100.00);
            $table->decimal('security_score', 5, 2)->default(95.00);
            $table->decimal('quality_score', 5, 2)->default(90.00);
            $table->decimal('grounding_score', 5, 2)->default(95.00);
            $table->decimal('evaluation_score', 5, 2)->default(90.00);
            $table->decimal('observability_score', 5, 2)->default(100.00);
            $table->decimal('performance_score', 5, 2)->default(90.00);
            $table->decimal('cost_score', 5, 2)->default(95.00);
            $table->decimal('overall_readiness_score', 5, 2)->default(94.38);
            $table->string('readiness_status')->default('PRODUCTION_READY'); // PRODUCTION_READY, CONDITIONALLY_READY, BLOCKED
            $table->json('blockers')->nullable();
            $table->timestamp('evaluated_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'use_case_code']);
        });

        // 10. AI Model Performance Benchmark Matrix
        Schema::create('hcm_ai_model_performance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('model_code');
            $table->string('provider');
            $table->string('benchmark_version')->default('2026.Q4');
            $table->decimal('accuracy_pct', 5, 2)->default(94.00);
            $table->decimal('grounding_pct', 5, 2)->default(96.00);
            $table->decimal('safety_pct', 5, 2)->default(100.00);
            $table->integer('avg_latency_ms')->default(420);
            $table->integer('p95_latency_ms')->default(780);
            $table->decimal('cost_per_1k_tokens', 10, 6)->default(0.002000);
            $table->decimal('availability_pct', 5, 2)->default(99.95);
            $table->decimal('tool_success_pct', 5, 2)->default(98.50);
            $table->string('benchmark_status')->default('APPROVED'); // RECOMMENDED, APPROVED, EVALUATING, DEPRECATED
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'model_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hcm_ai_model_performance');
        Schema::dropIfExists('hcm_ai_production_readiness');
        Schema::dropIfExists('hcm_ai_budget_policies');
        Schema::dropIfExists('hcm_ai_improvement_items');
        Schema::dropIfExists('hcm_ai_feedback');
        Schema::dropIfExists('hcm_ai_regressions');
        Schema::dropIfExists('hcm_ai_eval_runs');
        Schema::dropIfExists('hcm_ai_eval_cases');
        Schema::dropIfExists('hcm_ai_eval_datasets');
        Schema::dropIfExists('hcm_ai_interaction_telemetry');
    }
};
