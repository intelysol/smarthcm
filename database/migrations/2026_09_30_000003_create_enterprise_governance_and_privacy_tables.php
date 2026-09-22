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
        if (! Schema::hasTable('governance_frameworks')) {
            Schema::create('governance_frameworks', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('code', 50)->index();
                $table->string('name', 150);
                $table->string('authority', 150);
                $table->string('jurisdiction', 100);
                $table->string('version', 50);
                $table->string('status', 30)->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('governance_controls')) {
            Schema::create('governance_controls', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->nullable()->index();
                $table->uuid('framework_id')->index();
                $table->string('code', 50)->index();
                $table->string('title', 200);
                $table->text('objective');
                $table->string('control_type', 50)->default('PREVENTIVE');
                $table->string('frequency', 50)->default('CONTINUOUS');
                $table->string('status', 30)->default('ACTIVE');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('governance_control_tests')) {
            Schema::create('governance_control_tests', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('control_id')->index();
                $table->text('test_procedure');
                $table->string('tested_by', 100);
                $table->string('result', 30); // PASS, FAIL, PARTIAL
                $table->string('evidence_reference', 255)->nullable();
                $table->string('evidence_hash_sha256', 64)->nullable();
                $table->timestamp('tested_at');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('governance_findings')) {
            Schema::create('governance_findings', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('control_id')->index();
                $table->string('title', 200);
                $table->string('severity', 30)->default('MEDIUM'); // LOW, MEDIUM, HIGH, CRITICAL
                $table->string('owner', 100);
                $table->text('remediation_plan')->nullable();
                $table->string('status', 30)->default('OPEN'); // OPEN, IN_REMEDIATION, VERIFIED, CLOSED
                $table->date('due_date')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('governance_exceptions')) {
            Schema::create('governance_exceptions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('control_id')->index();
                $table->text('reason');
                $table->text('business_justification');
                $table->string('risk_level', 30)->default('MEDIUM');
                $table->text('compensating_control');
                $table->string('approved_by', 100)->nullable();
                $table->timestamp('expires_at');
                $table->string('status', 30)->default('approved'); // approved, expired, revoked
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('governance_attestations')) {
            Schema::create('governance_attestations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('subject_type', 100);
                $table->text('statement');
                $table->string('attestor', 100);
                $table->string('version', 50)->default('1.0');
                $table->string('signature_hash', 64);
                $table->timestamp('attested_at');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('privacy_processing_activities')) {
            Schema::create('privacy_processing_activities', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('name', 150);
                $table->text('purpose');
                $table->string('business_owner', 100);
                $table->json('data_categories');
                $table->string('legal_basis', 100);
                $table->string('processing_location', 100)->default('EU/EEA');
                $table->string('retention_policy_ref', 100)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('privacy_requests')) {
            Schema::create('privacy_requests', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('request_reference', 100)->unique();
                $table->uuid('user_id')->index();
                $table->string('request_type', 50); // access, rectification, deletion, portability
                $table->boolean('identity_verified')->default(false);
                $table->string('status', 30)->default('received'); // received, in_progress, completed, blocked
                $table->text('blocked_reason')->nullable();
                $table->string('export_path', 255)->nullable();
                $table->string('export_hash_sha256', 64)->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('privacy_impact_assessments')) {
            Schema::create('privacy_impact_assessments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('processing_activity_id')->index();
                $table->string('title', 200);
                $table->text('necessity_summary');
                $table->string('risk_level', 30)->default('MEDIUM');
                $table->json('mitigations')->nullable();
                $table->boolean('dpo_approval')->default(false);
                $table->string('status', 30)->default('draft');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privacy_impact_assessments');
        Schema::dropIfExists('privacy_requests');
        Schema::dropIfExists('privacy_processing_activities');
        Schema::dropIfExists('governance_attestations');
        Schema::dropIfExists('governance_exceptions');
        Schema::dropIfExists('governance_findings');
        Schema::dropIfExists('governance_control_tests');
        Schema::dropIfExists('governance_controls');
        Schema::dropIfExists('governance_frameworks');
    }
};
