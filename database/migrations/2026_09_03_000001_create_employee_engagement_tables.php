<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Survey Definition
        Schema::create('engagement_surveys', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('survey_type', 40)->default('engagement');
            $table->text('instructions')->nullable();
            $table->string('confidentiality_type', 30)->default('anonymous'); // named, confidential, anonymous
            $table->boolean('allow_multiple_responses')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('draft'); // draft, review, scheduled, open, closed, archived
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 2. Survey Versions (Immutable snapshot)
        Schema::create('engagement_survey_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('survey_id')->index();
            $table->unsignedInteger('version_number');
            $table->json('snapshot');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('survey_id')->references('id')->on('engagement_surveys')->cascadeOnDelete();
            $table->unique(['survey_id', 'version_number']);
        });

        // 3. Survey Sections
        Schema::create('engagement_survey_sections', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('survey_id')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('survey_id')->references('id')->on('engagement_surveys')->cascadeOnDelete();
        });

        // 4. Centralized Question Bank
        Schema::create('engagement_question_bank', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('code', 50)->nullable();
            $table->text('question');
            $table->text('description')->nullable();
            $table->string('question_type', 40); // single_choice, multiple_choice, rating, likert, nps, text, etc.
            $table->string('category', 60); // Leadership, Wellbeing, Growth, Culture, etc.
            $table->string('dimension', 60); // Leadership, Trust, Recognition, etc.
            $table->json('default_scale')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 5. Survey Questions
        Schema::create('engagement_survey_questions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('survey_id')->index();
            $table->uuid('section_id')->nullable()->index();
            $table->uuid('bank_question_id')->nullable()->index();
            $table->text('question');
            $table->text('description')->nullable();
            $table->string('question_type', 40);
            $table->string('category', 60);
            $table->string('dimension', 60);
            $table->json('scale_config')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('survey_id')->references('id')->on('engagement_surveys')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('engagement_survey_sections')->nullOnDelete();
            $table->foreign('bank_question_id')->references('id')->on('engagement_question_bank')->nullOnDelete();
        });

        // 6. Question Branching Rules
        Schema::create('engagement_question_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('survey_id')->index();
            $table->uuid('source_question_id')->index();
            $table->uuid('target_question_id')->index();
            $table->string('operator', 30); // equals, not_equals, contains, greater_than, less_than
            $table->string('value', 255);
            $table->string('action', 30)->default('show'); // show, hide, skip_to
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('survey_id')->references('id')->on('engagement_surveys')->cascadeOnDelete();
            $table->foreign('source_question_id')->references('id')->on('engagement_survey_questions')->cascadeOnDelete();
            $table->foreign('target_question_id')->references('id')->on('engagement_survey_questions')->cascadeOnDelete();
        });

        // 7. Survey Templates
        Schema::create('engagement_survey_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('code', 50);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('survey_type', 40);
            $table->json('structure');
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 8. Survey Campaigns
        Schema::create('engagement_campaigns', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('survey_id')->index();
            $table->uuid('survey_version_id')->nullable()->index();
            $table->string('code', 50);
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('timezone', 50)->default('UTC');
            $table->string('status', 30)->default('draft'); // draft, scheduled, active, closed, cancelled
            $table->unsignedInteger('minimum_response_threshold')->default(5);
            $table->boolean('is_recurring')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('survey_id')->references('id')->on('engagement_surveys')->cascadeOnDelete();
            $table->foreign('survey_version_id')->references('id')->on('engagement_survey_versions')->nullOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 9. Campaign Schedules (Recurring Pulses)
        Schema::create('engagement_campaign_schedules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('campaign_id')->index();
            $table->string('frequency', 30); // weekly, biweekly, monthly, quarterly, custom
            $table->string('cron_expression', 100)->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('campaign_id')->references('id')->on('engagement_campaigns')->cascadeOnDelete();
        });

        // 10. Survey Audiences (Targeting)
        Schema::create('engagement_survey_audiences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('campaign_id')->index();
            $table->string('target_type', 40); // all, company, department, location, job, job_grade, employee_group, custom
            $table->uuid('target_id')->nullable();
            $table->json('filter_criteria')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('campaign_id')->references('id')->on('engagement_campaigns')->cascadeOnDelete();
        });

        // 11. Campaign Recipients (Participation eligibility without response link)
        Schema::create('engagement_campaign_recipients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('campaign_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('delivery_status', 30)->default('pending');
            $table->timestamp('invited_at')->nullable();
            $table->unsignedInteger('reminder_count')->default(0);
            $table->timestamp('last_reminded_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('campaign_id')->references('id')->on('engagement_campaigns')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['campaign_id', 'employee_id']);
        });

        // 12. Anonymous Participation Tokens (Hashed single-use tokens)
        Schema::create('engagement_participation_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('campaign_id')->index();
            $table->string('token_hash', 64)->index();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('campaign_id')->references('id')->on('engagement_campaigns')->cascadeOnDelete();
            $table->unique(['campaign_id', 'token_hash']);
        });

        // 13. Survey Responses (Decoupled from employee_id for anonymous surveys)
        Schema::create('engagement_responses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('survey_id')->index();
            $table->uuid('survey_version_id')->nullable()->index();
            $table->uuid('campaign_id')->index();
            $table->uuid('employee_id')->nullable()->index(); // NULL for anonymous surveys
            $table->string('confidentiality_type', 30)->default('anonymous');
            $table->string('response_status', 30)->default('in_progress'); // in_progress, submitted, abandoned
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->uuid('department_id')->nullable()->index(); // Coarse demographic for aggregated reporting
            $table->uuid('location_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('survey_id')->references('id')->on('engagement_surveys')->cascadeOnDelete();
            $table->foreign('survey_version_id')->references('id')->on('engagement_survey_versions')->nullOnDelete();
            $table->foreign('campaign_id')->references('id')->on('engagement_campaigns')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });

        // 14. Response Answers
        Schema::create('engagement_response_answers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('response_id')->index();
            $table->uuid('question_id')->index();
            $table->string('option_id', 50)->nullable();
            $table->decimal('numeric_value', 8, 4)->nullable();
            $table->text('text_value')->nullable();
            $table->string('nps_category', 20)->nullable(); // detractor, passive, promoter
            $table->string('favorability_status', 20)->nullable(); // favorable, neutral, unfavorable
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('response_id')->references('id')->on('engagement_responses')->cascadeOnDelete();
            $table->foreign('question_id')->references('id')->on('engagement_survey_questions')->cascadeOnDelete();
        });

        // 15. Action Plans
        Schema::create('engagement_action_plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('campaign_id')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('scope_type', 40)->default('company'); // company, department, location, team
            $table->uuid('scope_id')->nullable();
            $table->uuid('owner_id')->nullable()->index();
            $table->date('due_date')->nullable();
            $table->string('priority', 20)->default('medium');
            $table->string('status', 30)->default('open'); // open, in_progress, completed, cancelled
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('campaign_id')->references('id')->on('engagement_campaigns')->nullOnDelete();
            $table->foreign('owner_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 16. Action Items
        Schema::create('engagement_action_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('action_plan_id')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->uuid('owner_id')->nullable()->index();
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('planned');
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('action_plan_id')->references('id')->on('engagement_action_plans')->cascadeOnDelete();
            $table->foreign('owner_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 17. Engagement Goals
        Schema::create('engagement_goals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('title');
            $table->string('metric_type', 50)->default('engagement_score');
            $table->decimal('baseline_value', 8, 4);
            $table->decimal('target_value', 8, 4);
            $table->decimal('current_value', 8, 4)->nullable();
            $table->date('deadline');
            $table->uuid('owner_id')->nullable()->index();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('owner_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 18. Employee Suggestions
        Schema::create('employee_suggestions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->nullable()->index();
            $table->boolean('is_anonymous')->default(false);
            $table->string('category', 60);
            $table->string('title');
            $table->text('description');
            $table->string('status', 30)->default('submitted'); // submitted, under_review, accepted, implemented, rejected, deferred, archived
            $table->text('review_notes')->nullable();
            $table->uuid('reviewed_by')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedInteger('votes_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('employees')->nullOnDelete();
        });

        // 19. Suggestion Votes
        Schema::create('employee_suggestion_votes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('suggestion_id')->index();
            $table->uuid('employee_id')->index();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('suggestion_id')->references('id')->on('employee_suggestions')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['suggestion_id', 'employee_id']);
        });

        // 20. Employee Recognition Wall
        Schema::create('engagement_recognitions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('sender_employee_id')->index();
            $table->uuid('recipient_employee_id')->index();
            $table->string('recognition_type', 40)->default('peer'); // peer, manager, achievement, teamwork, innovation, etc.
            $table->string('value_tag', 60)->nullable();
            $table->string('title');
            $table->text('message');
            $table->string('visibility', 30)->default('team'); // private, team, department, organization
            $table->string('status', 30)->default('published'); // draft, submitted, moderation, published, archived
            $table->uuid('moderated_by')->nullable()->index();
            $table->timestamp('moderated_at')->nullable();
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('sender_employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('recipient_employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('moderated_by')->references('id')->on('employees')->nullOnDelete();
        });

        // 21. Culture Initiatives
        Schema::create('culture_initiatives', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 60);
            $table->uuid('owner_id')->nullable()->index();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('active'); // draft, planned, active, completed, on_hold, cancelled
            $table->unsignedInteger('employees_reached')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('owner_id')->references('id')->on('employees')->nullOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 22. Culture Initiative Actions
        Schema::create('culture_initiative_actions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('culture_initiative_id')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('planned');
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('culture_initiative_id')->references('id')->on('culture_initiatives')->cascadeOnDelete();
        });

        // 23. Text Analysis (Aggregated topics & sentiment assistance without raw respondent link)
        Schema::create('engagement_text_analysis', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('campaign_id')->index();
            $table->uuid('question_id')->nullable()->index();
            $table->string('topic', 100);
            $table->string('sentiment', 20); // positive, neutral, negative
            $table->decimal('confidence', 5, 4)->default(0);
            $table->unsignedInteger('sample_count')->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('campaign_id')->references('id')->on('engagement_campaigns')->cascadeOnDelete();
            $table->foreign('question_id')->references('id')->on('engagement_survey_questions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_text_analysis');
        Schema::dropIfExists('culture_initiative_actions');
        Schema::dropIfExists('culture_initiatives');
        Schema::dropIfExists('engagement_recognitions');
        Schema::dropIfExists('employee_suggestion_votes');
        Schema::dropIfExists('employee_suggestions');
        Schema::dropIfExists('engagement_goals');
        Schema::dropIfExists('engagement_action_items');
        Schema::dropIfExists('engagement_action_plans');
        Schema::dropIfExists('engagement_response_answers');
        Schema::dropIfExists('engagement_responses');
        Schema::dropIfExists('engagement_participation_tokens');
        Schema::dropIfExists('engagement_campaign_recipients');
        Schema::dropIfExists('engagement_survey_audiences');
        Schema::dropIfExists('engagement_campaign_schedules');
        Schema::dropIfExists('engagement_campaigns');
        Schema::dropIfExists('engagement_survey_templates');
        Schema::dropIfExists('engagement_question_rules');
        Schema::dropIfExists('engagement_survey_questions');
        Schema::dropIfExists('engagement_question_bank');
        Schema::dropIfExists('engagement_survey_sections');
        Schema::dropIfExists('engagement_survey_versions');
        Schema::dropIfExists('engagement_surveys');
    }
};
