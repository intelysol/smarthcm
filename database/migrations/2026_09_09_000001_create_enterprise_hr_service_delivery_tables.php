<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Service Categories
        Schema::create('hr_service_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50)->nullable();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable()->default('fa-folder');
            $table->unsignedInteger('display_order')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Service Definitions & Versions
        Schema::create('hr_service_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_category_id')->index();
            $table->string('service_code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('audience', 50)->default('all_employees');
            $table->string('confidentiality_level', 30)->default('normal');
            $table->uuid('default_queue_id')->nullable();
            $table->uuid('workflow_definition_id')->nullable();
            $table->uuid('sla_policy_id')->nullable();
            $table->unsignedInteger('current_version')->default(1);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('requires_employee_acknowledgement')->default(false);
            $table->boolean('allow_reopen')->default(true);
            $table->unsignedInteger('reopen_window_days')->default(7);
            $table->string('status', 30)->default('active');
            $table->boolean('is_popular')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('hr_service_category_id')->references('id')->on('hr_service_categories')->cascadeOnDelete();
        });

        Schema::create('hr_service_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_definition_id')->index();
            $table->unsignedInteger('version_number')->default(1);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('change_summary')->nullable();
            $table->json('configuration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('hr_service_definition_id')->references('id')->on('hr_service_definitions')->cascadeOnDelete();
        });

        Schema::create('hr_service_form_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_version_id')->index();
            $table->string('form_name', 150);
            $table->json('schema')->nullable(); // Array of dynamic fields (name, label, type, required, options, validation, rules)
            $table->timestamps();

            $table->foreign('hr_service_version_id')->references('id')->on('hr_service_versions')->cascadeOnDelete();
        });

        // 3. Service Queues & Routing Rules
        Schema::create('hr_service_queues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('email', 150)->nullable();
            $table->string('assignment_method', 30)->default('manual'); // manual, round_robin, workload_based
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hr_service_queue_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_queue_id')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_in_queue', 50)->default('agent'); // agent, lead, supervisor
            $table->unsignedInteger('active_tickets_count')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->foreign('hr_service_queue_id')->references('id')->on('hr_service_queues')->cascadeOnDelete();
        });

        Schema::create('hr_service_assignment_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 150);
            $table->unsignedInteger('priority')->default(10);
            $table->uuid('hr_service_category_id')->nullable();
            $table->uuid('hr_service_definition_id')->nullable();
            $table->uuid('branch_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('target_queue_id')->index();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('criteria')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('target_queue_id')->references('id')->on('hr_service_queues')->cascadeOnDelete();
        });

        // 4. SLA Policies & Escalations
        Schema::create('hr_service_sla_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('response_time_minutes')->default(240); // 4 hours
            $table->unsignedInteger('resolution_time_minutes')->default(2880); // 48 hours
            $table->boolean('use_business_hours')->default(true);
            $table->string('business_calendar_code', 50)->nullable()->default('STANDARD');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hr_service_sla_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_request_id')->index();
            $table->uuid('hr_service_sla_policy_id')->nullable();
            $table->timestamp('response_due_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('resolution_due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('status', 30)->default('running'); // running, paused, met, warning, breached
            $table->unsignedInteger('total_paused_minutes')->default(0);
            $table->timestamp('last_paused_at')->nullable();
            $table->timestamps();

            $table->foreign('hr_service_sla_policy_id')->references('id')->on('hr_service_sla_policies')->nullOnDelete();
        });

        Schema::create('hr_service_sla_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_sla_instance_id')->index();
            $table->string('event_type', 50); // started, paused, resumed, warning, breached, met
            $table->timestamp('event_at');
            $table->text('details')->nullable();
            $table->timestamps();

            $table->foreign('hr_service_sla_instance_id')->references('id')->on('hr_service_sla_instances')->cascadeOnDelete();
        });

        Schema::create('hr_service_escalations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_request_id')->index();
            $table->unsignedInteger('escalation_level')->default(1); // 1 (80% warning), 2 (100% lead), 3 (120% manager)
            $table->string('reason', 150);
            $table->foreignId('escalated_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('escalated_at');
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
        });

        // 5. Service Requests & Request Elements
        Schema::create('hr_service_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('hr_service_definition_id')->index();
            $table->uuid('hr_service_version_id')->nullable();
            $table->string('request_number', 50)->index();
            $table->string('subject', 200);
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent, critical
            $table->string('status', 30)->default('draft'); // draft, submitted, received, under_review, assigned, waiting_for_employee, waiting_for_approval, in_progress, resolved, rejected, cancelled, closed, reopened
            $table->string('confidentiality_level', 30)->default('normal');
            
            // Server-side resolved context snapshot
            $table->uuid('company_id')->nullable();
            $table->uuid('branch_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('cost_center_id')->nullable();
            $table->uuid('reporting_manager_id')->nullable();

            // Assignment
            $table->uuid('assigned_queue_id')->nullable()->index();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // SLA
            $table->uuid('sla_instance_id')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Integration references
            $table->string('source_domain_module', 50)->nullable(); // e.g. attendance, payroll, leave, benefits, expenses, employee_relations
            $table->string('source_entity_type', 80)->nullable();
            $table->string('source_entity_id', 80)->nullable();
            $table->uuid('employee_relation_case_id')->nullable(); // Escalation to ER case

            $table->json('form_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('hr_service_definition_id')->references('id')->on('hr_service_definitions')->cascadeOnDelete();
            $table->foreign('assigned_queue_id')->references('id')->on('hr_service_queues')->nullOnDelete();
        });

        Schema::create('hr_service_request_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_request_id')->index();
            $table->string('field_key', 80);
            $table->string('field_label', 150);
            $table->string('field_type', 50)->default('text');
            $table->text('field_value')->nullable();
            $table->json('raw_value')->nullable();
            $table->timestamps();

            $table->foreign('hr_service_request_id')->references('id')->on('hr_service_requests')->cascadeOnDelete();
        });

        Schema::create('hr_service_request_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_request_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('employee_id')->nullable();
            $table->string('comment_type', 20)->default('public'); // public (visible to employee), internal (HR only)
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('hr_service_request_id')->references('id')->on('hr_service_requests')->cascadeOnDelete();
        });

        Schema::create('hr_service_request_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_request_id')->index();
            $table->uuid('from_queue_id')->nullable();
            $table->uuid('to_queue_id')->nullable();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->foreign('hr_service_request_id')->references('id')->on('hr_service_requests')->cascadeOnDelete();
        });

        Schema::create('hr_service_request_status_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_request_id')->index();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->foreign('hr_service_request_id')->references('id')->on('hr_service_requests')->cascadeOnDelete();
        });

        Schema::create('hr_service_request_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('parent_request_id')->index();
            $table->uuid('child_request_id')->index();
            $table->string('link_type', 30)->default('related'); // parent, child, related, merged_into, duplicated_by, converted_to_case
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('parent_request_id')->references('id')->on('hr_service_requests')->cascadeOnDelete();
            $table->foreign('child_request_id')->references('id')->on('hr_service_requests')->cascadeOnDelete();
        });

        Schema::create('hr_service_request_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_request_id')->index();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_title', 150);
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 50)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->boolean('is_confidential')->default(false);
            $table->timestamps();

            $table->foreign('hr_service_request_id')->references('id')->on('hr_service_requests')->cascadeOnDelete();
        });

        // 6. Document Templates & Generated Documents
        Schema::create('hr_service_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_definition_id')->nullable()->index();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('template_type', 50)->default('employment_certificate'); // salary_certificate, experience_letter, confirmation_letter, etc.
            $table->longText('template_body'); // Contains {{employee.name}}, {{employee.position}}, {{employee.joining_date}}, etc.
            $table->json('placeholders')->nullable();
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hr_service_generated_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_service_request_id')->index();
            $table->uuid('hr_service_template_id')->nullable()->index();
            $table->uuid('employee_id')->index();
            $table->string('document_number', 50)->index();
            $table->string('title', 150);
            $table->longText('rendered_content');
            $table->string('pdf_file_path')->nullable();
            $table->string('status', 30)->default('draft'); // draft, pending_approval, approved, issued, rejected
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('acknowledged_ip', 45)->nullable();
            $table->timestamps();

            $table->foreign('hr_service_request_id')->references('id')->on('hr_service_requests')->cascadeOnDelete();
            $table->foreign('hr_service_template_id')->references('id')->on('hr_service_templates')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 7. Knowledge Base & Feedback
        Schema::create('hr_knowledge_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50)->nullable();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable()->default('fa-book');
            $table->unsignedInteger('display_order')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hr_knowledge_articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_knowledge_category_id')->index();
            $table->uuid('hr_service_definition_id')->nullable()->index(); // Optional link for deflection
            $table->string('slug', 150)->index();
            $table->string('title', 200);
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->json('keywords')->nullable();
            $table->string('audience', 50)->default('all');
            $table->string('status', 30)->default('published'); // draft, published, archived
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->unsignedInteger('not_helpful_count')->default(0);
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('hr_knowledge_category_id')->references('id')->on('hr_knowledge_categories')->cascadeOnDelete();
        });

        Schema::create('hr_knowledge_article_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_knowledge_article_id')->index();
            $table->unsignedInteger('version_number')->default(1);
            $table->string('title', 200);
            $table->longText('content');
            $table->text('change_notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('hr_knowledge_article_id')->references('id')->on('hr_knowledge_articles')->cascadeOnDelete();
        });

        Schema::create('hr_knowledge_feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_knowledge_article_id')->index();
            $table->uuid('employee_id')->nullable()->index();
            $table->boolean('is_helpful')->default(true);
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->foreign('hr_knowledge_article_id')->references('id')->on('hr_knowledge_articles')->cascadeOnDelete();
        });

        // 8. Announcements & Acknowledgements
        Schema::create('hr_announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('title', 200);
            $table->string('category', 50)->default('general'); // holiday, policy, payroll, general
            $table->longText('content');
            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent
            $table->boolean('requires_acknowledgement')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 30)->default('published'); // draft, published, archived
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hr_announcement_audiences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_announcement_id')->index();
            $table->string('audience_type', 50)->default('tenant'); // tenant, company, branch, department, employee_group
            $table->uuid('audience_id')->nullable(); // Target ID
            $table->timestamps();

            $table->foreign('hr_announcement_id')->references('id')->on('hr_announcements')->cascadeOnDelete();
        });

        Schema::create('hr_announcement_acknowledgements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('hr_announcement_id')->index();
            $table->uuid('employee_id')->index();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('hr_announcement_id')->references('id')->on('hr_announcements')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['hr_announcement_id', 'employee_id'], 'hr_annc_ack_annc_emp_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_announcement_acknowledgements');
        Schema::dropIfExists('hr_announcement_audiences');
        Schema::dropIfExists('hr_announcements');
        Schema::dropIfExists('hr_knowledge_feedback');
        Schema::dropIfExists('hr_knowledge_article_versions');
        Schema::dropIfExists('hr_knowledge_articles');
        Schema::dropIfExists('hr_knowledge_categories');
        Schema::dropIfExists('hr_service_generated_documents');
        Schema::dropIfExists('hr_service_templates');
        Schema::dropIfExists('hr_service_request_documents');
        Schema::dropIfExists('hr_service_request_links');
        Schema::dropIfExists('hr_service_request_status_history');
        Schema::dropIfExists('hr_service_request_assignments');
        Schema::dropIfExists('hr_service_request_comments');
        Schema::dropIfExists('hr_service_request_fields');
        Schema::dropIfExists('hr_service_requests');
        Schema::dropIfExists('hr_service_escalations');
        Schema::dropIfExists('hr_service_sla_events');
        Schema::dropIfExists('hr_service_sla_instances');
        Schema::dropIfExists('hr_service_sla_policies');
        Schema::dropIfExists('hr_service_assignment_rules');
        Schema::dropIfExists('hr_service_queue_members');
        Schema::dropIfExists('hr_service_queues');
        Schema::dropIfExists('hr_service_form_definitions');
        Schema::dropIfExists('hr_service_versions');
        Schema::dropIfExists('hr_service_definitions');
        Schema::dropIfExists('hr_service_categories');
    }
};
