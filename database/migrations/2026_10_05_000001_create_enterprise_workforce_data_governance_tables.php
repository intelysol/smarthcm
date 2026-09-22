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
        // 1. Governed Data Assets Catalog
        Schema::create('hcm_gov_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('company_id')->nullable();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->string('domain'); // CORE_HR, PAYROLL, TALENT, WORKFORCE_PLANNING, CAPACITY, SCHEDULING, ATTENDANCE, COST, PRODUCTIVITY, OPTIMIZATION, COMMAND_CENTER
            $table->text('business_definition');
            $table->text('technical_definition')->nullable();
            $table->string('system_of_record'); // e.g. Core HR, Attendance Engine, Payroll
            $table->string('source_table')->nullable();
            $table->string('business_owner');
            $table->string('technical_owner');
            $table->string('data_steward')->nullable();
            $table->string('security_classification')->default('INTERNAL'); // PUBLIC, INTERNAL, CONFIDENTIAL, RESTRICTED, HIGHLY_RESTRICTED
            $table->string('freshness_status')->default('FRESH'); // FRESH, STALE, DEGRADED, UNAVAILABLE
            $table->integer('expected_refresh_seconds')->default(86400);
            $table->timestamp('last_refreshed_at')->nullable();
            $table->decimal('current_quality_score', 5, 2)->default(100.00);
            $table->string('status')->default('ACTIVE'); // ACTIVE, DRAFT, DEPRECATED
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'domain']);
            $table->index(['tenant_id', 'status']);
        });

        // 2. Data Quality Rules
        Schema::create('hcm_gov_quality_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('asset_id')->nullable();
            $table->string('rule_code')->unique();
            $table->string('name');
            $table->string('dimension'); // COMPLETENESS, ACCURACY, CONSISTENCY, VALIDITY, UNIQUENESS, TIMELINESS, INTEGRITY, CONFORMITY
            $table->string('severity')->default('HIGH'); // CRITICAL, HIGH, MEDIUM, LOW, INFORMATIONAL
            $table->text('description');
            $table->string('target_entity'); // Employee, Position, Department, CostLine, etc.
            $table->string('target_field')->nullable();
            $table->text('condition_expression');
            $table->text('expected_condition_text');
            $table->text('suggested_remediation')->nullable();
            $table->integer('sla_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('asset_id')->references('id')->on('hcm_gov_assets')->nullOnDelete();
            $table->index(['tenant_id', 'dimension', 'is_active']);
        });

        // 3. Data Quality Runs & Batch Executions
        Schema::create('hcm_gov_quality_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('run_code')->unique();
            $table->string('domain')->nullable();
            $table->string('trigger_type')->default('SCHEDULED'); // MANUAL, SCHEDULED, EVENT_DRIVEN
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_rules_evaluated')->default(0);
            $table->integer('total_records_scanned')->default(0);
            $table->integer('total_issues_found')->default(0);
            $table->integer('critical_issues_found')->default(0);
            $table->decimal('overall_score', 5, 2)->default(100.00);
            $table->string('status')->default('RUNNING'); // RUNNING, COMPLETED, FAILED
            $table->json('dimension_scores')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        // 4. Data Quality Issues
        Schema::create('hcm_gov_quality_issues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('run_id')->nullable();
            $table->uuid('rule_id');
            $table->uuid('asset_id')->nullable();
            $table->string('issue_code')->unique();
            $table->string('severity')->default('HIGH');
            $table->string('target_entity');
            $table->string('target_record_id');
            $table->string('target_field')->nullable();
            $table->text('current_value')->nullable();
            $table->text('expected_condition');
            $table->text('suggested_correction')->nullable();
            $table->string('status')->default('OPEN'); // DETECTED, OPEN, ASSIGNED, INVESTIGATING, CORRECTION_REQUESTED, CORRECTED, VALIDATED, RESOLVED, ACCEPTED_EXCEPTION
            $table->uuid('assigned_steward_id')->nullable();
            $table->string('root_cause_category')->nullable(); // SOURCE_SYSTEM, INTEGRATION, MAPPING, MANUAL_ENTRY, CONFIGURATION
            $table->text('root_cause_explanation')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('rule_id')->references('id')->on('hcm_gov_quality_rules')->cascadeOnDelete();
            $table->foreign('asset_id')->references('id')->on('hcm_gov_assets')->nullOnDelete();
            $table->index(['tenant_id', 'status', 'severity']);
        });

        // 5. Data Quality Exceptions
        Schema::create('hcm_gov_quality_exceptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('issue_id');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('justification');
            $table->timestamp('valid_until');
            $table->string('status')->default('ACTIVE'); // ACTIVE, EXPIRED, REVOKED
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('issue_id')->references('id')->on('hcm_gov_quality_issues')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        // 6. Master Data Mappings (Cross-system references)
        Schema::create('hcm_gov_master_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('entity_type', 60); // DEPARTMENT, COST_CENTER, JOB, LOCATION, EMPLOYEE
            $table->string('source_system', 60); // SAP, WORKDAY, NETSUITE, PAYROLL_ADP, HCM_CORE
            $table->string('source_id', 100);
            $table->string('source_code', 100);
            $table->string('target_system', 60)->default('HCM_CORE');
            $table->string('target_id', 100);
            $table->string('target_code', 100);
            $table->string('mapping_status')->default('MAPPED'); // UNMAPPED, MAPPED, AMBIGUOUS, CONFLICT, RETIRED, PENDING_REVIEW
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'entity_type', 'mapping_status'], 'hcm_gov_mst_map_t_ent_stat_idx');
            $table->unique(['tenant_id', 'entity_type', 'source_system', 'source_code'], 'uniq_master_mapping');
        });

        // 7. Master Data Reconciliations
        Schema::create('hcm_gov_reconciliations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('reconciliation_code')->unique();
            $table->string('name');
            $table->string('source_domain'); // e.g. CORE_HR
            $table->string('target_domain'); // e.g. PAYROLL, FINANCE_ERP
            $table->string('comparison_entity'); // EMPLOYEE_HEADCOUNT, COST_CENTERS, DEPARTMENTS
            $table->integer('source_record_count')->default(0);
            $table->integer('target_record_count')->default(0);
            $table->integer('matched_count')->default(0);
            $table->integer('unmatched_count')->default(0);
            $table->integer('conflict_count')->default(0);
            $table->decimal('variance_amount', 18, 2)->default(0);
            $table->string('status')->default('BALANCED'); // BALANCED, DISCREPANCY_DETECTED, INVESTIGATING, RESOLVED
            $table->json('discrepancy_details')->nullable();
            $table->timestamp('reconciled_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        // 8. Lineage Nodes & Edges
        Schema::create('hcm_gov_lineage_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('node_code')->unique();
            $table->string('name');
            $table->string('node_type'); // SOURCE_TABLE, LOGICAL_MODEL, TRANSFORMATION, KPI, REPORT, DASHBOARD_WIDGET
            $table->string('domain');
            $table->string('system_name');
            $table->json('schema_definition')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'node_type']);
        });

        Schema::create('hcm_gov_lineage_edges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('source_node_id');
            $table->uuid('target_node_id');
            $table->string('relationship_type'); // SOURCE, TRANSFORM, CALCULATE, AGGREGATE, ALLOCATE, DERIVE, DISPLAY, EXPORT
            $table->text('transformation_logic')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('source_node_id')->references('id')->on('hcm_gov_lineage_nodes')->cascadeOnDelete();
            $table->foreign('target_node_id')->references('id')->on('hcm_gov_lineage_nodes')->cascadeOnDelete();
            $table->index(['tenant_id', 'source_node_id', 'target_node_id'], 'hcm_gov_lin_edg_t_src_tgt_idx');
        });

        // 9. Central Governed KPI Registry & Certification
        Schema::create('hcm_gov_kpi_registries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('kpi_code')->unique();
            $table->string('name');
            $table->text('business_definition');
            $table->text('technical_formula');
            $table->string('business_owner');
            $table->string('technical_owner');
            $table->string('source_module');
            $table->string('unit')->default('count');
            $table->string('frequency')->default('MONTHLY');
            $table->integer('version')->default(1);
            $table->string('lifecycle_status')->default('PUBLISHED'); // DRAFT, BUSINESS_REVIEW, TECHNICAL_REVIEW, APPROVED, PUBLISHED, DEPRECATED, RETIRED
            $table->string('certification_status')->default('UNCERTIFIED'); // CERTIFIED, UNCERTIFIED, UNDER_REVIEW, DEPRECATED
            $table->foreignId('certified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('certified_at')->nullable();
            $table->string('security_classification')->default('INTERNAL');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'certification_status', 'lifecycle_status'], 'hcm_gov_kpi_reg_t_cert_stat_idx');
        });

        // 10. Data Contracts & Monitoring
        Schema::create('hcm_gov_contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('contract_code')->unique();
            $table->string('name');
            $table->string('producer_module');
            $table->json('consumer_modules');
            $table->integer('version')->default(1);
            $table->json('schema_contract'); // fields, data types, requiredness, descriptions
            $table->string('status')->default('HEALTHY'); // HEALTHY, WARNING, BROKEN, DEPRECATED
            $table->timestamp('last_validated_at')->nullable();
            $table->text('breaking_change_notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        // 11. Immutable Governance Audit Log
        Schema::create('hcm_gov_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // QUALITY_RUN, RULE_CHANGE, ISSUE_RESOLVED, EXCEPTION_APPROVED, MAPPING_UPDATED, KPI_CERTIFIED, CONTRACT_PUBLISHED
            $table->string('target_entity_type')->nullable();
            $table->string('target_entity_id')->nullable();
            $table->text('summary');
            $table->json('details')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'action', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hcm_gov_audits');
        Schema::dropIfExists('hcm_gov_contracts');
        Schema::dropIfExists('hcm_gov_kpi_registries');
        Schema::dropIfExists('hcm_gov_lineage_edges');
        Schema::dropIfExists('hcm_gov_lineage_nodes');
        Schema::dropIfExists('hcm_gov_reconciliations');
        Schema::dropIfExists('hcm_gov_master_mappings');
        Schema::dropIfExists('hcm_gov_quality_exceptions');
        Schema::dropIfExists('hcm_gov_quality_issues');
        Schema::dropIfExists('hcm_gov_quality_runs');
        Schema::dropIfExists('hcm_gov_quality_rules');
        Schema::dropIfExists('hcm_gov_assets');
    }
};
