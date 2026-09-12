<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Individual Development Plans (IDP)
        Schema::create('hcm_learning_development_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('title', 150);
            $table->text('goal');
            $table->string('skill_target', 100)->nullable();
            $table->string('competency_target', 100)->nullable();
            $table->string('target_level', 50)->nullable(); // beginner, intermediate, advanced, expert
            $table->date('target_completion_date');
            $table->string('status', 30)->default('draft'); // draft, active, in_progress, completed, cancelled
            $table->uuid('manager_id')->nullable()->index();
            $table->uuid('mentor_id')->nullable()->index();
            $table->text('manager_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('mentor_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 2. IDP Activities
        Schema::create('hcm_learning_development_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('development_plan_id')->index();
            $table->string('activity_type', 40); // course, mentoring, coaching, stretch_assignment, project, certification, reading
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->uuid('course_id')->nullable()->index();
            $table->date('target_date')->nullable();
            $table->string('status', 30)->default('planned'); // planned, in_progress, completed, skipped
            $table->timestamp('completed_at')->nullable();
            $table->text('evidence_notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('development_plan_id')->references('id')->on('hcm_learning_development_plans')->cascadeOnDelete();
            $table->foreign('course_id')->references('id')->on('learning_courses')->nullOnDelete();
        });

        // 3. External Learning Records
        Schema::create('hcm_learning_external_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('provider_name', 150);
            $table->string('course_title', 150);
            $table->date('completion_date');
            $table->unsignedInteger('duration_hours')->default(0);
            $table->decimal('credits_earned', 6, 2)->default(0.00);
            $table->string('credential_id', 100)->nullable();
            $table->string('status', 30)->default('pending_verification'); // pending_verification, verified, rejected
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 4. Learning Evidence Documents
        Schema::create('hcm_learning_evidence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('external_record_id')->index();
            $table->string('evidence_type', 40); // certificate, transcript, attendance_proof, assessment_report
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->uuid('document_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('external_record_id')->references('id')->on('hcm_learning_external_records')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_learning_evidence');
        Schema::dropIfExists('hcm_learning_external_records');
        Schema::dropIfExists('hcm_learning_development_activities');
        Schema::dropIfExists('hcm_learning_development_plans');
    }
};
