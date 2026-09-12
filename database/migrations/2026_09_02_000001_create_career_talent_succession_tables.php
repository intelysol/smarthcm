<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Skill Categories
        Schema::create('career_skill_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 2. Skills Master
        Schema::create('career_skills', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->uuid('category_id')->nullable()->index();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('skill_type', 40)->default('technical'); // technical, functional, soft_skill, language, tool, domain, leadership
            $table->unsignedInteger('assessment_interval_months')->nullable();
            $table->string('status', 30)->default('active');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('career_skill_categories')->nullOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 3. Skill Levels (1-5 or custom levels)
        Schema::create('career_skill_levels', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->unsignedSmallInteger('level_number');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->json('behavioral_indicators')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'level_number']);
        });

        // 4. Skill to Competency Mappings (Integration with Performance Competencies)
        Schema::create('career_skill_competency_mappings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('skill_id')->index();
            $table->uuid('competency_id')->index();
            $table->decimal('weight', 5, 2)->default(1.0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('skill_id')->references('id')->on('career_skills')->cascadeOnDelete();
            $table->foreign('competency_id')->references('id')->on('competencies')->cascadeOnDelete();
            $table->unique(['tenant_id', 'skill_id', 'competency_id'], 'career_skill_comp_unique');
        });

        // 5. Employee Skills
        Schema::create('employee_skills', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('skill_id')->index();
            $table->unsignedSmallInteger('current_level')->default(1);
            $table->unsignedSmallInteger('target_level')->nullable();
            $table->string('source', 40)->default('employee'); // employee, manager, assessment, performance, learning, certification, import, system
            $table->string('verification_status', 40)->default('unverified'); // unverified, self_declared, manager_verified, assessment_verified, system_verified
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->date('last_assessed_at')->nullable();
            $table->date('next_assessment_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('skill_id')->references('id')->on('career_skills')->cascadeOnDelete();
            $table->unique(['tenant_id', 'employee_id', 'skill_id']);
        });

        // 6. Employee Skill Evidence
        Schema::create('employee_skill_evidence', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_skill_id')->index();
            $table->string('evidence_type', 40); // certification, course, project, performance_review, assessment, manager_validation, work_experience
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->uuid('document_id')->nullable();
            $table->string('reference_type', 60)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->boolean('verified')->default(false);
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_skill_id')->references('id')->on('employee_skills')->cascadeOnDelete();
        });

        // 7. Role Skill Requirements (Job requirements)
        Schema::create('career_job_skill_requirements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('job_id')->index();
            $table->uuid('skill_id')->index();
            $table->unsignedSmallInteger('required_level')->default(1);
            $table->string('importance', 30)->default('medium'); // critical, high, medium, low
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('job_id')->references('id')->on('organization_job_definitions')->cascadeOnDelete();
            $table->foreign('skill_id')->references('id')->on('career_skills')->cascadeOnDelete();
            $table->unique(['tenant_id', 'job_id', 'skill_id']);
        });

        // 8. Skill Gaps (Read model / calculated cache)
        Schema::create('career_skill_gaps', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('skill_id')->index();
            $table->uuid('target_job_id')->nullable()->index();
            $table->unsignedSmallInteger('current_level')->default(0);
            $table->unsignedSmallInteger('required_level')->default(1);
            $table->unsignedSmallInteger('gap')->default(1);
            $table->string('priority', 30)->default('medium'); // critical, high, medium, low
            $table->string('source', 40)->default('target_job');
            $table->string('status', 30)->default('open'); // open, in_progress, resolved
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('skill_id')->references('id')->on('career_skills')->cascadeOnDelete();
            $table->foreign('target_job_id')->references('id')->on('organization_job_definitions')->nullOnDelete();
        });

        // 9. Employee Career Aspirations
        Schema::create('employee_career_aspirations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('target_job_id')->nullable()->index();
            $table->string('target_career_level', 50)->nullable();
            $table->unsignedInteger('preferred_timeline_months')->nullable();
            $table->string('preferred_location', 100)->nullable();
            $table->json('interest_areas')->nullable();
            $table->json('preferences')->nullable(); // management, technical, specialist, project, international, remote
            $table->string('visibility', 30)->default('private'); // private, manager_shared, hr_shared, talent_pool
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('active'); // active, on_hold, completed, cancelled
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('target_job_id')->references('id')->on('organization_job_definitions')->nullOnDelete();
        });

        // 10. Career Paths
        Schema::create('career_paths', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50);
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->uuid('department_id')->nullable()->index();
            $table->string('job_family', 100)->nullable();
            $table->string('status', 30)->default('active');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 11. Career Path Steps
        Schema::create('career_path_steps', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('career_path_id')->index();
            $table->uuid('job_id')->index();
            $table->string('career_level', 50)->nullable();
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->decimal('minimum_experience_years', 4, 1)->default(0);
            $table->decimal('performance_min_rating', 5, 2)->nullable();
            $table->json('required_skills')->nullable();
            $table->json('required_competencies')->nullable();
            $table->json('required_certifications')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('career_path_id')->references('id')->on('career_paths')->cascadeOnDelete();
            $table->foreign('job_id')->references('id')->on('organization_job_definitions')->cascadeOnDelete();
            $table->unique(['career_path_id', 'sequence']);
        });

        // 12. Development Recommendations
        Schema::create('career_development_recommendations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('gap_type', 40); // skill_gap, competency_gap, experience_gap, certification_gap
            $table->uuid('gap_id')->nullable();
            $table->string('recommendation_type', 40); // learning_course, learning_path, development_action, project_assignment, mentoring, certification
            $table->uuid('recommended_item_id')->nullable();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('priority', 30)->default('medium');
            $table->string('status', 30)->default('suggested'); // suggested, accepted, in_progress, completed, dismissed
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 13. Individual Career Plans (IDP)
        Schema::create('career_plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('current_job_id')->nullable()->index();
            $table->uuid('target_job_id')->nullable()->index();
            $table->date('target_date')->nullable();
            $table->string('readiness_level', 40)->default('developing'); // not_ready, developing, nearly_ready, ready, ready_now
            $table->decimal('readiness_score', 5, 2)->default(0.0);
            $table->string('visibility', 30)->default('private'); // private, manager, hr, talent_team
            $table->string('status', 30)->default('draft'); // draft, under_review, approved, in_progress, completed, archived
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('current_job_id')->references('id')->on('organization_job_definitions')->nullOnDelete();
            $table->foreign('target_job_id')->references('id')->on('organization_job_definitions')->nullOnDelete();
        });

        // 14. Career Plan Actions
        Schema::create('career_plan_actions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('plan_id')->index();
            $table->string('action_type', 40); // training, mentoring, coaching, project, job_rotation, stretch_assignment, certification
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('planned'); // planned, in_progress, completed, cancelled
            $table->decimal('completion_percentage', 5, 2)->default(0.0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('career_plans')->cascadeOnDelete();
        });

        // 15. Career Mobility Preferences
        Schema::create('career_mobility_preferences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->unique();
            $table->string('mobility_type', 50)->default('lateral'); // department_transfer, location_transfer, job_change, promotion, lateral_move, temporary_assignment
            $table->uuid('preferred_department_id')->nullable()->index();
            $table->string('preferred_location', 100)->nullable();
            $table->boolean('willing_to_relocate')->default(false);
            $table->string('remote_preference', 30)->default('hybrid'); // on_site, hybrid, remote
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('preferred_department_id')->references('id')->on('departments')->nullOnDelete();
        });

        // 16. Mentoring Programs
        Schema::create('career_mentoring_programs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 17. Mentoring Relationships
        Schema::create('career_mentoring_relationships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->uuid('program_id')->nullable()->index();
            $table->uuid('mentor_employee_id')->index();
            $table->uuid('mentee_employee_id')->index();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('matching_score', 5, 2)->nullable();
            $table->string('status', 30)->default('active'); // active, completed, terminated
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('career_mentoring_programs')->nullOnDelete();
            $table->foreign('mentor_employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('mentee_employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['tenant_id', 'mentor_employee_id', 'mentee_employee_id', 'status'], 'career_mentor_pair_unique');
        });

        // 18. Mentoring Goals
        Schema::create('career_mentoring_goals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('relationship_id')->index();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->date('target_date')->nullable();
            $table->string('status', 30)->default('in_progress'); // in_progress, achieved, cancelled
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('relationship_id')->references('id')->on('career_mentoring_relationships')->cascadeOnDelete();
        });

        // 19. Career Job Rotations
        Schema::create('career_job_rotations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('current_job_id')->index();
            $table->uuid('rotation_job_id')->index();
            $table->uuid('department_id')->nullable()->index();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('objective')->nullable();
            $table->string('status', 30)->default('planned'); // planned, active, completed, cancelled
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('current_job_id')->references('id')->on('organization_job_definitions')->cascadeOnDelete();
            $table->foreign('rotation_job_id')->references('id')->on('organization_job_definitions')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });

        // 20. Stretch Assignments
        Schema::create('career_stretch_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->uuid('leader_employee_id')->nullable()->index();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->json('skills_targeted')->nullable();
            $table->string('status', 30)->default('active'); // active, completed, cancelled
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('leader_employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 21. Career Talent Evidence
        Schema::create('career_talent_evidence', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('evidence_source', 40); // performance, learning, certification, project, manager_assessment, skill_assessment, experience, recognition
            $table->string('source_id', 100)->nullable();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->decimal('impact_rating', 4, 2)->nullable();
            $table->uuid('recorded_by')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 22. Talent Pools
        Schema::create('talent_pools', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->json('criteria')->nullable(); // rules for high potential, technical experts, future leaders
            $table->uuid('owner_id')->nullable();
            $table->string('status', 30)->default('active');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 23. Talent Pool Members
        Schema::create('talent_pool_members', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('pool_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('added_by')->nullable();
            $table->timestamp('added_at');
            $table->text('reason')->nullable();
            $table->string('status', 30)->default('active'); // active, graduated, removed
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('pool_id')->references('id')->on('talent_pools')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['pool_id', 'employee_id']);
        });

        // 24. Talent Review Sessions (Calibration / 9-Box Sessions)
        Schema::create('talent_review_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->string('title', 200);
            $table->string('scope_type', 40)->default('organization'); // department, business_unit, location, leadership_group
            $table->uuid('scope_id')->nullable();
            $table->date('review_date');
            $table->string('status', 30)->default('draft'); // draft, manager_input, hr_review, calibration, completed, published
            $table->uuid('workflow_instance_id')->nullable();
            $table->uuid('finalized_by')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 25. Talent Review Records (Individual 9-Box Placements)
        Schema::create('talent_review_records', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('session_id')->index();
            $table->uuid('employee_id')->index();
            $table->decimal('performance_rating', 5, 2)->default(3.0); // 1-5
            $table->decimal('potential_rating', 5, 2)->default(3.0); // 1-5 (Low, Med, High)
            $table->string('nine_box_position', 40)->default('medium_performance_medium_potential'); // 9 grid cells
            $table->string('readiness_level', 40)->default('developing');
            $table->string('retention_risk', 30)->default('low'); // low, medium, high, critical
            $table->string('vacancy_risk', 30)->default('low');
            $table->string('mobility_rating', 30)->default('high');
            $table->string('development_priority', 40)->default('core_development');
            $table->text('manager_notes')->nullable();
            $table->text('calibration_notes')->nullable();
            $table->text('override_reason')->nullable();
            $table->boolean('is_overridden')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('session_id')->references('id')->on('talent_review_sessions')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['session_id', 'employee_id']);
        });

        // 26. Succession Plans
        Schema::create('succession_plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('scope_type', 40)->default('organization');
            $table->uuid('scope_id')->nullable();
            $table->uuid('owner_id')->nullable();
            $table->date('review_date')->nullable();
            $table->string('status', 30)->default('draft'); // draft, active, under_review, completed, archived
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 27. Succession Positions (Critical Positions under Succession Management)
        Schema::create('succession_positions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->uuid('succession_plan_id')->index();
            $table->uuid('position_id')->index();
            $table->uuid('job_id')->index();
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('current_incumbent_id')->nullable()->index();
            $table->string('criticality', 40)->default('critical'); // critical, business_critical, leadership_critical, technical_critical
            $table->string('vacancy_risk', 30)->default('medium'); // low, medium, high, critical
            $table->string('retirement_risk', 30)->default('low');
            $table->decimal('risk_score', 5, 2)->default(50.0);
            $table->string('status', 30)->default('active');
            $table->uuid('emergency_successor_id')->nullable()->index();
            $table->uuid('interim_successor_id')->nullable()->index();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('succession_plan_id')->references('id')->on('succession_plans')->cascadeOnDelete();
            $table->foreign('position_id')->references('id')->on('positions')->cascadeOnDelete();
            $table->foreign('job_id')->references('id')->on('organization_job_definitions')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('current_incumbent_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('emergency_successor_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('interim_successor_id')->references('id')->on('employees')->nullOnDelete();
            $table->unique(['succession_plan_id', 'position_id']);
        });

        // 28. Succession Candidates
        Schema::create('succession_candidates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->uuid('succession_position_id')->index();
            $table->uuid('employee_id')->index();
            $table->unsignedSmallInteger('priority')->default(1); // 1st choice, 2nd choice
            $table->string('readiness_timeframe', 40)->default('ready_1_to_2_years'); // ready_now, ready_under_1_year, ready_1_to_2_years, ready_2_to_3_years, long_term
            $table->decimal('readiness_score', 5, 2)->default(0.0);
            $table->decimal('potential_rating', 5, 2)->nullable();
            $table->decimal('performance_rating', 5, 2)->nullable();
            $table->string('risk_level', 30)->default('low');
            $table->string('development_status', 40)->default('in_progress');
            $table->boolean('is_emergency_choice')->default(false);
            $table->string('override_readiness', 40)->nullable();
            $table->text('override_reason')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('succession_position_id')->references('id')->on('succession_positions')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['succession_position_id', 'employee_id']);
        });

        // 29. Succession Development Actions
        Schema::create('succession_development_actions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('candidate_id')->index();
            $table->string('action_type', 40); // training, mentoring, coaching, job_rotation, project_leadership, acting_assignment, certification, cross_functional
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->string('status', 30)->default('planned');
            $table->decimal('completion_percentage', 5, 2)->default(0.0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('candidate_id')->references('id')->on('succession_candidates')->cascadeOnDelete();
        });

        // 30. Succession Scenarios (Simulations)
        Schema::create('succession_scenarios', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->index();
            $table->uuid('succession_plan_id')->index();
            $table->string('name', 200);
            $table->string('trigger_event', 50); // incumbent_leaves, incumbent_promoted, incumbent_retires, position_expands, new_position
            $table->text('description')->nullable();
            $table->string('status', 30)->default('draft'); // draft, active, simulated
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('succession_plan_id')->references('id')->on('succession_plans')->cascadeOnDelete();
        });

        // 31. Succession Scenario Candidates (Simulated placements)
        Schema::create('succession_scenario_candidates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('scenario_id')->index();
            $table->uuid('succession_position_id')->index();
            $table->uuid('proposed_successor_id')->index();
            $table->string('role_assignment', 50)->default('permanent'); // permanent, interim, acting
            $table->text('impact_analysis')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('scenario_id')->references('id')->on('succession_scenarios')->cascadeOnDelete();
            $table->foreign('succession_position_id')->references('id')->on('succession_positions')->cascadeOnDelete();
            $table->foreign('proposed_successor_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('succession_scenario_candidates');
        Schema::dropIfExists('succession_scenarios');
        Schema::dropIfExists('succession_development_actions');
        Schema::dropIfExists('succession_candidates');
        Schema::dropIfExists('succession_positions');
        Schema::dropIfExists('succession_plans');
        Schema::dropIfExists('talent_review_records');
        Schema::dropIfExists('talent_review_sessions');
        Schema::dropIfExists('talent_pool_members');
        Schema::dropIfExists('talent_pools');
        Schema::dropIfExists('career_talent_evidence');
        Schema::dropIfExists('career_stretch_assignments');
        Schema::dropIfExists('career_job_rotations');
        Schema::dropIfExists('career_mentoring_goals');
        Schema::dropIfExists('career_mentoring_relationships');
        Schema::dropIfExists('career_mentoring_programs');
        Schema::dropIfExists('career_mobility_preferences');
        Schema::dropIfExists('career_plan_actions');
        Schema::dropIfExists('career_plans');
        Schema::dropIfExists('career_development_recommendations');
        Schema::dropIfExists('career_path_steps');
        Schema::dropIfExists('career_paths');
        Schema::dropIfExists('employee_career_aspirations');
        Schema::dropIfExists('career_skill_gaps');
        Schema::dropIfExists('career_job_skill_requirements');
        Schema::dropIfExists('employee_skill_evidence');
        Schema::dropIfExists('employee_skills');
        Schema::dropIfExists('career_skill_competency_mappings');
        Schema::dropIfExists('career_skill_levels');
        Schema::dropIfExists('career_skills');
        Schema::dropIfExists('career_skill_categories');
    }
};
