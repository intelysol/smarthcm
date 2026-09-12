<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('tenant_id')->constrained('users')->nullOnDelete();
        });

        Schema::create('employee_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->unique();
            $table->string('language', 10)->default('en');
            $table->string('theme', 20)->default('system');
            $table->string('timezone', 80)->nullable();
            $table->json('notification_preferences')->nullable();
            $table->json('email_preferences')->nullable();
            $table->json('mobile_preferences')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        Schema::create('portal_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('type', 80);
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->uuid('department_id')->nullable()->index();
            $table->string('title');
            $table->longText('body');
            $table->json('attachments')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->boolean('requires_acknowledgement')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });

        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->uuid('announcement_id');
            $table->uuid('employee_id');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            $table->primary(['announcement_id', 'employee_id']);
            $table->foreign('announcement_id')->references('id')->on('announcements')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('requester_employee_id')->index();
            $table->uuid('current_approver_employee_id')->nullable()->index();
            $table->string('module', 80);
            $table->string('subject_type', 120);
            $table->string('subject_id', 80);
            $table->string('status', 20)->default('pending')->index();
            $table->string('title');
            $table->text('comments')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('requester_employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('current_approver_employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        Schema::create('approval_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('approval_request_id')->index();
            $table->uuid('actor_employee_id');
            $table->string('action', 20);
            $table->text('comments')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->foreign('approval_request_id')->references('id')->on('approval_requests')->cascadeOnDelete();
            $table->foreign('actor_employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_actions');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('portal_notifications');
        Schema::dropIfExists('employee_preferences');
        Schema::table('employees', fn (Blueprint $table) => $table->dropConstrainedForeignId('user_id'));
    }
};
