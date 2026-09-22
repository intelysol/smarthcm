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
        // 1. AI Use-Case Registry
        Schema::create('hcm_ai_gov_use_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('use_case_code')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('domain'); // CORE_HR, WORKFORCE_PLANNING, RECRUITMENT, LEARNING, PERFORMANCE, INTELLIGENCE
            $table->string('business_owner');
            $table->string('technical_owner');
            $table->string('status')->default('DRAFT'); // DRAFT, ASSESSMENT, REVIEW, APPROVED, PRODUCTION, SUSPENDED, RETIRED, PROHIBITED
            $table->string('risk_level')->default('MEDIUM'); // LOW, MEDIUM, HIGH, CRITICAL, PROHIBITED
            $table->string('human_oversight')->default('REVIEW'); // NONE, REVIEW, APPROVAL, DUAL_APPROVAL, COMMITTEE_REVIEW
            $table->json('affected_populations')->nullable();
            $table->json('model_dependencies')->nullable();
            $table->json('tool_dependencies')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status', 'risk_level']);
        });

        // 2. Governed AI Model Registry
        Schema::create('hcm_ai_gov_models', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('model_code')->unique();
            $table->string('provider'); // OPENAI, ANTHROPIC, GOOGLE, AZURE, LOCAL
            $table->string('model_name');
            $table->string('version');
            $table->string('model_type')->default('LLM'); // LLM, SLM, EMBEDDING, CLASSIFIER
            $table->string('status')->default('TESTING'); // DRAFT, TESTING, APPROVED, PRODUCTION, SUSPENDED, DEPRECATED, RETIRED
            $table->string('risk_tier')->default('MEDIUM'); // LOW, MEDIUM, HIGH
            $table->text('capabilities')->nullable();
            $table->text('limitations')->nullable();
            $table->json('data_restrictions')->nullable();
            $table->decimal('cost_per_1k_tokens', 10, 6)->default(0.000000);
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        // 3. AI Impact Assessments
        Schema::create('hcm_ai_gov_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('use_case_id');
            $table->string('assessment_code')->unique();
            $table->string('assessor_name');
            $table->decimal('privacy_risk_score', 5, 2)->default(0.0);
            $table->decimal('bias_risk_score', 5, 2)->default(0.0);
            $table->decimal('security_risk_score', 5, 2)->default(0.0);
            $table->decimal('decision_impact_score', 5, 2)->default(0.0);
            $table->text('human_oversight_mechanism');
            $table->text('contestability_remediation');
            $table->string('recommendation')->default('APPROVE'); // APPROVE, CONDITIONAL_APPROVE, REJECT, PROHIBIT
            $table->string('status')->default('APPROVED'); // DRAFT, IN_REVIEW, APPROVED, REJECTED
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('use_case_id')->references('id')->on('hcm_ai_gov_use_cases')->cascadeOnDelete();
        });

        // 4. Governance Controls & Testing
        Schema::create('hcm_ai_gov_controls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('control_code')->unique();
            $table->string('title');
            $table->string('category'); // HUMAN_OVERSIGHT, DATA_MINIMIZATION, TENANT_ISOLATION, PROMPT_GOVERNANCE, DRIFT_MONITORING
            $table->text('description');
            $table->string('test_status')->default('PASS'); // PASS, FAIL, PARTIAL, NOT_TESTED
            $table->timestamp('last_tested_at')->nullable();
            $table->text('evidence_summary')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'test_status']);
        });

        // 5. Bias & Fairness Monitoring
        Schema::create('hcm_ai_gov_fairness_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('use_case_id')->nullable();
            $table->string('check_code')->unique();
            $table->string('metric_evaluated'); // SELECTION_RATE_PARITY, APPROVAL_RATE_VARIANCE, IMPACT_RATIO
            $table->decimal('variance_percentage', 6, 2);
            $table->decimal('threshold_allowed', 6, 2)->default(10.00);
            $table->string('fairness_status')->default('COMPLIANT'); // COMPLIANT, WARNING, NON_COMPLIANT
            $table->json('aggregate_data_sample')->nullable();
            $table->text('investigation_notes')->nullable();
            $table->timestamp('evaluated_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'fairness_status']);
        });

        // 6. AI Incidents & Security Events
        Schema::create('hcm_ai_gov_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('incident_code')->unique();
            $table->string('incident_type'); // PROMPT_INJECTION, DATA_LEAK, HALLUCINATION, UNAUTHORIZED_ACTION, MODEL_DRIFT
            $table->string('severity')->default('HIGH'); // LOW, MEDIUM, HIGH, CRITICAL
            $table->text('summary');
            $table->json('evidence_payload')->nullable();
            $table->string('status')->default('DETECTED'); // DETECTED, TRIAGED, INVESTIGATING, CONTAINED, REMEDIATED, CLOSED
            $table->text('containment_action')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('contained_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'severity', 'status']);
        });

        // 7. Granular Kill Switches
        Schema::create('hcm_ai_gov_kill_switches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('scope'); // GLOBAL, TENANT, USE_CASE, MODEL, AGENT, TOOL
            $table->string('target_identifier'); // Specific code (e.g. ALL, USE_CASE_CODE, MODEL_NAME)
            $table->boolean('is_active')->default(true);
            $table->text('activation_reason');
            $table->foreignId('activated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('activated_at');
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'scope', 'is_active']);
        });

        // 8. Immutable AI Governance Audit Log
        Schema::create('hcm_ai_gov_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type'); // USE_CASE_APPROVED, KILL_SWITCH_TRIGGERED, INCIDENT_RESOLVED, PROHIBITED_BLOCKED
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->text('summary');
            $table->json('details')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'event_type', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hcm_ai_gov_audits');
        Schema::dropIfExists('hcm_ai_gov_kill_switches');
        Schema::dropIfExists('hcm_ai_gov_incidents');
        Schema::dropIfExists('hcm_ai_gov_fairness_checks');
        Schema::dropIfExists('hcm_ai_gov_controls');
        Schema::dropIfExists('hcm_ai_gov_assessments');
        Schema::dropIfExists('hcm_ai_gov_models');
        Schema::dropIfExists('hcm_ai_gov_use_cases');
    }
};
