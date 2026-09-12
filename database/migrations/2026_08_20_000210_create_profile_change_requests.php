<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void { Schema::create('profile_change_requests', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('tenant_id')->index(); $t->uuid('employee_id')->index(); $t->foreignId('requested_by')->constrained('users')->cascadeOnDelete(); $t->string('request_type', 80); $t->json('requested_changes'); $t->text('reason')->nullable(); $t->string('status', 20)->default('submitted')->index(); $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamp('reviewed_at')->nullable(); $t->timestamp('effective_at')->nullable(); $t->timestamps(); $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete(); }); }
    public function down(): void { Schema::dropIfExists('profile_change_requests'); }
};
