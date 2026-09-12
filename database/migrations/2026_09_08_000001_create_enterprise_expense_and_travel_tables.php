<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Expense Categories
        if (!Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 50)->nullable();
                $table->string('name', 150);
                $table->string('category_type', 50)->default('general');
                $table->text('description')->nullable();
                $table->decimal('max_amount', 19, 4)->nullable();
                $table->decimal('receipt_threshold', 19, 4)->default(0.0000);
                $table->boolean('receipt_required')->default(true);
                $table->boolean('is_reimbursable')->default(true);
                $table->string('tax_treatment', 50)->default('standard');
                $table->string('accounting_code', 80)->nullable();
                $table->json('rules')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('expense_categories', function (Blueprint $table) {
                if (!Schema::hasColumn('expense_categories', 'code')) {
                    $table->string('code', 50)->nullable()->after('tenant_id');
                }
                if (!Schema::hasColumn('expense_categories', 'category_type')) {
                    $table->string('category_type', 50)->default('general')->after('name');
                }
                if (!Schema::hasColumn('expense_categories', 'description')) {
                    $table->text('description')->nullable()->after('category_type');
                }
                if (!Schema::hasColumn('expense_categories', 'max_amount')) {
                    $table->decimal('max_amount', 19, 4)->nullable()->after('description');
                }
                if (!Schema::hasColumn('expense_categories', 'receipt_threshold')) {
                    $table->decimal('receipt_threshold', 19, 4)->default(0.0000)->after('max_amount');
                }
                if (!Schema::hasColumn('expense_categories', 'receipt_required')) {
                    $table->boolean('receipt_required')->default(true)->after('receipt_threshold');
                }
                if (!Schema::hasColumn('expense_categories', 'is_reimbursable')) {
                    $table->boolean('is_reimbursable')->default(true)->after('receipt_required');
                }
                if (!Schema::hasColumn('expense_categories', 'tax_treatment')) {
                    $table->string('tax_treatment', 50)->default('standard')->after('is_reimbursable');
                }
                if (!Schema::hasColumn('expense_categories', 'accounting_code')) {
                    $table->string('accounting_code', 80)->nullable()->after('tax_treatment');
                }
                if (!Schema::hasColumn('expense_categories', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('rules');
                }
                if (!Schema::hasColumn('expense_categories', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // 2. Expense Policies & Versions
        if (!Schema::hasTable('expense_policies')) {
            Schema::create('expense_policies', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('policy_code', 50)->nullable();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->unsignedInteger('current_version')->default(1);
                $table->string('status', 30)->default('active');
                $table->json('assignment_rules')->nullable();
                $table->json('rules')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('expense_policies', function (Blueprint $table) {
                if (!Schema::hasColumn('expense_policies', 'policy_code')) {
                    $table->string('policy_code', 50)->nullable()->after('tenant_id');
                }
                if (!Schema::hasColumn('expense_policies', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }
                if (!Schema::hasColumn('expense_policies', 'effective_from')) {
                    $table->date('effective_from')->nullable()->after('description');
                }
                if (!Schema::hasColumn('expense_policies', 'effective_to')) {
                    $table->date('effective_to')->nullable()->after('effective_from');
                }
                if (!Schema::hasColumn('expense_policies', 'current_version')) {
                    $table->unsignedInteger('current_version')->default(1)->after('effective_to');
                }
                if (!Schema::hasColumn('expense_policies', 'status')) {
                    $table->string('status', 30)->default('active')->after('current_version');
                }
                if (!Schema::hasColumn('expense_policies', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        Schema::create('expense_policy_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('expense_policy_id')->index();
            $table->unsignedInteger('version_number')->default(1);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->decimal('daily_meal_limit', 19, 4)->nullable();
            $table->decimal('daily_hotel_limit', 19, 4)->nullable();
            $table->decimal('receipt_required_threshold', 19, 4)->default(0.0000);
            $table->boolean('allow_policy_override')->default(true);
            $table->json('rules_configuration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expense_policy_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('expense_policy_id')->index();
            $table->string('scope_type', 50); // legal_entity, branch, department, job_grade, employee, travel_type
            $table->uuid('scope_id')->nullable()->index();
            $table->unsignedInteger('priority')->default(10);
            $table->timestamps();
        });

        // 3. Travel Requests, Segments, Authorizations, Bookings
        if (!Schema::hasTable('travel_requests')) {
            Schema::create('travel_requests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->string('request_number', 50)->nullable();
                $table->string('travel_type', 40)->default('domestic');
                $table->string('destination', 150);
                $table->text('purpose');
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('estimated_cost', 19, 4)->default(0.0000);
                $table->string('currency', 10)->default('USD');
                $table->text('business_justification')->nullable();
                $table->uuid('project_id')->nullable()->index();
                $table->uuid('cost_center_id')->nullable()->index();
                $table->decimal('per_diem', 19, 4)->default(0.0000);
                $table->boolean('advance_required')->default(false);
                $table->string('status', 30)->default('draft');
                $table->uuid('approved_by')->nullable()->index();
                $table->timestamp('approved_at')->nullable();
                $table->uuid('workflow_instance_id')->nullable();
                $table->json('itinerary')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('travel_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('travel_requests', 'request_number')) {
                    $table->string('request_number', 50)->nullable()->after('employee_id');
                }
                if (!Schema::hasColumn('travel_requests', 'currency')) {
                    $table->string('currency', 10)->default('USD')->after('estimated_cost');
                }
                if (!Schema::hasColumn('travel_requests', 'business_justification')) {
                    $table->text('business_justification')->nullable()->after('currency');
                }
                if (!Schema::hasColumn('travel_requests', 'project_id')) {
                    $table->uuid('project_id')->nullable()->after('business_justification');
                }
                if (!Schema::hasColumn('travel_requests', 'cost_center_id')) {
                    $table->uuid('cost_center_id')->nullable()->after('project_id');
                }
                if (!Schema::hasColumn('travel_requests', 'approved_by')) {
                    $table->uuid('approved_by')->nullable()->after('status');
                }
                if (!Schema::hasColumn('travel_requests', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('travel_requests', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        Schema::create('travel_request_segments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('travel_request_id')->index();
            $table->unsignedSmallInteger('segment_order')->default(1);
            $table->string('origin', 100);
            $table->string('destination', 100);
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time')->nullable();
            $table->string('transport_type', 40)->default('flight');
            $table->string('carrier_name', 100)->nullable();
            $table->string('booking_reference', 80)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('travel_authorizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('travel_request_id')->index();
            $table->string('authorization_number', 60)->unique();
            $table->decimal('approved_budget', 19, 4);
            $table->string('currency', 10)->default('USD');
            $table->date('valid_from');
            $table->date('valid_to');
            $table->uuid('approved_by')->index();
            $table->timestamp('approved_at');
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('travel_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('travel_request_id')->index();
            $table->string('booking_type', 40); // flight, hotel, train, car_rental, bus
            $table->string('provider_name', 120);
            $table->string('booking_reference', 80);
            $table->string('confirmation_number', 80)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->decimal('cost', 19, 4)->default(0.0000);
            $table->string('currency', 10)->default('USD');
            $table->string('status', 30)->default('confirmed');
            $table->timestamps();
        });

        // 4. Travel Advances, Disbursements, Settlements
        if (!Schema::hasTable('travel_advances')) {
            Schema::create('travel_advances', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('travel_request_id')->nullable()->index();
                $table->string('advance_number', 60)->nullable();
                $table->decimal('requested_amount', 19, 4);
                $table->decimal('approved_amount', 19, 4)->default(0.0000);
                $table->decimal('disbursed_amount', 19, 4)->default(0.0000);
                $table->decimal('settled_amount', 19, 4)->default(0.0000);
                $table->string('currency', 10)->default('USD');
                $table->text('purpose')->nullable();
                $table->string('status', 30)->default('requested');
                $table->uuid('approved_by')->nullable()->index();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('travel_advances', function (Blueprint $table) {
                if (!Schema::hasColumn('travel_advances', 'tenant_id')) {
                    $table->uuid('tenant_id')->nullable()->after('id')->index();
                }
                if (!Schema::hasColumn('travel_advances', 'employee_id')) {
                    $table->uuid('employee_id')->nullable()->after('tenant_id')->index();
                }
                if (!Schema::hasColumn('travel_advances', 'advance_number')) {
                    $table->string('advance_number', 60)->nullable()->after('travel_request_id');
                }
                if (!Schema::hasColumn('travel_advances', 'approved_amount')) {
                    $table->decimal('approved_amount', 19, 4)->default(0.0000)->after('requested_amount');
                }
                if (!Schema::hasColumn('travel_advances', 'currency')) {
                    $table->string('currency', 10)->default('USD')->after('settled_amount');
                }
                if (!Schema::hasColumn('travel_advances', 'purpose')) {
                    $table->text('purpose')->nullable()->after('currency');
                }
                if (!Schema::hasColumn('travel_advances', 'approved_by')) {
                    $table->uuid('approved_by')->nullable()->after('status');
                }
                if (!Schema::hasColumn('travel_advances', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('travel_advances', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        Schema::create('travel_advance_disbursements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('travel_advance_id')->index();
            $table->string('disbursement_number', 60)->unique();
            $table->decimal('disbursed_amount', 19, 4);
            $table->string('currency', 10)->default('USD');
            $table->date('disbursement_date');
            $table->string('payment_method', 40)->default('bank_transfer');
            $table->string('finance_reference', 100)->nullable();
            $table->uuid('disbursed_by')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('travel_advance_settlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('travel_advance_id')->index();
            $table->uuid('expense_claim_id')->nullable()->index();
            $table->decimal('settled_amount', 19, 4);
            $table->decimal('remaining_balance', 19, 4)->default(0.0000);
            $table->date('settlement_date');
            $table->string('settlement_type', 40)->default('claim_offset'); // claim_offset, employee_refund, payroll_recovery
            $table->string('finance_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Expense Claims, Lines, Receipts, Results, Exceptions
        if (!Schema::hasTable('expense_claims')) {
            Schema::create('expense_claims', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('travel_authorization_id')->nullable()->index();
                $table->uuid('travel_request_id')->nullable()->index();
                $table->uuid('policy_id')->nullable()->index();
                $table->string('claim_number', 60);
                $table->string('title', 150)->nullable();
                $table->date('claim_date')->nullable();
                $table->decimal('claimed_total', 19, 4)->default(0.0000);
                $table->decimal('approved_total', 19, 4)->default(0.0000);
                $table->decimal('advance_settled_amount', 19, 4)->default(0.0000);
                $table->decimal('net_reimbursement_amount', 19, 4)->default(0.0000);
                $table->string('currency', 10)->default('USD');
                $table->string('base_currency', 10)->default('USD');
                $table->decimal('exchange_rate', 14, 6)->default(1.000000);
                $table->string('status', 30)->default('draft');
                $table->unsignedSmallInteger('approval_level')->default(1);
                $table->uuid('approved_by')->nullable()->index();
                $table->timestamp('approved_at')->nullable();
                $table->uuid('finance_reviewed_by')->nullable()->index();
                $table->timestamp('finance_reviewed_at')->nullable();
                $table->boolean('is_period_locked')->default(false);
                $table->uuid('workflow_instance_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('expense_claims', function (Blueprint $table) {
                if (!Schema::hasColumn('expense_claims', 'travel_authorization_id')) {
                    $table->uuid('travel_authorization_id')->nullable()->after('employee_id')->index();
                }
                if (!Schema::hasColumn('expense_claims', 'travel_request_id')) {
                    $table->uuid('travel_request_id')->nullable()->after('travel_authorization_id')->index();
                }
                if (!Schema::hasColumn('expense_claims', 'title')) {
                    $table->string('title', 150)->nullable()->after('claim_number');
                }
                if (!Schema::hasColumn('expense_claims', 'claim_date')) {
                    $table->date('claim_date')->nullable()->after('title');
                }
                if (!Schema::hasColumn('expense_claims', 'advance_settled_amount')) {
                    $table->decimal('advance_settled_amount', 19, 4)->default(0.0000)->after('approved_total');
                }
                if (!Schema::hasColumn('expense_claims', 'net_reimbursement_amount')) {
                    $table->decimal('net_reimbursement_amount', 19, 4)->default(0.0000)->after('advance_settled_amount');
                }
                if (!Schema::hasColumn('expense_claims', 'base_currency')) {
                    $table->string('base_currency', 10)->default('USD')->after('currency');
                }
                if (!Schema::hasColumn('expense_claims', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 14, 6)->default(1.000000)->after('base_currency');
                }
                if (!Schema::hasColumn('expense_claims', 'approval_level')) {
                    $table->unsignedSmallInteger('approval_level')->default(1)->after('status');
                }
                if (!Schema::hasColumn('expense_claims', 'approved_by')) {
                    $table->uuid('approved_by')->nullable()->after('approval_level');
                }
                if (!Schema::hasColumn('expense_claims', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('expense_claims', 'finance_reviewed_by')) {
                    $table->uuid('finance_reviewed_by')->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('expense_claims', 'finance_reviewed_at')) {
                    $table->timestamp('finance_reviewed_at')->nullable()->after('finance_reviewed_by');
                }
                if (!Schema::hasColumn('expense_claims', 'is_period_locked')) {
                    $table->boolean('is_period_locked')->default(false)->after('finance_reviewed_at');
                }
                if (!Schema::hasColumn('expense_claims', 'notes')) {
                    $table->text('notes')->nullable()->after('workflow_instance_id');
                }
                if (!Schema::hasColumn('expense_claims', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        Schema::create('expense_claim_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('expense_claim_id')->index();
            $table->uuid('expense_category_id')->index();
            $table->date('expense_date');
            $table->string('merchant', 120)->nullable();
            $table->text('description');
            $table->decimal('original_amount', 19, 4);
            $table->string('original_currency', 10)->default('USD');
            $table->decimal('exchange_rate', 14, 6)->default(1.000000);
            $table->date('exchange_rate_date')->nullable();
            $table->string('exchange_rate_source', 60)->default('system');
            $table->decimal('base_amount', 19, 4);
            $table->string('base_currency', 10)->default('USD');
            $table->string('tax_type', 40)->nullable();
            $table->decimal('tax_rate', 8, 4)->default(0.0000);
            $table->decimal('tax_amount', 19, 4)->default(0.0000);
            $table->boolean('is_tax_inclusive')->default(false);
            $table->boolean('is_tax_recoverable')->default(true);
            $table->decimal('non_recoverable_tax', 19, 4)->default(0.0000);
            $table->uuid('project_id')->nullable()->index();
            $table->string('project_code', 60)->nullable();
            $table->uuid('cost_center_id')->nullable()->index();
            $table->uuid('department_id')->nullable()->index();
            $table->string('location', 100)->nullable();
            $table->string('payment_method', 40)->default('personal_card'); // cash, personal_card, corporate_card, bank_transfer, payroll
            $table->uuid('corporate_card_transaction_id')->nullable()->index();
            $table->boolean('is_mileage')->default(false);
            $table->decimal('mileage_distance', 10, 2)->default(0.00);
            $table->string('mileage_unit', 10)->default('km'); // km, miles
            $table->decimal('mileage_rate', 10, 4)->default(0.0000);
            $table->boolean('is_per_diem')->default(false);
            $table->decimal('per_diem_days', 8, 2)->default(0.00);
            $table->decimal('per_diem_rate', 19, 4)->default(0.0000);
            $table->decimal('meal_deductions', 19, 4)->default(0.0000);
            $table->string('policy_status', 40)->default('compliant'); // compliant, warning, excess_reduced, violation_blocked, exception_required
            $table->decimal('approved_amount', 19, 4)->default(0.0000);
            $table->decimal('approved_base_amount', 19, 4)->default(0.0000);
            $table->text('exception_reason')->nullable();
            $table->uuid('override_approver_id')->nullable()->index();
            $table->timestamp('override_at')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('expense_claim_line_id')->nullable()->index();
            $table->uuid('employee_id')->index();
            $table->string('file_path', 255);
            $table->string('file_name', 200);
            $table->string('file_type', 50)->default('image/jpeg');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('receipt_hash', 64)->nullable()->index();
            $table->string('merchant_extracted', 120)->nullable();
            $table->string('invoice_number', 80)->nullable();
            $table->decimal('extracted_amount', 19, 4)->nullable();
            $table->string('extracted_currency', 10)->nullable();
            $table->decimal('extracted_tax', 19, 4)->nullable();
            $table->json('ocr_payload')->nullable();
            $table->boolean('is_verified_by_employee')->default(true);
            $table->string('status', 30)->default('attached');
            $table->timestamps();
        });

        Schema::create('expense_policy_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('expense_claim_line_id')->index();
            $table->uuid('expense_policy_id')->nullable()->index();
            $table->string('rule_name', 100);
            $table->string('rule_type', 50);
            $table->boolean('is_violation')->default(false);
            $table->decimal('allowed_limit', 19, 4)->nullable();
            $table->decimal('actual_amount', 19, 4);
            $table->decimal('excess_amount', 19, 4)->default(0.0000);
            $table->string('action_taken', 40)->default('allowed'); // allowed, warning, excess_reduced, blocked, exception_required
            $table->text('violation_details')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_exceptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('expense_claim_id')->index();
            $table->uuid('expense_claim_line_id')->nullable()->index();
            $table->string('exception_type', 60); // missing_receipt, over_limit, duplicate_expense, invalid_date, future_date, invalid_category, unapproved_travel, expired_travel_auth, missing_cost_center, policy_violation, advance_unsettled
            $table->string('severity', 20)->default('warning'); // warning, blocker
            $table->text('reason');
            $table->string('status', 30)->default('open'); // open, under_review, resolved, approved, rejected, waived
            $table->uuid('resolved_by')->nullable()->index();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });

        // 6. Mileage Rates, Per Diem Rates, Exchange Rates
        Schema::create('mileage_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('vehicle_type', 50)->default('standard_car'); // car, motorcycle, bicycle, ev
            $table->string('location', 100)->nullable();
            $table->uuid('job_grade_id')->nullable()->index();
            $table->decimal('rate_per_unit', 10, 4);
            $table->string('unit', 10)->default('km'); // km, miles
            $table->string('currency', 10)->default('USD');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('per_diem_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('destination_type', 40)->default('domestic'); // domestic, international
            $table->string('destination_country', 80)->nullable();
            $table->string('destination_city', 80)->nullable();
            $table->uuid('job_grade_id')->nullable()->index();
            $table->decimal('daily_rate', 19, 4);
            $table->string('currency', 10)->default('USD');
            $table->decimal('departure_day_percentage', 6, 2)->default(75.00);
            $table->decimal('return_day_percentage', 6, 2)->default(75.00);
            $table->decimal('breakfast_deduction_percentage', 6, 2)->default(20.00);
            $table->decimal('lunch_deduction_percentage', 6, 2)->default(30.00);
            $table->decimal('dinner_deduction_percentage', 6, 2)->default(30.00);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expense_exchange_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('from_currency', 10);
            $table->string('to_currency', 10);
            $table->decimal('rate', 14, 6);
            $table->date('effective_date');
            $table->string('source', 60)->default('finance');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Corporate Cards, Transactions, Matches
        Schema::create('corporate_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('card_token', 100)->unique();
            $table->string('card_masked_number', 30);
            $table->string('card_holder_name', 120);
            $table->string('card_provider', 60)->default('Visa');
            $table->date('expiry_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });

        Schema::create('corporate_card_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('corporate_card_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('transaction_reference', 100)->unique();
            $table->dateTime('transaction_date');
            $table->string('merchant_name', 120);
            $table->decimal('amount', 19, 4);
            $table->string('currency', 10)->default('USD');
            $table->decimal('base_amount', 19, 4);
            $table->string('base_currency', 10)->default('USD');
            $table->decimal('exchange_rate', 14, 6)->default(1.000000);
            $table->string('category_hint', 80)->nullable();
            $table->boolean('is_matched')->default(false);
            $table->uuid('matched_claim_line_id')->nullable()->index();
            $table->string('match_status', 30)->default('unmatched'); // unmatched, matched, dispute, personal_expense
            $table->timestamps();
        });

        Schema::create('corporate_card_matches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('corporate_card_transaction_id')->index();
            $table->uuid('expense_claim_line_id')->index();
            $table->decimal('matched_amount', 19, 4);
            $table->decimal('match_confidence', 5, 2)->default(100.00);
            $table->uuid('matched_by')->index();
            $table->timestamp('matched_at');
            $table->timestamps();
        });

        // 8. Expense Reimbursements & Lines
        Schema::create('expense_reimbursements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('reimbursement_number', 60)->unique();
            $table->decimal('total_reimbursement_amount', 19, 4);
            $table->string('currency', 10)->default('USD');
            $table->string('reimbursement_method', 40)->default('bank_payment'); // payroll, bank_payment, accounts_payable, cash
            $table->uuid('payment_batch_id')->nullable()->index();
            $table->uuid('payroll_period_id')->nullable()->index();
            $table->string('status', 30)->default('pending'); // pending, approved, processing, paid, cancelled
            $table->string('payment_reference', 100)->nullable();
            $table->date('payment_date')->nullable();
            $table->uuid('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->uuid('paid_by')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_reimbursement_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('expense_reimbursement_id')->index();
            $table->uuid('expense_claim_id')->index();
            $table->decimal('claimed_amount', 19, 4);
            $table->decimal('approved_amount', 19, 4);
            $table->decimal('advance_deduction_amount', 19, 4)->default(0.0000);
            $table->decimal('payable_amount', 19, 4);
            $table->string('currency', 10)->default('USD');
            $table->timestamps();
        });

        // 9. Allocations, Accounting Exports, Period Locks
        Schema::create('expense_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('expense_claim_line_id')->index();
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('cost_center_id')->nullable()->index();
            $table->uuid('project_id')->nullable()->index();
            $table->decimal('allocation_percentage', 6, 2);
            $table->decimal('allocated_amount', 19, 4);
            $table->string('currency', 10)->default('USD');
            $table->timestamps();
        });

        Schema::create('expense_accounting_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('batch_number', 60)->unique();
            $table->date('export_date');
            $table->decimal('total_amount', 19, 4);
            $table->string('currency', 10)->default('USD');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 30)->default('draft'); // draft, exported, posted
            $table->json('gl_export_payload')->nullable();
            $table->uuid('exported_by')->index();
            $table->timestamp('exported_at');
            $table->timestamps();
        });

        Schema::create('expense_period_locks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('period_name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_locked')->default(false);
            $table->uuid('locked_by')->nullable()->index();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_period_locks');
        Schema::dropIfExists('expense_accounting_exports');
        Schema::dropIfExists('expense_allocations');
        Schema::dropIfExists('expense_reimbursement_lines');
        Schema::dropIfExists('expense_reimbursements');
        Schema::dropIfExists('corporate_card_matches');
        Schema::dropIfExists('corporate_card_transactions');
        Schema::dropIfExists('corporate_cards');
        Schema::dropIfExists('expense_exchange_rates');
        Schema::dropIfExists('per_diem_rates');
        Schema::dropIfExists('mileage_rates');
        Schema::dropIfExists('expense_exceptions');
        Schema::dropIfExists('expense_policy_results');
        Schema::dropIfExists('expense_receipts');
        Schema::dropIfExists('expense_claim_lines');
        Schema::dropIfExists('travel_advance_settlements');
        Schema::dropIfExists('travel_advance_disbursements');
        Schema::dropIfExists('travel_bookings');
        Schema::dropIfExists('travel_authorizations');
        Schema::dropIfExists('travel_request_segments');
        Schema::dropIfExists('expense_policy_assignments');
        Schema::dropIfExists('expense_policy_versions');
    }
};
