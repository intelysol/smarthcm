<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->string('event_type', 80); $t->string('action', 40); $t->string('entity_type', 120)->nullable(); $t->string('entity_id', 80)->nullable(); $t->string('actor_type', 30)->default('user'); $t->string('actor_id', 80)->nullable(); $t->string('source', 50)->default('application'); $t->string('module', 80)->nullable(); $t->string('severity', 10)->default('info'); $t->string('request_id', 100)->nullable(); $t->string('correlation_id', 100)->nullable()->index(); $t->text('reason')->nullable(); $t->json('before_data')->nullable(); $t->json('after_data')->nullable(); $t->json('changed_fields')->nullable(); $t->json('metadata')->nullable(); $t->string('previous_hash', 64)->nullable(); $t->string('integrity_hash', 64); $t->timestamp('occurred_at')->index(); $t->timestamps(); $t->index(['tenant_id','entity_type','entity_id']); });
        if (! Schema::hasTable('security_events')) {
            Schema::create('security_events', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->string('event_type', 100); $t->string('severity', 10); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $t->unsignedSmallInteger('risk_score')->default(0); $t->json('metadata')->nullable(); $t->timestamp('occurred_at')->index(); $t->timestamps(); });
        }
    }
    public function down(): void { Schema::dropIfExists('security_events'); Schema::dropIfExists('audit_events'); }
};
