<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Mobility Programs & Policies
        Schema::create('hcm_mobility_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50)->index();
            $table->string('name', 150);
            $table->string('mobility_type', 60)->default('international_assignment');
            $table->text('description')->nullable();
            $table->unsignedInteger('min_duration_months')->default(1);
            $table->unsignedInteger('max_duration_months')->nullable();
            $table->boolean('requires_relocation')->default(true);
            $table->boolean('requires_compliance_check')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hcm_mobility_policy_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('program_id')->index();
            $table->unsignedInteger('version_number')->default(1);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('housing_policy_type', 50)->default('company_provided'); // company_provided, allowance, self_paid
            $table->decimal('housing_monthly_cap', 19, 4)->nullable();
            $table->decimal('relocation_allowance', 19, 4)->default(0.0000);
            $table->decimal('mobility_premium_percentage', 6, 2)->default(0.00);
            $table->decimal('hardship_allowance_percentage', 6, 2)->default(0.00);
            $table->boolean('tax_equalization_enabled')->default(true);
            $table->boolean('education_allowance_enabled')->default(false);
            $table->decimal('education_allowance_per_child', 19, 4)->default(0.0000);
            $table->json('rules_configuration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hcm_mobility_policy_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('policy_version_id')->index();
            $table->string('rule_category', 50); // eligibility, housing, travel, per_diem, shipment
            $table->string('rule_name', 100);
            $table->json('conditions')->nullable();
            $table->json('actions')->nullable();
            $table->timestamps();
        });

        // 2. Mobility Requests
        Schema::create('hcm_mobility_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('program_id')->nullable()->index();
            $table->string('request_number', 60)->unique();
            $table->string('mobility_type', 60)->default('international_assignment');
            $table->string('status', 40)->default('draft'); // draft, submitted, eligibility_cleared, approved, rejected, cancelled

            // Home Organization details
            $table->uuid('home_company_id')->index();
            $table->string('home_country', 80);
            $table->uuid('home_branch_id')->nullable()->index();
            $table->uuid('home_department_id')->nullable()->index();
            $table->uuid('home_position_id')->nullable()->index();
            $table->uuid('home_job_id')->nullable()->index();
            $table->uuid('home_manager_id')->nullable()->index();

            // Host Organization details
            $table->uuid('host_company_id')->index();
            $table->string('host_country', 80);
            $table->uuid('host_branch_id')->nullable()->index();
            $table->uuid('host_department_id')->nullable()->index();
            $table->uuid('host_position_id')->nullable()->index();
            $table->uuid('host_job_id')->nullable()->index();
            $table->uuid('host_manager_id')->nullable()->index();

            $table->date('proposed_start_date');
            $table->date('proposed_end_date')->nullable();
            $table->unsignedInteger('duration_months')->default(12);
            $table->text('business_justification');
            $table->string('assignment_reason', 100)->nullable();
            $table->uuid('project_id')->nullable()->index();
            $table->string('cost_center_code', 80)->nullable();
            $table->uuid('assignment_sponsor_id')->nullable()->index();
            $table->uuid('mobility_owner_id')->nullable()->index();

            $table->string('eligibility_status', 40)->default('pending'); // eligible, conditionally_eligible, ineligible, requires_review
            $table->json('eligibility_details')->nullable();

            $table->uuid('workflow_instance_id')->nullable()->index();
            $table->uuid('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Mobility Assignments (Active Profile) & Versions
        Schema::create('hcm_mobility_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('mobility_request_id')->nullable()->index();
            $table->uuid('employee_id')->index();
            $table->uuid('program_id')->nullable()->index();
            $table->string('assignment_number', 60)->unique();
            $table->string('mobility_type', 60)->default('international_assignment');
            $table->string('status', 40)->default('planning'); // planning, active, on_hold, extension_pending, repatriating, completed, cancelled
            $table->unsignedInteger('current_version')->default(1);

            // Home & Host Countries
            $table->string('home_country', 80);
            $table->string('host_country', 80);

            // Home Organization References
            $table->uuid('home_company_id')->index();
            $table->uuid('home_branch_id')->nullable()->index();
            $table->uuid('home_department_id')->nullable()->index();
            $table->uuid('home_position_id')->nullable()->index();
            $table->uuid('home_job_id')->nullable()->index();
            $table->uuid('home_manager_id')->nullable()->index();

            // Host Organization References
            $table->uuid('host_company_id')->index();
            $table->uuid('host_branch_id')->nullable()->index();
            $table->uuid('host_department_id')->nullable()->index();
            $table->uuid('host_position_id')->nullable()->index();
            $table->uuid('host_job_id')->nullable()->index();
            $table->uuid('host_manager_id')->nullable()->index();

            $table->date('start_date');
            $table->date('planned_end_date');
            $table->date('actual_end_date')->nullable();

            $table->string('home_currency', 10)->default('USD');
            $table->string('host_currency', 10)->default('USD');
            $table->string('assignment_currency', 10)->default('USD');

            $table->uuid('assignment_sponsor_id')->nullable()->index();
            $table->uuid('mobility_owner_id')->nullable()->index();
            $table->string('purpose', 150)->nullable();
            $table->string('project_code', 80)->nullable();
            $table->string('cost_center_code', 80)->nullable();

            $table->boolean('is_repatriated')->default(false);
            $table->date('repatriation_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hcm_mobility_assignment_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->unsignedInteger('version_number')->default(1);
            $table->string('version_reason', 100)->nullable();
            $table->json('snapshot_payload');
            $table->uuid('created_by')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('hcm_mobility_assignment_terms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('home_employment_terms', 100)->default('continuous_employment');
            $table->string('host_employment_terms', 100)->default('seconded');
            $table->string('housing_policy', 100)->nullable();
            $table->string('relocation_policy', 100)->nullable();
            $table->string('travel_policy', 100)->nullable();
            $table->string('expense_policy', 100)->nullable();
            $table->string('tax_treatment', 100)->default('tax_equalization'); // tax_equalization, tax_protection, host_tax, home_tax
            $table->string('repatriation_terms', 100)->default('return_to_equivalent');
            $table->json('additional_terms')->nullable();
            $table->timestamps();
        });

        Schema::create('hcm_mobility_assignment_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('location_type', 30); // home, host
            $table->string('country', 80);
            $table->string('city', 100)->nullable();
            $table->string('state_province', 100)->nullable();
            $table->text('address')->nullable();
            $table->uuid('work_location_id')->nullable()->index();
            $table->timestamps();
        });

        // 4. Assignment Cost Estimation, Budgets & Cost Allocations
        Schema::create('hcm_mobility_assignment_costs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('cost_category', 60); // base_compensation, mobility_premium, hardship_allowance, housing, relocation, per_diem, schooling, insurance, tax_support, immigration, temporary_accommodation, shipment, home_leave, repatriation
            $table->string('cost_name', 120);
            $table->decimal('source_amount', 19, 4);
            $table->string('source_currency', 10)->default('USD');
            $table->decimal('exchange_rate', 14, 6)->default(1.000000);
            $table->date('exchange_rate_date')->nullable();
            $table->decimal('converted_amount', 19, 4);
            $table->string('converted_currency', 10)->default('USD');
            $table->string('frequency', 30)->default('one_time'); // one_time, monthly, annual
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hcm_mobility_assignment_budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('budget_category', 60);
            $table->decimal('approved_budget', 19, 4)->default(0.0000);
            $table->decimal('committed_amount', 19, 4)->default(0.0000);
            $table->decimal('actual_amount', 19, 4)->default(0.0000);
            $table->decimal('variance_amount', 19, 4)->default(0.0000);
            $table->string('currency', 10)->default('USD');
            $table->timestamps();
        });

        Schema::create('hcm_mobility_cost_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('entity_role', 30); // home, host, shared
            $table->uuid('company_id')->index();
            $table->uuid('department_id')->nullable()->index();
            $table->string('cost_center_code', 80)->nullable();
            $table->decimal('allocation_percentage', 6, 2);
            $table->decimal('allocated_amount', 19, 4)->default(0.0000);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });

        // 5. Relocation Cases & Items
        Schema::create('hcm_mobility_relocation_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('case_number', 60)->unique();
            $table->string('status', 40)->default('initiated'); // initiated, in_progress, settling_in, completed, on_hold
            $table->string('relocation_provider_name', 120)->nullable();
            $table->string('provider_reference', 80)->nullable();
            $table->date('target_move_date')->nullable();
            $table->date('actual_move_date')->nullable();
            $table->boolean('family_relocating')->default(false);
            $table->json('relocating_dependent_ids')->nullable(); // references HcmEmployeeDependent IDs
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hcm_mobility_relocation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('relocation_case_id')->index();
            $table->string('item_type', 60); // moving_household, temporary_accommodation, permanent_accommodation, shipment, storage, orientation, schooling, bank_setup, utility_setup, local_registration
            $table->string('title', 150);
            $table->string('status', 40)->default('pending'); // pending, scheduled, in_progress, completed, waived
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->decimal('cost_estimate', 19, 4)->default(0.0000);
            $table->string('currency', 10)->default('USD');
            $table->text('details')->nullable();
            $table->timestamps();
        });

        // 6. Assignment Tasks
        Schema::create('hcm_mobility_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('stage', 40); // pre_move, arrival, active, exit
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('assigned_role', 60)->default('mobility_specialist'); // employee, manager, mobility_specialist, compliance_officer, finance
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('status', 40)->default('pending'); // pending, in_progress, completed, overdue, waived
            $table->timestamp('completed_at')->nullable();
            $table->uuid('completed_by')->nullable()->index();
            $table->timestamps();
        });

        // 7. Cross-Domain Links
        Schema::create('hcm_mobility_compliance_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('compliance_type', 50); // visa, work_permit, residency, registration
            $table->uuid('compliance_record_id')->index(); // references HcmEmployeeVisaRecord or HcmEmployeeWorkPermit
            $table->string('compliance_status', 40)->default('pending'); // pending, approved, active, expired, not_required
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hcm_mobility_document_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('document_type', 60); // assignment_letter, relocation_agreement, visa_copy, tax_agreement, amendment, repatriation_letter
            $table->uuid('document_id')->index(); // references EmployeeDocument
            $table->string('verification_status', 40)->default('verified');
            $table->timestamps();
        });

        Schema::create('hcm_mobility_expense_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('link_type', 40); // travel_request, expense_claim, travel_advance
            $table->uuid('entity_id')->index(); // references travel_requests, expense_claims, travel_advances
            $table->decimal('amount', 19, 4)->default(0.0000);
            $table->string('currency', 10)->default('USD');
            $table->timestamps();
        });

        Schema::create('hcm_mobility_benefit_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->uuid('benefit_plan_id')->nullable()->index();
            $table->uuid('benefit_election_id')->nullable()->index();
            $table->string('benefit_category', 60)->default('international_health');
            $table->string('status', 40)->default('active');
            $table->timestamps();
        });

        Schema::create('hcm_mobility_compensation_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->uuid('compensation_recommendation_id')->nullable()->index('hcm_mob_comp_link_rec_id_idx');
            $table->decimal('home_salary', 19, 4)->nullable();
            $table->decimal('host_salary', 19, 4)->nullable();
            $table->decimal('mobility_allowance', 19, 4)->default(0.0000);
            $table->decimal('cost_of_living_allowance', 19, 4)->default(0.0000);
            $table->decimal('housing_allowance', 19, 4)->default(0.0000);
            $table->decimal('hardship_allowance', 19, 4)->default(0.0000);
            $table->string('currency', 10)->default('USD');
            $table->timestamps();
        });

        // 8. Extensions, Changes & Repatriation
        Schema::create('hcm_mobility_extensions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('extension_number', 60)->unique();
            $table->date('current_end_date');
            $table->date('proposed_end_date');
            $table->text('extension_reason');
            $table->decimal('additional_estimated_cost', 19, 4)->default(0.0000);
            $table->string('currency', 10)->default('USD');
            $table->string('status', 40)->default('requested'); // requested, approved, rejected
            $table->uuid('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('hcm_mobility_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('change_type', 60); // host_entity, host_location, position, manager, cost_center
            $table->json('previous_values')->nullable();
            $table->json('proposed_values')->nullable();
            $table->date('effective_date');
            $table->text('reason');
            $table->uuid('personnel_action_request_id')->nullable()->index(); // linked to Epic 2.28
            $table->string('status', 40)->default('pending'); // pending, approved, executed, rejected
            $table->uuid('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('hcm_mobility_repatriations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('assignment_id')->index();
            $table->string('repatriation_number', 60)->unique();
            $table->date('planned_return_date');
            $table->date('actual_return_date')->nullable();
            $table->string('outcome_type', 60)->default('return_to_equivalent'); // return_to_original, return_to_equivalent, new_position, host_permanent_transfer, separation
            $table->uuid('return_company_id')->nullable()->index();
            $table->uuid('return_department_id')->nullable()->index();
            $table->uuid('return_position_id')->nullable()->index();
            $table->uuid('return_manager_id')->nullable()->index();
            $table->string('status', 40)->default('planning'); // planning, handover, completed, cancelled
            $table->boolean('expense_settlement_completed')->default(false);
            $table->boolean('advance_settlement_completed')->default(false);
            $table->boolean('compliance_closure_completed')->default(false);
            $table->boolean('payroll_transition_completed')->default(false);
            $table->uuid('personnel_action_request_id')->nullable()->index(); // linked to Epic 2.28
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 9. Business Travelers & Integration Records
        Schema::create('hcm_mobility_business_travelers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('traveler_trip_number', 60)->unique();
            $table->string('destination_country', 80);
            $table->string('destination_city', 100)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('trip_days')->default(1);
            $table->string('business_purpose', 200);
            $table->string('compliance_risk_level', 30)->default('low'); // low, medium, high
            $table->boolean('visa_required')->default(false);
            $table->boolean('visa_cleared')->default(true);
            $table->uuid('expense_travel_request_id')->nullable()->index(); // linked to Epic 2.38
            $table->string('status', 40)->default('registered'); // registered, active, completed, cancelled
            $table->timestamps();
        });

        Schema::create('hcm_mobility_integration_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('integration_target', 60); // finance_gl, payroll, personnel_actions, external_vendor
            $table->string('entity_type', 80);
            $table->uuid('entity_id')->index();
            $table->string('status', 30)->default('staged'); // staged, dispatched, acknowledged, failed
            $table->json('payload');
            $table->string('external_reference', 100)->nullable();
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestamp('dispatched_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_mobility_integration_records');
        Schema::dropIfExists('hcm_mobility_business_travelers');
        Schema::dropIfExists('hcm_mobility_repatriations');
        Schema::dropIfExists('hcm_mobility_changes');
        Schema::dropIfExists('hcm_mobility_extensions');
        Schema::dropIfExists('hcm_mobility_compensation_links');
        Schema::dropIfExists('hcm_mobility_benefit_links');
        Schema::dropIfExists('hcm_mobility_expense_links');
        Schema::dropIfExists('hcm_mobility_document_links');
        Schema::dropIfExists('hcm_mobility_compliance_links');
        Schema::dropIfExists('hcm_mobility_tasks');
        Schema::dropIfExists('hcm_mobility_relocation_items');
        Schema::dropIfExists('hcm_mobility_relocation_cases');
        Schema::dropIfExists('hcm_mobility_cost_allocations');
        Schema::dropIfExists('hcm_mobility_assignment_budgets');
        Schema::dropIfExists('hcm_mobility_assignment_costs');
        Schema::dropIfExists('hcm_mobility_assignment_locations');
        Schema::dropIfExists('hcm_mobility_assignment_terms');
        Schema::dropIfExists('hcm_mobility_assignment_versions');
        Schema::dropIfExists('hcm_mobility_assignments');
        Schema::dropIfExists('hcm_mobility_requests');
        Schema::dropIfExists('hcm_mobility_policy_rules');
        Schema::dropIfExists('hcm_mobility_policy_versions');
        Schema::dropIfExists('hcm_mobility_programs');
    }
};
