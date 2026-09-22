<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('status', 20)->default('active')->index();
                $table->string('locale', 10)->default('en');
                $table->string('timezone', 80)->nullable();
                $table->string('avatar_path')->nullable();
                $table->boolean('mfa_enabled')->default(false);
            });
        }

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('name', 100);
                $table->string('label', 150);
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false)->index();
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'name']);
            });
        }

        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table): void {
                $table->uuid('role_id');
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->timestamps();
                $table->primary(['role_id', 'permission_id']);
                $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table): void {
                $table->uuid('role_id');
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->primary(['role_id', 'user_id']);
                $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('first_name', 100)->nullable();
                $table->string('last_name', 100)->nullable();
                $table->string('phone', 40)->nullable();
                $table->string('job_title', 150)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->index(['tenant_id', 'last_name', 'first_name']);
            });
        }

        if (!Schema::hasTable('user_preferences')) {
            Schema::create('user_preferences', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('theme', 20)->default('system');
                $table->string('language', 10)->default('en');
                $table->string('timezone', 80)->nullable();
                $table->json('preferences')->nullable();
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('channel', 30);
                $table->string('notification_type', 100);
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['user_id', 'channel', 'notification_type'], 'notif_pref_user_chan_type_uq');
            });
        }

        if (!Schema::hasTable('platform_notifications')) {
            Schema::create('platform_notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('type', 100);
                $table->string('title');
                $table->text('body')->nullable();
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->index(['tenant_id', 'user_id', 'read_at']);
            });
        }

        if (!Schema::hasTable('navigation_favorites')) {
            Schema::create('navigation_favorites', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('route_name', 160);
                $table->string('label', 160);
                $table->string('url', 500);
                $table->unsignedSmallInteger('position')->default(0);
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['user_id', 'route_name']);
            });
        }

        if (!Schema::hasTable('recent_pages')) {
            Schema::create('recent_pages', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('route_name', 160);
                $table->string('label', 160);
                $table->string('url', 500);
                $table->timestamp('visited_at')->index();
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['user_id', 'route_name']);
            });
        }

        if (!Schema::hasTable('feature_flags')) {
            Schema::create('feature_flags', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('key', 100);
                $table->string('name', 160);
                $table->text('description')->nullable();
                $table->boolean('is_enabled')->default(false)->index();
                $table->json('targeting_rules')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'key']);
            });
        }

        if (!Schema::hasTable('login_histories')) {
            Schema::create('login_histories', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->nullable()->index();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('email')->index();
                $table->string('event', 30);
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('occurred_at')->index();
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
                $table->index(['tenant_id', 'user_id', 'occurred_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login_histories');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('recent_pages');
        Schema::dropIfExists('navigation_favorites');
        Schema::dropIfExists('platform_notifications');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['status', 'locale', 'timezone', 'avatar_path', 'mfa_enabled']);
        });
    }
};
