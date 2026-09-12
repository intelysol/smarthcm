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
        // 1. Concierge Chat Sessions
        Schema::create('hcm_ai_concierge_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('user_id');
            $table->uuid('employee_id')->nullable();
            $table->string('persona')->default('EMPLOYEE'); // EMPLOYEE, MANAGER, HR_USER
            $table->string('title')->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE, ARCHIVED, CLOSED
            $table->json('session_metadata')->nullable();
            $table->timestamp('last_active_at');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'user_id', 'status']);
        });

        // 2. Concierge Messages
        Schema::create('hcm_ai_concierge_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('session_id');
            $table->string('role'); // user, assistant, system, tool
            $table->text('content');
            $table->json('response_metadata')->nullable(); // intent, tokens, latency
            $table->json('citations')->nullable(); // policy sources, document references
            $table->uuid('action_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('session_id')->references('id')->on('hcm_ai_concierge_sessions')->cascadeOnDelete();
            $table->index(['session_id', 'created_at']);
        });

        // 3. Concierge Prepared Actions
        Schema::create('hcm_ai_concierge_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('session_id')->nullable();
            $table->uuid('user_id');
            $table->uuid('employee_id');
            $table->string('action_type'); // SUBMIT_LEAVE_REQUEST, SUBMIT_ATTENDANCE_CORRECTION, CREATE_HR_REQUEST, SUBMIT_EXPENSE
            $table->string('risk_level')->default('MEDIUM'); // LOW, MEDIUM, HIGH
            $table->json('parameters');
            $table->json('preview_data'); // calculated days, remaining balance, approver
            $table->string('status')->default('PROPOSED'); // PROPOSED, CONFIRMED, CANCELLED, SUBMITTED, FAILED
            $table->string('workflow_reference_id')->nullable();
            $table->text('user_comment')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'employee_id', 'status']);
        });

        // 4. Concierge Proactive Suggestions
        Schema::create('hcm_ai_concierge_suggestions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('user_id');
            $table->uuid('employee_id');
            $table->string('category'); // LEAVE, ATTENDANCE, LEARNING, PERFORMANCE, COMPLIANCE
            $table->string('title');
            $table->text('description');
            $table->string('action_label')->nullable();
            $table->string('action_prompt')->nullable();
            $table->string('severity')->default('INFO'); // INFO, REMINDER, URGENT
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_dismissed')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'employee_id', 'is_dismissed']);
        });

        // 5. Concierge Feedback
        Schema::create('hcm_ai_concierge_feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('message_id');
            $table->uuid('user_id');
            $table->boolean('is_positive');
            $table->string('reason_category')->nullable(); // ACCURACY, HELPFULNESS, TONE, INCORRECT_POLICY
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('message_id')->references('id')->on('hcm_ai_concierge_messages')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hcm_ai_concierge_feedback');
        Schema::dropIfExists('hcm_ai_concierge_suggestions');
        Schema::dropIfExists('hcm_ai_concierge_actions');
        Schema::dropIfExists('hcm_ai_concierge_messages');
        Schema::dropIfExists('hcm_ai_concierge_sessions');
    }
};
