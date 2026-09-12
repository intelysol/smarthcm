<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('tenant_code', 50)->nullable()->unique()->after('uuid');
            $table->string('legal_name')->nullable()->after('name');
            $table->string('status', 20)->default('active')->index()->after('slug');
            $table->string('type', 40)->default('organization')->after('status');
            $table->string('locale', 10)->default('en')->after('timezone');
            $table->string('country_code', 2)->nullable()->after('currency');
            $table->string('primary_email')->nullable();
            $table->string('primary_phone', 40)->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->unsignedInteger('version')->default(1);
        });

        DB::table('tenants')->orderBy('id')->each(function (object $tenant): void {
            DB::table('tenants')->where('id', $tenant->id)->update([
                'uuid' => $tenant->id,
                'tenant_code' => strtoupper(substr(str_replace('-', '', $tenant->id), 0, 12)),
                'status' => $tenant->is_active ? 'active' : 'suspended',
            ]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_platform_admin')->default(false)->index()->after('status');
        });

        Schema::create('tenant_user', function (Blueprint $table): void {
            $table->uuid('tenant_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('active')->index();
            $table->string('role_placeholder', 100)->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->primary(['tenant_id', 'user_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        DB::table('users')->whereNotNull('tenant_id')->orderBy('id')->each(function (object $user): void {
            DB::table('tenant_user')->updateOrInsert(
                ['tenant_id' => $user->tenant_id, 'user_id' => $user->id],
                ['status' => 'active', 'joined_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            );
        });

        Schema::create('tenant_domains', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('domain')->unique();
            $table->string('type', 30)->default('subdomain');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('verification_token', 100)->nullable()->unique();
            $table->timestamp('verified_at')->nullable();
            $table->string('ssl_status', 30)->default('not_requested');
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'is_primary']);
        });

        foreach (['tenant_settings', 'tenant_features', 'tenant_branding', 'tenant_localizations', 'tenant_security_policies', 'tenant_usage'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->unique();
                $table->json($name === 'tenant_usage' ? 'metrics' : 'configuration')->nullable();
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        Schema::create('tenant_invitations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('email')->index();
            $table->string('token', 100)->unique();
            $table->string('status', 20)->default('pending')->index();
            $table->string('role_placeholder', 100)->nullable();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'email']);
        });

        Schema::create('tenant_audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100)->index();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_audit_logs');
        Schema::dropIfExists('tenant_invitations');
        foreach (['tenant_usage', 'tenant_security_policies', 'tenant_localizations', 'tenant_branding', 'tenant_features', 'tenant_settings', 'tenant_domains', 'tenant_user'] as $name) {
            Schema::dropIfExists($name);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_platform_admin'));
        Schema::table('tenants', fn (Blueprint $table) => $table->dropColumn(['uuid', 'tenant_code', 'legal_name', 'status', 'type', 'locale', 'country_code', 'primary_email', 'primary_phone', 'website', 'logo', 'activated_at', 'suspended_at', 'version']));
    }
};
