<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('data_lifecycle_policies')) {
            Schema::create('data_lifecycle_policies', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('domain', 60);
                $table->string('data_class', 100);
                $table->string('classification', 40)->default('INTERNAL');
                $table->unsignedInteger('active_days')->default(365);
                $table->unsignedInteger('retention_days')->default(2555);
                $table->string('archive_strategy', 40)->default('cold_storage');
                $table->boolean('legal_hold_supported')->default(true);
                $table->boolean('is_system_locked')->default(false);
                $table->unsignedInteger('version')->default(1);
                $table->timestamps();

                $table->index(['tenant_id', 'domain', 'data_class']);
            });
        }

        if (! Schema::hasTable('data_lifecycle_archives')) {
            Schema::create('data_lifecycle_archives', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('data_class', 100);
                $table->string('original_table', 120);
                $table->unsignedInteger('record_count')->default(0);
                $table->unsignedInteger('file_count')->default(0);
                $table->string('checksum_sha256', 64);
                $table->string('storage_path', 255);
                $table->json('manifest');
                $table->timestamp('retention_until')->nullable();
                $table->string('status', 30)->default('ARCHIVED');
                $table->unsignedInteger('version')->default(1);
                $table->string('archived_by', 100)->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'data_class', 'status']);
            });
        }

        if (! Schema::hasTable('data_lifecycle_legal_holds')) {
            Schema::create('data_lifecycle_legal_holds', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('hold_reference', 100);
                $table->string('scope_type', 50)->default('tenant');
                $table->string('scope_id', 100)->nullable();
                $table->json('data_classes')->nullable();
                $table->text('reason');
                $table->string('created_by', 100)->nullable();
                $table->timestamp('effective_from');
                $table->timestamp('effective_until')->nullable();
                $table->string('status', 20)->default('active');
                $table->string('released_by', 100)->nullable();
                $table->timestamp('released_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'scope_type', 'status']);
            });
        }

        if (! Schema::hasTable('data_lifecycle_jobs')) {
            Schema::create('data_lifecycle_jobs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('job_type', 30)->default('dry_run');
                $table->string('target_class', 100);
                $table->string('status', 30)->default('pending');
                $table->unsignedInteger('total_records')->default(0);
                $table->unsignedInteger('processed_records')->default(0);
                $table->unsignedInteger('skipped_records')->default(0);
                $table->unsignedInteger('protected_records')->default(0);
                $table->text('failure_reason')->nullable();
                $table->string('checkpoint_token', 100)->nullable();
                $table->json('metadata')->nullable();
                $table->string('initiated_by', 100)->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'job_type', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_lifecycle_jobs');
        Schema::dropIfExists('data_lifecycle_legal_holds');
        Schema::dropIfExists('data_lifecycle_archives');
        Schema::dropIfExists('data_lifecycle_policies');
    }
};
