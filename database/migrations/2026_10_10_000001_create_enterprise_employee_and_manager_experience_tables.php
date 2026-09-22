<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Experience User Preferences
        Schema::create('hcm_experience_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->json('widget_preferences')->nullable();
            $table->json('quick_action_order')->nullable();
            $table->json('notification_channels')->nullable();
            $table->string('theme_preference', 20)->default('system');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['tenant_id', 'employee_id']);
        });

        // 2. Tenant-Governed Quick Actions
        Schema::create('hcm_employee_quick_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('key', 50);
            $table->string('title', 100);
            $table->string('icon', 50)->default('fa-bolt');
            $table->string('route', 150);
            $table->string('required_permission', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'key']);
        });

        // 3. Experience Layer Audits (Sensitive Self-Service Action Tracking)
        Schema::create('hcm_experience_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('employee_id')->index();
            $table->string('action_type', 60)->index(); // PAYSLIP_VIEW, DOCUMENT_DOWNLOAD, APPROVAL_DECISION, etc.
            $table->string('target_type', 80)->nullable();
            $table->string('target_id', 80)->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_experience_audits');
        Schema::dropIfExists('hcm_employee_quick_actions');
        Schema::dropIfExists('hcm_experience_preferences');
    }
};
