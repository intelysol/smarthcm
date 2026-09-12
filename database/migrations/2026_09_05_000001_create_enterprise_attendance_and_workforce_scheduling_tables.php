<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Work Calendars
        if (! Schema::hasTable('work_calendars')) {
            Schema::create('work_calendars', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('company_id')->nullable()->index();
                $table->uuid('location_id')->nullable()->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->string('timezone', 50)->default('UTC');
                $table->text('description')->nullable();
                $table->boolean('is_default')->default(false);
                $table->string('status', 30)->default('active'); // active, inactive
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 2. Work Calendar Days
        if (! Schema::hasTable('work_calendar_days')) {
            Schema::create('work_calendar_days', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('work_calendar_id')->index();
                $table->unsignedTinyInteger('day_of_week'); // 1 = Monday, 7 = Sunday
                $table->boolean('is_working_day')->default(true);
                $table->unsignedInteger('standard_hours_minutes')->default(480); // 8 hours = 480 mins
                $table->string('notes', 255)->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('work_calendar_id')->references('id')->on('work_calendars')->cascadeOnDelete();
                $table->unique(['work_calendar_id', 'day_of_week']);
            });
        }

        // 3. Work Calendar Exceptions
        if (! Schema::hasTable('work_calendar_exceptions')) {
            Schema::create('work_calendar_exceptions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('work_calendar_id')->index();
                $table->date('exception_date');
                $table->boolean('is_working_day')->default(true);
                $table->unsignedInteger('custom_hours_minutes')->nullable();
                $table->string('reason', 255)->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('work_calendar_id')->references('id')->on('work_calendars')->cascadeOnDelete();
                $table->unique(['work_calendar_id', 'exception_date']);
            });
        }

        // 4. Extend Holiday Calendars if exists, or create
        if (Schema::hasTable('holiday_calendars')) {
            Schema::table('holiday_calendars', function (Blueprint $table): void {
                if (! Schema::hasColumn('holiday_calendars', 'code')) {
                    $table->string('code', 60)->nullable()->after('tenant_id');
                }
                if (! Schema::hasColumn('holiday_calendars', 'year')) {
                    $table->unsignedSmallInteger('year')->nullable()->after('name');
                }
                if (! Schema::hasColumn('holiday_calendars', 'description')) {
                    $table->text('description')->nullable()->after('year');
                }
                if (! Schema::hasColumn('holiday_calendars', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('description');
                }
                if (! Schema::hasColumn('holiday_calendars', 'location_id')) {
                    $table->uuid('location_id')->nullable()->after('branch_id');
                }
            });
        }

        // 5. Extend Holidays if exists
        if (Schema::hasTable('holidays')) {
            Schema::table('holidays', function (Blueprint $table): void {
                if (! Schema::hasColumn('holidays', 'is_optional')) {
                    $table->boolean('is_optional')->default(false)->after('is_recurring');
                }
            });
        }

        // 6. Shift Definitions
        if (! Schema::hasTable('shift_definitions')) {
            Schema::create('shift_definitions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('shift_code', 60);
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('shift_type', 30)->default('normal'); // normal, overnight, flexible, split
                $table->string('start_time', 10); // e.g. 08:00
                $table->string('end_time', 10);   // e.g. 17:00
                $table->string('timezone', 50)->default('UTC');
                $table->unsignedInteger('duration_minutes')->default(480);
                $table->string('core_start_time', 10)->nullable(); // for flexible
                $table->string('core_end_time', 10)->nullable();   // for flexible
                $table->unsignedInteger('required_daily_minutes')->nullable();
                $table->string('split_second_start', 10)->nullable(); // for split
                $table->string('split_second_end', 10)->nullable();   // for split
                $table->unsignedInteger('grace_period_minutes')->default(15);
                $table->unsignedInteger('late_threshold_minutes')->default(30);
                $table->unsignedInteger('early_departure_threshold_minutes')->default(15);
                $table->boolean('overtime_eligible')->default(true);
                $table->unsignedInteger('min_overtime_threshold_minutes')->default(30);
                $table->unsignedInteger('rounding_rule_minutes')->default(1); // 1, 5, 15, 30
                $table->boolean('is_night_shift')->default(false);
                $table->boolean('is_flexible')->default(false);
                $table->boolean('is_split')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('color_code', 20)->default('#4f46e5');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'shift_code']);
            });
        }

        // 7. Shift Breaks
        if (! Schema::hasTable('shift_breaks')) {
            Schema::create('shift_breaks', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('shift_definition_id')->index();
                $table->string('break_name', 100);
                $table->string('break_type', 30)->default('unpaid'); // paid, unpaid, fixed, flexible
                $table->string('start_time', 10)->nullable();
                $table->string('end_time', 10)->nullable();
                $table->unsignedInteger('duration_minutes')->default(60);
                $table->boolean('is_automatic_deduction')->default(false);
                $table->unsignedInteger('minimum_break_minutes')->default(15);
                $table->unsignedInteger('maximum_break_minutes')->default(90);
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(1);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('shift_definition_id')->references('id')->on('shift_definitions')->cascadeOnDelete();
            });
        }

        // 8. Shift Patterns
        if (! Schema::hasTable('shift_patterns')) {
            Schema::create('shift_patterns', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->unsignedSmallInteger('cycle_length_days')->default(7); // 7 for weekly, 14 biweekly, etc.
                $table->string('rotation_type', 40)->default('fixed'); // fixed, weekly_rotating, custom_cycle
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'code']);
            });
        }

        // 9. Shift Pattern Days
        if (! Schema::hasTable('shift_pattern_days')) {
            Schema::create('shift_pattern_days', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('shift_pattern_id')->index();
                $table->unsignedSmallInteger('day_sequence'); // 1..cycle_length_days
                $table->uuid('shift_definition_id')->nullable()->index();
                $table->boolean('is_off_day')->default(false);
                $table->string('notes', 255)->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('shift_pattern_id')->references('id')->on('shift_patterns')->cascadeOnDelete();
                $table->foreign('shift_definition_id')->references('id')->on('shift_definitions')->nullOnDelete();
                $table->unique(['shift_pattern_id', 'day_sequence']);
            });
        }

        // 10. Roster Periods
        if (! Schema::hasTable('roster_periods')) {
            Schema::create('roster_periods', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('company_id')->nullable()->index();
                $table->uuid('department_id')->nullable()->index();
                $table->string('name', 150);
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status', 30)->default('draft'); // draft, pending_approval, approved, published, locked, cancelled
                $table->timestamp('published_at')->nullable();
                $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
                $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            });
        }

        // 11. Roster Assignments
        if (! Schema::hasTable('roster_assignments')) {
            Schema::create('roster_assignments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('roster_period_id')->nullable()->index();
                $table->uuid('employee_id')->index();
                $table->date('roster_date')->index();
                $table->uuid('shift_definition_id')->nullable()->index();
                $table->uuid('location_id')->nullable()->index();
                $table->uuid('department_id')->nullable()->index();
                $table->string('assignment_status', 30)->default('scheduled'); // scheduled, swapped, replaced, cancelled, off_day
                $table->boolean('is_published')->default(false);
                $table->uuid('replacement_employee_id')->nullable()->index();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('roster_period_id')->references('id')->on('roster_periods')->nullOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('shift_definition_id')->references('id')->on('shift_definitions')->nullOnDelete();
                $table->foreign('replacement_employee_id')->references('id')->on('employees')->nullOnDelete();
                $table->unique(['tenant_id', 'employee_id', 'roster_date']);
            });
        }

        // 12. Roster Conflicts
        if (! Schema::hasTable('roster_conflicts')) {
            Schema::create('roster_conflicts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('roster_assignment_id')->nullable()->index();
                $table->uuid('employee_id')->index();
                $table->date('conflict_date')->index();
                $table->string('conflict_type', 40); // leave_conflict, overlapping_shifts, insufficient_rest_period, duplicate_assignment, excessive_hours, inactive_employment, overtime_risk
                $table->string('severity', 20)->default('warning'); // warning, blocking, informational
                $table->text('message');
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('roster_assignment_id')->references('id')->on('roster_assignments')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // 13. Attendance Devices
        if (! Schema::hasTable('attendance_devices')) {
            Schema::create('attendance_devices', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('device_code', 60);
                $table->string('name', 150);
                $table->string('vendor', 60)->default('zkteco'); // zkteco, biometric, rfid, mobile, csv, api, virtual
                $table->string('model', 80)->nullable();
                $table->string('serial_number', 100)->nullable();
                $table->string('ip_address', 60)->nullable();
                $table->unsignedInteger('port')->nullable()->default(4370);
                $table->uuid('location_id')->nullable()->index();
                $table->uuid('branch_id')->nullable()->index();
                $table->string('timezone', 50)->default('UTC');
                $table->string('connector_type', 40)->default('zkteco'); // zkteco, biometric, rfid, mobile, csv, api
                $table->string('secret_reference', 150)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_synced_at')->nullable();
                $table->string('connection_status', 30)->default('offline'); // online, offline, error
                $table->json('metadata')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->unique(['tenant_id', 'device_code']);
            });
        }

        // 14. Attendance Sync Runs
        if (! Schema::hasTable('attendance_sync_runs')) {
            Schema::create('attendance_sync_runs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('device_id')->index();
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->string('status', 30)->default('running'); // running, success, failed, partial
                $table->unsignedInteger('events_received')->default(0);
                $table->unsignedInteger('events_imported')->default(0);
                $table->unsignedInteger('events_failed')->default(0);
                $table->unsignedInteger('events_duplicated')->default(0);
                $table->text('error_message')->nullable();
                $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('device_id')->references('id')->on('attendance_devices')->cascadeOnDelete();
            });
        }

        // 15. Raw Attendance Events (Immutable Ledger)
        if (! Schema::hasTable('attendance_raw_events')) {
            Schema::create('attendance_raw_events', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('device_id')->nullable()->index();
                $table->string('employee_device_identifier', 100)->index();
                $table->timestamp('event_timestamp')->index();
                $table->string('event_type', 30)->default('UNKNOWN');
                $table->string('device_event_id', 100)->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamp('received_at')->useCurrent();
                $table->string('source', 40)->default('device');
                $table->string('idempotency_key', 128)->index();
                $table->boolean('is_processed')->default(false)->index();
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('device_id')->references('id')->on('attendance_devices')->nullOnDelete();
                $table->unique(['tenant_id', 'idempotency_key']);
            });
        }

        // 16. Normalized Attendance Events
        if (! Schema::hasTable('attendance_events')) {
            Schema::create('attendance_events', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('raw_event_id')->nullable()->index();
                $table->uuid('employee_id')->index();
                $table->timestamp('event_timestamp')->index();
                $table->date('local_date')->index();
                $table->string('local_time', 15);
                $table->string('timezone', 50)->default('UTC');
                $table->string('event_type', 30); // check_in, check_out, break_start, break_end, unknown
                $table->string('source', 40)->default('device');
                $table->uuid('location_id')->nullable()->index();
                $table->uuid('device_id')->nullable()->index();
                $table->string('confidence_status', 30)->default('valid');
                $table->string('idempotency_key', 128)->index();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('raw_event_id')->references('id')->on('attendance_raw_events')->nullOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('device_id')->references('id')->on('attendance_devices')->nullOnDelete();
                $table->unique(['tenant_id', 'idempotency_key']);
            });
        }

        // 17. Attendance Sessions
        if (! Schema::hasTable('attendance_sessions')) {
            Schema::create('attendance_sessions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->date('session_date')->index();
                $table->uuid('work_calendar_id')->nullable()->index();
                $table->uuid('shift_definition_id')->nullable()->index();
                $table->uuid('roster_assignment_id')->nullable()->index();
                $table->timestamp('scheduled_start_time')->nullable();
                $table->timestamp('scheduled_end_time')->nullable();
                $table->unsignedInteger('scheduled_minutes')->default(0);
                $table->timestamp('actual_start_time')->nullable();
                $table->timestamp('actual_end_time')->nullable();
                $table->unsignedInteger('gross_duration_minutes')->default(0);
                $table->unsignedInteger('total_break_minutes')->default(0);
                $table->unsignedInteger('unpaid_break_minutes')->default(0);
                $table->unsignedInteger('paid_break_minutes')->default(0);
                $table->unsignedInteger('net_worked_minutes')->default(0);
                $table->unsignedInteger('regular_minutes')->default(0);
                $table->unsignedInteger('overtime_minutes')->default(0);
                $table->unsignedInteger('approved_overtime_minutes')->default(0);
                $table->unsignedInteger('late_minutes')->default(0);
                $table->unsignedInteger('early_departure_minutes')->default(0);
                $table->unsignedInteger('undertime_minutes')->default(0);
                $table->string('status', 40)->default('absent')->index();
                $table->boolean('is_overnight')->default(false);
                $table->boolean('is_split')->default(false);
                $table->boolean('is_flexible')->default(false);
                $table->boolean('is_adjusted')->default(false);
                $table->boolean('is_approved')->default(false);
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('work_calendar_id')->references('id')->on('work_calendars')->nullOnDelete();
                $table->foreign('shift_definition_id')->references('id')->on('shift_definitions')->nullOnDelete();
                $table->foreign('roster_assignment_id')->references('id')->on('roster_assignments')->nullOnDelete();
                $table->unique(['tenant_id', 'employee_id', 'session_date']);
            });
        }

        // 18. Attendance Session Breaks
        if (! Schema::hasTable('attendance_session_breaks')) {
            Schema::create('attendance_session_breaks', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('attendance_session_id')->index();
                $table->uuid('shift_break_id')->nullable()->index();
                $table->timestamp('break_start_time');
                $table->timestamp('break_end_time')->nullable();
                $table->unsignedInteger('duration_minutes')->default(0);
                $table->boolean('is_paid')->default(false);
                $table->boolean('is_exceeded')->default(false);
                $table->unsignedInteger('excess_minutes')->default(0);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->cascadeOnDelete();
                $table->foreign('shift_break_id')->references('id')->on('shift_breaks')->nullOnDelete();
            });
        }

        // 19. Attendance Exceptions
        if (! Schema::hasTable('attendance_exceptions')) {
            Schema::create('attendance_exceptions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('attendance_session_id')->nullable()->index();
                $table->date('exception_date')->index();
                $table->string('exception_type', 40)->index();
                $table->string('severity', 20)->default('warning');
                $table->string('status', 30)->default('open')->index();
                $table->text('explanation')->nullable();
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('resolution_type', 40)->nullable();
                $table->text('resolution_notes')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->cascadeOnDelete();
            });
        }

        // 20. Attendance Adjustments
        if (! Schema::hasTable('attendance_adjustments')) {
            Schema::create('attendance_adjustments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('attendance_session_id')->nullable()->index();
                $table->date('adjustment_date')->index();
                $table->string('adjustment_type', 40);
                $table->json('original_values')->nullable();
                $table->json('requested_values');
                $table->text('reason');
                $table->uuid('supporting_document_id')->nullable()->index();
                $table->string('status', 30)->default('pending')->index();
                $table->uuid('workflow_instance_id')->nullable()->index();
                $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->cascadeOnDelete();
            });
        }

        // 21. Extend or Create Attendance Policies
        if (Schema::hasTable('attendance_policies')) {
            Schema::table('attendance_policies', function (Blueprint $table): void {
                if (! Schema::hasColumn('attendance_policies', 'code')) {
                    $table->string('code', 60)->nullable()->after('tenant_id');
                }
                if (! Schema::hasColumn('attendance_policies', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }
                if (! Schema::hasColumn('attendance_policies', 'grace_period_minutes')) {
                    $table->unsignedInteger('grace_period_minutes')->default(15)->after('description');
                }
                if (! Schema::hasColumn('attendance_policies', 'late_threshold_minutes')) {
                    $table->unsignedInteger('late_threshold_minutes')->default(30)->after('grace_period_minutes');
                }
                if (! Schema::hasColumn('attendance_policies', 'early_departure_threshold_minutes')) {
                    $table->unsignedInteger('early_departure_threshold_minutes')->default(15)->after('late_threshold_minutes');
                }
                if (! Schema::hasColumn('attendance_policies', 'half_day_late_threshold_minutes')) {
                    $table->unsignedInteger('half_day_late_threshold_minutes')->default(120)->after('early_departure_threshold_minutes');
                }
                if (! Schema::hasColumn('attendance_policies', 'minimum_working_hours_minutes')) {
                    $table->unsignedInteger('minimum_working_hours_minutes')->default(240)->after('half_day_late_threshold_minutes');
                }
                if (! Schema::hasColumn('attendance_policies', 'overtime_minimum_minutes')) {
                    $table->unsignedInteger('overtime_minimum_minutes')->default(30)->after('minimum_working_hours_minutes');
                }
                if (! Schema::hasColumn('attendance_policies', 'overtime_approval_required')) {
                    $table->boolean('overtime_approval_required')->default(true)->after('overtime_minimum_minutes');
                }
                if (! Schema::hasColumn('attendance_policies', 'rounding_interval_minutes')) {
                    $table->unsignedInteger('rounding_interval_minutes')->default(1)->after('overtime_approval_required');
                }
                if (! Schema::hasColumn('attendance_policies', 'rounding_method')) {
                    $table->string('rounding_method', 20)->default('nearest')->after('rounding_interval_minutes');
                }
                if (! Schema::hasColumn('attendance_policies', 'auto_deduct_breaks')) {
                    $table->boolean('auto_deduct_breaks')->default(false)->after('rounding_method');
                }
                if (! Schema::hasColumn('attendance_policies', 'missing_punch_policy')) {
                    $table->string('missing_punch_policy', 40)->default('flag_exception')->after('auto_deduct_breaks');
                }
                if (! Schema::hasColumn('attendance_policies', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('attendance_policies', 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('attendance_policies', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        // 22. Attendance Policy Assignments
        if (! Schema::hasTable('attendance_policy_assignments')) {
            Schema::create('attendance_policy_assignments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('attendance_policy_id')->index();
                $table->string('scope_type', 30)->default('global');
                $table->uuid('scope_id')->nullable()->index();
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('attendance_policy_id')->references('id')->on('attendance_policies')->cascadeOnDelete();
            });
        }

        // 23. Attendance Periods (Locking / Cutoff Control)
        if (! Schema::hasTable('attendance_periods')) {
            Schema::create('attendance_periods', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('company_id')->nullable()->index();
                $table->string('period_name', 100);
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status', 30)->default('open')->index();
                $table->timestamp('locked_at')->nullable();
                $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reopened_at')->nullable();
                $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('reopen_reason')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
                $table->unique(['tenant_id', 'company_id', 'start_date', 'end_date'], 'att_period_tenant_company_dates_unique');
            });
        }

        // 24. Timesheets
        if (! Schema::hasTable('timesheets')) {
            Schema::create('timesheets', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('attendance_period_id')->nullable()->index();
                $table->string('period_type', 30)->default('monthly');
                $table->date('start_date')->index();
                $table->date('end_date')->index();
                $table->unsignedInteger('total_scheduled_minutes')->default(0);
                $table->unsignedInteger('total_worked_minutes')->default(0);
                $table->unsignedInteger('total_regular_minutes')->default(0);
                $table->unsignedInteger('total_overtime_minutes')->default(0);
                $table->unsignedInteger('total_approved_overtime_minutes')->default(0);
                $table->unsignedInteger('total_late_minutes')->default(0);
                $table->unsignedInteger('total_early_departure_minutes')->default(0);
                $table->unsignedInteger('total_absence_minutes')->default(0);
                $table->unsignedInteger('total_paid_leave_minutes')->default(0);
                $table->unsignedInteger('total_unpaid_leave_minutes')->default(0);
                $table->unsignedInteger('total_holiday_minutes')->default(0);
                $table->string('status', 30)->default('draft')->index();
                $table->timestamp('submitted_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('exported_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('attendance_period_id')->references('id')->on('attendance_periods')->nullOnDelete();
                $table->unique(['tenant_id', 'employee_id', 'start_date', 'end_date'], 'timesheet_tenant_emp_dates_unique');
            });
        }

        // 25. Timesheet Entries
        if (! Schema::hasTable('timesheet_entries')) {
            Schema::create('timesheet_entries', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('timesheet_id')->index();
                $table->uuid('attendance_session_id')->nullable()->index();
                $table->date('entry_date')->index();
                $table->string('shift_code', 60)->nullable();
                $table->unsignedInteger('scheduled_minutes')->default(0);
                $table->unsignedInteger('worked_minutes')->default(0);
                $table->unsignedInteger('regular_minutes')->default(0);
                $table->unsignedInteger('overtime_minutes')->default(0);
                $table->unsignedInteger('approved_overtime_minutes')->default(0);
                $table->unsignedInteger('late_minutes')->default(0);
                $table->unsignedInteger('early_minutes')->default(0);
                $table->unsignedInteger('absence_minutes')->default(0);
                $table->boolean('is_holiday')->default(false);
                $table->boolean('is_weekend')->default(false);
                $table->boolean('is_leave')->default(false);
                $table->string('status', 30)->default('present');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('timesheet_id')->references('id')->on('timesheets')->cascadeOnDelete();
                $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->nullOnDelete();
                $table->unique(['timesheet_id', 'entry_date']);
            });
        }

        // 26. Overtime Requests
        if (! Schema::hasTable('overtime_requests')) {
            Schema::create('overtime_requests', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('employee_id')->index();
                $table->uuid('attendance_session_id')->nullable()->index();
                $table->date('overtime_date')->index();
                $table->unsignedInteger('calculated_overtime_minutes')->default(0);
                $table->unsignedInteger('requested_overtime_minutes')->default(0);
                $table->unsignedInteger('approved_overtime_minutes')->default(0);
                $table->string('overtime_type', 30)->default('regular_ot');
                $table->text('reason')->nullable();
                $table->string('status', 30)->default('pending')->index();
                $table->uuid('workflow_instance_id')->nullable()->index();
                $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
        Schema::dropIfExists('timesheet_entries');
        Schema::dropIfExists('timesheets');
        Schema::dropIfExists('attendance_periods');
        Schema::dropIfExists('attendance_policy_assignments');
        Schema::dropIfExists('attendance_adjustments');
        Schema::dropIfExists('attendance_exceptions');
        Schema::dropIfExists('attendance_session_breaks');
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('attendance_events');
        Schema::dropIfExists('attendance_raw_events');
        Schema::dropIfExists('attendance_sync_runs');
        Schema::dropIfExists('attendance_devices');
        Schema::dropIfExists('roster_conflicts');
        Schema::dropIfExists('roster_assignments');
        Schema::dropIfExists('roster_periods');
        Schema::dropIfExists('shift_pattern_days');
        Schema::dropIfExists('shift_patterns');
        Schema::dropIfExists('shift_breaks');
        Schema::dropIfExists('shift_definitions');
        Schema::dropIfExists('work_calendar_exceptions');
        Schema::dropIfExists('work_calendar_days');
        Schema::dropIfExists('work_calendars');
    }
};
