<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Personal Information
        Schema::create('hcm_personal_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->unique();
            $table->string('legal_first_name', 100);
            $table->string('legal_middle_name', 100)->nullable();
            $table->string('legal_last_name', 100);
            $table->string('preferred_name', 100)->nullable();
            $table->string('display_name', 150)->nullable();
            $table->string('previous_name', 150)->nullable();
            $table->string('name_prefix', 20)->nullable();
            $table->string('name_suffix', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('nationality', 50)->nullable();
            $table->string('citizenship', 50)->nullable();
            $table->string('preferred_language', 30)->nullable();
            $table->string('country_of_birth', 50)->nullable();
            $table->string('place_of_birth', 100)->nullable();
            $table->string('personal_email', 150)->nullable()->index();
            $table->string('personal_mobile', 50)->nullable()->index();
            $table->string('personal_phone', 50)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 2. Employee Addresses
        Schema::create('hcm_employee_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('address_type', 50); // residential, permanent, mailing, temporary, previous
            $table->string('address_line_1', 255);
            $table->string('address_line_2', 255)->nullable();
            $table->string('city', 100);
            $table->string('state_province', 100)->nullable();
            $table->string('postal_code', 30)->nullable();
            $table->string('country_code', 10);
            $table->string('district_region', 100)->nullable();
            $table->boolean('is_current')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('verification_status', 30)->default('unverified'); // unverified, pending, verified, rejected
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 3. Emergency Contacts
        Schema::create('hcm_emergency_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('contact_name', 150);
            $table->string('relationship', 50); // spouse, parent, child, sibling, relative, friend, guardian, other
            $table->integer('priority')->default(1);
            $table->boolean('is_primary')->default(false);
            $table->string('mobile', 50);
            $table->string('telephone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('country', 50)->nullable();
            $table->string('preferred_communication_method', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 4. Employee Dependents (Basic Family Profile)
        Schema::create('hcm_employee_dependents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('name', 150);
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('relationship', 50); // spouse, child, parent, legal_dependent, guardian, other
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('nationality', 50)->nullable();
            $table->text('national_id_number')->nullable(); // encrypted
            $table->boolean('is_student')->default(false);
            $table->boolean('is_disabled')->default(false);
            $table->string('contact_phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 5. Configurable Employee Government Identifiers
        Schema::create('hcm_employee_identifiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('identifier_type', 50); // national_id, passport, tax_id, ssn, residency_id, work_permit, visa, other
            $table->text('identifier_value'); // encrypted at rest
            $table->string('masked_value', 100);
            $table->string('country_code', 10)->nullable();
            $table->string('issuing_authority', 100)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('verification_status', 30)->default('unverified'); // unverified, pending, verified, rejected, expired
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 6. Bank Detail Change Requests (Orchestrated to Payroll)
        Schema::create('hcm_employee_bank_change_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('request_number', 50)->unique();
            $table->string('request_type', 50); // add_account, update_account, remove_account, payment_method
            $table->string('bank_name', 100);
            $table->string('branch_name', 100)->nullable();
            $table->string('account_title', 150);
            $table->text('account_number_encrypted');
            $table->string('masked_account_number', 50);
            $table->text('iban_encrypted')->nullable();
            $table->string('masked_iban', 50)->nullable();
            $table->string('payment_method', 50)->default('bank_transfer');
            $table->string('reason', 255)->nullable();
            $table->string('status', 30)->default('pending')->index(); // pending, approved, rejected, cancelled, applied_to_payroll
            $table->timestamp('payroll_actioned_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 7. General Employee Personal Data Change Requests
        Schema::create('hcm_employee_data_change_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('request_number', 50)->unique();
            $table->string('category', 50); // personal, address, emergency_contact, dependent, identifier
            $table->string('status', 30)->default('pending')->index(); // draft, submitted, under_review, pending_approval, approved, rejected, applied, cancelled
            $table->date('effective_date');
            $table->string('reason', 255)->nullable();
            $table->uuid('supporting_document_id')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 8. Change Request Items (Side-by-Side Current vs Proposed)
        Schema::create('hcm_employee_data_change_request_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('change_request_id')->index();
            $table->string('target_entity', 50); // personal_data, address, emergency_contact, dependent, identifier
            $table->uuid('target_id')->nullable();
            $table->string('field_name', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();

            $table->foreign('change_request_id')->references('id')->on('hcm_employee_data_change_requests')->cascadeOnDelete();
        });

        // 9. Data Verifications
        Schema::create('hcm_employee_data_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('verifiable_type', 100);
            $table->uuid('verifiable_id')->index();
            $table->string('verification_method', 50); // document, hr_manual, otp, external_service
            $table->string('status', 30)->default('pending')->index(); // pending, verified, rejected, expired
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 10. Data Quality Results
        Schema::create('hcm_employee_data_quality_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->unique();
            $table->decimal('completeness_score', 5, 2)->default(0);
            $table->decimal('validity_score', 5, 2)->default(0);
            $table->decimal('verification_score', 5, 2)->default(0);
            $table->decimal('freshness_score', 5, 2)->default(0);
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->integer('issues_count')->default(0);
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 11. Data Quality Issues
        Schema::create('hcm_employee_data_quality_issues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('issue_code', 80);
            $table->string('severity', 30); // info, warning, error, critical
            $table->string('category', 50); // completeness, validity, freshness, verification
            $table->text('description');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 12. Bulk Data Batches and Items
        Schema::create('hcm_employee_data_bulk_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('batch_number', 50)->unique();
            $table->string('category', 50); // personal, address, emergency_contact, dependent, identifier
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->integer('total_items')->default(0);
            $table->integer('valid_items')->default(0);
            $table->integer('error_items')->default(0);
            $table->integer('processed_items')->default(0);
            $table->string('status', 30)->default('draft')->index(); // draft, validated, processing, completed, failed
            $table->timestamps();
        });

        Schema::create('hcm_employee_data_bulk_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('batch_id')->index();
            $table->string('employee_identifier', 100);
            $table->uuid('resolved_employee_id')->nullable()->index();
            $table->json('payload');
            $table->string('status', 30)->default('pending')->index(); // pending, valid, error, processed
            $table->json('validation_errors')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('hcm_employee_data_bulk_batches')->cascadeOnDelete();
            $table->foreign('resolved_employee_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_employee_data_bulk_items');
        Schema::dropIfExists('hcm_employee_data_bulk_batches');
        Schema::dropIfExists('hcm_employee_data_quality_issues');
        Schema::dropIfExists('hcm_employee_data_quality_results');
        Schema::dropIfExists('hcm_employee_data_verifications');
        Schema::dropIfExists('hcm_employee_data_change_request_items');
        Schema::dropIfExists('hcm_employee_data_change_requests');
        Schema::dropIfExists('hcm_employee_bank_change_requests');
        Schema::dropIfExists('hcm_employee_identifiers');
        Schema::dropIfExists('hcm_employee_dependents');
        Schema::dropIfExists('hcm_emergency_contacts');
        Schema::dropIfExists('hcm_employee_addresses');
        Schema::dropIfExists('hcm_personal_data');
    }
};
