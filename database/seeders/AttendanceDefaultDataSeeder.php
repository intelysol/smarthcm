<?php

namespace Database\Seeders;

use App\Domains\Attendance\Models\AttendancePolicy;
use App\Domains\Attendance\Models\HolidayCalendar;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Models\ShiftPattern;
use App\Domains\Attendance\Models\WorkCalendar;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class AttendanceDefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::query()->get();
        if ($tenants->isEmpty()) {
            return;
        }

        foreach ($tenants as $tenant) {
            $this->seedTenantData($tenant->id);
        }
    }

    public function seedTenantData(string $tenantId): void
    {
        $company = \App\Domains\Organization\Models\Company::query()->where('tenant_id', $tenantId)->first();
        if (! $company) {
            $company = \App\Domains\Organization\Models\Company::query()->create([
                'tenant_id' => $tenantId,
                'name' => 'Default Company',
                'legal_name' => 'Default Legal Entity',
                'company_code' => 'DEF-' . substr($tenantId, 0, 4),
            ]);
        }

        // 1. Work Calendar (Standard 5-day week)
        $workCalendar = WorkCalendar::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'STD-5DAY'],
            [
                'company_id' => $company->id,
                'name' => 'Standard 5-Day Work Calendar',
                'timezone' => 'UTC',
                'description' => 'Standard Monday to Friday working week with Saturday and Sunday as weekly rest days.',
                'is_default' => true,
                'status' => 'active',
            ]
        );

        for ($day = 1; $day <= 7; $day++) {
            $isWorking = in_array($day, [1, 2, 3, 4, 5], true);
            $workCalendar->days()->firstOrCreate(
                ['day_of_week' => $day],
                [
                    'tenant_id' => $tenantId,
                    'is_working_day' => $isWorking,
                    'standard_hours_minutes' => $isWorking ? 480 : 0,
                    'notes' => $isWorking ? 'Standard Working Day' : 'Weekly Rest Day',
                ]
            );
        }

        // 2. Holiday Calendar
        $holidayCalendar = HolidayCalendar::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'HOL-2026'],
            [
                'company_id' => $company->id,
                'name' => 'Corporate Holidays 2026',
                'year' => 2026,
                'description' => 'Annual corporate and public holidays.',
                'is_default' => true,
                'status' => 'active',
            ]
        );

        $holidays = [
            ['name' => "New Year's Day", 'date' => '2026-01-01', 'type' => 'public'],
            ['name' => 'Labor Day', 'date' => '2026-05-01', 'type' => 'public'],
            ['name' => 'Independence Day', 'date' => '2026-08-14', 'type' => 'national'],
            ['name' => 'Annual Company Foundation Day', 'date' => '2026-10-15', 'type' => 'company'],
        ];

        foreach ($holidays as $h) {
            $holidayCalendar->holidays()->firstOrCreate(
                ['holiday_date' => $h['date'], 'name' => $h['name']],
                [
                    'tenant_id' => $tenantId,
                    'is_recurring' => false,
                    'is_optional' => false,
                    'holiday_type' => $h['type'],
                ]
            );
        }

        // 3. Shift Definitions
        // Morning Shift
        $morningShift = ShiftDefinition::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'shift_code' => 'SHIFT-MORN'],
            [
                'name' => 'Standard Morning Shift',
                'description' => '08:00 AM to 05:00 PM with 1-hour unpaid lunch break.',
                'shift_type' => 'normal',
                'start_time' => '08:00',
                'end_time' => '17:00',
                'timezone' => 'UTC',
                'duration_minutes' => 480,
                'grace_period_minutes' => 15,
                'late_threshold_minutes' => 30,
                'early_departure_threshold_minutes' => 15,
                'overtime_eligible' => true,
                'min_overtime_threshold_minutes' => 30,
                'rounding_rule_minutes' => 1,
                'is_night_shift' => false,
                'is_flexible' => false,
                'is_split' => false,
                'is_active' => true,
                'color_code' => '#4f46e5',
            ]
        );

        $morningShift->breaks()->firstOrCreate(
            ['break_name' => 'Lunch Break'],
            [
                'tenant_id' => $tenantId,
                'break_type' => 'unpaid',
                'start_time' => '12:30',
                'end_time' => '13:30',
                'duration_minutes' => 60,
                'is_automatic_deduction' => false,
                'minimum_break_minutes' => 30,
                'maximum_break_minutes' => 90,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // Evening Shift
        ShiftDefinition::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'shift_code' => 'SHIFT-EVE'],
            [
                'name' => 'Evening Shift',
                'description' => '04:00 PM to 12:00 AM midnight.',
                'shift_type' => 'normal',
                'start_time' => '16:00',
                'end_time' => '00:00',
                'timezone' => 'UTC',
                'duration_minutes' => 480,
                'grace_period_minutes' => 15,
                'late_threshold_minutes' => 30,
                'early_departure_threshold_minutes' => 15,
                'overtime_eligible' => true,
                'min_overtime_threshold_minutes' => 30,
                'is_night_shift' => false,
                'is_flexible' => false,
                'is_split' => false,
                'is_active' => true,
                'color_code' => '#06b6d4',
            ]
        );

        // Overnight Shift
        ShiftDefinition::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'shift_code' => 'SHIFT-NIGHT'],
            [
                'name' => 'Overnight Shift',
                'description' => '10:00 PM to 06:00 AM next day.',
                'shift_type' => 'overnight',
                'start_time' => '22:00',
                'end_time' => '06:00',
                'timezone' => 'UTC',
                'duration_minutes' => 480,
                'grace_period_minutes' => 15,
                'late_threshold_minutes' => 30,
                'early_departure_threshold_minutes' => 15,
                'overtime_eligible' => true,
                'min_overtime_threshold_minutes' => 30,
                'is_night_shift' => true,
                'is_flexible' => false,
                'is_split' => false,
                'is_active' => true,
                'color_code' => '#8b5cf6',
            ]
        );

        // Flexible Shift
        ShiftDefinition::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'shift_code' => 'SHIFT-FLEX'],
            [
                'name' => 'Flexible Hours Shift',
                'description' => 'Core presence 10:00 to 15:00, 8 required daily hours.',
                'shift_type' => 'flexible',
                'start_time' => '07:00',
                'end_time' => '19:00',
                'timezone' => 'UTC',
                'duration_minutes' => 480,
                'core_start_time' => '10:00',
                'core_end_time' => '15:00',
                'required_daily_minutes' => 480,
                'grace_period_minutes' => 0,
                'late_threshold_minutes' => 0,
                'early_departure_threshold_minutes' => 0,
                'overtime_eligible' => true,
                'min_overtime_threshold_minutes' => 30,
                'is_night_shift' => false,
                'is_flexible' => true,
                'is_split' => false,
                'is_active' => true,
                'color_code' => '#10b981',
            ]
        );

        // Split Shift
        ShiftDefinition::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'shift_code' => 'SHIFT-SPLIT'],
            [
                'name' => 'Split Shift (Morning + Evening)',
                'description' => '08:00 to 12:00 and 16:00 to 20:00.',
                'shift_type' => 'split',
                'start_time' => '08:00',
                'end_time' => '12:00',
                'split_second_start' => '16:00',
                'split_second_end' => '20:00',
                'timezone' => 'UTC',
                'duration_minutes' => 480,
                'grace_period_minutes' => 15,
                'late_threshold_minutes' => 30,
                'early_departure_threshold_minutes' => 15,
                'overtime_eligible' => true,
                'min_overtime_threshold_minutes' => 30,
                'is_night_shift' => false,
                'is_flexible' => false,
                'is_split' => true,
                'is_active' => true,
                'color_code' => '#f59e0b',
            ]
        );

        // 4. Shift Pattern (5 Days Morning / 2 Days Off)
        $pattern = ShiftPattern::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'PAT-5x2-MORN'],
            [
                'name' => '5x2 Morning Shift Pattern',
                'cycle_length_days' => 7,
                'rotation_type' => 'fixed',
                'description' => 'Mon-Fri Morning Shift, Sat-Sun Off.',
                'is_active' => true,
            ]
        );

        for ($seq = 1; $seq <= 7; $seq++) {
            $isOff = in_array($seq, [6, 7], true);
            $pattern->patternDays()->firstOrCreate(
                ['day_sequence' => $seq],
                [
                    'tenant_id' => $tenantId,
                    'shift_definition_id' => $isOff ? null : $morningShift->id,
                    'is_off_day' => $isOff,
                    'notes' => $isOff ? 'Off Day' : 'Morning Shift',
                ]
            );
        }

        // 5. Default Attendance Policy
        $policy = AttendancePolicy::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'POL-GLOBAL-DEFAULT'],
            [
                'name' => 'Standard Enterprise Attendance Policy',
                'description' => 'Default corporate attendance rules, grace thresholds, break tolerances, and overtime approvals.',
                'grace_period_minutes' => 15,
                'late_threshold_minutes' => 30,
                'early_departure_threshold_minutes' => 15,
                'half_day_late_threshold_minutes' => 120,
                'minimum_working_hours_minutes' => 240,
                'overtime_minimum_minutes' => 30,
                'overtime_approval_required' => true,
                'rounding_interval_minutes' => 1,
                'rounding_method' => 'nearest',
                'auto_deduct_breaks' => false,
                'missing_punch_policy' => 'flag_exception',
                'rules' => [],
                'is_active' => true,
            ]
        );

        $policy->assignments()->firstOrCreate(
            ['scope_type' => 'global', 'scope_id' => null],
            [
                'tenant_id' => $tenantId,
                'effective_from' => '2026-01-01',
                'effective_to' => null,
            ]
        );
    }
}
