<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->string('trigger', 100)->index();
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 20)->default('draft')->index();
            $table->json('conditions')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'trigger', 'version']);
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_id')->index();
            $table->unsignedInteger('step_order');
            $table->string('name');
            $table->string('routing_strategy', 30)->default('sequential');
            $table->string('approver_type', 30);
            $table->uuid('approver_employee_id')->nullable();
            $table->string('role')->nullable();
            $table->json('conditions')->nullable();
            $table->unsignedInteger('sla_hours')->nullable();
            $table->unsignedInteger('reminder_hours')->nullable();
            $table->timestamps();
            $table->foreign('workflow_id')->references('id')->on('workflows')->cascadeOnDelete();
            $table->foreign('approver_employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->unique(['workflow_id', 'step_order']);
        });

        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('workflow_id')->index();
            $table->uuid('requester_employee_id')->index();
            $table->string('subject_type', 120);
            $table->string('subject_id', 80);
            $table->json('context')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->uuid('current_step_id')->nullable()->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('workflow_id')->references('id')->on('workflows')->restrictOnDelete();
            $table->foreign('requester_employee_id')->references('id')->on('employees')->restrictOnDelete();
            $table->foreign('current_step_id')->references('id')->on('workflow_steps')->nullOnDelete();
            $table->index(['tenant_id', 'subject_type', 'subject_id']);
        });

        Schema::create('workflow_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_instance_id')->index();
            $table->uuid('workflow_step_id')->index();
            $table->uuid('assignee_employee_id')->index();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('acted_at')->nullable();
            $table->text('comments')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->foreign('workflow_instance_id')->references('id')->on('workflow_instances')->cascadeOnDelete();
            $table->foreign('workflow_step_id')->references('id')->on('workflow_steps')->restrictOnDelete();
            $table->foreign('assignee_employee_id')->references('id')->on('employees')->restrictOnDelete();
            $table->unique(['workflow_instance_id', 'workflow_step_id', 'assignee_employee_id'], 'workflow_assignment_unique');
        });

        Schema::create('workflow_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_instance_id')->index();
            $table->uuid('actor_employee_id')->nullable();
            $table->string('event_type', 40)->index();
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20)->nullable();
            $table->text('comments')->nullable();
            $table->json('attachments')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
            $table->foreign('workflow_instance_id')->references('id')->on('workflow_instances')->cascadeOnDelete();
            $table->foreign('actor_employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        Schema::create('workflow_delegations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('delegator_employee_id')->index();
            $table->uuid('delegate_employee_id')->index();
            $table->string('trigger', 100)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('delegator_employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('delegate_employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_delegations'); Schema::dropIfExists('workflow_events'); Schema::dropIfExists('workflow_assignments'); Schema::dropIfExists('workflow_instances'); Schema::dropIfExists('workflow_steps'); Schema::dropIfExists('workflows');
    }
};
