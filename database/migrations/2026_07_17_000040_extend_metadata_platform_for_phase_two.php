<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('metadata_entities', function (Blueprint $table): void {
            $table->string('entity_type', 30)->default('master')->after('module')->index();
            $table->string('category', 80)->nullable()->after('entity_type')->index();
            $table->string('icon', 80)->nullable()->after('category');
            $table->string('color', 20)->nullable()->after('icon');
            $table->text('description')->nullable()->after('color');
            $table->boolean('supports_soft_deletes')->default(true);
            $table->boolean('supports_audit')->default(true);
            $table->unsignedBigInteger('row_version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->index(['tenant_id', 'status', 'entity_type']);
        });

        Schema::table('metadata_fields', function (Blueprint $table): void {
            $table->unsignedBigInteger('row_version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->index(['entity_id', 'sort_order']);
        });

        Schema::table('metadata_forms', function (Blueprint $table): void {
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        Schema::table('metadata_relationships', function (Blueprint $table): void {
            $table->string('key', 80)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->unique(['source_entity_id', 'key']);
        });

        Schema::table('metadata_records', function (Blueprint $table): void {
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('row_version')->default(1);
            $table->index(['tenant_id', 'entity_id', 'status']);
        });

        Schema::table('metadata_artifacts', function (Blueprint $table): void {
            $table->string('name', 160)->nullable()->after('key');
            $table->text('description')->nullable()->after('name');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->index(['tenant_id', 'artifact_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('metadata_artifacts', fn (Blueprint $table) => $table->dropColumn(['name', 'description', 'created_by', 'updated_by', 'deleted_at']));
        Schema::table('metadata_records', fn (Blueprint $table) => $table->dropColumn(['updated_by', 'row_version']));
        Schema::table('metadata_relationships', fn (Blueprint $table) => $table->dropColumn(['key', 'created_by', 'deleted_at']));
        Schema::table('metadata_forms', fn (Blueprint $table) => $table->dropColumn(['version', 'created_by', 'updated_by', 'deleted_at']));
        Schema::table('metadata_fields', fn (Blueprint $table) => $table->dropColumn(['row_version', 'created_by', 'updated_by', 'deleted_at']));
        Schema::table('metadata_entities', fn (Blueprint $table) => $table->dropColumn(['entity_type', 'category', 'icon', 'color', 'description', 'supports_soft_deletes', 'supports_audit', 'row_version', 'created_by', 'updated_by', 'deleted_by', 'deleted_at']));
    }
};
