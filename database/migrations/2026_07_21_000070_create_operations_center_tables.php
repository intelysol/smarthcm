<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('ops_metrics', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->string('metric', 100); $t->decimal('value', 20, 6); $t->string('unit', 20)->nullable(); $t->json('dimensions')->nullable(); $t->timestamp('recorded_at')->index(); $t->timestamps(); });
        Schema::create('ops_alert_rules', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->string('name'); $t->string('metric', 100); $t->string('operator', 10); $t->decimal('threshold', 20, 6); $t->string('severity', 20)->default('warning'); $t->json('channels')->nullable(); $t->boolean('is_active')->default(true); $t->timestamps(); });
        Schema::create('ops_alerts', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->uuid('rule_id')->nullable(); $t->string('severity', 20); $t->string('status', 20)->default('open'); $t->string('message'); $t->json('payload')->nullable(); $t->timestamp('triggered_at'); $t->timestamp('resolved_at')->nullable(); $t->timestamps(); });
        Schema::create('ops_incidents', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->string('title'); $t->text('description')->nullable(); $t->string('severity', 20); $t->string('status', 20)->default('open'); $t->unsignedBigInteger('assignee_id')->nullable(); $t->json('timeline')->nullable(); $t->text('root_cause')->nullable(); $t->timestamp('resolved_at')->nullable(); $t->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('ops_incidents'); Schema::dropIfExists('ops_alerts'); Schema::dropIfExists('ops_alert_rules'); Schema::dropIfExists('ops_metrics'); }
};
