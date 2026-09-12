<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Benefit Programs
        if (! Schema::hasTable('benefit_programs')) {
            Schema::create('benefit_programs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('category', 50)->default('health_medical'); // health_medical, employee_protection, retirement, insurance, wellness, financial, transportation, meal, education, other
                $table->string('status', 30)->default('active'); // active, inactive, draft
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 2. Benefit Program Versions
        if (! Schema::hasTable('benefit_program_versions')) {
            Schema::create('benefit_program_versions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('benefit_program_id')->index();
                $table->unsignedInteger('version_number');
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->json('configuration')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('benefit_program_id')->references('id')->on('benefit_programs')->cascadeOnDelete();
            });
        }

        // 3. Extend Benefit Plans
        if (Schema::hasTable('benefit_plans')) {
            Schema::table('benefit_plans', function (Blueprint $table): void {
                if (! Schema::hasColumn('benefit_plans', 'benefit_program_id')) {
                    $table->uuid('benefit_program_id')->nullable()->after('tenant_id')->index();
                    $table->foreign('benefit_program_id')->references('id')->on('benefit_programs')->nullOnDelete();
                }
                if (! Schema::hasColumn('benefit_plans', 'is_mandatory')) {
                    $table->boolean('is_mandatory')->default(false)->after('status');
                }
                if (! Schema::hasColumn('benefit_plans', 'is_waivable')) {
                    $table->boolean('is_waivable')->default(true)->after('is_mandatory');
                }
                if (! Schema::hasColumn('benefit_plans', 'min_coverage')) {
                    $table->decimal('min_coverage', 19, 4)->nullable()->after('is_waivable');
                }
                if (! Schema::hasColumn('benefit_plans', 'max_coverage')) {
                    $table->decimal('max_coverage', 19, 4)->nullable()->after('min_coverage');
                }
                if (! Schema::hasColumn('benefit_plans', 'enrollment_type')) {
                    $table->string('enrollment_type', 40)->default('open_and_life_event')->after('max_coverage');
                }
            });
        }

        // 4. Benefit Coverages / Coverage Tiers
        if (! Schema::hasTable('benefit_coverages')) {
            Schema::create('benefit_coverages', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('benefit_plan_id')->index();
                $table->string('code', 50);
                $table->string('name', 100); // employee_only, employee_plus_spouse, employee_plus_children, family, custom
                $table->decimal('coverage_multiplier', 8, 4)->default(1.0000);
                $table->decimal('employee_cost_factor', 8, 4)->default(1.0000);
                $table->decimal('employer_cost_factor', 8, 4)->default(1.0000);
                $table->decimal('fixed_employee_cost', 19, 4)->nullable();
                $table->decimal('fixed_employer_cost', 19, 4)->nullable();
                $table->unsignedSmallInteger('max_dependents')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('benefit_plan_id')->references('id')->on('benefit_plans')->cascadeOnDelete();
                $table->unique(['tenant_id', 'benefit_plan_id', 'code']);
            });
        }

        // 5. Benefit Life Event Types (Configuration)
        if (! Schema::hasTable('benefit_life_event_types')) {
            Schema::create('benefit_life_event_types', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->unsignedSmallInteger('notification_window_days')->default(30);
                $table->unsignedSmallInteger('election_window_days')->default(30);
                $table->unsignedSmallInteger('documentation_deadline_days')->default(30);
                $table->boolean('requires_document')->default(true);
                $table->string('effective_date_rule', 50)->default('event_date'); // event_date, first_of_following_month, immediate
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 6. Extend Benefit Life Events
        if (Schema::hasTable('benefit_life_events')) {
            Schema::table('benefit_life_events', function (Blueprint $table): void {
                if (! Schema::hasColumn('benefit_life_events', 'life_event_type_id')) {
                    $table->uuid('life_event_type_id')->nullable()->after('employee_id')->index();
                    $table->foreign('life_event_type_id')->references('id')->on('benefit_life_event_types')->nullOnDelete();
                }
                if (! Schema::hasColumn('benefit_life_events', 'election_window_end')) {
                    $table->date('election_window_end')->nullable()->after('event_date');
                }
                if (! Schema::hasColumn('benefit_life_events', 'documentation_deadline')) {
                    $table->date('documentation_deadline')->nullable()->after('election_window_end');
                }
                if (! Schema::hasColumn('benefit_life_events', 'documentation_status')) {
                    $table->string('documentation_status', 30)->default('pending')->after('documentation_deadline');
                }
                if (! Schema::hasColumn('benefit_life_events', 'affected_plans')) {
                    $table->json('affected_plans')->nullable()->after('documentation_status');
                }
            });
        }

        // 7. Benefit Elections
        if (! Schema::hasTable('benefit_elections')) {
            Schema::create('benefit_elections', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('benefit_plan_id')->index();
                $table->uuid('benefit_enrollment_window_id')->nullable()->index();
                $table->uuid('benefit_life_event_id')->nullable()->index();
                $table->uuid('benefit_coverage_id')->nullable()->index();
                $table->string('coverage_level', 50)->default('employee_only');
                $table->date('election_date');
                $table->date('effective_date');
                $table->decimal('employee_cost_estimated', 19, 4)->default(0);
                $table->decimal('employer_cost_estimated', 19, 4)->default(0);
                $table->decimal('total_cost_estimated', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->string('status', 30)->default('elected'); // elected, waived, confirmed, approved, rejected, cancelled
                $table->boolean('is_waived')->default(false);
                $table->text('waiver_reason')->nullable();
                $table->uuid('supporting_document_id')->nullable();
                $table->json('selected_dependents')->nullable();
                $table->json('beneficiaries_data')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('benefit_plan_id')->references('id')->on('benefit_plans')->cascadeOnDelete();
                $table->foreign('benefit_enrollment_window_id')->references('id')->on('benefit_enrollment_windows')->nullOnDelete();
                $table->foreign('benefit_life_event_id')->references('id')->on('benefit_life_events')->nullOnDelete();
                $table->foreign('benefit_coverage_id')->references('id')->on('benefit_coverages')->nullOnDelete();
            });
        }

        // 8. Benefit Waivers
        if (! Schema::hasTable('benefit_waivers')) {
            Schema::create('benefit_waivers', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('benefit_plan_id')->index();
                $table->uuid('benefit_enrollment_window_id')->nullable()->index();
                $table->string('reason', 255);
                $table->uuid('supporting_document_id')->nullable();
                $table->date('waiver_date');
                $table->string('status', 30)->default('submitted'); // submitted, approved, rejected
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('benefit_plan_id')->references('id')->on('benefit_plans')->cascadeOnDelete();
            });
        }

        // 9. Benefit Provider Mappings
        if (! Schema::hasTable('benefit_provider_mappings')) {
            Schema::create('benefit_provider_mappings', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('benefit_provider_id')->index();
                $table->uuid('benefit_plan_id')->index();
                $table->string('external_plan_code', 100);
                $table->string('external_plan_name', 150)->nullable();
                $table->string('policy_number', 100)->nullable();
                $table->string('group_number', 100)->nullable();
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->string('sync_status', 30)->default('synced'); // synced, pending, failed
                $table->timestamp('last_exported_at')->nullable();
                $table->json('mapping_metadata')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('benefit_provider_id')->references('id')->on('benefit_providers')->cascadeOnDelete();
                $table->foreign('benefit_plan_id')->references('id')->on('benefit_plans')->cascadeOnDelete();
                $table->unique(['tenant_id', 'benefit_provider_id', 'benefit_plan_id'], 'provider_plan_unique');
            });
        }

        // 10. Benefit Integrations Log (Orchestration with Provider & Payroll)
        if (! Schema::hasTable('benefit_integrations')) {
            Schema::create('benefit_integrations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('integration_type', 40); // provider, payroll
                $table->string('target_name', 150);
                $table->string('reference_type', 80)->nullable();
                $table->string('reference_id', 100)->nullable();
                $table->string('direction', 20)->default('export'); // export, import
                $table->string('status', 30)->default('pending'); // pending, sent, received, accepted, rejected, applied
                $table->json('payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 11. Benefit Statements
        if (! Schema::hasTable('benefit_statements')) {
            Schema::create('benefit_statements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->unsignedSmallInteger('statement_year');
                $table->date('statement_date');
                $table->decimal('total_employer_cost', 19, 4)->default(0);
                $table->decimal('total_employee_cost', 19, 4)->default(0);
                $table->decimal('total_benefit_value', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->json('statement_data')->nullable(); // breakdown by health, life, retirement, wellness, perks
                $table->string('status', 30)->default('published'); // draft, published, archived
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->unique(['tenant_id', 'employee_id', 'statement_year']);
            });
        }

        // 12. Benefit Eligibility Results (Historical Evaluation Audit)
        if (! Schema::hasTable('benefit_eligibility_results')) {
            Schema::create('benefit_eligibility_results', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('benefit_plan_id')->index();
                $table->date('evaluation_date');
                $table->string('status', 30)->default('eligible'); // eligible, not_eligible, pending, requires_review
                $table->text('reason')->nullable();
                $table->json('criteria_evaluation')->nullable();
                $table->date('effective_date')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('benefit_plan_id')->references('id')->on('benefit_plans')->cascadeOnDelete();
            });
        }

        // 13. Benefit Reconciliations (Benefits Planned vs Payroll Actuals)
        if (! Schema::hasTable('benefit_reconciliations')) {
            Schema::create('benefit_reconciliations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_period_id')->nullable()->index();
                $table->uuid('benefit_enrollment_id')->nullable()->index();
                $table->uuid('employee_id')->index();
                $table->decimal('benefit_amount', 19, 4)->default(0);
                $table->decimal('payroll_deduction_amount', 19, 4)->default(0);
                $table->decimal('variance', 19, 4)->default(0);
                $table->string('status', 30)->default('matched'); // matched, missing_in_payroll, amount_mismatch, rejected, pending
                $table->timestamp('reconciled_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('benefit_enrollment_id')->references('id')->on('benefit_enrollments')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('benefit_reconciliations');
        Schema::dropIfExists('benefit_eligibility_results');
        Schema::dropIfExists('benefit_statements');
        Schema::dropIfExists('benefit_integrations');
        Schema::dropIfExists('benefit_provider_mappings');
        Schema::dropIfExists('benefit_waivers');
        Schema::dropIfExists('benefit_elections');
        Schema::dropIfExists('benefit_coverages');
        Schema::dropIfExists('benefit_life_event_types');
        Schema::dropIfExists('benefit_program_versions');
        Schema::dropIfExists('benefit_programs');
    }
};
