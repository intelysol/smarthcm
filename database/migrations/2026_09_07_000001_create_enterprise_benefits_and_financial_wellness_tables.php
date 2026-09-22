<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Benefit Categories
        if (! Schema::hasTable('benefit_categories')) {
            Schema::create('benefit_categories', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('category_type', 40); // health_insurance, life_insurance, dental, vision, medical_allowance, meal, transport, communication, retirement, wellness, eap, other
                $table->text('description')->nullable();
                $table->boolean('is_statutory')->default(false);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 2. Benefit Providers
        if (! Schema::hasTable('benefit_providers')) {
            Schema::create('benefit_providers', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('provider_code', 60);
                $table->string('name', 150);
                $table->string('provider_type', 40)->default('insurance_company'); // insurance_company, fund_manager, bank, medical_network, wellness_partner
                $table->string('contract_number', 100)->nullable();
                $table->string('policy_number', 100)->nullable();
                $table->string('contact_person', 150)->nullable();
                $table->string('contact_email', 150)->nullable();
                $table->string('contact_phone', 40)->nullable();
                $table->string('website', 200)->nullable();
                $table->date('contract_start_date')->nullable();
                $table->date('contract_expiry_date')->nullable();
                $table->json('integration_configuration')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'provider_code']);
            });
        }

        // 3. Extend or Create Benefit Plans
        if (Schema::hasTable('benefit_plans')) {
            Schema::table('benefit_plans', function (Blueprint $table): void {
                if (! Schema::hasColumn('benefit_plans', 'benefit_category_id')) {
                    $table->uuid('benefit_category_id')->nullable()->after('tenant_id')->index();
                }
                if (! Schema::hasColumn('benefit_plans', 'benefit_provider_id')) {
                    $table->uuid('benefit_provider_id')->nullable()->after('benefit_category_id')->index();
                }
                if (! Schema::hasColumn('benefit_plans', 'currency')) {
                    $table->string('currency', 3)->default('USD')->after('status');
                }
                if (! Schema::hasColumn('benefit_plans', 'coverage_level')) {
                    $table->string('coverage_level', 40)->default('employee_only')->after('currency'); // employee_only, employee_plus_spouse, employee_plus_children, family
                }
                if (! Schema::hasColumn('benefit_plans', 'employee_cost')) {
                    $table->decimal('employee_cost', 19, 4)->default(0)->after('coverage_level');
                }
                if (! Schema::hasColumn('benefit_plans', 'employer_cost')) {
                    $table->decimal('employer_cost', 19, 4)->default(0)->after('employee_cost');
                }
                if (! Schema::hasColumn('benefit_plans', 'annual_limit')) {
                    $table->decimal('annual_limit', 19, 4)->nullable()->after('employer_cost');
                }
                if (! Schema::hasColumn('benefit_plans', 'monthly_limit')) {
                    $table->decimal('monthly_limit', 19, 4)->nullable()->after('annual_limit');
                }
                if (! Schema::hasColumn('benefit_plans', 'waiting_period_days')) {
                    $table->unsignedSmallInteger('waiting_period_days')->default(0)->after('monthly_limit');
                }
                if (! Schema::hasColumn('benefit_plans', 'requires_beneficiary')) {
                    $table->boolean('requires_beneficiary')->default(false)->after('waiting_period_days');
                }
                if (! Schema::hasColumn('benefit_plans', 'requires_dependents')) {
                    $table->boolean('requires_dependents')->default(false)->after('requires_beneficiary');
                }
                if (! Schema::hasColumn('benefit_plans', 'version')) {
                    $table->unsignedInteger('version')->default(1)->after('requires_dependents');
                }
                if (! Schema::hasColumn('benefit_plans', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        } else {
            Schema::create('benefit_plans', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('benefit_category_id')->nullable()->index();
                $table->uuid('benefit_provider_id')->nullable()->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('benefit_type', 40);
                $table->string('coverage_level', 40)->default('employee_only');
                $table->string('currency', 3)->default('USD');
                $table->decimal('employee_cost', 19, 4)->default(0);
                $table->decimal('employer_cost', 19, 4)->default(0);
                $table->decimal('annual_limit', 19, 4)->nullable();
                $table->decimal('monthly_limit', 19, 4)->nullable();
                $table->unsignedSmallInteger('waiting_period_days')->default(0);
                $table->boolean('requires_beneficiary')->default(false);
                $table->boolean('requires_dependents')->default(false);
                $table->unsignedInteger('version')->default(1);
                $table->text('description')->nullable();
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->json('coverage')->nullable();
                $table->json('eligibility_rules')->nullable();
                $table->json('contribution_rules')->nullable();
                $table->json('tax_rules')->nullable();
                $table->string('status', 20)->default('active');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 4. Benefit Plan Versions
        if (! Schema::hasTable('benefit_plan_versions')) {
            Schema::create('benefit_plan_versions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('benefit_plan_id')->index();
                $table->unsignedInteger('version_number');
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->decimal('employee_cost', 19, 4)->default(0);
                $table->decimal('employer_cost', 19, 4)->default(0);
                $table->decimal('annual_limit', 19, 4)->nullable();
                $table->json('coverage_details')->nullable();
                $table->json('eligibility_criteria')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('benefit_plan_id')->references('id')->on('benefit_plans')->cascadeOnDelete();
            });
        }

        // 5. Benefit Eligibility Rules
        if (! Schema::hasTable('benefit_eligibility_rules')) {
            Schema::create('benefit_eligibility_rules', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('benefit_plan_id')->index();
                $table->string('rule_name', 150);
                $table->json('criteria'); // department_ids, location_ids, job_grade_ids, employment_type_ids, min_service_months, min_age, max_age
                $table->boolean('is_strict')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('benefit_plan_id')->references('id')->on('benefit_plans')->cascadeOnDelete();
            });
        }

        // 6. Benefit Enrollment Windows (Open Enrollment)
        if (! Schema::hasTable('benefit_enrollment_windows')) {
            Schema::create('benefit_enrollment_windows', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('name', 150);
                $table->unsignedSmallInteger('plan_year');
                $table->date('start_date');
                $table->date('close_date');
                $table->date('effective_date');
                $table->string('status', 30)->default('draft'); // draft, open, closed, locked
                $table->boolean('allow_late_enrollment')->default(false);
                $table->text('description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 7. Extend or Create Benefit Enrollments
        if (Schema::hasTable('benefit_enrollments')) {
            Schema::table('benefit_enrollments', function (Blueprint $table): void {
                if (! Schema::hasColumn('benefit_enrollments', 'benefit_enrollment_window_id')) {
                    $table->uuid('benefit_enrollment_window_id')->nullable()->after('benefit_plan_id')->index();
                }
                if (! Schema::hasColumn('benefit_enrollments', 'benefit_plan_version_id')) {
                    $table->uuid('benefit_plan_version_id')->nullable()->after('benefit_enrollment_window_id')->index();
                }
                if (! Schema::hasColumn('benefit_enrollments', 'currency')) {
                    $table->string('currency', 3)->default('USD')->after('employer_contribution');
                }
                if (! Schema::hasColumn('benefit_enrollments', 'coverage_level')) {
                    $table->string('coverage_level', 40)->default('employee_only')->after('currency');
                }
                if (! Schema::hasColumn('benefit_enrollments', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('workflow_instance_id')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('benefit_enrollments', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (! Schema::hasColumn('benefit_enrollments', 'notes')) {
                    $table->text('notes')->nullable()->after('approved_at');
                }
                if (! Schema::hasColumn('benefit_enrollments', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        } else {
            Schema::create('benefit_enrollments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('benefit_plan_id')->index();
                $table->uuid('benefit_enrollment_window_id')->nullable()->index();
                $table->uuid('benefit_plan_version_id')->nullable()->index();
                $table->string('enrollment_type', 30)->default('open_enrollment'); // open_enrollment, new_hire, life_event, admin_override
                $table->string('coverage_level', 40)->default('employee_only');
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->decimal('employee_contribution', 19, 4)->default(0);
                $table->decimal('employer_contribution', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->string('status', 30)->default('pending'); // draft, submitted, pending_approval, approved, active, suspended, cancelled, expired
                $table->uuid('workflow_instance_id')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('benefit_plan_id')->references('id')->on('benefit_plans')->cascadeOnDelete();
            });
        }

        // 8. Extend or Create Benefit Dependents
        if (Schema::hasTable('benefit_dependents')) {
            Schema::table('benefit_dependents', function (Blueprint $table): void {
                if (! Schema::hasColumn('benefit_dependents', 'tenant_id')) {
                    $table->uuid('tenant_id')->nullable()->after('id')->index();
                }
                if (! Schema::hasColumn('benefit_dependents', 'employee_id')) {
                    $table->uuid('employee_id')->nullable()->after('tenant_id')->index();
                }
                if (! Schema::hasColumn('benefit_dependents', 'family_member_id')) {
                    $table->uuid('family_member_id')->nullable()->after('employee_id')->index();
                }
                if (! Schema::hasColumn('benefit_dependents', 'gender')) {
                    $table->string('gender', 30)->nullable()->after('date_of_birth');
                }
                if (! Schema::hasColumn('benefit_dependents', 'national_id')) {
                    $table->string('national_id', 80)->nullable()->after('gender');
                }
                if (! Schema::hasColumn('benefit_dependents', 'effective_from')) {
                    $table->date('effective_from')->nullable()->after('is_eligible');
                }
                if (! Schema::hasColumn('benefit_dependents', 'effective_to')) {
                    $table->date('effective_to')->nullable()->after('effective_from');
                }
            });
        } else {
            Schema::create('benefit_dependents', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('benefit_enrollment_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('family_member_id')->nullable()->index();
                $table->string('name', 150);
                $table->string('relationship', 40); // spouse, child, parent, legal_dependent
                $table->date('date_of_birth')->nullable();
                $table->string('gender', 30)->nullable();
                $table->string('national_id', 80)->nullable();
                $table->boolean('is_eligible')->default(true);
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->json('coverage')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('benefit_enrollment_id')->references('id')->on('benefit_enrollments')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 9. Benefit Beneficiaries
        if (! Schema::hasTable('benefit_beneficiaries')) {
            Schema::create('benefit_beneficiaries', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('benefit_enrollment_id')->nullable()->index();
                $table->uuid('employee_id')->index();
                $table->string('plan_type', 40)->default('life_insurance'); // life_insurance, retirement, provident_fund, pension
                $table->string('name', 150);
                $table->string('relationship', 40);
                $table->decimal('percentage_allocation', 5, 2)->default(100.00); // sum must equal 100%
                $table->string('contact_phone', 40)->nullable();
                $table->string('contact_email', 150)->nullable();
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->boolean('is_primary')->default(true);
                $table->boolean('is_contingent')->default(false);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 10. Benefit Contributions (Period Line Items)
        if (! Schema::hasTable('benefit_contributions')) {
            Schema::create('benefit_contributions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('benefit_enrollment_id')->index();
                $table->uuid('payroll_period_id')->nullable()->index();
                $table->decimal('employee_amount', 19, 4)->default(0);
                $table->decimal('employer_amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->date('contribution_date');
                $table->string('status', 30)->default('calculated'); // calculated, deducted_payroll, posted, waived
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('benefit_enrollment_id')->references('id')->on('benefit_enrollments')->cascadeOnDelete();
            });
        }

        // 11. Benefit Life Events
        if (! Schema::hasTable('benefit_life_events')) {
            Schema::create('benefit_life_events', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('event_type', 40); // marriage, divorce, birth, adoption, death_of_dependent, loss_of_coverage
                $table->date('event_date');
                $table->string('status', 30)->default('submitted'); // submitted, approved, rejected
                $table->text('description')->nullable();
                $table->uuid('document_id')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 12. Insurance Policies (Group Policies)
        if (! Schema::hasTable('insurance_policies')) {
            Schema::create('insurance_policies', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('benefit_provider_id')->index();
                $table->string('policy_number', 100);
                $table->string('policy_name', 150);
                $table->string('insurance_type', 40); // health, life, accidental, dental, vision, critical_illness
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('total_premium', 19, 4)->default(0);
                $table->decimal('max_aggregate_limit', 19, 4)->nullable();
                $table->json('policy_terms')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('benefit_provider_id')->references('id')->on('benefit_providers')->cascadeOnDelete();
            });
        }

        // 13. Insurance Coverages (Individual / Family Coverage Certificates)
        if (! Schema::hasTable('insurance_coverages')) {
            Schema::create('insurance_coverages', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('insurance_policy_id')->index();
                $table->uuid('benefit_enrollment_id')->nullable()->index();
                $table->string('certificate_number', 100)->nullable();
                $table->string('coverage_tier', 40)->default('employee_only');
                $table->decimal('sum_insured', 19, 4)->default(0);
                $table->decimal('employee_premium', 19, 4)->default(0);
                $table->decimal('employer_premium', 19, 4)->default(0);
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('insurance_policy_id')->references('id')->on('insurance_policies')->cascadeOnDelete();
            });
        }

        // 14. Insurance Premiums
        if (! Schema::hasTable('insurance_premiums')) {
            Schema::create('insurance_premiums', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('insurance_coverage_id')->index();
                $table->uuid('payroll_period_id')->nullable()->index();
                $table->decimal('employee_amount', 19, 4)->default(0);
                $table->decimal('employer_amount', 19, 4)->default(0);
                $table->date('due_date');
                $table->string('status', 30)->default('due'); // due, paid, deducted_payroll
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('insurance_coverage_id')->references('id')->on('insurance_coverages')->cascadeOnDelete();
            });
        }

        // 15. Extend or Create Insurance Claims
        if (Schema::hasTable('benefit_claims')) {
            Schema::table('benefit_claims', function (Blueprint $table): void {
                if (! Schema::hasColumn('benefit_claims', 'insurance_policy_id')) {
                    $table->uuid('insurance_policy_id')->nullable()->after('tenant_id')->index();
                }
                if (! Schema::hasColumn('benefit_claims', 'claim_number')) {
                    $table->string('claim_number', 80)->nullable()->after('insurance_policy_id');
                }
                if (! Schema::hasColumn('benefit_claims', 'incident_date')) {
                    $table->date('incident_date')->nullable()->after('claim_type');
                }
                if (! Schema::hasColumn('benefit_claims', 'service_provider_name')) {
                    $table->string('service_provider_name', 150)->nullable()->after('incident_date'); // Hospital / Clinic / Lab
                }
                if (! Schema::hasColumn('benefit_claims', 'currency')) {
                    $table->string('currency', 3)->default('USD')->after('approved_amount');
                }
                if (! Schema::hasColumn('benefit_claims', 'is_sensitive_medical')) {
                    $table->boolean('is_sensitive_medical')->default(true)->after('currency');
                }
                if (! Schema::hasColumn('benefit_claims', 'diagnosis_details')) {
                    $table->text('diagnosis_details')->nullable()->after('is_sensitive_medical');
                }
                if (! Schema::hasColumn('benefit_claims', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('diagnosis_details');
                }
                if (! Schema::hasColumn('benefit_claims', 'settled_at')) {
                    $table->timestamp('settled_at')->nullable()->after('rejection_reason');
                }
                if (! Schema::hasColumn('benefit_claims', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('settled_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('benefit_claims', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        } else {
            Schema::create('benefit_claims', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('insurance_policy_id')->nullable()->index();
                $table->string('claim_number', 80);
                $table->uuid('employee_id')->index();
                $table->uuid('benefit_enrollment_id')->nullable()->index();
                $table->string('claim_type', 40); // inpatient, outpatient, prescription, dental, optical, maternity
                $table->date('incident_date');
                $table->string('service_provider_name', 150)->nullable();
                $table->decimal('claimed_amount', 19, 4);
                $table->decimal('approved_amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->boolean('is_sensitive_medical')->default(true);
                $table->text('diagnosis_details')->nullable();
                $table->string('status', 30)->default('draft'); // draft, submitted, under_review, info_required, approved, partially_approved, rejected, paid, closed
                $table->text('rejection_reason')->nullable();
                $table->json('document_ids')->nullable();
                $table->uuid('workflow_instance_id')->nullable();
                $table->timestamp('settled_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 16. Insurance Claim Lines (Itemized Receipts / Invoices)
        if (! Schema::hasTable('insurance_claim_lines')) {
            Schema::create('insurance_claim_lines', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('benefit_claim_id')->index();
                $table->string('item_description', 200);
                $table->decimal('claimed_amount', 19, 4);
                $table->decimal('approved_amount', 19, 4)->default(0);
                $table->string('invoice_number', 100)->nullable();
                $table->date('invoice_date');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('benefit_claim_id')->references('id')->on('benefit_claims')->cascadeOnDelete();
            });
        }

        // 17. Insurance Claim Documents
        if (! Schema::hasTable('insurance_claim_documents')) {
            Schema::create('insurance_claim_documents', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('benefit_claim_id')->index();
                $table->string('document_type', 50); // medical_bill, receipt, prescription, lab_report, discharge_summary
                $table->string('title', 150);
                $table->string('file_path', 255);
                $table->string('mime_type', 100)->nullable();
                $table->unsignedInteger('file_size')->nullable();
                $table->boolean('is_sensitive')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('benefit_claim_id')->references('id')->on('benefit_claims')->cascadeOnDelete();
            });
        }

        // 18. Retirement Plans
        if (! Schema::hasTable('retirement_plans')) {
            Schema::create('retirement_plans', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('plan_code', 60);
                $table->string('name', 150);
                $table->string('plan_type', 40); // provident_fund, pension, defined_contribution, retirement_savings, gratuity
                $table->string('currency', 3)->default('USD');
                $table->string('provider', 150)->nullable();
                $table->string('country', 3)->default('USA');
                $table->decimal('default_employee_rate', 8, 4)->default(5.00); // 5%
                $table->decimal('default_employer_match_rate', 8, 4)->default(100.00); // 100% of employee rate up to max
                $table->decimal('max_employer_contribution_rate', 8, 4)->default(7.50); // 7.5% cap
                $table->string('contribution_base', 40)->default('basic_salary'); // basic_salary, gross_salary, eligible_earnings
                $table->string('vesting_type', 40)->default('graded'); // immediate, cliff, graded, customized
                $table->unsignedSmallInteger('cliff_months')->default(0);
                $table->boolean('is_mandatory')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('version')->default(1);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'plan_code']);
            });
        }

        // 19. Retirement Plan Versions
        if (! Schema::hasTable('retirement_plan_versions')) {
            Schema::create('retirement_plan_versions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('retirement_plan_id')->index();
                $table->unsignedInteger('version_number');
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->decimal('employee_rate', 8, 4);
                $table->decimal('employer_rate', 8, 4);
                $table->json('vesting_schedule')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('retirement_plan_id')->references('id')->on('retirement_plans')->cascadeOnDelete();
            });
        }

        // 20. Retirement Enrollments
        if (! Schema::hasTable('retirement_enrollments')) {
            Schema::create('retirement_enrollments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('retirement_plan_id')->index();
                $table->date('enrollment_date');
                $table->decimal('employee_contribution_rate', 8, 4)->default(5.00);
                $table->decimal('employer_contribution_rate', 8, 4)->default(7.50);
                $table->decimal('voluntary_additional_amount', 19, 4)->default(0);
                $table->string('status', 30)->default('active'); // active, suspended, closed
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('retirement_plan_id')->references('id')->on('retirement_plans')->cascadeOnDelete();
                $table->unique(['employee_id', 'retirement_plan_id']);
            });
        }

        // 21. Retirement Accounts (Balance Headers)
        if (! Schema::hasTable('retirement_accounts')) {
            Schema::create('retirement_accounts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('retirement_plan_id')->index();
                $table->string('account_number', 80);
                $table->string('currency', 3)->default('USD');
                $table->decimal('opening_balance', 19, 4)->default(0);
                $table->decimal('total_employee_contributions', 19, 4)->default(0);
                $table->decimal('total_employer_contributions', 19, 4)->default(0);
                $table->decimal('total_investment_returns', 19, 4)->default(0);
                $table->decimal('total_withdrawals', 19, 4)->default(0);
                $table->decimal('current_balance', 19, 4)->default(0);
                $table->decimal('vested_balance', 19, 4)->default(0);
                $table->string('status', 30)->default('active'); // active, dormant, settled, closed
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('retirement_plan_id')->references('id')->on('retirement_plans')->cascadeOnDelete();
                $table->unique(['tenant_id', 'account_number']);
            });
        }

        // 22. Retirement Transactions (Immutable Financial Ledger)
        if (! Schema::hasTable('retirement_transactions')) {
            Schema::create('retirement_transactions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('retirement_account_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('transaction_type', 40); // opening, employee_contribution, employer_contribution, employer_match, investment_return, adjustment, withdrawal, transfer, correction, closure
                $table->date('transaction_date');
                $table->decimal('amount', 19, 4);
                $table->decimal('running_balance', 19, 4);
                $table->string('currency', 3)->default('USD');
                $table->string('source_reference_type', 80)->nullable();
                $table->string('source_reference_id', 100)->nullable();
                $table->text('description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('retirement_account_id')->references('id')->on('retirement_accounts')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 23. Retirement Vesting Rules
        if (! Schema::hasTable('retirement_vesting_rules')) {
            Schema::create('retirement_vesting_rules', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('retirement_plan_id')->index();
                $table->unsignedSmallInteger('completed_years');
                $table->decimal('vesting_percentage', 5, 2); // e.g. 20.00%, 40.00%, 100.00%
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('retirement_plan_id')->references('id')->on('retirement_plans')->cascadeOnDelete();
            });
        }

        // 24. Retirement Withdrawals
        if (! Schema::hasTable('retirement_withdrawals')) {
            Schema::create('retirement_withdrawals', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('retirement_account_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('withdrawal_type', 40); // retirement, partial, emergency, transfer, termination
                $table->decimal('requested_amount', 19, 4);
                $table->decimal('approved_amount', 19, 4)->default(0);
                $table->decimal('tax_withheld', 19, 4)->default(0);
                $table->decimal('net_disbursed_amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->string('status', 30)->default('submitted'); // submitted, approved, rejected, disbursed, cancelled
                $table->text('reason')->nullable();
                $table->uuid('workflow_instance_id')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('disbursed_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('retirement_account_id')->references('id')->on('retirement_accounts')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 25. Loan Products
        if (! Schema::hasTable('loan_products')) {
            Schema::create('loan_products', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('loan_type', 40); // personal, emergency, education, vehicle, housing, medical, salary_advance
                $table->string('currency', 3)->default('USD');
                $table->decimal('minimum_amount', 19, 4)->default(100);
                $table->decimal('maximum_amount', 19, 4)->default(50000);
                $table->unsignedSmallInteger('max_installments')->default(36);
                $table->decimal('interest_rate_annual', 6, 4)->default(0); // e.g. 5.0000%
                $table->string('interest_method', 40)->default('reducing_balance'); // flat_rate, reducing_balance, zero_interest, custom_rule
                $table->unsignedSmallInteger('min_service_months')->default(6);
                $table->decimal('max_salary_multiple', 5, 2)->default(3.00); // Max 3x monthly basic salary
                $table->decimal('max_monthly_deduction_ratio', 5, 2)->default(40.00); // Max 40% of net salary
                $table->unsignedSmallInteger('deduction_priority')->default(3);
                $table->boolean('requires_guarantor')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('version')->default(1);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 26. Loan Product Versions
        if (! Schema::hasTable('loan_product_versions')) {
            Schema::create('loan_product_versions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('loan_product_id')->index();
                $table->unsignedInteger('version_number');
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->decimal('interest_rate_annual', 6, 4);
                $table->string('interest_method', 40);
                $table->decimal('maximum_amount', 19, 4);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('loan_product_id')->references('id')->on('loan_products')->cascadeOnDelete();
            });
        }

        // 27. Loan Eligibility Rules
        if (! Schema::hasTable('loan_eligibility_rules')) {
            Schema::create('loan_eligibility_rules', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('loan_product_id')->index();
                $table->string('rule_name', 150);
                $table->json('criteria');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('loan_product_id')->references('id')->on('loan_products')->cascadeOnDelete();
            });
        }

        // 28. Loan Applications
        if (! Schema::hasTable('loan_applications')) {
            Schema::create('loan_applications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('application_number', 80);
                $table->uuid('employee_id')->index();
                $table->uuid('loan_product_id')->index();
                $table->decimal('requested_amount', 19, 4);
                $table->unsignedSmallInteger('requested_tenure_months');
                $table->decimal('approved_amount', 19, 4)->nullable();
                $table->unsignedSmallInteger('approved_tenure_months')->nullable();
                $table->decimal('interest_rate', 6, 4)->nullable();
                $table->string('interest_method', 40)->default('reducing_balance');
                $table->string('currency', 3)->default('USD');
                $table->text('purpose')->nullable();
                $table->string('status', 30)->default('draft'); // draft, submitted, under_review, approved, rejected, cancelled, disbursed, closed
                $table->uuid('workflow_instance_id')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('loan_product_id')->references('id')->on('loan_products')->cascadeOnDelete();
                $table->unique(['tenant_id', 'application_number']);
            });
        }

        // 29. Loan Agreements
        if (! Schema::hasTable('loan_agreements')) {
            Schema::create('loan_agreements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('loan_application_id')->index();
                $table->string('agreement_number', 80);
                $table->decimal('principal_amount', 19, 4);
                $table->decimal('interest_rate', 6, 4);
                $table->decimal('monthly_installment', 19, 4);
                $table->unsignedSmallInteger('tenure_months');
                $table->date('repayment_start_date');
                $table->date('repayment_end_date');
                $table->text('terms_and_conditions')->nullable();
                $table->string('document_reference', 100)->nullable();
                $table->timestamp('signed_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('loan_application_id')->references('id')->on('loan_applications')->cascadeOnDelete();
            });
        }

        // 30. Loan Disbursements
        if (! Schema::hasTable('loan_disbursements')) {
            Schema::create('loan_disbursements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('loan_application_id')->index();
                $table->decimal('disbursed_amount', 19, 4);
                $table->date('disbursement_date');
                $table->string('disbursement_method', 40)->default('bank_transfer');
                $table->string('transaction_reference', 100)->nullable();
                $table->string('bank_account_info', 100)->nullable();
                $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('loan_application_id')->references('id')->on('loan_applications')->cascadeOnDelete();
            });
        }

        // 31. Loan Schedules (Versioned Amortization Plan)
        if (! Schema::hasTable('loan_schedules')) {
            Schema::create('loan_schedules', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('loan_application_id')->index();
                $table->unsignedInteger('schedule_version')->default(1);
                $table->decimal('total_principal', 19, 4);
                $table->decimal('total_interest', 19, 4)->default(0);
                $table->decimal('total_payable', 19, 4);
                $table->decimal('total_paid', 19, 4)->default(0);
                $table->decimal('remaining_balance', 19, 4);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('loan_application_id')->references('id')->on('loan_applications')->cascadeOnDelete();
            });
        }

        // 32. Loan Installments
        if (! Schema::hasTable('loan_installments')) {
            Schema::create('loan_installments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('loan_schedule_id')->index();
                $table->uuid('employee_id')->index();
                $table->unsignedSmallInteger('installment_number');
                $table->date('due_date');
                $table->decimal('principal_amount', 19, 4);
                $table->decimal('interest_amount', 19, 4)->default(0);
                $table->decimal('total_installment', 19, 4);
                $table->decimal('paid_amount', 19, 4)->default(0);
                $table->decimal('balance_remaining', 19, 4);
                $table->string('status', 30)->default('scheduled'); // scheduled, deducted_payroll, direct_paid, missed, waived, refunded
                $table->uuid('payroll_run_id')->nullable()->index();
                $table->date('paid_date')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('loan_schedule_id')->references('id')->on('loan_schedules')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 33. Loan Transactions (Immutable Financial Ledger)
        if (! Schema::hasTable('loan_transactions')) {
            Schema::create('loan_transactions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('loan_application_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('transaction_type', 40); // disbursement, repayment, interest_accrual, extra_payment, waiver, settlement
                $table->date('transaction_date');
                $table->decimal('amount', 19, 4);
                $table->decimal('principal_portion', 19, 4)->default(0);
                $table->decimal('interest_portion', 19, 4)->default(0);
                $table->decimal('running_balance', 19, 4);
                $table->string('source_reference_type', 80)->nullable();
                $table->string('source_reference_id', 100)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('loan_application_id')->references('id')->on('loan_applications')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 34. Loan Restructures
        if (! Schema::hasTable('loan_restructures')) {
            Schema::create('loan_restructures', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('loan_application_id')->index();
                $table->string('restructure_type', 40); // extend_tenure, modify_installment, payment_holiday, rate_reduction
                $table->unsignedInteger('previous_schedule_version');
                $table->unsignedInteger('new_schedule_version');
                $table->decimal('previous_balance', 19, 4);
                $table->decimal('new_balance', 19, 4);
                $table->text('reason');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('loan_application_id')->references('id')->on('loan_applications')->cascadeOnDelete();
            });
        }

        // 35. Loan Settlements
        if (! Schema::hasTable('loan_settlements')) {
            Schema::create('loan_settlements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('loan_application_id')->index();
                $table->decimal('outstanding_principal', 19, 4);
                $table->decimal('outstanding_interest', 19, 4)->default(0);
                $table->decimal('rebate_amount', 19, 4)->default(0);
                $table->decimal('final_settlement_amount', 19, 4);
                $table->date('settlement_date');
                $table->string('payment_method', 40)->default('payroll_deduction');
                $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('loan_application_id')->references('id')->on('loan_applications')->cascadeOnDelete();
            });
        }

        // 36. Salary Advances
        if (! Schema::hasTable('salary_advances')) {
            Schema::create('salary_advances', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('advance_number', 80);
                $table->uuid('employee_id')->index();
                $table->decimal('requested_amount', 19, 4);
                $table->decimal('approved_amount', 19, 4)->default(0);
                $table->unsignedSmallInteger('repayment_months')->default(1); // 1 to 3 months
                $table->date('effective_date');
                $table->text('reason')->nullable();
                $table->string('status', 30)->default('pending'); // pending, approved, rejected, disbursed, recovered, cancelled
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->unique(['tenant_id', 'advance_number']);
            });
        }

        // 37. Salary Advance Schedules
        if (! Schema::hasTable('salary_advance_schedules')) {
            Schema::create('salary_advance_schedules', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('salary_advance_id')->index();
                $table->unsignedSmallInteger('installment_number');
                $table->date('due_date');
                $table->decimal('amount', 19, 4);
                $table->string('status', 30)->default('scheduled'); // scheduled, deducted_payroll, direct_paid, waived
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('salary_advance_id')->references('id')->on('salary_advances')->cascadeOnDelete();
            });
        }

        // 38. Financial Wellness Programs
        if (! Schema::hasTable('financial_wellness_programs')) {
            Schema::create('financial_wellness_programs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('program_type', 40); // retirement_planning, debt_counseling, savings_mastery, tax_planning, budget_coaching
                $table->text('description')->nullable();
                $table->string('partner_organization', 150)->nullable();
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 39. Financial Wellness Resources
        if (! Schema::hasTable('financial_wellness_resources')) {
            Schema::create('financial_wellness_resources', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('financial_wellness_program_id')->nullable()->index();
                $table->string('title', 150);
                $table->string('resource_type', 40); // article, video, calculator, workshop, guide
                $table->text('summary')->nullable();
                $table->string('url_or_path', 255)->nullable();
                $table->boolean('is_featured')->default(false);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('financial_wellness_program_id', 'fk_fin_well_res_prog_id')->references('id')->on('financial_wellness_programs')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_wellness_resources');
        Schema::dropIfExists('financial_wellness_programs');
        Schema::dropIfExists('salary_advance_schedules');
        Schema::dropIfExists('salary_advances');
        Schema::dropIfExists('loan_settlements');
        Schema::dropIfExists('loan_restructures');
        Schema::dropIfExists('loan_transactions');
        Schema::dropIfExists('loan_installments');
        Schema::dropIfExists('loan_schedules');
        Schema::dropIfExists('loan_disbursements');
        Schema::dropIfExists('loan_agreements');
        Schema::dropIfExists('loan_applications');
        Schema::dropIfExists('loan_eligibility_rules');
        Schema::dropIfExists('loan_product_versions');
        Schema::dropIfExists('loan_products');
        Schema::dropIfExists('retirement_withdrawals');
        Schema::dropIfExists('retirement_vesting_rules');
        Schema::dropIfExists('retirement_transactions');
        Schema::dropIfExists('retirement_accounts');
        Schema::dropIfExists('retirement_enrollments');
        Schema::dropIfExists('retirement_plan_versions');
        Schema::dropIfExists('retirement_plans');
        Schema::dropIfExists('insurance_claim_documents');
        Schema::dropIfExists('insurance_claim_lines');
        Schema::dropIfExists('insurance_premiums');
        Schema::dropIfExists('insurance_coverages');
        Schema::dropIfExists('insurance_policies');
        Schema::dropIfExists('benefit_life_events');
        Schema::dropIfExists('benefit_contributions');
        Schema::dropIfExists('benefit_beneficiaries');
        Schema::dropIfExists('benefit_plan_versions');
        Schema::dropIfExists('benefit_eligibility_rules');
        Schema::dropIfExists('benefit_enrollment_windows');
        Schema::dropIfExists('benefit_providers');
        Schema::dropIfExists('benefit_categories');
    }
};
