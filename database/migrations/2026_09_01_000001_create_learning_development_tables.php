<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('learning_categories', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->string('name');
            $t->string('code', 50)->nullable();
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->unique(['tenant_id', 'name']);
        });

        Schema::create('learning_providers', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->string('name');
            $t->string('type', 30)->default('internal'); // internal, external, vendor, university, certification_body
            $t->string('contact')->nullable();
            $t->string('email')->nullable();
            $t->string('website')->nullable();
            $t->text('description')->nullable();
            $t->string('status', 20)->default('active');
            $t->timestamps();
            $t->softDeletes();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('learning_instructors', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->string('instructor_type', 30)->default('employee'); // employee, external, provider
            $t->uuid('employee_id')->nullable()->index();
            $t->uuid('provider_id')->nullable()->index();
            $t->string('name');
            $t->string('email')->nullable();
            $t->text('bio')->nullable();
            $t->json('expertise')->nullable();
            $t->string('status', 20)->default('active');
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $t->foreign('provider_id')->references('id')->on('learning_providers')->nullOnDelete();
        });

        Schema::create('learning_venues', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->string('venue_type', 30)->default('physical'); // physical, virtual, external
            $t->string('name');
            $t->string('location')->nullable();
            $t->unsignedInteger('capacity')->nullable();
            $t->string('meeting_url_reference')->nullable();
            $t->string('meeting_provider', 50)->nullable();
            $t->string('status', 20)->default('active');
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('learning_courses', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('uuid')->unique();
            $t->uuid('tenant_id')->index();
            $t->string('code', 50);
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('short_description', 500)->nullable();
            $t->uuid('category_id')->nullable()->index();
            $t->string('difficulty', 30)->default('intermediate'); // beginner, intermediate, advanced, expert
            $t->string('delivery_type', 30)->default('self_paced'); // self_paced, classroom, virtual, blended, external
            $t->decimal('duration', 8, 2)->default(1);
            $t->string('duration_unit', 20)->default('hours'); // minutes, hours, days, weeks
            $t->string('language', 10)->default('en');
            $t->string('status', 30)->default('draft'); // draft, review, published, active, suspended, archived
            $t->string('visibility', 30)->default('internal'); // public, internal, restricted, mandatory
            $t->uuid('provider_id')->nullable()->index();
            $t->unsignedInteger('current_version')->default(1);
            $t->decimal('passing_score', 5, 2)->nullable();
            $t->decimal('credit_points', 6, 2)->default(0);
            $t->unsignedInteger('max_attempts')->nullable();
            $t->boolean('requires_attendance')->default(false);
            $t->decimal('min_attendance_percentage', 5, 2)->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('category_id')->references('id')->on('learning_categories')->nullOnDelete();
            $t->foreign('provider_id')->references('id')->on('learning_providers')->nullOnDelete();
            $t->unique(['tenant_id', 'code']);
            $t->index(['tenant_id', 'status']);
        });

        Schema::create('learning_course_versions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->index();
            $t->unsignedInteger('version_number');
            $t->string('title');
            $t->text('description')->nullable();
            $t->json('content_snapshot')->nullable();
            $t->text('change_log')->nullable();
            $t->string('status', 30)->default('draft'); // draft, published, archived
            $t->timestamp('published_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
            $t->unique(['course_id', 'version_number']);
        });

        Schema::create('learning_course_objectives', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->index();
            $t->string('objective');
            $t->text('description')->nullable();
            $t->text('expected_outcome')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
        });

        Schema::create('learning_course_prerequisites', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->index();
            $t->string('prerequisite_type', 40); // course, certification, competency, learning_path
            $t->uuid('prerequisite_id')->index();
            $t->boolean('is_mandatory')->default(true);
            $t->string('min_grade_or_level')->nullable();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
        });

        Schema::create('learning_programs', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('uuid')->unique();
            $t->uuid('tenant_id')->index();
            $t->string('code', 50);
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('status', 30)->default('draft');
            $t->decimal('total_credits', 6, 2)->default(0);
            $t->string('completion_rule', 30)->default('all_required'); // all_required, minimum_credits, minimum_courses
            $t->decimal('min_required_credits', 6, 2)->nullable();
            $t->unsignedInteger('min_required_courses')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->unique(['tenant_id', 'code']);
        });

        Schema::create('learning_program_courses', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('program_id')->index();
            $t->uuid('course_id')->index();
            $t->boolean('is_required')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->foreign('program_id')->references('id')->on('learning_programs')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
            $t->unique(['program_id', 'course_id']);
        });

        Schema::create('learning_paths', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('uuid')->unique();
            $t->uuid('tenant_id')->index();
            $t->string('code', 50);
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('target_role')->nullable();
            $t->uuid('job_id')->nullable()->index();
            $t->uuid('job_grade_id')->nullable()->index();
            $t->string('status', 30)->default('draft');
            $t->string('rule_type', 30)->default('all_required');
            $t->timestamps();
            $t->softDeletes();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->unique(['tenant_id', 'code']);
        });

        Schema::create('learning_path_items', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('learning_path_id')->index();
            $t->string('item_type', 30); // course, program, assessment
            $t->uuid('item_id')->index();
            $t->string('requirement_level', 30)->default('required'); // required, optional, recommended, elective
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->foreign('learning_path_id')->references('id')->on('learning_paths')->cascadeOnDelete();
        });

        Schema::create('learning_course_modules', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->index();
            $t->uuid('course_version_id')->nullable()->index();
            $t->string('title');
            $t->text('description')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
            $t->foreign('course_version_id')->references('id')->on('learning_course_versions')->nullOnDelete();
        });

        Schema::create('learning_course_lessons', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('module_id')->index();
            $t->string('title');
            $t->text('summary')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->unsignedInteger('estimated_duration_minutes')->default(0);
            $t->timestamps();
            $t->foreign('module_id')->references('id')->on('learning_course_modules')->cascadeOnDelete();
        });

        Schema::create('learning_content', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->string('content_type', 30); // video, document, presentation, audio, article, external_link, SCORM, interactive
            $t->string('title');
            $t->longText('body')->nullable();
            $t->string('external_url')->nullable();
            $t->uuid('document_id')->nullable()->index();
            $t->json('metadata')->nullable();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('document_id')->references('id')->on('documents')->nullOnDelete();
        });

        Schema::create('learning_assessments', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('uuid')->unique();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->nullable()->index();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('assessment_type', 30)->default('quiz'); // quiz, exam, knowledge_check, pre_test, post_test
            $t->string('scoring_method', 30)->default('percentage'); // percentage, points, pass_fail, rating
            $t->decimal('total_points', 8, 2)->default(100);
            $t->decimal('passing_percentage', 5, 2)->default(70);
            $t->unsignedInteger('time_limit_minutes')->nullable();
            $t->unsignedInteger('max_attempts')->default(3);
            $t->boolean('randomize_questions')->default(false);
            $t->string('status', 20)->default('active');
            $t->timestamps();
            $t->softDeletes();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
        });

        Schema::create('learning_items', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->nullable()->index();
            $t->uuid('module_id')->nullable()->index();
            $t->uuid('lesson_id')->nullable()->index();
            $t->string('item_type', 30); // lesson, video, document, quiz, assignment, assessment, external_activity
            $t->string('title');
            $t->uuid('content_id')->nullable()->index();
            $t->uuid('assessment_id')->nullable()->index();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_mandatory')->default(true);
            $t->string('completion_rule', 30)->default('completed'); // viewed, completed, min_duration, quiz_passed, attendance
            $t->unsignedInteger('min_duration_seconds')->default(0);
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
            $t->foreign('module_id')->references('id')->on('learning_course_modules')->cascadeOnDelete();
            $t->foreign('lesson_id')->references('id')->on('learning_course_lessons')->cascadeOnDelete();
            $t->foreign('content_id')->references('id')->on('learning_content')->nullOnDelete();
            $t->foreign('assessment_id')->references('id')->on('learning_assessments')->nullOnDelete();
        });

        Schema::create('learning_questions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('assessment_id')->index();
            $t->string('question_type', 30); // single_choice, multiple_choice, true_false, short_answer, numeric, rating
            $t->text('question_text');
            $t->text('explanation')->nullable();
            $t->decimal('points', 6, 2)->default(1);
            $t->unsignedInteger('sort_order')->default(0);
            $t->json('metadata')->nullable();
            $t->timestamps();
            $t->foreign('assessment_id')->references('id')->on('learning_assessments')->cascadeOnDelete();
        });

        Schema::create('learning_question_options', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('question_id')->index();
            $t->text('option_text');
            $t->boolean('is_correct')->default(false);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->foreign('question_id')->references('id')->on('learning_questions')->cascadeOnDelete();
        });

        Schema::create('learning_sessions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->index();
            $t->uuid('course_version_id')->nullable()->index();
            $t->uuid('provider_id')->nullable()->index();
            $t->uuid('instructor_id')->nullable()->index();
            $t->uuid('venue_id')->nullable()->index();
            $t->string('title')->nullable();
            $t->dateTime('start_datetime');
            $t->dateTime('end_datetime');
            $t->unsignedInteger('capacity')->default(0);
            $t->dateTime('enrollment_deadline')->nullable();
            $t->string('status', 30)->default('scheduled'); // scheduled, open, full, in_progress, completed, cancelled
            $t->timestamps();
            $t->softDeletes();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
            $t->foreign('course_version_id')->references('id')->on('learning_course_versions')->nullOnDelete();
            $t->foreign('provider_id')->references('id')->on('learning_providers')->nullOnDelete();
            $t->foreign('instructor_id')->references('id')->on('learning_instructors')->nullOnDelete();
            $t->foreign('venue_id')->references('id')->on('learning_venues')->nullOnDelete();
        });

        Schema::create('learning_session_attendance', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('session_id')->index();
            $t->uuid('employee_id')->index();
            $t->string('status', 30)->default('present'); // present, absent, late, excused, partial
            $t->dateTime('check_in')->nullable();
            $t->dateTime('check_out')->nullable();
            $t->unsignedInteger('attendance_minutes')->default(0);
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('session_id')->references('id')->on('learning_sessions')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->unique(['session_id', 'employee_id']);
        });

        Schema::create('learning_enrollments', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('uuid')->unique();
            $t->uuid('tenant_id')->index();
            $t->uuid('employee_id')->index();
            $t->uuid('course_id')->index();
            $t->uuid('course_version_id')->nullable()->index();
            $t->uuid('program_id')->nullable()->index();
            $t->uuid('path_id')->nullable()->index();
            $t->uuid('session_id')->nullable()->index();
            $t->string('enrollment_type', 30)->default('self'); // self, manager, hr, mandatory, system, performance, compliance
            $t->string('status', 30)->default('enrolled'); // requested, approved, enrolled, in_progress, completed, rejected, cancelled, withdrawn, expired, failed
            $t->decimal('progress_percentage', 5, 2)->default(0);
            $t->dateTime('enrolled_at')->nullable();
            $t->dateTime('started_at')->nullable();
            $t->dateTime('completed_at')->nullable();
            $t->date('due_date')->nullable();
            $t->decimal('score', 5, 2)->nullable();
            $t->boolean('passed')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->unsignedInteger('version')->default(1);
            $t->timestamps();
            $t->softDeletes();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
            $t->foreign('course_version_id')->references('id')->on('learning_course_versions')->nullOnDelete();
            $t->foreign('program_id')->references('id')->on('learning_programs')->nullOnDelete();
            $t->foreign('path_id')->references('id')->on('learning_paths')->nullOnDelete();
            $t->foreign('session_id')->references('id')->on('learning_sessions')->nullOnDelete();
            $t->index(['tenant_id', 'employee_id', 'status']);
        });

        Schema::create('learning_waitlists', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('session_id')->index();
            $t->uuid('employee_id')->index();
            $t->string('status', 30)->default('waiting'); // waiting, offered, enrolled, expired, cancelled
            $t->unsignedInteger('position')->default(1);
            $t->dateTime('offered_at')->nullable();
            $t->dateTime('expires_at')->nullable();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('session_id')->references('id')->on('learning_sessions')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->unique(['session_id', 'employee_id']);
        });

        Schema::create('learning_nominations', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->index();
            $t->uuid('employee_id')->index();
            $t->uuid('nominated_by')->index();
            $t->string('status', 30)->default('pending'); // pending, accepted, declined, approved, enrolled
            $t->boolean('is_mandatory')->default(false);
            $t->text('reason')->nullable();
            $t->text('response_notes')->nullable();
            $t->dateTime('responded_at')->nullable();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->foreign('nominated_by')->references('id')->on('employees')->cascadeOnDelete();
        });

        Schema::create('learning_requirements', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('uuid')->unique();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->index();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('target_type', 40)->default('company'); // company, department, location, job, job_grade, employment_type, employee_group, individual
            $t->uuid('target_id')->nullable()->index();
            $t->string('deadline_type', 40)->default('absolute'); // absolute, relative_hiring, relative_promotion, relative_assignment
            $t->date('due_date')->nullable();
            $t->unsignedInteger('days_offset')->nullable();
            $t->boolean('is_recurring')->default(false);
            $t->unsignedInteger('recurrence_interval_months')->nullable();
            $t->string('compliance_category')->nullable();
            $t->string('status', 20)->default('active');
            $t->timestamps();
            $t->softDeletes();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
        });

        Schema::create('learning_requirement_assignments', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('requirement_id')->index();
            $t->uuid('employee_id')->index();
            $t->uuid('course_id')->index();
            $t->uuid('enrollment_id')->nullable()->index();
            $t->string('status', 30)->default('assigned'); // required, assigned, enrolled, in_progress, completed, overdue, waived, exempted
            $t->dateTime('assigned_at');
            $t->dateTime('due_at');
            $t->dateTime('completed_at')->nullable();
            $t->dateTime('waived_at')->nullable();
            $t->text('waiver_reason')->nullable();
            $t->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('requirement_id')->references('id')->on('learning_requirements')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
            $t->foreign('enrollment_id')->references('id')->on('learning_enrollments')->nullOnDelete();
            $t->unique(['requirement_id', 'employee_id']);
        });

        Schema::create('learning_progress', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('employee_id')->index();
            $t->uuid('enrollment_id')->index();
            $t->uuid('learning_item_id')->index();
            $t->string('status', 30)->default('not_started'); // not_started, in_progress, completed
            $t->decimal('progress_percentage', 5, 2)->default(0);
            $t->unsignedInteger('time_spent_seconds')->default(0);
            $t->decimal('video_watched_percentage', 5, 2)->default(0);
            $t->dateTime('started_at')->nullable();
            $t->dateTime('completed_at')->nullable();
            $t->dateTime('last_accessed_at')->nullable();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->foreign('enrollment_id')->references('id')->on('learning_enrollments')->cascadeOnDelete();
            $t->foreign('learning_item_id')->references('id')->on('learning_items')->cascadeOnDelete();
            $t->unique(['enrollment_id', 'learning_item_id']);
        });

        Schema::create('learning_credits', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('employee_id')->index();
            $t->decimal('total_earned', 8, 2)->default(0);
            $t->decimal('total_required', 8, 2)->default(0);
            $t->date('period_start')->nullable();
            $t->date('period_end')->nullable();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->unique(['tenant_id', 'employee_id']);
        });

        Schema::create('learning_credit_transactions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('employee_id')->index();
            $t->string('source_type', 40); // course_completion, external_training, conference, certification, internal_training, manual_adjustment
            $t->uuid('source_id')->nullable()->index();
            $t->decimal('credits', 6, 2);
            $t->string('description');
            $t->dateTime('awarded_at');
            $t->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        Schema::create('learning_assessment_attempts', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('assessment_id')->index();
            $t->uuid('employee_id')->index();
            $t->uuid('enrollment_id')->nullable()->index();
            $t->unsignedInteger('attempt_number')->default(1);
            $t->dateTime('started_at');
            $t->dateTime('submitted_at')->nullable();
            $t->decimal('score_obtained', 8, 2)->default(0);
            $t->decimal('score_percentage', 5, 2)->default(0);
            $t->boolean('passed')->default(false);
            $t->string('status', 30)->default('in_progress'); // in_progress, submitted, graded, expired
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('assessment_id')->references('id')->on('learning_assessments')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->foreign('enrollment_id')->references('id')->on('learning_enrollments')->nullOnDelete();
            $t->unique(['assessment_id', 'employee_id', 'attempt_number']);
        });

        Schema::create('learning_assessment_answers', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('attempt_id')->index();
            $t->uuid('question_id')->index();
            $t->uuid('selected_option_id')->nullable()->index();
            $t->json('selected_option_ids')->nullable();
            $t->text('text_answer')->nullable();
            $t->decimal('numeric_answer', 12, 4)->nullable();
            $t->boolean('is_correct')->default(false);
            $t->decimal('points_awarded', 6, 2)->default(0);
            $t->timestamps();
            $t->foreign('attempt_id')->references('id')->on('learning_assessment_attempts')->cascadeOnDelete();
            $t->foreign('question_id')->references('id')->on('learning_questions')->cascadeOnDelete();
            $t->foreign('selected_option_id')->references('id')->on('learning_question_options')->nullOnDelete();
            $t->unique(['attempt_id', 'question_id']);
        });

        Schema::create('learning_certificates', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('uuid')->unique();
            $t->uuid('tenant_id')->index();
            $t->uuid('employee_id')->index();
            $t->uuid('course_id')->nullable()->index();
            $t->uuid('course_version_id')->nullable()->index();
            $t->string('certificate_number', 100);
            $t->string('title');
            $t->date('issued_at');
            $t->date('expiry_date')->nullable();
            $t->string('status', 30)->default('active'); // active, expired, renewed, revoked, pending_verification
            $t->boolean('is_external')->default(false);
            $t->string('issuing_body')->nullable();
            $t->uuid('document_id')->nullable()->index();
            $t->string('verification_code', 64)->nullable()->index();
            $t->dateTime('verified_at')->nullable();
            $t->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->nullOnDelete();
            $t->foreign('course_version_id')->references('id')->on('learning_course_versions')->nullOnDelete();
            $t->foreign('document_id')->references('id')->on('documents')->nullOnDelete();
            $t->unique(['tenant_id', 'certificate_number']);
        });

        Schema::create('learning_certification_renewals', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('certificate_id')->index();
            $t->date('renewal_due_date');
            $t->string('status', 30)->default('pending'); // pending, in_progress, completed, overdue, waived
            $t->uuid('new_certificate_id')->nullable()->index();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('certificate_id')->references('id')->on('learning_certificates')->cascadeOnDelete();
            $t->foreign('new_certificate_id')->references('id')->on('learning_certificates')->nullOnDelete();
        });

        Schema::create('employee_learning_records', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('employee_id')->index();
            $t->uuid('course_id')->index();
            $t->uuid('course_version_id')->nullable()->index();
            $t->uuid('provider_id')->nullable()->index();
            $t->uuid('enrollment_id')->nullable()->index();
            $t->uuid('certificate_id')->nullable()->index();
            $t->date('completion_date');
            $t->decimal('final_score', 5, 2)->nullable();
            $t->decimal('credits_awarded', 6, 2)->default(0);
            $t->decimal('learning_hours', 6, 2)->default(0);
            $t->string('status', 30)->default('completed'); // completed, passed, failed, exempted
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
            $t->foreign('course_version_id')->references('id')->on('learning_course_versions')->nullOnDelete();
            $t->foreign('provider_id')->references('id')->on('learning_providers')->nullOnDelete();
            $t->foreign('enrollment_id')->references('id')->on('learning_enrollments')->nullOnDelete();
            $t->foreign('certificate_id')->references('id')->on('learning_certificates')->nullOnDelete();
        });

        Schema::create('learning_feedback', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->index();
            $t->uuid('employee_id')->index();
            $t->uuid('instructor_id')->nullable()->index();
            $t->uuid('session_id')->nullable()->index();
            $t->unsignedTinyInteger('rating_course')->default(5);
            $t->unsignedTinyInteger('rating_instructor')->nullable();
            $t->unsignedTinyInteger('rating_content')->nullable();
            $t->unsignedTinyInteger('rating_venue')->nullable();
            $t->text('comments')->nullable();
            $t->boolean('is_private')->default(true);
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->foreign('instructor_id')->references('id')->on('learning_instructors')->nullOnDelete();
            $t->foreign('session_id')->references('id')->on('learning_sessions')->nullOnDelete();
        });

        Schema::create('learning_recommendations', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('employee_id')->index();
            $t->uuid('course_id')->nullable()->index();
            $t->uuid('program_id')->nullable()->index();
            $t->uuid('path_id')->nullable()->index();
            $t->string('source', 40)->default('manager'); // performance, competency_gap, career_path, mandatory, manager, employee_interest
            $t->uuid('competency_id')->nullable()->index();
            $t->uuid('recommended_by')->nullable()->index();
            $t->text('reason')->nullable();
            $t->string('status', 30)->default('pending'); // pending, accepted, dismissed, enrolled
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->nullOnDelete();
            $t->foreign('program_id')->references('id')->on('learning_programs')->nullOnDelete();
            $t->foreign('path_id')->references('id')->on('learning_paths')->nullOnDelete();
            $t->foreign('recommended_by')->references('id')->on('employees')->nullOnDelete();
        });

        Schema::create('learning_training_costs', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->nullable()->index();
            $t->uuid('session_id')->nullable()->index();
            $t->uuid('employee_id')->nullable()->index();
            $t->uuid('provider_id')->nullable()->index();
            $t->string('cost_type', 40); // course_fee, provider_fee, travel, venue, materials, other
            $t->decimal('amount', 15, 2);
            $t->string('currency', 10)->default('USD');
            $t->string('description')->nullable();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->nullOnDelete();
            $t->foreign('session_id')->references('id')->on('learning_sessions')->nullOnDelete();
            $t->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $t->foreign('provider_id')->references('id')->on('learning_providers')->nullOnDelete();
        });

        Schema::create('learning_budgets', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->string('title');
            $t->string('fiscal_year', 20);
            $t->string('scope_type', 40)->default('company'); // company, department, location, project, cost_center
            $t->uuid('scope_id')->nullable()->index();
            $t->decimal('total_allocated', 15, 2)->default(0);
            $t->decimal('total_spent', 15, 2)->default(0);
            $t->string('currency', 10)->default('USD');
            $t->string('status', 20)->default('active');
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('learning_budget_allocations', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('budget_id')->index();
            $t->string('allocated_to_type', 40); // department, course, session, employee
            $t->uuid('allocated_to_id')->nullable()->index();
            $t->decimal('amount', 15, 2);
            $t->decimal('spent_amount', 15, 2)->default(0);
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('budget_id')->references('id')->on('learning_budgets')->cascadeOnDelete();
        });

        Schema::create('learning_course_evaluations', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->index();
            $t->uuid('course_id')->index();
            $t->decimal('pre_score_avg', 5, 2)->nullable();
            $t->decimal('post_score_avg', 5, 2)->nullable();
            $t->decimal('feedback_score_avg', 3, 2)->nullable();
            $t->decimal('completion_rate', 5, 2)->nullable();
            $t->unsignedInteger('sample_size')->default(0);
            $t->dateTime('calculated_at');
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('learning_courses')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        foreach ([
            'learning_course_evaluations',
            'learning_budget_allocations',
            'learning_budgets',
            'learning_training_costs',
            'learning_recommendations',
            'learning_feedback',
            'employee_learning_records',
            'learning_certification_renewals',
            'learning_certificates',
            'learning_assessment_answers',
            'learning_assessment_attempts',
            'learning_credit_transactions',
            'learning_credits',
            'learning_progress',
            'learning_requirement_assignments',
            'learning_requirements',
            'learning_nominations',
            'learning_waitlists',
            'learning_enrollments',
            'learning_session_attendance',
            'learning_sessions',
            'learning_question_options',
            'learning_questions',
            'learning_items',
            'learning_assessments',
            'learning_content',
            'learning_course_lessons',
            'learning_course_modules',
            'learning_path_items',
            'learning_paths',
            'learning_program_courses',
            'learning_programs',
            'learning_course_prerequisites',
            'learning_course_objectives',
            'learning_course_versions',
            'learning_courses',
            'learning_venues',
            'learning_instructors',
            'learning_providers',
            'learning_categories',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
