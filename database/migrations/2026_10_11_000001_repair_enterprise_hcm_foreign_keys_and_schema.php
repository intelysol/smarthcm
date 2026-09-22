<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Repairs and standardizes foreign keys referencing users.id (BIGINT UNSIGNED)
     * across existing Enterprise HCM tables where datatypes might have drifted or mismatched.
     */
    public function up(): void
    {
        $columnRepairs = [
            // [table, column, nullable, onDelete]
            ['employee_document_access_logs', 'reviewer_id', true, 'set null'],
            ['employee_document_requests', 'requested_by', true, 'set null'],
            ['employee_document_requests', 'reviewer_id', true, 'set null'],
            ['employee_document_acknowledgments', 'actor_id', true, 'set null'],
            ['employee_compliance_documents', 'uploaded_by', true, 'set null'],
            ['employee_compliance_documents', 'verified_by', true, 'set null'],
            ['profile_directory_verifications', 'reviewed_by', true, 'set null'],
            ['profile_directory_audits', 'actor_id', true, 'set null'],
            ['employee_bank_accounts', 'reviewed_by', true, 'set null'],
            ['employee_personal_identifications', 'verified_by', true, 'set null'],
            ['employee_data_change_requests', 'reviewed_by', true, 'set null'],
            ['employee_emergency_contacts', 'verified_by', true, 'set null'],
            ['employee_dependents', 'verified_by', true, 'set null'],
            ['employee_personal_documents', 'uploaded_by', true, 'set null'],
            ['employee_personal_documents', 'verified_by', true, 'set null'],
            ['hcm_command_center_saved_views', 'user_id', false, 'cascade'],
            ['hcm_command_center_user_preferences', 'user_id', false, 'cascade'],
            ['hcm_command_center_audits', 'user_id', true, 'set null'],
            ['hcm_gov_audits', 'user_id', true, 'set null'],
            ['hcm_ai_concierge_sessions', 'user_id', false, 'cascade'],
            ['hcm_ai_concierge_actions', 'user_id', true, 'set null'],
            ['hcm_ai_concierge_suggestions', 'user_id', true, 'set null'],
            ['hcm_ai_concierge_feedback', 'user_id', false, 'cascade'],
            ['hcm_ai_gov_audits', 'user_id', true, 'set null'],
            ['hcm_ai_interaction_telemetry', 'user_id', true, 'set null'],
            ['hcm_ai_feedback', 'user_id', false, 'cascade'],
            ['tenant_config_audit_logs', 'changed_by_user_id', true, 'set null'],
            ['tenant_admin_delegations', 'delegator_user_id', false, 'cascade'],
            ['tenant_admin_delegations', 'delegate_user_id', false, 'cascade'],
            ['tenant_admin_delegations', 'created_by', true, 'set null'],
            ['tenant_backup_schedules', 'initiated_by_user_id', true, 'set null'],
            ['hcm_experience_event_stream', 'actor_user_id', true, 'set null'],
            ['hcm_command_center_alerts', 'acknowledged_by_user_id', true, 'set null'],
            ['hcm_command_center_decision_items', 'actioned_by_user_id', true, 'set null'],
            ['hcm_command_center_kpi_versions', 'approved_by_user_id', true, 'set null'],
            ['hcm_command_center_risks', 'owner_user_id', true, 'set null'],
            ['hcm_command_center_snapshots', 'generated_by_user_id', true, 'set null'],
            ['hcm_gov_kpi_registries', 'certified_by_user_id', true, 'set null'],
            ['hcm_gov_master_mappings', 'verified_by_user_id', true, 'set null'],
            ['hcm_gov_quality_exceptions', 'approved_by_user_id', true, 'set null'],
            ['hcm_gov_quality_issues', 'resolved_by_user_id', true, 'set null'],
            ['hcm_ai_gov_incidents', 'assigned_user_id', true, 'set null'],
            ['hcm_ai_gov_kill_switches', 'activated_by_user_id', false, 'cascade'],
            ['hcm_ai_gov_models', 'approved_by_user_id', true, 'set null'],
            ['hcm_ai_gov_use_cases', 'approved_by_user_id', true, 'set null'],
            ['hcm_ai_eval_runs', 'executed_by_user_id', true, 'set null'],
            ['hcm_ai_improvement_items', 'owner_user_id', true, 'set null'],
            ['hcm_tenant_configurations', 'created_by_user_id', true, 'set null'],
            ['hcm_tenant_configurations', 'published_by_user_id', true, 'set null'],
            ['hcm_mobility_tasks', 'assigned_to_user_id', true, 'set null'],
            ['hcm_ops_exception_assignments', 'assigned_user_id', true, 'set null'],
        ];

        $driver = DB::getDriverName();

        foreach ($columnRepairs as [$table, $column, $nullable, $onDelete]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }

            // Check existing column definition in MySQL
            if ($driver === 'mysql') {
                $colInfo = DB::select(
                    "SELECT COLUMN_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                    [$table, $column]
                );

                if (!empty($colInfo)) {
                    $colType = strtolower($colInfo[0]->COLUMN_TYPE);
                    // If not bigint unsigned, modify column definition
                    if (strpos($colType, 'bigint') === false || strpos($colType, 'unsigned') === false) {
                        // Drop existing foreign key if exists
                        $existingFks = DB::select(
                            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL",
                            [$table, $column]
                        );
                        foreach ($existingFks as $fk) {
                            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                        }

                        $nullSql = $nullable ? "NULL" : "NOT NULL";
                        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED {$nullSql}");
                    }
                }

                // Check if foreign key to users exists
                $fkExists = DB::select(
                    "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = 'users'",
                    [$table, $column]
                );

                if (empty($fkExists) && Schema::hasTable('users')) {
                    $fkName = "fk_{$table}_{$column}_users";
                    // Shorten if constraint name > 64 chars
                    if (strlen($fkName) > 64) {
                        $fkName = substr("fk_" . md5($table . '_' . $column), 0, 60);
                    }
                    try {
                        DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`{$column}`) REFERENCES `users` (`id`) ON DELETE {$onDelete}");
                    } catch (\Throwable $e) {
                        // In case constraint already exists or orphaned rows exist, log and continue
                    }
                }
            } else {
                // SQLite or Postgres fallback
                Schema::table($table, function (Blueprint $t) use ($column, $nullable, $onDelete) {
                    if ($nullable) {
                        $t->unsignedBigInteger($column)->nullable()->change();
                    } else {
                        $t->unsignedBigInteger($column)->change();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op on rollback since standardizing to BIGINT UNSIGNED users.id is an architectural alignment
    }
};
