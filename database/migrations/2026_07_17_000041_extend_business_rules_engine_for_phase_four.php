<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_rules', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('name');
            $table->string('category', 40)->default('decision')->after('domain')->index();
            $table->json('tags')->nullable()->after('settings');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->index(['tenant_id', 'trigger', 'status', 'priority']);
            $table->index(['tenant_id', 'effective_from', 'effective_to']);
        });
        Schema::table('rule_executions', function (Blueprint $table): void {
            $table->json('trace')->nullable()->after('output');
            $table->string('correlation_id', 100)->nullable()->index();
        });
        Schema::create('rule_dependencies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('business_rule_id')->index();
            $table->string('dependency_type', 40);
            $table->string('dependency_key', 160);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->foreign('business_rule_id')->references('id')->on('business_rules')->cascadeOnDelete();
            $table->unique(['business_rule_id', 'dependency_type', 'dependency_key']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('rule_dependencies');
        Schema::table('rule_executions', fn (Blueprint $table) => $table->dropColumn(['trace', 'correlation_id']));
        Schema::table('business_rules', fn (Blueprint $table) => $table->dropColumn(['description', 'category', 'tags', 'reviewed_at', 'reviewed_by', 'updated_by', 'deleted_at']));
    }
};
