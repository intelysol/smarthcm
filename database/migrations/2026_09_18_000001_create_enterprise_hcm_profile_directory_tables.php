<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Employee Profile Preferences
        Schema::create('employee_profile_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->unique();
            $table->boolean('show_personal_email')->default(false);
            $table->boolean('show_personal_phone')->default(false);
            $table->boolean('show_birthday')->default(true);
            $table->string('theme_preference', 30)->default('light');
            $table->json('custom_settings')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 2. Employee Profile Change Requests
        Schema::create('employee_profile_change_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('request_number', 50)->unique();
            $table->string('status', 30)->default('pending')->index(); // pending, approved, rejected, cancelled
            $table->timestamp('requested_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 3. Employee Profile Change Request Items
        Schema::create('employee_profile_change_request_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('change_request_id')->index();
            $table->string('field_name', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();

            $table->foreign('change_request_id')->references('id')->on('employee_profile_change_requests')->cascadeOnDelete();
        });

        // 4. Employee Organization Materialized Read Model (Non-Authoritative Projection)
        Schema::create('employee_org_read_models', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('manager_id')->nullable()->index();
            $table->uuid('company_id')->nullable()->index();
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->uuid('location_id')->nullable()->index();
            $table->uuid('job_id')->nullable();
            $table->uuid('position_id')->nullable();
            $table->string('employee_number', 50)->index();
            $table->string('full_name', 150)->index();
            $table->string('job_title', 100)->nullable();
            $table->string('department_name', 100)->nullable();
            $table->string('branch_name', 100)->nullable();
            $table->string('location_name', 100)->nullable();
            $table->string('hierarchy_path', 255)->nullable(); // e.g. /CEO_ID/VP_ID/MGR_ID/
            $table->integer('depth_level')->default(0);
            $table->integer('span_of_control')->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->string('photo_path', 255)->nullable();
            $table->text('searchable_text')->nullable(); // combined text for instant fast searching
            $table->timestamps();

            $table->unique(['tenant_id', 'employee_id']);
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 5. Employee Profile Audits
        Schema::create('employee_profile_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->nullable()->index();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_name', 50)->index();
            $table->string('section', 50)->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_profile_audits');
        Schema::dropIfExists('employee_org_read_models');
        Schema::dropIfExists('employee_profile_change_request_items');
        Schema::dropIfExists('employee_profile_change_requests');
        Schema::dropIfExists('employee_profile_preferences');
    }
};
