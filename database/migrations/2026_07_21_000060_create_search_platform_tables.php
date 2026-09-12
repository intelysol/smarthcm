<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('search_entries', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('tenant_id')->index(); $t->string('module', 60)->index(); $t->string('entity_type', 120)->index(); $t->string('entity_id', 80); $t->string('title'); $t->longText('content')->nullable(); $t->json('metadata')->nullable(); $t->json('tags')->nullable(); $t->string('status', 40)->nullable()->index(); $t->unsignedBigInteger('owner_id')->nullable()->index(); $t->string('url')->nullable(); $t->timestamp('indexed_at')->nullable(); $t->unsignedInteger('index_version')->default(1); $t->timestamps(); $t->unique(['tenant_id', 'module', 'entity_type', 'entity_id']); $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); });
        Schema::create('saved_searches', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('tenant_id')->index(); $t->unsignedBigInteger('user_id')->index(); $t->string('name'); $t->string('query')->nullable(); $t->json('filters')->nullable(); $t->boolean('is_pinned')->default(false); $t->boolean('is_shared')->default(false); $t->string('schedule')->nullable(); $t->timestamps(); $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); });
        Schema::create('search_queries', function (Blueprint $t): void { $t->uuid('id')->primary(); $t->uuid('tenant_id')->index(); $t->unsignedBigInteger('user_id')->index(); $t->string('query'); $t->json('filters')->nullable(); $t->unsignedInteger('result_count')->default(0); $t->unsignedInteger('duration_ms')->nullable(); $t->uuid('selected_entry_id')->nullable(); $t->timestamps(); $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete(); $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); });
    }
    public function down(): void { Schema::dropIfExists('search_queries'); Schema::dropIfExists('saved_searches'); Schema::dropIfExists('search_entries'); }
};
