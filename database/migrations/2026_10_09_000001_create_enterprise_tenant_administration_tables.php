<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tenant Onboarding Wizard
        Schema::create('hcm_tenant_onboarding_wizards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('status')->default('IN_PROGRESS'); // IN_PROGRESS, COMPLETED
            $table->integer('current_step')->default(1); // 1: Company, 2: Org, 3: Workforce, 4: Security, 5: Modules
            $table->integer('progress_pct')->default(0); // 0 - 100
            $table->json('step_data')->nullable();
            $table->json('completed_steps')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        // 2. Tenant Hierarchical Configurations
        Schema::create('hcm_tenant_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('scope')->default('TENANT'); // PLATFORM, TENANT, LEGAL_ENTITY, BUSINESS_UNIT, DEPARTMENT, LOCATION, EMPLOYEE_GROUP
            $table->string('scope_id')->nullable(); // Target ID if scope != PLATFORM/TENANT
            $table->string('category')->default('GENERAL'); // GENERAL, ORGANIZATION, PEOPLE, TIME, LEAVE, PAYROLL, BENEFITS, RECRUITMENT, PERFORMANCE, SECURITY, AI, INTEGRATIONS, LOCALIZATION, BRANDING
            $table->string('config_key');
            $table->json('config_value')->nullable();
            $table->string('value_type')->default('STRING'); // STRING, INTEGER, DECIMAL, BOOLEAN, JSON
            $table->boolean('is_overridable')->default(true);
            $table->integer('version_number')->default(1);
            $table->string('status')->default('PUBLISHED'); // DRAFT, PUBLISHED, SUPERSEDED, ARCHIVED
            $table->date('effective_date')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'scope', 'config_key']);
            $table->index(['tenant_id', 'category']);
        });

        // 3. Configuration Versions for Non-Destructive Rollback & Audit
        Schema::create('hcm_tenant_config_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('config_id')->nullable();
            $table->string('config_key');
            $table->integer('version_number');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->text('change_reason')->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('config_id')->references('id')->on('hcm_tenant_configurations')->nullOnDelete();
            $table->index(['tenant_id', 'config_key', 'version_number'], 'hcm_t_cfg_ver_t_key_ver_idx');
        });

        // 4. Governed Feature Flags & Module Dependencies
        Schema::create('hcm_tenant_feature_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('feature_key');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->json('dependencies')->nullable(); // Keys of mandatory predecessor features
            $table->string('rollout_scope')->default('TENANT'); // TENANT, ROLE, USER_GROUP, PERCENTAGE
            $table->string('rollout_value')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'feature_key']);
        });

        // 5. Tenant-Isolated Branding
        Schema::create('hcm_tenant_brandings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->unique();
            $table->string('company_name');
            $table->string('logo_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('primary_color')->default('#4f46e5'); // Indigo-600
            $table->string('secondary_color')->default('#0ea5e9'); // Sky-500
            $table->string('accent_color')->default('#10b981'); // Emerald-500
            $table->text('custom_css')->nullable();
            $table->string('login_banner_text')->nullable();
            $table->string('portal_title')->default('SmartHCM Enterprise Portal');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 6. Tenant Localization & Pakistan / International Foundation
        Schema::create('hcm_tenant_localizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->unique();
            $table->string('country_code')->default('PK'); // PK, AE, SA, GB, US
            $table->string('language')->default('en');
            $table->string('timezone')->default('Asia/Karachi');
            $table->string('currency')->default('PKR');
            $table->string('currency_symbol')->default('Rs');
            $table->string('date_format')->default('d/m/Y');
            $table->string('time_format')->default('H:i');
            $table->string('week_start')->default('MONDAY');
            $table->integer('fiscal_year_start_month')->default(7); // July in Pakistan
            $table->string('tax_identifier_type')->default('CNIC'); // CNIC, SSN, EmiratesID
            $table->string('national_id_mask')->default('#####-#######-#'); // 13-digit Pakistani CNIC pattern
            $table->boolean('is_pakistan_statutory_enabled')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 7. Configurable Role Templates
        Schema::create('hcm_tenant_role_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable(); // Null = System Global Template
            $table->string('template_code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('assigned_permissions');
            $table->boolean('is_system_template')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'template_code']);
        });

        // 8. Administrative Delegations
        Schema::create('hcm_tenant_delegations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreignId('delegator_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegate_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('scope')->default('APPROVALS'); // APPROVALS, HR_ADMIN, FULL_ADMIN
            $table->json('permissions')->nullable();
            $table->text('reason');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status')->default('ACTIVE'); // ACTIVE, EXPIRED, REVOKED
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        // 9. Enterprise Setup Health Scores
        Schema::create('hcm_tenant_setup_health', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->unique();
            $table->decimal('overall_score', 5, 2)->default(0.00);
            $table->json('category_scores');
            $table->json('remediation_items')->nullable();
            $table->timestamp('last_assessed_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 10. Governed Data Transfers (Import/Export)
        Schema::create('hcm_tenant_data_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('transfer_type'); // IMPORT, EXPORT
            $table->string('domain'); // EMPLOYEES, DEPARTMENTS, LOCATIONS, POSITIONS, USERS, PAYROLL
            $table->string('file_name')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, VALIDATING, COMPLETED, FAILED
            $table->integer('total_rows')->default(0);
            $table->integer('valid_rows')->default(0);
            $table->integer('invalid_rows')->default(0);
            $table->integer('warnings_count')->default(0);
            $table->json('error_report')->nullable();
            $table->foreignId('initiated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hcm_tenant_data_transfers');
        Schema::dropIfExists('hcm_tenant_setup_health');
        Schema::dropIfExists('hcm_tenant_delegations');
        Schema::dropIfExists('hcm_tenant_role_templates');
        Schema::dropIfExists('hcm_tenant_localizations');
        Schema::dropIfExists('hcm_tenant_brandings');
        Schema::dropIfExists('hcm_tenant_feature_flags');
        Schema::dropIfExists('hcm_tenant_config_versions');
        Schema::dropIfExists('hcm_tenant_configurations');
        Schema::dropIfExists('hcm_tenant_onboarding_wizards');
    }
};
