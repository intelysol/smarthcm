<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuration_definitions', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->string('key', 160)->unique(); $t->string('name'); $t->text('description')->nullable(); $t->string('category', 80); $t->string('data_type', 20); $t->json('default_value')->nullable(); $t->json('validation_rules')->nullable(); $t->json('scopes'); $t->boolean('is_sensitive')->default(false); $t->boolean('is_user_configurable')->default(false); $t->timestamps(); });
        Schema::create('configuration_values', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('definition_id')->index(); $t->string('scope', 20); $t->string('scope_id', 80)->nullable(); $t->json('value')->nullable(); $t->boolean('is_encrypted')->default(false); $t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamps(); $t->foreign('definition_id')->references('id')->on('configuration_definitions')->cascadeOnDelete(); $t->unique(['definition_id','scope','scope_id']); });
        Schema::create('configuration_audit_logs', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->uuid('definition_id')->index(); $t->string('scope', 20); $t->string('scope_id', 80)->nullable(); $t->json('previous_value')->nullable(); $t->json('new_value')->nullable(); $t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamp('occurred_at'); $t->timestamps(); $t->foreign('definition_id')->references('id')->on('configuration_definitions')->cascadeOnDelete(); });
    }
    public function down(): void { Schema::dropIfExists('configuration_audit_logs'); Schema::dropIfExists('configuration_values'); Schema::dropIfExists('configuration_definitions'); }
};
