<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_policies', function (Blueprint $table) {
            $table->uuid('id')->primary(); $table->uuid('tenant_id')->index(); $table->string('name'); $table->json('rules'); $table->boolean('is_active')->default(true); $table->timestamps(); $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
        Schema::create('shift_rosters', function (Blueprint $table) {
            $table->uuid('id')->primary(); $table->uuid('tenant_id')->index(); $table->uuid('employee_id')->index(); $table->uuid('shift_id'); $table->date('roster_date'); $table->timestamps(); $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete(); $table->foreign('shift_id')->references('id')->on('shifts')->restrictOnDelete(); $table->unique(['employee_id', 'roster_date']);
        });
        Schema::create('attendance_punches', function (Blueprint $table) {
            $table->uuid('id')->primary(); $table->uuid('tenant_id')->index(); $table->uuid('employee_id')->index(); $table->timestamp('punched_at')->index(); $table->string('punch_type', 20); $table->string('source', 40); $table->decimal('latitude', 10, 7)->nullable(); $table->decimal('longitude', 10, 7)->nullable(); $table->string('device_identifier')->nullable(); $table->string('ip_address', 45)->nullable(); $table->json('metadata')->nullable(); $table->timestamps(); $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary(); $table->uuid('tenant_id')->index(); $table->uuid('employee_id')->index(); $table->uuid('shift_id')->nullable(); $table->date('attendance_date'); $table->timestamp('check_in_at')->nullable(); $table->timestamp('check_out_at')->nullable(); $table->unsignedInteger('work_minutes')->default(0); $table->unsignedInteger('break_minutes')->default(0); $table->unsignedInteger('overtime_minutes')->default(0); $table->unsignedInteger('late_minutes')->default(0); $table->unsignedInteger('early_departure_minutes')->default(0); $table->string('status', 30)->default('absent'); $table->string('source', 40)->nullable(); $table->timestamps(); $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete(); $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete(); $table->unique(['employee_id', 'attendance_date']);
        });
        Schema::create('attendance_regularizations', function (Blueprint $table) {
            $table->uuid('id')->primary(); $table->uuid('tenant_id')->index(); $table->uuid('employee_id')->index(); $table->date('attendance_date'); $table->string('reason_type', 40); $table->text('reason'); $table->json('requested_values'); $table->uuid('workflow_instance_id')->nullable()->index(); $table->string('status', 20)->default('pending'); $table->timestamps(); $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete(); $table->foreign('workflow_instance_id')->references('id')->on('workflow_instances')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('attendance_regularizations'); Schema::dropIfExists('daily_attendances'); Schema::dropIfExists('attendance_punches'); Schema::dropIfExists('shift_rosters'); Schema::dropIfExists('attendance_policies'); }
};
