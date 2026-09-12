<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Job Sub-Families
        if (!Schema::hasTable('job_sub_families')) {
            Schema::create('job_sub_families', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('job_family_id')->index();
                $table->string('code', 80);
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('status', 20)->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('job_family_id')->references('id')->on('job_families')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 2. Career Tracks
        if (!Schema::hasTable('career_tracks')) {
            Schema::create('career_tracks', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 80);
                $table->string('name', 150);
                $table->string('track_type', 50)->default('individual_contributor'); // individual_contributor, management, technical_specialist, professional, executive
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 3. Career Levels
        if (!Schema::hasTable('career_levels')) {
            Schema::create('career_levels', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('career_track_id')->index();
                $table->string('level_code', 40); // L1, L2, L3...
                $table->string('name', 150);
                $table->unsignedSmallInteger('rank_order')->default(1);
                $table->decimal('typical_experience_years', 4, 1)->default(0);
                $table->text('scope_description')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('career_track_id')->references('id')->on('career_tracks')->cascadeOnDelete();
                $table->unique(['tenant_id', 'career_track_id', 'level_code']);
            });
        }

        // 4. Job Levels
        if (!Schema::hasTable('job_levels')) {
            Schema::create('job_levels', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 80);
                $table->string('name', 150); // Entry, Professional, Senior, Lead, Manager, Director, Executive
                $table->unsignedSmallInteger('numerical_level')->default(1);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 5. Job Profiles
        if (!Schema::hasTable('job_profiles')) {
            Schema::create('job_profiles', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('job_id')->nullable()->index(); // optional link to organization_job_definitions
                $table->uuid('job_family_id')->index();
                $table->uuid('job_sub_family_id')->nullable()->index();
                $table->uuid('career_track_id')->nullable()->index();
                $table->uuid('career_level_id')->nullable()->index();
                $table->uuid('job_level_id')->nullable()->index();
                $table->uuid('job_grade_id')->nullable()->index(); // optional link to job_grades
                $table->string('code', 80);
                $table->string('title', 180);
                $table->text('summary')->nullable();
                $table->json('responsibilities')->nullable();
                $table->json('requirements')->nullable();
                $table->string('education_requirement', 150)->nullable();
                $table->decimal('experience_years_min', 4, 1)->default(0);
                $table->json('certifications')->nullable();
                $table->string('travel_requirement', 80)->nullable();
                $table->string('remote_eligibility', 50)->default('hybrid'); // onsite, hybrid, remote
                $table->string('status', 30)->default('draft'); // draft, review, approved, published, retired
                $table->unsignedInteger('current_version')->default(1);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('job_family_id')->references('id')->on('job_families')->cascadeOnDelete();
                $table->foreign('job_sub_family_id')->references('id')->on('job_sub_families')->nullOnDelete();
                $table->foreign('career_track_id')->references('id')->on('career_tracks')->nullOnDelete();
                $table->foreign('career_level_id')->references('id')->on('career_levels')->nullOnDelete();
                $table->foreign('job_level_id')->references('id')->on('job_levels')->nullOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 6. Job Profile Versions
        if (!Schema::hasTable('job_profile_versions')) {
            Schema::create('job_profile_versions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('job_profile_id')->index();
                $table->unsignedInteger('version_number');
                $table->json('snapshot_data');
                $table->text('change_summary')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('job_profile_id')->references('id')->on('job_profiles')->cascadeOnDelete();
                $table->unique(['tenant_id', 'job_profile_id', 'version_number']);
            });
        }

        // 7. Job Profile Skills (linking to CareerSkill)
        if (!Schema::hasTable('job_profile_skills')) {
            Schema::create('job_profile_skills', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('job_profile_id')->index();
                $table->uuid('career_skill_id')->index();
                $table->boolean('is_required')->default(true);
                $table->string('target_proficiency', 40)->default('intermediate'); // beginner, intermediate, advanced, expert
                $table->string('criticality', 30)->default('medium'); // low, medium, high, critical
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('job_profile_id')->references('id')->on('job_profiles')->cascadeOnDelete();
                $table->foreign('career_skill_id')->references('id')->on('career_skills')->cascadeOnDelete();
            });
        }

        // 8. Job Profile Competencies (linking to Competency & CompetencyLevel)
        if (!Schema::hasTable('job_profile_competencies')) {
            Schema::create('job_profile_competencies', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('job_profile_id')->index();
                $table->uuid('competency_id')->index();
                $table->uuid('competency_level_id')->nullable()->index();
                $table->boolean('is_required')->default(true);
                $table->string('criticality', 30)->default('medium');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('job_profile_id')->references('id')->on('job_profiles')->cascadeOnDelete();
                $table->foreign('competency_id')->references('id')->on('competencies')->cascadeOnDelete();
            });
        }

        // 9. Job Evaluations
        if (!Schema::hasTable('job_evaluations')) {
            Schema::create('job_evaluations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('job_profile_id')->index();
                $table->string('evaluation_model', 80)->default('point_factor');
                $table->unsignedSmallInteger('knowledge_score')->default(0);
                $table->unsignedSmallInteger('problem_solving_score')->default(0);
                $table->unsignedSmallInteger('accountability_score')->default(0);
                $table->unsignedSmallInteger('impact_score')->default(0);
                $table->unsignedSmallInteger('leadership_score')->default(0);
                $table->unsignedInteger('total_points')->default(0);
                $table->uuid('suggested_job_grade_id')->nullable()->index();
                $table->foreignId('evaluator_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 30)->default('finalized'); // draft, finalized, approved
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('job_profile_id')->references('id')->on('job_profiles')->cascadeOnDelete();
            });
        }

        // 10. Organization Design Scenarios
        if (!Schema::hasTable('org_design_scenarios')) {
            Schema::create('org_design_scenarios', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 80);
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('status', 30)->default('draft'); // draft, under_review, approved, implemented, archived
                $table->date('effective_target_date')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 11. Organization Design Scenario Nodes
        if (!Schema::hasTable('org_design_scenario_nodes')) {
            Schema::create('org_design_scenario_nodes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('scenario_id')->index();
                $table->string('node_type', 50); // company, business_unit, division, department, team
                $table->uuid('source_node_id')->nullable()->index(); // link to live org entity if existing
                $table->uuid('parent_scenario_node_id')->nullable()->index();
                $table->string('code', 80);
                $table->string('name', 150);
                $table->string('action_type', 30)->default('existing'); // existing, add, modify, merge, split, remove
                $table->json('metadata_payload')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('scenario_id')->references('id')->on('org_design_scenarios')->cascadeOnDelete();
            });
        }

        // 12. Organization Design Governance & Health Issues
        if (!Schema::hasTable('org_design_health_issues')) {
            Schema::create('org_design_health_issues', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('issue_type', 60); // missing_family, duplicate_title, orphaned_position, retired_reference, invalid_career_path
                $table->string('severity', 30)->default('warning'); // info, warning, critical
                $table->string('entity_type', 60);
                $table->string('entity_id', 80);
                $table->string('title', 180);
                $table->json('details')->nullable();
                $table->string('status', 30)->default('open'); // open, resolved, ignored
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('org_design_health_issues');
        Schema::dropIfExists('org_design_scenario_nodes');
        Schema::dropIfExists('org_design_scenarios');
        Schema::dropIfExists('job_evaluations');
        Schema::dropIfExists('job_profile_competencies');
        Schema::dropIfExists('job_profile_skills');
        Schema::dropIfExists('job_profile_versions');
        Schema::dropIfExists('job_profiles');
        Schema::dropIfExists('job_levels');
        Schema::dropIfExists('career_levels');
        Schema::dropIfExists('career_tracks');
        Schema::dropIfExists('job_sub_families');
    }
};
