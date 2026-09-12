<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Service Feedback & CSAT
        if (!Schema::hasTable('hr_service_feedback')) {
            Schema::create('hr_service_feedback', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('hr_service_request_id')->index();
                $table->uuid('employee_id')->index();
                $table->unsignedTinyInteger('rating'); // 1 to 5 stars
                $table->string('satisfaction_level', 30)->default('satisfied'); // very_satisfied, satisfied, neutral, dissatisfied, very_dissatisfied
                $table->text('comments')->nullable();
                $table->unsignedTinyInteger('timeliness_rating')->nullable();
                $table->unsignedTinyInteger('knowledge_rating')->nullable();
                $table->unsignedTinyInteger('helpfulness_rating')->nullable();
                $table->timestamps();

                $table->foreign('hr_service_request_id')->references('id')->on('hr_service_requests')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 2. Dynamic Service Form Rules
        if (!Schema::hasTable('hr_service_form_rules')) {
            Schema::create('hr_service_form_rules', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('hr_service_definition_id')->index();
                $table->string('source_field_key', 80);
                $table->string('operator', 30)->default('equals'); // equals, not_equals, in, not_in, contains, greater_than
                $table->json('trigger_values');
                $table->string('action', 40)->default('show'); // show, hide, require, optional, set_value, set_options
                $table->string('target_field_key', 80);
                $table->json('action_payload')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('hr_service_definition_id')->references('id')->on('hr_service_definitions')->cascadeOnDelete();
            });
        }

        // 3. Specialized Shared Services Teams
        if (!Schema::hasTable('hr_service_teams')) {
            Schema::create('hr_service_teams', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 50)->index();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->foreignId('lead_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('supported_categories')->nullable();
                $table->json('supported_countries')->nullable();
                $table->string('working_hours_calendar', 50)->default('STANDARD');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('hr_service_team_members')) {
            Schema::create('hr_service_team_members', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('hr_service_team_id')->index();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role', 40)->default('agent'); // agent, specialist, lead, supervisor
                $table->unsignedInteger('max_concurrent_capacity')->default(15);
                $table->boolean('is_available')->default(true);
                $table->timestamps();

                $table->foreign('hr_service_team_id')->references('id')->on('hr_service_teams')->cascadeOnDelete();
            });
        }

        // 4. Omnichannel Inbound Messages
        if (!Schema::hasTable('hr_service_omnichannel_messages')) {
            Schema::create('hr_service_omnichannel_messages', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('channel', 40); // email, teams, whatsapp, web_chat, mobile_app, api
                $table->string('external_message_id', 150)->nullable()->index();
                $table->string('sender_identifier', 150);
                $table->string('sender_name', 150)->nullable();
                $table->uuid('matched_employee_id')->nullable()->index();
                $table->uuid('hr_service_request_id')->nullable()->index();
                $table->string('subject', 255)->nullable();
                $table->longText('body');
                $table->json('raw_payload')->nullable();
                $table->string('processing_status', 30)->default('pending'); // pending, processed, converted_to_request, ignored, failed
                $table->timestamps();

                $table->foreign('matched_employee_id')->references('id')->on('employees')->nullOnDelete();
                $table->foreign('hr_service_request_id')->references('id')->on('hr_service_requests')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_service_omnichannel_messages');
        Schema::dropIfExists('hr_service_team_members');
        Schema::dropIfExists('hr_service_teams');
        Schema::dropIfExists('hr_service_form_rules');
        Schema::dropIfExists('hr_service_feedback');
    }
};
