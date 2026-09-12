<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Payroll Legal Entities
        if (! Schema::hasTable('payroll_legal_entities')) {
            Schema::create('payroll_legal_entities', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('company_id')->nullable()->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('currency', 3)->default('USD');
                $table->string('country', 3)->default('USA');
                $table->string('region', 60)->nullable();
                $table->string('timezone', 50)->default('UTC');
                $table->string('default_pay_frequency', 30)->default('monthly'); // monthly, biweekly, weekly, semi_monthly
                $table->json('tax_configuration')->nullable();
                $table->json('bank_configuration')->nullable();
                $table->json('accounting_configuration')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 2. Payroll Calendars
        if (! Schema::hasTable('payroll_calendars')) {
            Schema::create('payroll_calendars', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_legal_entity_id')->nullable()->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('frequency', 30)->default('monthly'); // monthly, biweekly, weekly, semi_monthly
                $table->unsignedSmallInteger('period_start_day')->default(1); // e.g. 1st of month
                $table->unsignedSmallInteger('cutoff_day_offset')->default(25); // e.g. 25th of month
                $table->unsignedSmallInteger('pay_day_offset')->default(5); // e.g. 5th of next month
                $table->text('description')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_legal_entity_id')->references('id')->on('payroll_legal_entities')->nullOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 3. Extend or Create Payroll Periods
        if (Schema::hasTable('payroll_periods')) {
            Schema::table('payroll_periods', function (Blueprint $table): void {
                if (! Schema::hasColumn('payroll_periods', 'payroll_calendar_id')) {
                    $table->uuid('payroll_calendar_id')->nullable()->after('tenant_id')->index();
                }
                if (! Schema::hasColumn('payroll_periods', 'payroll_legal_entity_id')) {
                    $table->uuid('payroll_legal_entity_id')->nullable()->after('payroll_calendar_id')->index();
                }
                if (! Schema::hasColumn('payroll_periods', 'period_name')) {
                    $table->string('period_name', 150)->nullable()->after('payroll_legal_entity_id');
                }
                if (! Schema::hasColumn('payroll_periods', 'currency')) {
                    $table->string('currency', 3)->default('USD')->after('end_date');
                }
                if (! Schema::hasColumn('payroll_periods', 'lock_date')) {
                    $table->timestamp('lock_date')->nullable()->after('payment_date');
                }
                if (! Schema::hasColumn('payroll_periods', 'locked_by')) {
                    $table->foreignId('locked_by')->nullable()->after('lock_date')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('payroll_periods', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('payroll_periods', 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('payroll_periods', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        } else {
            Schema::create('payroll_periods', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_calendar_id')->nullable()->index();
                $table->uuid('payroll_legal_entity_id')->nullable()->index();
                $table->string('period_name', 150);
                $table->date('start_date');
                $table->date('end_date');
                $table->date('cutoff_date')->nullable();
                $table->date('payment_date')->nullable();
                $table->string('currency', 3)->default('USD');
                $table->string('status', 30)->default('open')->index();
                $table->timestamp('lock_date')->nullable();
                $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_calendar_id')->references('id')->on('payroll_calendars')->nullOnDelete();
                $table->foreign('payroll_legal_entity_id')->references('id')->on('payroll_legal_entities')->nullOnDelete();
            });
        }

        // 4. Extend or Create Payroll Runs
        if (Schema::hasTable('payroll_runs')) {
            Schema::table('payroll_runs', function (Blueprint $table): void {
                if (! Schema::hasColumn('payroll_runs', 'run_number')) {
                    $table->string('run_number', 60)->nullable()->after('payroll_period_id');
                }
                if (! Schema::hasColumn('payroll_runs', 'name')) {
                    $table->string('name', 150)->nullable()->after('run_number');
                }
                if (! Schema::hasColumn('payroll_runs', 'run_type')) {
                    $table->string('run_type', 30)->default('regular')->after('name'); // regular, off_cycle, final_settlement, bonus, correction
                }
                if (! Schema::hasColumn('payroll_runs', 'payroll_legal_entity_id')) {
                    $table->uuid('payroll_legal_entity_id')->nullable()->after('run_type')->index();
                }
                if (! Schema::hasColumn('payroll_runs', 'currency')) {
                    $table->string('currency', 3)->default('USD')->after('payroll_legal_entity_id');
                }
                if (! Schema::hasColumn('payroll_runs', 'employee_count')) {
                    $table->unsignedInteger('employee_count')->default(0)->after('currency');
                }
                if (! Schema::hasColumn('payroll_runs', 'earnings_total')) {
                    $table->decimal('earnings_total', 19, 4)->default(0)->after('gross_total');
                }
                if (! Schema::hasColumn('payroll_runs', 'tax_total')) {
                    $table->decimal('tax_total', 19, 4)->default(0)->after('deduction_total');
                }
                if (! Schema::hasColumn('payroll_runs', 'employer_cost_total')) {
                    $table->decimal('employer_cost_total', 19, 4)->default(0)->after('tax_total');
                }
                if (! Schema::hasColumn('payroll_runs', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('locked_at');
                }
                if (! Schema::hasColumn('payroll_runs', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('payroll_runs', 'calculated_at')) {
                    $table->timestamp('calculated_at')->nullable()->after('approved_by');
                }
                if (! Schema::hasColumn('payroll_runs', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('calculated_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('payroll_runs', 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('payroll_runs', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        } else {
            Schema::create('payroll_runs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_period_id')->index();
                $table->string('run_number', 60);
                $table->string('name', 150);
                $table->string('run_type', 30)->default('regular');
                $table->uuid('payroll_legal_entity_id')->nullable()->index();
                $table->string('currency', 3)->default('USD');
                $table->string('status', 30)->default('draft')->index();
                $table->unsignedInteger('employee_count')->default(0);
                $table->decimal('gross_total', 19, 4)->default(0);
                $table->decimal('earnings_total', 19, 4)->default(0);
                $table->decimal('deduction_total', 19, 4)->default(0);
                $table->decimal('tax_total', 19, 4)->default(0);
                $table->decimal('employer_cost_total', 19, 4)->default(0);
                $table->decimal('net_total', 19, 4)->default(0);
                $table->timestamp('calculated_at')->nullable();
                $table->timestamp('locked_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('posted_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->cascadeOnDelete();
            });
        }

        // 5. Extend or Create Compensation Components
        if (Schema::hasTable('compensation_components')) {
            // Already created or extend
        } elseif (Schema::hasTable('payroll_components')) {
            // Rename or alias: create compensation_components view/table
            Schema::create('compensation_components', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('component_type', 40); // basic, allowance, benefit, bonus, commission, overtime, reimbursement, deduction, tax, pension, employer_contribution, loan, advance, adjustment, arrear
                $table->string('calculation_type', 40)->default('fixed'); // fixed, percentage_of_basic, percentage_of_gross, formula, hourly_rate, attendance_based, custom_rule
                $table->decimal('default_amount', 19, 4)->nullable();
                $table->decimal('percentage', 8, 4)->nullable();
                $table->string('formula', 255)->nullable();
                $table->boolean('is_taxable')->default(true);
                $table->boolean('is_pensionable')->default(false);
                $table->boolean('is_overtime_eligible')->default(false);
                $table->boolean('is_recurring')->default(true);
                $table->boolean('is_statutory')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('priority_order')->default(10);
                $table->text('description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        } else {
            Schema::create('compensation_components', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('component_type', 40);
                $table->string('calculation_type', 40)->default('fixed');
                $table->decimal('default_amount', 19, 4)->nullable();
                $table->decimal('percentage', 8, 4)->nullable();
                $table->string('formula', 255)->nullable();
                $table->boolean('is_taxable')->default(true);
                $table->boolean('is_pensionable')->default(false);
                $table->boolean('is_overtime_eligible')->default(false);
                $table->boolean('is_recurring')->default(true);
                $table->boolean('is_statutory')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('priority_order')->default(10);
                $table->text('description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 6. Extend or Create Compensation Structures
        if (Schema::hasTable('compensation_structures')) {
            // Already created
        } else {
            Schema::create('compensation_structures', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('currency', 3)->default('USD');
                $table->text('description')->nullable();
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

        // 7. Compensation Structure Components
        if (! Schema::hasTable('compensation_structure_components')) {
            Schema::create('compensation_structure_components', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('compensation_structure_id')->index();
                $table->uuid('compensation_component_id')->index();
                $table->string('calculation_type', 40)->default('fixed');
                $table->decimal('default_amount', 19, 4)->nullable();
                $table->decimal('percentage', 8, 4)->nullable();
                $table->string('formula', 255)->nullable();
                $table->unsignedSmallInteger('sequence')->default(1);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('compensation_structure_id')->references('id')->on('compensation_structures')->cascadeOnDelete();
                $table->foreign('compensation_component_id')->references('id')->on('compensation_components')->cascadeOnDelete();
            });
        }

        // 8. Employee Compensations (Header)
        if (! Schema::hasTable('employee_compensations')) {
            Schema::create('employee_compensations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('compensation_structure_id')->nullable()->index();
                $table->string('currency', 3)->default('USD');
                $table->string('pay_frequency', 30)->default('monthly');
                $table->decimal('base_salary', 19, 4)->default(0);
                $table->decimal('gross_salary', 19, 4)->default(0);
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->string('reason_for_change', 100)->nullable(); // hire, merit_increase, promotion, transfer, market_adjustment, correction
                $table->string('status', 30)->default('approved'); // draft, pending_approval, approved, superseded
                $table->boolean('is_active')->default(true);
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('compensation_structure_id')->references('id')->on('compensation_structures')->nullOnDelete();
            });
        }

        // 9. Employee Compensation Components (Line Items)
        if (! Schema::hasTable('employee_compensation_components')) {
            Schema::create('employee_compensation_components', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_compensation_id')->index();
                $table->uuid('compensation_component_id')->index();
                $table->string('calculation_type', 40)->default('fixed');
                $table->decimal('amount', 19, 4)->default(0);
                $table->decimal('percentage', 8, 4)->nullable();
                $table->string('formula', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_compensation_id')->references('id')->on('employee_compensations')->cascadeOnDelete();
                $table->foreign('compensation_component_id')->references('id')->on('compensation_components')->cascadeOnDelete();
            });
        }

        // 10. Payroll Inputs
        if (! Schema::hasTable('payroll_inputs')) {
            Schema::create('payroll_inputs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_period_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('status', 30)->default('collected'); // collected, validated, processed, rejected
                $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('collected_at')->useCurrent();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->unique(['payroll_period_id', 'employee_id']);
            });
        }

        // 11. Payroll Input Lines
        if (! Schema::hasTable('payroll_input_lines')) {
            Schema::create('payroll_input_lines', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_input_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('source_module', 60); // attendance, leave, benefits, loans, advances, bonuses, commissions, manual_adjustment
                $table->string('source_entity_type', 80)->nullable();
                $table->string('source_entity_id', 100)->nullable();
                $table->string('input_type', 50); // regular_hours, overtime_hours, unpaid_leave_days, loan_installment, bonus, adjustment
                $table->decimal('quantity', 12, 4)->default(1);
                $table->decimal('rate', 19, 4)->nullable();
                $table->decimal('amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->date('effective_date');
                $table->string('approval_status', 30)->default('approved'); // pending, approved, rejected
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_input_id')->references('id')->on('payroll_inputs')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 12. Payroll Earnings
        if (! Schema::hasTable('payroll_earnings')) {
            Schema::create('payroll_earnings', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_run_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('compensation_component_id')->nullable()->index();
                $table->string('earning_code', 60);
                $table->string('earning_name', 150);
                $table->string('earning_type', 40)->default('basic'); // basic, allowance, overtime, bonus, commission, arrear, reimbursement, adjustment
                $table->decimal('rate', 19, 4)->nullable();
                $table->decimal('quantity', 12, 4)->default(1);
                $table->decimal('amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->boolean('is_taxable')->default(true);
                $table->boolean('is_pensionable')->default(false);
                $table->string('calculation_source', 100)->nullable(); // salary_structure, overtime_record, bonus_record, adjustment
                $table->string('source_reference_id', 100)->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 13. Payroll Deductions
        if (! Schema::hasTable('payroll_deductions')) {
            Schema::create('payroll_deductions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_run_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('compensation_component_id')->nullable()->index();
                $table->string('deduction_code', 60);
                $table->string('deduction_name', 150);
                $table->string('deduction_type', 40); // tax, loan, advance, insurance, pension, unpaid_leave, benefit, other
                $table->decimal('amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->unsignedSmallInteger('priority_order')->default(10);
                $table->string('calculation_source', 100)->nullable();
                $table->string('source_reference_id', 100)->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 14. Payroll Taxes
        if (! Schema::hasTable('payroll_taxes')) {
            Schema::create('payroll_taxes', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_run_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('tax_code', 60);
                $table->string('tax_name', 150);
                $table->string('tax_rule_version', 50)->nullable();
                $table->decimal('taxable_income', 19, 4)->default(0);
                $table->decimal('exemptions', 19, 4)->default(0);
                $table->decimal('tax_amount', 19, 4)->default(0);
                $table->decimal('rebate_amount', 19, 4)->default(0);
                $table->decimal('net_tax_deducted', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->json('tax_bracket_breakdown')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 15. Payroll Employer Contributions
        if (! Schema::hasTable('payroll_employer_contributions')) {
            Schema::create('payroll_employer_contributions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_run_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('contribution_code', 60);
                $table->string('contribution_name', 150);
                $table->string('contribution_type', 40); // pension, social_security, insurance, medical
                $table->decimal('base_amount', 19, 4)->default(0);
                $table->decimal('rate_percentage', 8, 4)->nullable();
                $table->decimal('amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 16. Payroll Calculation Snapshots
        if (! Schema::hasTable('payroll_calculation_snapshots')) {
            Schema::create('payroll_calculation_snapshots', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_run_id')->index();
                $table->uuid('employee_id')->index();
                $table->decimal('gross_pay', 19, 4)->default(0);
                $table->decimal('total_deductions', 19, 4)->default(0);
                $table->decimal('total_tax', 19, 4)->default(0);
                $table->decimal('total_employer_cost', 19, 4)->default(0);
                $table->decimal('net_pay', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->string('rounding_method', 30)->default('half_up');
                $table->json('employee_snapshot');
                $table->json('compensation_snapshot');
                $table->json('inputs_snapshot');
                $table->json('earnings_snapshot');
                $table->json('deductions_snapshot');
                $table->json('taxes_snapshot');
                $table->json('employer_contributions_snapshot');
                $table->string('tax_rule_version', 50)->nullable();
                $table->string('policy_version', 50)->nullable();
                $table->string('idempotency_key', 128)->index();
                $table->timestamp('calculated_at')->useCurrent();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->unique(['payroll_run_id', 'employee_id']);
            });
        }

        // 17. Payroll Calculation Lines (For Fast Reporting/Rollups)
        if (! Schema::hasTable('payroll_calculation_lines')) {
            Schema::create('payroll_calculation_lines', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_calculation_snapshot_id')->index();
                $table->uuid('payroll_run_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('line_category', 30); // earning, deduction, tax, employer_contribution
                $table->string('line_code', 60);
                $table->string('line_name', 150);
                $table->decimal('amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_calculation_snapshot_id')->references('id')->on('payroll_calculation_snapshots')->cascadeOnDelete();
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 18. Payroll Bonuses
        if (! Schema::hasTable('payroll_bonuses')) {
            Schema::create('payroll_bonuses', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('bonus_type', 40); // performance, annual, festival, retention, spot, manual
                $table->string('title', 150);
                $table->decimal('amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->date('effective_date');
                $table->uuid('payroll_period_id')->nullable()->index();
                $table->string('status', 30)->default('approved'); // pending, approved, paid, rejected
                $table->text('reason')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->nullOnDelete();
            });
        }

        // 19. Payroll Arrears
        if (! Schema::hasTable('payroll_arrears')) {
            Schema::create('payroll_arrears', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('title', 150);
                $table->date('origin_start_date');
                $table->date('origin_end_date');
                $table->decimal('previous_amount', 19, 4)->default(0);
                $table->decimal('revised_amount', 19, 4)->default(0);
                $table->decimal('difference_amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->uuid('payroll_period_id')->nullable()->index();
                $table->string('status', 30)->default('pending'); // pending, approved, processed, cancelled
                $table->text('reason')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->nullOnDelete();
            });
        }

        // 20. Payroll Loan Repayments
        if (! Schema::hasTable('payroll_loan_repayments')) {
            Schema::create('payroll_loan_repayments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('employee_loan_id')->nullable()->index();
                $table->uuid('payroll_run_id')->nullable()->index();
                $table->decimal('installment_amount', 19, 4)->default(0);
                $table->decimal('principal_amount', 19, 4)->default(0);
                $table->decimal('interest_amount', 19, 4)->default(0);
                $table->decimal('remaining_balance', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->date('payment_date');
                $table->string('status', 30)->default('deducted'); // scheduled, deducted, waived
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 21. Payroll Advance Repayments
        if (! Schema::hasTable('payroll_advance_repayments')) {
            Schema::create('payroll_advance_repayments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('payroll_run_id')->nullable()->index();
                $table->string('advance_reference', 80);
                $table->decimal('original_advance_amount', 19, 4)->default(0);
                $table->decimal('installment_amount', 19, 4)->default(0);
                $table->decimal('remaining_balance', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->date('deduction_date');
                $table->string('status', 30)->default('deducted');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 22. Payroll Adjustments
        if (! Schema::hasTable('payroll_adjustments')) {
            Schema::create('payroll_adjustments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('payroll_period_id')->nullable()->index();
                $table->string('adjustment_type', 30); // earning, deduction, arrear, reimbursement
                $table->string('code', 60);
                $table->string('title', 150);
                $table->decimal('amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->date('effective_date');
                $table->text('reason');
                $table->string('status', 30)->default('pending')->index(); // pending, approved, rejected, applied
                $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->nullOnDelete();
            });
        }

        // 23. Payroll Variances
        if (! Schema::hasTable('payroll_variances')) {
            Schema::create('payroll_variances', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_run_id')->index();
                $table->uuid('employee_id')->index();
                $table->decimal('current_gross', 19, 4)->default(0);
                $table->decimal('previous_gross', 19, 4)->default(0);
                $table->decimal('gross_variance_amount', 19, 4)->default(0);
                $table->decimal('gross_variance_percentage', 8, 4)->default(0);
                $table->decimal('current_net', 19, 4)->default(0);
                $table->decimal('previous_net', 19, 4)->default(0);
                $table->decimal('net_variance_amount', 19, 4)->default(0);
                $table->string('variance_type', 40)->default('normal'); // normal, large_increase, large_decrease, new_hire, termination, unexpected_bonus, unexpected_deduction
                $table->boolean('is_flagged')->default(false);
                $table->text('explanation')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 24. Payroll Exceptions
        if (! Schema::hasTable('payroll_exceptions')) {
            Schema::create('payroll_exceptions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_run_id')->index();
                $table->uuid('employee_id')->nullable()->index();
                $table->string('exception_type', 50); // negative_net, missing_salary, unapproved_overtime, large_variance, missing_bank_account, inactive_employee, tax_rule_missing
                $table->string('severity', 20)->default('warning'); // blocking, warning, informational
                $table->text('message');
                $table->boolean('is_resolved')->default(false);
                $table->text('resolution_notes')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
            });
        }

        // 25. Payroll Payslips
        if (! Schema::hasTable('payroll_payslips')) {
            Schema::create('payroll_payslips', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_run_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('payroll_calculation_snapshot_id')->index();
                $table->string('payslip_number', 80);
                $table->date('pay_date');
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('gross_pay', 19, 4)->default(0);
                $table->decimal('total_deductions', 19, 4)->default(0);
                $table->decimal('total_tax', 19, 4)->default(0);
                $table->decimal('net_pay', 19, 4)->default(0);
                $table->decimal('ytd_gross', 19, 4)->default(0);
                $table->decimal('ytd_tax', 19, 4)->default(0);
                $table->decimal('ytd_net', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->string('masked_bank_account', 50)->nullable();
                $table->string('pdf_document_id', 100)->nullable();
                $table->boolean('is_published')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->timestamp('viewed_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('payroll_calculation_snapshot_id')->references('id')->on('payroll_calculation_snapshots')->cascadeOnDelete();
                $table->unique(['tenant_id', 'payslip_number']);
            });
        }

        // 26. Payroll Payment Batches
        if (! Schema::hasTable('payroll_payment_batches')) {
            Schema::create('payroll_payment_batches', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_run_id')->index();
                $table->string('batch_number', 60);
                $table->string('payment_method', 40)->default('bank_transfer'); // bank_transfer, direct_deposit, cheque, cash
                $table->string('bank_format', 50)->default('standard_csv'); // standard_csv, nacha, sepa, iso20022
                $table->string('currency', 3)->default('USD');
                $table->unsignedInteger('total_records')->default(0);
                $table->decimal('total_amount', 19, 4)->default(0);
                $table->string('status', 30)->default('draft')->index(); // draft, generated, submitted, processing, paid, failed, reconciled
                $table->text('export_file_path')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
                $table->unique(['tenant_id', 'batch_number']);
            });
        }

        // 27. Payroll Payment Lines
        if (! Schema::hasTable('payroll_payment_lines')) {
            Schema::create('payroll_payment_lines', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_payment_batch_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('payroll_payslip_id')->nullable()->index();
                $table->string('bank_name', 100)->nullable();
                $table->string('routing_number', 50)->nullable();
                $table->string('masked_account_number', 50);
                $table->string('account_holder_name', 150);
                $table->decimal('amount', 19, 4)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->string('payment_status', 30)->default('pending'); // pending, processed, failed, reconciled
                $table->string('transaction_reference', 100)->nullable();
                $table->text('failure_reason')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_payment_batch_id')->references('id')->on('payroll_payment_batches')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 28. Payroll Accounting Exports
        if (! Schema::hasTable('payroll_accounting_exports')) {
            Schema::create('payroll_accounting_exports', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_run_id')->index();
                $table->string('journal_voucher_number', 80);
                $table->date('entry_date');
                $table->string('currency', 3)->default('USD');
                $table->decimal('total_debits', 19, 4)->default(0);
                $table->decimal('total_credits', 19, 4)->default(0);
                $table->json('distribution_lines'); // debits (salary expense, allowances, employer cost) & credits (payable, taxes, loans)
                $table->string('status', 30)->default('draft'); // draft, exported, posted, failed
                $table->timestamp('exported_at')->nullable();
                $table->foreignId('exported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
                $table->unique(['tenant_id', 'journal_voucher_number']);
            });
        }

        // 29. Payroll Tax Rules & Versions
        if (! Schema::hasTable('payroll_tax_rules')) {
            Schema::create('payroll_tax_rules', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('country', 3)->default('USA');
                $table->string('region', 60)->nullable();
                $table->string('calculation_mode', 40)->default('progressive_brackets'); // progressive_brackets, flat_rate, annualized, exempt
                $table->decimal('flat_rate_percentage', 8, 4)->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        if (! Schema::hasTable('payroll_tax_rule_versions')) {
            Schema::create('payroll_tax_rule_versions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_tax_rule_id')->index();
                $table->string('version_name', 50); // e.g. 2026.01
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->decimal('standard_exemption', 19, 4)->default(0);
                $table->json('tax_brackets'); // [{ min: 0, max: 10000, rate: 0.05 }, { min: 10000, max: 50000, rate: 0.15 }, ...]
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_tax_rule_id')->references('id')->on('payroll_tax_rules')->cascadeOnDelete();
            });
        }

        // 30. Payroll Policies
        if (! Schema::hasTable('payroll_policies')) {
            Schema::create('payroll_policies', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('proration_method', 40)->default('calendar_days'); // calendar_days, working_days, fixed_30_days, actual_hours, none
                $table->string('rounding_method', 30)->default('half_up'); // half_up, half_down, bankers, floor, ceil
                $table->decimal('overtime_rate_multiplier', 5, 2)->default(1.50);
                $table->decimal('weekend_overtime_multiplier', 5, 2)->default(2.00);
                $table->decimal('holiday_overtime_multiplier', 5, 2)->default(2.50);
                $table->decimal('variance_threshold_percentage', 5, 2)->default(10.00); // flag if > 10%
                $table->json('deduction_priority_order')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 31. Payroll Period Locks (Audit Log)
        if (! Schema::hasTable('payroll_period_locks')) {
            Schema::create('payroll_period_locks', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('payroll_period_id')->index();
                $table->string('action', 30); // locked, reopened
                $table->string('previous_status', 30);
                $table->string('new_status', 30);
                $table->text('reason')->nullable();
                $table->foreignId('acted_by')->constrained('users')->cascadeOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_period_locks');
        Schema::dropIfExists('payroll_policies');
        Schema::dropIfExists('payroll_tax_rule_versions');
        Schema::dropIfExists('payroll_tax_rules');
        Schema::dropIfExists('payroll_accounting_exports');
        Schema::dropIfExists('payroll_payment_lines');
        Schema::dropIfExists('payroll_payment_batches');
        Schema::dropIfExists('payroll_payslips');
        Schema::dropIfExists('payroll_exceptions');
        Schema::dropIfExists('payroll_variances');
        Schema::dropIfExists('payroll_adjustments');
        Schema::dropIfExists('payroll_advance_repayments');
        Schema::dropIfExists('payroll_loan_repayments');
        Schema::dropIfExists('payroll_arrears');
        Schema::dropIfExists('payroll_bonuses');
        Schema::dropIfExists('payroll_calculation_lines');
        Schema::dropIfExists('payroll_calculation_snapshots');
        Schema::dropIfExists('payroll_employer_contributions');
        Schema::dropIfExists('payroll_taxes');
        Schema::dropIfExists('payroll_deductions');
        Schema::dropIfExists('payroll_earnings');
        Schema::dropIfExists('payroll_input_lines');
        Schema::dropIfExists('payroll_inputs');
        Schema::dropIfExists('employee_compensation_components');
        Schema::dropIfExists('employee_compensations');
        Schema::dropIfExists('compensation_structure_components');
        Schema::dropIfExists('compensation_structures');
        Schema::dropIfExists('compensation_components');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('payroll_calendars');
        Schema::dropIfExists('payroll_legal_entities');
    }
};
