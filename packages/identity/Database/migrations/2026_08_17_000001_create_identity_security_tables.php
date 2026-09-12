<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('username', 100)->nullable()->after('tenant_id');
            $table->string('phone', 40)->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable();
            $table->unsignedBigInteger('login_count')->default(0);
            $table->timestamp('password_expires_at')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('last_password_changed_at')->nullable();
            $table->unique(['tenant_id', 'username']);
        });

        Schema::create('user_devices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('fingerprint', 128)->index();
            $table->string('name', 160)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->timestamp('last_seen_at')->index();
            $table->timestamps();
            $table->unique(['user_id', 'fingerprint']);
        });

        Schema::create('user_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('device_id')->nullable()->index();
            $table->string('session_id', 128)->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('geo_location', 160)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_active_at')->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('password_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('password');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('login_attempts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('identifier')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('fingerprint', 128)->nullable();
            $table->boolean('successful')->index();
            $table->string('failure_reason', 100)->nullable();
            $table->timestamp('attempted_at')->index();
        });

        Schema::create('trusted_devices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('fingerprint', 128);
            $table->timestamp('expires_at')->index();
            $table->timestamps();
            $table->unique(['user_id', 'fingerprint']);
        });

        Schema::create('mfa_methods', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->text('secret')->nullable();
            $table->string('destination', 255)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'type']);
        });

        Schema::create('mfa_recovery_codes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('api_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('token', 64)->unique();
            $table->json('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('oauth_clients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('secret');
            $table->json('redirect_uris');
            $table->json('scopes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('security_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 100)->index();
            $table->string('severity', 20)->default('info')->index();
            $table->string('ip_address', 45)->nullable();
            $table->json('context')->nullable();
            $table->timestamp('occurred_at')->index();
        });
    }

    public function down(): void
    {
        foreach (['security_events', 'oauth_clients', 'api_tokens', 'mfa_recovery_codes', 'mfa_methods', 'trusted_devices', 'login_attempts', 'password_histories', 'user_sessions', 'user_devices'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'username']);
            $table->dropUnique(['uuid']);
            $table->dropColumn(['uuid', 'username', 'phone', 'phone_verified_at', 'last_login_at', 'login_count', 'password_expires_at', 'must_change_password', 'locked_at', 'last_password_changed_at']);
        });
    }
};
