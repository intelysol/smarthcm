<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('leave_types', function (Blueprint $table) { $table->uuid('id')->primary(); $table->uuid('tenant_id')->index(); $table->string('name'); $table->string('code', 40); $table->boolean('is_paid')->default(true); $table->json('rules')->nullable(); $table->timestamps(); $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); $table->unique(['tenant_id','code']); });
        Schema::create('leave_policies', function (Blueprint $table) { $table->uuid('id')->primary(); $table->uuid('tenant_id')->index(); $table->uuid('leave_type_id'); $table->string('name'); $table->json('rules'); $table->timestamps(); $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); $table->foreign('leave_type_id')->references('id')->on('leave_types')->cascadeOnDelete(); });
        Schema::create('leave_balances', function (Blueprint $table) { $table->uuid('id')->primary(); $table->uuid('tenant_id')->index(); $table->uuid('employee_id'); $table->uuid('leave_type_id'); $table->unsignedSmallInteger('year'); $table->decimal('entitled',10,2)->default(0); $table->decimal('earned',10,2)->default(0); $table->decimal('used',10,2)->default(0); $table->decimal('pending',10,2)->default(0); $table->decimal('carried_forward',10,2)->default(0); $table->decimal('encashed',10,2)->default(0); $table->timestamps(); $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete(); $table->foreign('leave_type_id')->references('id')->on('leave_types')->cascadeOnDelete(); $table->unique(['employee_id','leave_type_id','year']); });
        Schema::create('leave_applications', function (Blueprint $table) { $table->uuid('id')->primary(); $table->uuid('tenant_id')->index(); $table->uuid('employee_id')->index(); $table->uuid('leave_type_id'); $table->date('start_date'); $table->date('end_date'); $table->decimal('duration',8,2); $table->string('unit',20)->default('day'); $table->text('reason')->nullable(); $table->json('attachments')->nullable(); $table->string('status',20)->default('draft'); $table->uuid('workflow_instance_id')->nullable()->index(); $table->timestamps(); $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete(); $table->foreign('leave_type_id')->references('id')->on('leave_types')->restrictOnDelete(); $table->foreign('workflow_instance_id')->references('id')->on('workflow_instances')->nullOnDelete(); });
    }
    public function down(): void { Schema::dropIfExists('leave_applications'); Schema::dropIfExists('leave_balances'); Schema::dropIfExists('leave_policies'); Schema::dropIfExists('leave_types'); }
};
