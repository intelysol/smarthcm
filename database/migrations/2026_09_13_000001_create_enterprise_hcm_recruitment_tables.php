<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Recruitment Sources
        Schema::create('hcm_recruitment_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 100);
            $table->string('code', 50);
            $table->string('type', 40)->default('direct'); // career_site, referral, agency, job_board, linkedin, university, direct
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 2. Job Templates
        Schema::create('hcm_recruitment_job_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('title', 150);
            $table->string('code', 50);
            $table->text('job_summary')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('required_skills')->nullable();
            $table->json('preferred_skills')->nullable();
            $table->unsignedInteger('required_experience_years')->default(0);
            $table->string('education_level', 100)->nullable();
            $table->decimal('default_min_salary', 15, 2)->nullable();
            $table->decimal('default_max_salary', 15, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 3. Requisitions
        Schema::create('hcm_recruitment_requisitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('requisition_number', 50);
            $table->string('title', 150);
            $table->uuid('position_id')->nullable()->index(); // Core HR position
            $table->uuid('job_template_id')->nullable()->index();
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('location_id')->nullable()->index();
            $table->uuid('hiring_manager_id')->nullable()->index(); // Core HR Employee
            $table->uuid('recruiter_id')->nullable()->index(); // User
            $table->string('employment_type', 30)->default('full_time'); // full_time, part_time, contract, intern
            $table->unsignedInteger('openings')->default(1);
            $table->string('priority', 20)->default('medium'); // low, medium, high, critical
            $table->string('reason', 40)->default('growth'); // new_position, replacement, growth, expansion, backfill
            $table->decimal('min_salary', 15, 2)->nullable();
            $table->decimal('max_salary', 15, 2)->nullable();
            $table->decimal('budget_amount', 15, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->date('target_start_date')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('draft'); // draft, submitted, under_review, approved, open, hiring, filled, closed, cancelled, on_hold
            $table->boolean('is_confidential')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Workforce Planning Traceability (Epic 2.24)
            $table->uuid('workforce_plan_id')->nullable()->index();
            $table->uuid('position_plan_id')->nullable()->index();
            $table->uuid('hiring_plan_id')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'requisition_number']);
        });

        // 4. Requisition Approvals
        Schema::create('hcm_recruitment_requisition_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('requisition_id')->index();
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('stage', 50)->default('department_head'); // department_head, hr_head, finance_head
            $table->string('status', 30)->default('pending'); // pending, approved, rejected
            $table->text('comments')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('requisition_id')->references('id')->on('hcm_recruitment_requisitions')->cascadeOnDelete();
        });

        // 5. Job Postings
        Schema::create('hcm_recruitment_job_postings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('requisition_id')->index();
            $table->string('title', 150);
            $table->string('slug', 180)->index();
            $table->text('summary')->nullable();
            $table->longText('description');
            $table->string('posting_type', 30)->default('both'); // internal, external, both
            $table->string('location_display', 100)->nullable();
            $table->boolean('is_remote')->default(false);
            $table->string('status', 30)->default('draft'); // draft, published, closed, expired
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('requisition_id')->references('id')->on('hcm_recruitment_requisitions')->cascadeOnDelete();
            $table->unique(['tenant_id', 'slug']);
        });

        // 6. Candidates
        Schema::create('hcm_recruitment_candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('candidate_number', 50);
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 150)->index();
            $table->string('phone', 50)->nullable()->index();
            $table->string('location', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->uuid('source_id')->nullable()->index();
            $table->string('consent_status', 30)->default('given'); // given, pending, withdrawn
            $table->timestamp('consent_at')->nullable();
            $table->string('status', 30)->default('active'); // active, archived, blacklisted, hired
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('source_id')->references('id')->on('hcm_recruitment_sources')->nullOnDelete();
            $table->unique(['tenant_id', 'candidate_number']);
        });

        // 7. Candidate Profiles
        Schema::create('hcm_recruitment_candidate_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('candidate_id')->unique();
            $table->text('headline')->nullable();
            $table->text('summary')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('resume_filename')->nullable();
            $table->decimal('current_salary', 15, 2)->nullable();
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->unsignedInteger('notice_period_days')->default(0);
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->json('skills')->nullable();
            $table->json('experience_history')->nullable();
            $table->json('education_history')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('candidate_id')->references('id')->on('hcm_recruitment_candidates')->cascadeOnDelete();
        });

        // 8. Candidate Consents & Privacy
        Schema::create('hcm_recruitment_candidate_consents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('candidate_id')->index();
            $table->string('consent_type', 50)->default('recruitment_processing'); // recruitment_processing, talent_pool, background_check
            $table->string('version', 20)->default('v1.0');
            $table->boolean('is_granted')->default(true);
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->date('retention_expiry_date')->nullable();
            $table->boolean('is_legal_hold')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('candidate_id')->references('id')->on('hcm_recruitment_candidates')->cascadeOnDelete();
        });

        // 9. Candidate Tags
        Schema::create('hcm_recruitment_candidate_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('candidate_id')->index();
            $table->string('tag', 50);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('candidate_id')->references('id')->on('hcm_recruitment_candidates')->cascadeOnDelete();
            $table->unique(['tenant_id', 'candidate_id', 'tag']);
        });

        // 10. Talent Pools
        Schema::create('hcm_recruitment_talent_pools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('job_family', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('hcm_recruitment_talent_pool_candidates', function (Blueprint $table) {
            $table->uuid('tenant_id')->index();
            $table->uuid('talent_pool_id')->index();
            $table->uuid('candidate_id')->index();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('talent_pool_id')->references('id')->on('hcm_recruitment_talent_pools')->cascadeOnDelete();
            $table->foreign('candidate_id')->references('id')->on('hcm_recruitment_candidates')->cascadeOnDelete();
            $table->primary(['talent_pool_id', 'candidate_id']);
        });

        // 11. Pipeline Stages
        Schema::create('hcm_recruitment_application_stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 50);
            $table->string('code', 50);
            $table->unsignedInteger('stage_order')->default(1);
            $table->boolean('is_system_stage')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 12. Applications
        Schema::create('hcm_recruitment_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('application_number', 50);
            $table->uuid('candidate_id')->index();
            $table->uuid('requisition_id')->index();
            $table->uuid('stage_id')->nullable()->index();
            $table->string('status', 30)->default('new'); // new, screening, shortlisted, interview, assessment, offer, hired, rejected, withdrawn, on_hold
            $table->uuid('source_id')->nullable()->index();
            $table->text('cover_letter')->nullable();
            $table->decimal('screening_score', 5, 2)->nullable();
            $table->text('screening_notes')->nullable();
            $table->string('rejection_reason', 150)->nullable();
            $table->timestamp('applied_at');
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('hired_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('candidate_id')->references('id')->on('hcm_recruitment_candidates')->cascadeOnDelete();
            $table->foreign('requisition_id')->references('id')->on('hcm_recruitment_requisitions')->cascadeOnDelete();
            $table->foreign('stage_id')->references('id')->on('hcm_recruitment_application_stages')->nullOnDelete();
            $table->foreign('source_id')->references('id')->on('hcm_recruitment_sources')->nullOnDelete();
            $table->unique(['tenant_id', 'application_number']);
            $table->unique(['candidate_id', 'requisition_id']);
        });

        // 13. Application Activities & Audit Log
        Schema::create('hcm_recruitment_application_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('application_id')->index();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity_type', 50); // stage_change, status_change, note_added, interview_scheduled, offer_created
            $table->string('from_state', 50)->nullable();
            $table->string('to_state', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('application_id')->references('id')->on('hcm_recruitment_applications')->cascadeOnDelete();
        });

        // 14. Screenings
        Schema::create('hcm_recruitment_screenings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('application_id')->index();
            $table->foreignId('screened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('skills_match')->default(false);
            $table->boolean('experience_match')->default(false);
            $table->boolean('education_match')->default(false);
            $table->boolean('salary_match')->default(false);
            $table->string('result', 30)->default('passed'); // passed, failed, on_hold
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('application_id')->references('id')->on('hcm_recruitment_applications')->cascadeOnDelete();
        });

        // 15. Interviews
        Schema::create('hcm_recruitment_interviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('application_id')->index();
            $table->string('title', 150);
            $table->string('interview_type', 40)->default('technical'); // phone, video, technical, panel, manager, hr, final
            $table->dateTime('scheduled_at');
            $table->unsignedInteger('duration_minutes')->default(45);
            $table->string('location', 100)->nullable();
            $table->string('meeting_url')->nullable();
            $table->string('status', 30)->default('scheduled'); // scheduled, confirmed, completed, cancelled, rescheduled, no_show
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('application_id')->references('id')->on('hcm_recruitment_applications')->cascadeOnDelete();
        });

        Schema::create('hcm_recruitment_interview_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('interview_id')->index();
            $table->foreignId('interviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 40)->default('interviewer'); // lead, interviewer, observer
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('interview_id')->references('id')->on('hcm_recruitment_interviews')->cascadeOnDelete();
            $table->unique(['interview_id', 'interviewer_id'], 'hcm_rec_int_part_int_usr_unique');
        });

        // 16. Interview Evaluations & Scorecards
        Schema::create('hcm_recruitment_interview_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('interview_id')->index();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('technical_rating', 3, 1)->default(0.0);
            $table->decimal('communication_rating', 3, 1)->default(0.0);
            $table->decimal('problem_solving_rating', 3, 1)->default(0.0);
            $table->decimal('overall_score', 3, 1)->default(0.0);
            $table->string('recommendation', 30)->default('yes'); // strong_yes, yes, neutral, no, strong_no
            $table->text('strengths')->nullable();
            $table->text('concerns')->nullable();
            $table->text('confidential_notes')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('interview_id')->references('id')->on('hcm_recruitment_interviews')->cascadeOnDelete();
            $table->unique(['interview_id', 'evaluator_id'], 'hcm_rec_int_eval_int_eval_unique');
        });

        // 17. Offers & Versioning
        Schema::create('hcm_recruitment_offers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('offer_number', 50);
            $table->uuid('application_id')->unique();
            $table->uuid('candidate_id')->index();
            $table->uuid('requisition_id')->index();
            $table->unsignedInteger('current_version')->default(1);
            $table->decimal('base_salary', 15, 2);
            $table->decimal('bonus_amount', 15, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');
            $table->date('start_date');
            $table->date('expiry_date')->nullable();
            $table->string('employment_type', 30)->default('full_time');
            $table->json('benefits_summary')->nullable();
            $table->string('status', 30)->default('draft'); // draft, under_review, approved, sent, accepted, rejected, expired, withdrawn
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('application_id')->references('id')->on('hcm_recruitment_applications')->cascadeOnDelete();
            $table->foreign('candidate_id')->references('id')->on('hcm_recruitment_candidates')->cascadeOnDelete();
            $table->foreign('requisition_id')->references('id')->on('hcm_recruitment_requisitions')->cascadeOnDelete();
            $table->unique(['tenant_id', 'offer_number']);
        });

        Schema::create('hcm_recruitment_offer_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('offer_id')->index();
            $table->unsignedInteger('version_number');
            $table->decimal('base_salary', 15, 2);
            $table->decimal('bonus_amount', 15, 2)->default(0.00);
            $table->date('start_date');
            $table->date('expiry_date')->nullable();
            $table->string('change_rationale')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('offer_id')->references('id')->on('hcm_recruitment_offers')->cascadeOnDelete();
            $table->unique(['offer_id', 'version_number']);
        });

        Schema::create('hcm_recruitment_offer_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('offer_id')->index();
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 40)->default('finance'); // hiring_manager, hr, finance, executive
            $table->string('status', 30)->default('pending'); // pending, approved, rejected
            $table->text('comments')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('offer_id')->references('id')->on('hcm_recruitment_offers')->cascadeOnDelete();
        });

        // 18. Pre-Employment / Background Checks
        Schema::create('hcm_recruitment_background_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('application_id')->index();
            $table->string('check_type', 40); // reference, identity, criminal, education, employment
            $table->string('provider_name', 100)->nullable();
            $table->string('status', 30)->default('pending'); // pending, in_progress, passed, flagged, failed
            $table->text('findings')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('application_id')->references('id')->on('hcm_recruitment_applications')->cascadeOnDelete();
        });

        // 19. Hiring Decisions (Sanctioning & Core HR Handoff)
        Schema::create('hcm_recruitment_hiring_decisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('application_id')->unique();
            $table->uuid('candidate_id')->index();
            $table->uuid('requisition_id')->index();
            $table->foreignId('decision_maker_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision', 30)->default('hire'); // hire, reject, hold
            $table->date('final_start_date');
            $table->text('decision_rationale')->nullable();
            $table->uuid('core_hr_employee_id')->nullable()->index(); // Resulting Core HR Employee ID
            $table->string('handoff_status', 30)->default('pending'); // pending, handed_off, failed
            $table->timestamp('handed_off_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('application_id')->references('id')->on('hcm_recruitment_applications')->cascadeOnDelete();
            $table->foreign('candidate_id')->references('id')->on('hcm_recruitment_candidates')->cascadeOnDelete();
            $table->foreign('requisition_id')->references('id')->on('hcm_recruitment_requisitions')->cascadeOnDelete();
        });

        // 20. Employee Referrals
        Schema::create('hcm_recruitment_referrals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('requisition_id')->index();
            $table->uuid('candidate_id')->index();
            $table->uuid('referrer_employee_id')->index(); // Core HR Employee
            $table->string('status', 30)->default('submitted'); // submitted, shortlisted, interviewed, hired, rewarded
            $table->decimal('reward_amount', 12, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('requisition_id')->references('id')->on('hcm_recruitment_requisitions')->cascadeOnDelete();
            $table->foreign('candidate_id')->references('id')->on('hcm_recruitment_candidates')->cascadeOnDelete();
        });

        // 21. Candidate Communications
        Schema::create('hcm_recruitment_candidate_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('candidate_id')->index();
            $table->uuid('application_id')->nullable()->index();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel', 30)->default('email'); // email, sms, internal_note
            $table->string('subject', 200)->nullable();
            $table->longText('message_body');
            $table->boolean('is_internal_only')->default(false); // Internal recruiter notes never exposed to candidate
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('candidate_id')->references('id')->on('hcm_recruitment_candidates')->cascadeOnDelete();
            $table->foreign('application_id')->references('id')->on('hcm_recruitment_applications')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_recruitment_candidate_messages');
        Schema::dropIfExists('hcm_recruitment_referrals');
        Schema::dropIfExists('hcm_recruitment_hiring_decisions');
        Schema::dropIfExists('hcm_recruitment_background_checks');
        Schema::dropIfExists('hcm_recruitment_offer_approvals');
        Schema::dropIfExists('hcm_recruitment_offer_versions');
        Schema::dropIfExists('hcm_recruitment_offers');
        Schema::dropIfExists('hcm_recruitment_interview_evaluations');
        Schema::dropIfExists('hcm_recruitment_interview_participants');
        Schema::dropIfExists('hcm_recruitment_interviews');
        Schema::dropIfExists('hcm_recruitment_screenings');
        Schema::dropIfExists('hcm_recruitment_application_activities');
        Schema::dropIfExists('hcm_recruitment_applications');
        Schema::dropIfExists('hcm_recruitment_application_stages');
        Schema::dropIfExists('hcm_recruitment_talent_pool_candidates');
        Schema::dropIfExists('hcm_recruitment_talent_pools');
        Schema::dropIfExists('hcm_recruitment_candidate_tags');
        Schema::dropIfExists('hcm_recruitment_candidate_consents');
        Schema::dropIfExists('hcm_recruitment_candidate_profiles');
        Schema::dropIfExists('hcm_recruitment_candidates');
        Schema::dropIfExists('hcm_recruitment_job_postings');
        Schema::dropIfExists('hcm_recruitment_requisition_approvals');
        Schema::dropIfExists('hcm_recruitment_requisitions');
        Schema::dropIfExists('hcm_recruitment_job_templates');
        Schema::dropIfExists('hcm_recruitment_sources');
    }
};
