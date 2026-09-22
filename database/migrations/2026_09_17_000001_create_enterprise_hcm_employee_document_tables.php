<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. HCM Document Categories
        Schema::create('hcm_document_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        // 2. HCM Document Types
        Schema::create('hcm_document_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('category_id')->index();
            $table->string('code', 50);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('requires_verification')->default(true);
            $table->boolean('requires_acknowledgement')->default(false);
            $table->boolean('expires')->default(false);
            $table->integer('default_validity_days')->nullable();
            $table->integer('retention_years')->default(7);
            $table->string('confidentiality_level', 30)->default('HR');
            $table->boolean('employee_visible')->default(true);
            $table->boolean('manager_visible')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('metadata_schema')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->foreign('category_id')->references('id')->on('hcm_document_categories')->cascadeOnDelete();
        });

        // 3. Employee Documents Association
        Schema::create('hcm_employee_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('document_type_id')->index();
            $table->uuid('document_id')->index(); // FK to shared documents
            $table->string('title', 200);
            $table->string('document_number', 100)->nullable()->index();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable()->index();
            $table->string('status', 30)->default('submitted')->index();
            $table->string('verification_status', 30)->default('pending')->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->string('confidentiality_level', 30)->default('HR')->index();
            $table->boolean('employee_visible')->default(true);
            $table->boolean('manager_visible')->default(true);
            $table->string('source', 50)->default('employee_upload');
            $table->string('related_type', 100)->nullable();
            $table->uuid('related_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('document_type_id')->references('id')->on('hcm_document_types')->cascadeOnDelete();
            $table->foreign('document_id')->references('id')->on('documents')->cascadeOnDelete();
        });

        // 4. Employee Document Requirements
        Schema::create('employee_document_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('document_type_id')->index();
            $table->boolean('is_mandatory')->default(true);
            $table->string('status', 30)->default('required')->index();
            $table->uuid('employee_document_id')->nullable()->index();
            $table->date('due_date')->nullable();
            $table->uuid('waived_by')->nullable();
            $table->timestamp('waived_at')->nullable();
            $table->string('waiver_reason', 255)->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'document_type_id'], 'emp_doc_req_emp_doc_type_unique');
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('document_type_id')->references('id')->on('hcm_document_types')->cascadeOnDelete();
            $table->foreign('employee_document_id')->references('id')->on('hcm_employee_documents')->nullOnDelete();
        });

        // 5. Employee Document Verifications
        Schema::create('employee_document_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_document_id')->index();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision', 30); // verified, rejected
            $table->string('reason', 255)->nullable();
            $table->integer('version')->default(1);
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->foreign('employee_document_id')->references('id')->on('hcm_employee_documents')->cascadeOnDelete();
        });

        // 6. Employee Document Requests
        Schema::create('employee_document_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('document_type_id')->index();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('requested_at');
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('requested')->index();
            $table->text('instructions')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->uuid('employee_document_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('document_type_id')->references('id')->on('hcm_document_types')->cascadeOnDelete();
            $table->foreign('employee_document_id')->references('id')->on('hcm_employee_documents')->nullOnDelete();
        });

        // 7. Employee Document Acknowledgements
        Schema::create('employee_document_acknowledgements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_document_id')->index();
            $table->uuid('employee_id')->index();
            $table->integer('document_version')->default(1);
            $table->timestamp('acknowledged_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->foreign('employee_document_id')->references('id')->on('hcm_employee_documents')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 8. Employee Document Expiration Events
        Schema::create('employee_document_expiration_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_document_id')->index();
            $table->string('milestone', 30); // 90_days, 60_days, 30_days, 7_days, expired
            $table->timestamp('recorded_at');
            $table->boolean('notified')->default(false);
            $table->timestamps();

            $table->unique(['employee_document_id', 'milestone'], 'emp_doc_exp_doc_milestone_unique');
            $table->foreign('employee_document_id')->references('id')->on('hcm_employee_documents')->cascadeOnDelete();
        });

        // 9. Employee Document Bulk Batches
        Schema::create('employee_document_bulk_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('batch_number', 50)->unique();
            $table->uuid('document_type_id')->index();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->integer('total_items')->default(0);
            $table->integer('valid_items')->default(0);
            $table->integer('warning_items')->default(0);
            $table->integer('error_items')->default(0);
            $table->integer('processed_items')->default(0);
            $table->string('status', 30)->default('draft')->index(); // draft, validated, processing, completed, failed
            $table->timestamps();

            $table->foreign('document_type_id')->references('id')->on('hcm_document_types')->cascadeOnDelete();
        });

        // 10. Employee Document Bulk Items
        Schema::create('employee_document_bulk_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('batch_id')->index();
            $table->string('employee_identifier', 100);
            $table->uuid('resolved_employee_id')->nullable()->index();
            $table->string('file_name', 255);
            $table->string('status', 30)->default('pending')->index(); // pending, valid, invalid, processed, failed
            $table->json('validation_errors')->nullable();
            $table->uuid('employee_document_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('employee_document_bulk_batches')->cascadeOnDelete();
            $table->foreign('resolved_employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('employee_document_id')->references('id')->on('hcm_employee_documents')->nullOnDelete();
        });

        // 11. Employee Document Audits
        Schema::create('employee_document_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_document_id')->nullable()->index();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_name', 50)->index();
            $table->json('old_state')->nullable();
            $table->json('new_state')->nullable();
            $table->string('reason', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('employee_document_id')->references('id')->on('hcm_employee_documents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_document_audits');
        Schema::dropIfExists('employee_document_bulk_items');
        Schema::dropIfExists('employee_document_bulk_batches');
        Schema::dropIfExists('employee_document_expiration_events');
        Schema::dropIfExists('employee_document_acknowledgements');
        Schema::dropIfExists('employee_document_requests');
        Schema::dropIfExists('employee_document_verifications');
        Schema::dropIfExists('employee_document_requirements');
        Schema::dropIfExists('hcm_employee_documents');
        Schema::dropIfExists('hcm_document_types');
        Schema::dropIfExists('hcm_document_categories');
    }
};
