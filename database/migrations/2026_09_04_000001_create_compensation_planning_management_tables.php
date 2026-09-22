<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend compensation_cycles if needed
        if (Schema::hasTable('compensation_cycles')) {
            Schema::table('compensation_cycles', function (Blueprint $t): void {
                if (! Schema::hasColumn('compensation_cycles', 'description')) {
                    $t->text('description')->nullable()->after('name');
                }
                if (! Schema::hasColumn('compensation_cycles', 'currency')) {
                    $t->string('currency', 3)->default('USD')->after('cycle_type');
                }
                if (! Schema::hasColumn('compensation_cycles', 'guidelines')) {
                    $t->json('guidelines')->nullable()->after('status');
                }
                if (! Schema::hasColumn('compensation_cycles', 'eligibility_rules')) {
                    $t->json('eligibility_rules')->nullable()->after('guidelines');
                }
                if (! Schema::hasColumn('compensation_cycles', 'created_by')) {
                    $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('compensation_cycles', 'updated_by')) {
                    $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }

        // 2. Extend compensation_budgets
        if (Schema::hasTable('compensation_budgets')) {
            Schema::table('compensation_budgets', function (Blueprint $t): void {
                if (! Schema::hasColumn('compensation_budgets', 'tenant_id')) {
                    $t->uuid('tenant_id')->nullable()->after('id')->index();
                }
                if (! Schema::hasColumn('compensation_budgets', 'currency')) {
                    $t->string('currency', 3)->default('USD')->after('scope_id');
                }
                if (! Schema::hasColumn('compensation_budgets', 'budget_type')) {
                    $t->string('budget_type', 30)->default('merit')->after('scope_type'); // merit, bonus, promotion, market_adjustment
                }
                if (! Schema::hasColumn('compensation_budgets', 'holdback_amount')) {
                    $t->decimal('holdback_amount', 18, 2)->default(0)->after('consumed');
                }
            });
        }

        // 3. Compensation Merit Matrices
        if (! Schema::hasTable('compensation_merit_matrices')) {
            Schema::create('compensation_merit_matrices', function (Blueprint $t): void {
                $t->uuid('id')->primary();
                $t->uuid('tenant_id')->index();
                $t->uuid('compensation_cycle_id')->nullable()->index();
                $t->string('name', 120);
                $t->text('description')->nullable();
                $t->json('matrix_grid'); // [ { performance_rating: 'exceeds', compa_ratio_range: 'under_80', min_increase: 5.0, mid_increase: 7.0, max_increase: 9.0 } ]
                $t->boolean('is_active')->default(true);
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();

                $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $t->foreign('compensation_cycle_id')->references('id')->on('compensation_cycles')->nullOnDelete();
            });
        }

        // 4. Extend compensation_recommendations
        if (Schema::hasTable('compensation_recommendations')) {
            Schema::table('compensation_recommendations', function (Blueprint $t): void {
                if (! Schema::hasColumn('compensation_recommendations', 'tenant_id')) {
                    $t->uuid('tenant_id')->nullable()->after('id')->index();
                }
                if (! Schema::hasColumn('compensation_recommendations', 'job_grade_id')) {
                    $t->uuid('job_grade_id')->nullable()->after('employee_id');
                }
                if (! Schema::hasColumn('compensation_recommendations', 'currency')) {
                    $t->string('currency', 3)->default('USD')->after('job_grade_id');
                }
                if (! Schema::hasColumn('compensation_recommendations', 'compa_ratio_current')) {
                    $t->decimal('compa_ratio_current', 6, 4)->nullable()->after('current_base_salary');
                }
                if (! Schema::hasColumn('compensation_recommendations', 'compa_ratio_new')) {
                    $t->decimal('compa_ratio_new', 6, 4)->nullable()->after('recommended_base_salary');
                }
                if (! Schema::hasColumn('compensation_recommendations', 'range_penetration_current')) {
                    $t->decimal('range_penetration_current', 6, 4)->nullable()->after('compa_ratio_current');
                }
                if (! Schema::hasColumn('compensation_recommendations', 'range_penetration_new')) {
                    $t->decimal('range_penetration_new', 6, 4)->nullable()->after('compa_ratio_new');
                }
                if (! Schema::hasColumn('compensation_recommendations', 'performance_rating')) {
                    $t->string('performance_rating', 50)->nullable()->after('range_penetration_current');
                }
                if (! Schema::hasColumn('compensation_recommendations', 'guideline_min_pct')) {
                    $t->decimal('guideline_min_pct', 6, 2)->nullable();
                }
                if (! Schema::hasColumn('compensation_recommendations', 'guideline_max_pct')) {
                    $t->decimal('guideline_max_pct', 6, 2)->nullable();
                }
                if (! Schema::hasColumn('compensation_recommendations', 'promotion_grade_id')) {
                    $t->uuid('promotion_grade_id')->nullable();
                }
                if (! Schema::hasColumn('compensation_recommendations', 'promotion_increase_amount')) {
                    $t->decimal('promotion_increase_amount', 15, 2)->default(0);
                }
                if (! Schema::hasColumn('compensation_recommendations', 'market_adjustment_amount')) {
                    $t->decimal('market_adjustment_amount', 15, 2)->default(0);
                }
                if (! Schema::hasColumn('compensation_recommendations', 'lump_sum_amount')) {
                    $t->decimal('lump_sum_amount', 15, 2)->default(0);
                }
                if (! Schema::hasColumn('compensation_recommendations', 'proposed_by')) {
                    $t->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('compensation_recommendations', 'approved_by')) {
                    $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('compensation_recommendations', 'approved_at')) {
                    $t->timestamp('approved_at')->nullable();
                }
                if (! Schema::hasColumn('compensation_recommendations', 'effective_date')) {
                    $t->date('effective_date')->nullable();
                }
            });
        }

        // 5. Compensation Calibration Sessions & Records
        if (! Schema::hasTable('compensation_calibration_sessions')) {
            Schema::create('compensation_calibration_sessions', function (Blueprint $t): void {
                $t->uuid('id')->primary();
                $t->uuid('tenant_id')->index();
                $t->uuid('compensation_cycle_id')->index();
                $t->string('title', 150);
                $t->string('scope_type', 50)->default('department'); // department, legal_entity, grade, executive
                $t->string('scope_id', 80)->nullable();
                $t->string('status', 30)->default('scheduled'); // scheduled, in_progress, completed, locked
                $t->dateTime('session_date')->nullable();
                $t->json('facilitators')->nullable();
                $t->json('budget_metrics')->nullable();
                $t->text('notes')->nullable();
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();

                $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $t->foreign('compensation_cycle_id')->references('id')->on('compensation_cycles')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('compensation_calibration_records')) {
            Schema::create('compensation_calibration_records', function (Blueprint $t): void {
                $t->uuid('id')->primary();
                $t->uuid('tenant_id')->index();
                $t->uuid('compensation_calibration_session_id')->index('idx_comp_calib_rec_sess');
                $t->uuid('compensation_recommendation_id')->index('idx_comp_calib_rec_recom');
                $t->decimal('original_increase_pct', 8, 4);
                $t->decimal('original_increase_amount', 15, 2);
                $t->decimal('calibrated_increase_pct', 8, 4);
                $t->decimal('calibrated_increase_amount', 15, 2);
                $t->text('mandatory_calibration_reason');
                $t->foreignId('calibrated_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('calibrated_at')->useCurrent();
                $t->timestamps();

                $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $t->foreign('compensation_calibration_session_id', 'fk_comp_calib_rec_session')->references('id')->on('compensation_calibration_sessions')->cascadeOnDelete();
                $t->foreign('compensation_recommendation_id', 'fk_comp_calib_rec_recom')->references('id')->on('compensation_recommendations')->cascadeOnDelete();
            });
        }

        // 6. Compensation Payroll Exports (Integration Log)
        if (! Schema::hasTable('compensation_payroll_exports')) {
            Schema::create('compensation_payroll_exports', function (Blueprint $t): void {
                $t->uuid('id')->primary();
                $t->uuid('tenant_id')->index();
                $t->uuid('compensation_cycle_id')->index();
                $t->string('batch_reference', 60);
                $t->date('effective_date');
                $t->unsignedInteger('record_count')->default(0);
                $t->decimal('total_increase_payroll_impact', 18, 2)->default(0);
                $t->string('currency', 3)->default('USD');
                $t->string('status', 30)->default('exported'); // exported, received, accepted, rejected, applied
                $t->text('sync_payload')->nullable();
                $t->text('integration_response')->nullable();
                $t->foreignId('exported_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('exported_at')->useCurrent();
                $t->timestamps();

                $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $t->foreign('compensation_cycle_id')->references('id')->on('compensation_cycles')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('compensation_payroll_exports');
        Schema::dropIfExists('compensation_calibration_records');
        Schema::dropIfExists('compensation_calibration_sessions');
        Schema::dropIfExists('compensation_merit_matrices');
    }
};
