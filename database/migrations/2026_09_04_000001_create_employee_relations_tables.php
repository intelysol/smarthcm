<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Case Types (Configurable)
        Schema::create('employee_relation_case_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('code', 60);
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('category', 60)->default('workplace_conduct');
            $table->string('default_priority', 30)->default('normal'); // low, normal, high, urgent, critical
            $table->string('default_severity', 30)->default('moderate'); // informational, minor, moderate, serious, critical
            $table->string('default_confidentiality', 40)->default('standard_confidential');
            $table->boolean('is_anonymous_allowed')->default(true);
            $table->boolean('is_self_service_allowed')->default(true);
            $table->boolean('is_system')->default(false);
            $table->string('status', 30)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 2. Policy References
        Schema::create('employee_relation_policy_references', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('policy_code', 60)->nullable();
            $table->string('policy_name', 200);
            $table->string('category', 60)->default('company_policy'); // company_policy, hr_policy, code_of_conduct, safety_policy, attendance_policy, other
            $table->string('section_clause', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('document_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 3. Employee Relation Cases (Main Entity)
        Schema::create('employee_relation_cases', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('case_number', 60);
            $table->uuid('case_type_id')->index();
            $table->uuid('subject_employee_id')->nullable()->index();
            $table->string('subject_type', 40)->default('employee'); // employee, former_employee, applicant, manager, department, workplace, external_party
            $table->string('subject_name', 150)->nullable();
            $table->string('title', 255);
            $table->longText('summary');
            $table->string('priority', 30)->default('normal'); // low, normal, high, urgent, critical
            $table->string('severity', 30)->default('moderate'); // informational, minor, moderate, serious, critical
            $table->string('status', 40)->default('submitted'); // draft, submitted, triage, assigned, investigation, review, decision, action, appeal, closed, rejected, withdrawn, cancelled, on_hold, archived
            $table->string('confidentiality_level', 40)->default('standard_confidential'); // standard_confidential, highly_confidential, restricted, legal_restricted
            $table->date('incident_date')->nullable();
            $table->string('incident_location', 255)->nullable();
            $table->timestamp('opened_at')->useCurrent();
            $table->date('target_resolution_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_type_id')->references('id')->on('employee_relation_case_types')->cascadeOnDelete();
            $table->foreign('subject_employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->unique(['tenant_id', 'case_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'priority']);
            $table->index(['tenant_id', 'confidentiality_level']);
        });

        // 4. Case Reporters (Separated for Anonymity Protection)
        Schema::create('employee_relation_case_reporters', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('reporter_type', 40)->default('employee'); // employee, manager, hr, anonymous, external, system
            $table->uuid('reporter_employee_id')->nullable()->index();
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_name', 150)->nullable();
            $table->string('reporter_email', 150)->nullable();
            $table->string('reporter_phone', 50)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('allow_followup')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->foreign('reporter_employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 5. Case Tokens (For Anonymous Follow-up)
        Schema::create('employee_relation_case_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('token_hash', 64)->index(); // SHA-256 hash of raw token
            $table->timestamp('expires_at');
            $table->string('status', 30)->default('active'); // active, revoked, expired
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->unique(['tenant_id', 'token_hash']);
        });

        // 6. Case Triage
        Schema::create('employee_relation_case_triage', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->foreignId('triaged_by')->constrained('users')->cascadeOnDelete();
            $table->uuid('recommended_case_type_id')->nullable()->index();
            $table->string('recommended_priority', 30)->default('normal');
            $table->string('recommended_severity', 30)->default('moderate');
            $table->boolean('requires_investigation')->default(true);
            $table->string('required_investigator_type', 60)->nullable();
            $table->boolean('conflict_of_interest_checked')->default(false);
            $table->boolean('escalation_required')->default(false);
            $table->text('escalation_reason')->nullable();
            $table->text('triage_notes')->nullable();
            $table->timestamp('triaged_at')->useCurrent();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->foreign('recommended_case_type_id')->references('id')->on('employee_relation_case_types')->nullOnDelete();
        });

        // 7. Case Assignments
        Schema::create('employee_relation_case_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 40); // case_owner, hr_partner, investigator, reviewer, decision_maker, legal_reviewer
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->string('status', 30)->default('active'); // active, reassigned, completed, revoked
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->index(['tenant_id', 'user_id', 'status']);
        });

        // 8. Case Participants
        Schema::create('employee_relation_case_participants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('participant_type', 40); // subject, reporter, witness, investigator, manager, hr, reviewer, decision_maker, legal, support
            $table->uuid('employee_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 150)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('role_title', 100)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('access_restricted')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 9. Case Conflicts of Interest
        Schema::create('employee_relation_case_conflicts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('declaration', 40)->default('no_conflict'); // no_conflict, conflict_exists, potential_conflict
            $table->text('reason')->nullable();
            $table->string('relationship_type', 60)->nullable(); // manages_subject, close_reporting, named_in_complaint, is_reporter, is_witness, self_declared, other
            $table->string('status', 30)->default('pending'); // pending, reviewed, cleared, disqualified
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
        });

        // 10. Investigations
        Schema::create('employee_relation_investigations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->foreignId('investigator_id')->constrained('users')->cascadeOnDelete();
            $table->longText('scope');
            $table->date('start_date')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->string('status', 40)->default('active'); // not_started, active, paused, completed, cancelled
            $table->timestamp('completed_at')->nullable();
            $table->longText('summary_findings')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
        });

        // 11. Investigation Steps (Plan)
        Schema::create('employee_relation_investigation_steps', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('investigation_id')->index();
            $table->uuid('case_id')->index();
            $table->string('step_type', 60)->default('collect_evidence'); // collect_evidence, interview_reporter, interview_subject, interview_witness, review_documents, review_system_records, prepare_findings, custom
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending'); // pending, in_progress, completed, cancelled, overdue
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('investigation_id')->references('id')->on('employee_relation_investigations')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
        });

        // 12. Investigation Questions
        Schema::create('employee_relation_investigation_questions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('investigation_id')->index();
            $table->uuid('case_id')->index();
            $table->string('target_role', 40)->default('witness'); // reporter, subject, witness, investigator
            $table->uuid('participant_id')->nullable()->index();
            $table->text('question');
            $table->string('expected_response_type', 30)->default('text'); // text, boolean, rating, file
            $table->longText('answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('investigation_id')->references('id')->on('employee_relation_investigations')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->foreign('participant_id')->references('id')->on('employee_relation_case_participants')->nullOnDelete();
        });

        // 13. Allegations
        Schema::create('employee_relation_allegations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('allegation_number', 40);
            $table->string('title', 255);
            $table->longText('description');
            $table->uuid('policy_reference_id')->nullable()->index();
            $table->date('incident_date')->nullable();
            $table->string('status', 40)->default('under_investigation'); // draft, under_investigation, finding_recorded, withdrawn
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->foreign('policy_reference_id')->references('id')->on('employee_relation_policy_references')->nullOnDelete();
        });

        // 14. Statements
        Schema::create('employee_relation_statements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->uuid('participant_id')->index();
            $table->string('statement_type', 40)->default('witness'); // initial, witness, response, investigation, appeal
            $table->longText('content');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_confidential')->default(true);
            $table->unsignedInteger('current_version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->foreign('participant_id')->references('id')->on('employee_relation_case_participants')->cascadeOnDelete();
        });

        // 15. Statement Versions
        Schema::create('employee_relation_statement_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('statement_id')->index();
            $table->unsignedInteger('version_number');
            $table->longText('content');
            $table->text('change_reason')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('statement_id')->references('id')->on('employee_relation_statements')->cascadeOnDelete();
            $table->unique(['statement_id', 'version_number']);
        });

        // 16. Interviews
        Schema::create('employee_relation_interviews', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->uuid('participant_id')->index();
            $table->foreignId('investigator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('scheduled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('location', 255)->nullable();
            $table->string('format', 30)->default('in_person'); // in_person, video, phone, written
            $table->string('status', 30)->default('scheduled'); // scheduled, in_progress, completed, cancelled, rescheduled
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->foreign('participant_id')->references('id')->on('employee_relation_case_participants')->cascadeOnDelete();
        });

        // 17. Interview Notes
        Schema::create('employee_relation_interview_notes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('interview_id')->index();
            $table->uuid('case_id')->index();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->longText('notes');
            $table->boolean('is_confidential')->default(true);
            $table->string('visibility', 40)->default('investigator_only'); // investigator_only, hr_only, legal_only, case_team
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('interview_id')->references('id')->on('employee_relation_interviews')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
        });

        // 18. Evidence
        Schema::create('employee_relation_evidence', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('evidence_number', 40);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('evidence_type', 40)->default('document'); // document, image, video, audio, email, message, system_record, statement, other
            $table->string('source', 150);
            $table->foreignId('collected_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('received_at')->useCurrent();
            $table->string('file_path')->nullable();
            $table->uuid('document_id')->nullable()->index(); // Document Platform Integration
            $table->string('sha256_hash', 64)->nullable();
            $table->string('confidentiality', 40)->default('standard_confidential');
            $table->string('status', 30)->default('retained'); // retained, superseded, restricted, disposed
            $table->boolean('is_relevant')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->foreign('document_id')->references('id')->on('documents')->nullOnDelete();
        });

        // 19. Evidence History (Chain & Audit)
        Schema::create('employee_relation_evidence_history', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('evidence_id')->index();
            $table->uuid('case_id')->index();
            $table->string('action', 40); // uploaded, viewed, replaced, downloaded, accessed, marked_relevant, marked_irrelevant, disposed, status_changed
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('evidence_id')->references('id')->on('employee_relation_evidence')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
        });

        // 20. Case Notes
        Schema::create('employee_relation_case_notes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note_type', 40)->default('general_note'); // internal_hr_note, investigator_note, legal_note, decision_note, general_note
            $table->string('visibility', 40)->default('case_team'); // case_team, hr_only, investigator_only, legal_only, decision_maker
            $table->longText('content');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
        });

        // 21. Case Tasks
        Schema::create('employee_relation_case_tasks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('task_type', 50)->default('collect_evidence'); // collect_evidence, interview_employee, review_document, prepare_findings, send_notice, schedule_hearing, prepare_decision, custom
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('pending'); // pending, in_progress, completed, cancelled, overdue
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
        });

        // 22. Case SLAs
        Schema::create('employee_relation_case_slas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('sla_type', 40); // initial_response, triage, investigation, resolution, appeal
            $table->unsignedInteger('target_hours')->default(48);
            $table->timestamp('due_at');
            $table->timestamp('breached_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('escalation_sent_at')->nullable();
            $table->string('status', 30)->default('pending'); // pending, met, breached, exempt
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->index(['tenant_id', 'status', 'due_at']);
        });

        // 23. Case Hearings
        Schema::create('employee_relation_hearings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('title', 255);
            $table->timestamp('scheduled_at');
            $table->string('location', 255)->nullable();
            $table->foreignId('chairperson_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('scheduled'); // scheduled, in_progress, completed, cancelled, adjourned
            $table->longText('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
        });

        // 24. Hearing Outcomes
        Schema::create('employee_relation_hearing_outcomes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hearing_id')->index();
            $table->uuid('case_id')->index();
            $table->foreignId('entered_by')->constrained('users')->cascadeOnDelete();
            $table->longText('summary');
            $table->longText('recommendations')->nullable();
            $table->timestamp('entered_at')->useCurrent();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('hearing_id')->references('id')->on('employee_relation_hearings')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
        });

        // 25. Findings
        Schema::create('employee_relation_findings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->uuid('allegation_id')->nullable()->index();
            $table->foreignId('investigator_id')->constrained('users')->cascadeOnDelete();
            $table->string('finding', 40); // substantiated, partially_substantiated, unsubstantiated, inconclusive, withdrawn
            $table->string('confidence', 30)->default('high'); // low, medium, high
            $table->longText('rationale');
            $table->text('evidence_summary')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->foreign('allegation_id')->references('id')->on('employee_relation_allegations')->nullOnDelete();
        });

        // 26. Decisions
        Schema::create('employee_relation_decisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->foreignId('decision_maker_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision', 40); // no_action, counselling, verbal_warning, written_warning, final_warning, training_required, corrective_action, other
            $table->longText('reason');
            $table->date('effective_date')->nullable();
            $table->timestamp('decided_at')->useCurrent();
            $table->boolean('is_draft')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
        });

        // 27. Corrective Actions
        Schema::create('employee_relation_corrective_actions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->uuid('decision_id')->nullable()->index();
            $table->string('action_type', 50)->default('counselling'); // training, counselling, policy_acknowledgement, behavior_plan, attendance_improvement, performance_follow_up, written_warning, custom
            $table->text('description');
            $table->uuid('assigned_to_employee_id')->index();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->date('due_date');
            $table->string('status', 40)->default('assigned'); // assigned, employee_acknowledged, in_progress, completed, verified, overdue, cancelled
            $table->string('employee_acknowledgement_status', 30)->default('pending'); // pending, acknowledged, commented, declined
            $table->timestamp('employee_acknowledged_at')->nullable();
            $table->text('employee_comment')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->foreign('decision_id')->references('id')->on('employee_relation_decisions')->nullOnDelete();
            $table->foreign('assigned_to_employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 28. Appeals
        Schema::create('employee_relation_appeals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('appeal_number', 60);
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->longText('reason');
            $table->text('grounds')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 40)->default('submitted'); // submitted, under_review, decision_pending, resolved, rejected, withdrawn, closed
            $table->string('decision', 40)->nullable(); // upheld, partially_upheld, overturned, rejected, withdrawn, modified
            $table->longText('decision_reason')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->unique(['tenant_id', 'appeal_number']);
        });

        // 29. Correspondence Templates
        Schema::create('employee_relation_correspondence_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('code', 60);
            $table->string('name', 150);
            $table->string('template_type', 50)->default('outcome_notice'); // case_acknowledgement, interview_invitation, hearing_notice, outcome_notice, warning, appeal_acknowledgement, case_closure, custom
            $table->string('subject_template', 255);
            $table->longText('body_template');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        // 30. Correspondence
        Schema::create('employee_relation_correspondence', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('channel', 30)->default('system_message'); // email, letter, notice, memo, system_message
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_email', 150)->nullable();
            $table->string('recipient_name', 150)->nullable();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject', 255);
            $table->longText('body');
            $table->timestamp('sent_at')->nullable();
            $table->string('delivery_status', 30)->default('sent'); // draft, sent, delivered, failed
            $table->uuid('template_id')->nullable()->index();
            $table->uuid('document_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->foreign('template_id')->references('id')->on('employee_relation_correspondence_templates')->nullOnDelete();
            $table->foreign('document_id')->references('id')->on('documents')->nullOnDelete();
        });

        // 31. Retention Policies
        Schema::create('employee_relation_retention_policies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('name', 150);
            $table->uuid('case_type_id')->nullable()->index();
            $table->string('severity', 30)->nullable();
            $table->unsignedInteger('retention_years')->default(7);
            $table->string('legal_basis', 255)->nullable();
            $table->boolean('auto_dispose_eligible')->default(false);
            $table->string('status', 30)->default('active'); // active, inactive
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_type_id')->references('id')->on('employee_relation_case_types')->nullOnDelete();
        });

        // 32. Legal Holds
        Schema::create('employee_relation_legal_holds', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('case_id')->index();
            $table->string('hold_reference', 60);
            $table->text('reason');
            $table->foreignId('placed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('placed_at')->useCurrent();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->text('release_reason')->nullable();
            $table->string('status', 30)->default('active'); // active, released
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('case_id')->references('id')->on('employee_relation_cases')->cascadeOnDelete();
            $table->unique(['tenant_id', 'hold_reference']);
            $table->index(['tenant_id', 'case_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_relation_legal_holds');
        Schema::dropIfExists('employee_relation_retention_policies');
        Schema::dropIfExists('employee_relation_correspondence');
        Schema::dropIfExists('employee_relation_correspondence_templates');
        Schema::dropIfExists('employee_relation_appeals');
        Schema::dropIfExists('employee_relation_corrective_actions');
        Schema::dropIfExists('employee_relation_decisions');
        Schema::dropIfExists('employee_relation_findings');
        Schema::dropIfExists('employee_relation_hearing_outcomes');
        Schema::dropIfExists('employee_relation_hearings');
        Schema::dropIfExists('employee_relation_case_slas');
        Schema::dropIfExists('employee_relation_case_tasks');
        Schema::dropIfExists('employee_relation_case_notes');
        Schema::dropIfExists('employee_relation_evidence_history');
        Schema::dropIfExists('employee_relation_evidence');
        Schema::dropIfExists('employee_relation_interview_notes');
        Schema::dropIfExists('employee_relation_interviews');
        Schema::dropIfExists('employee_relation_statement_versions');
        Schema::dropIfExists('employee_relation_statements');
        Schema::dropIfExists('employee_relation_allegations');
        Schema::dropIfExists('employee_relation_investigation_questions');
        Schema::dropIfExists('employee_relation_investigation_steps');
        Schema::dropIfExists('employee_relation_investigations');
        Schema::dropIfExists('employee_relation_case_conflicts');
        Schema::dropIfExists('employee_relation_case_participants');
        Schema::dropIfExists('employee_relation_case_assignments');
        Schema::dropIfExists('employee_relation_case_triage');
        Schema::dropIfExists('employee_relation_case_tokens');
        Schema::dropIfExists('employee_relation_case_reporters');
        Schema::dropIfExists('employee_relation_cases');
        Schema::dropIfExists('employee_relation_policy_references');
        Schema::dropIfExists('employee_relation_case_types');
    }
};
