<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class AttendancePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::query()->firstOrCreate(
            ['name' => 'attendance'],
            [
                'label' => 'Time, Attendance & Workforce Scheduling',
                'description' => 'Permissions for managing calendars, shifts, rosters, attendance devices, sessions, exceptions, adjustments, timesheets, and overtime.',
            ]
        );

        $permissions = [
            'hcm.attendance.view' => 'View Attendance Sessions and Records',
            'hcm.attendance.manage' => 'Manage General Attendance Configurations',
            'hcm.attendance.policy.manage' => 'Manage Attendance Policies & Assignments',
            'hcm.attendance.calendar.view' => 'View Work & Holiday Calendars',
            'hcm.attendance.calendar.manage' => 'Manage Work & Holiday Calendars',
            'hcm.attendance.shift.view' => 'View Shift Definitions & Patterns',
            'hcm.attendance.shift.manage' => 'Manage Shift Definitions & Patterns',
            'hcm.attendance.roster.view' => 'View Rosters and Schedules',
            'hcm.attendance.roster.manage' => 'Manage Roster Assignments',
            'hcm.attendance.roster.publish' => 'Publish and Lock Rosters',
            'hcm.attendance.event.view' => 'View Normalized Attendance Events',
            'hcm.attendance.raw_event.view' => 'View Raw Attendance Device Logs',
            'hcm.attendance.adjust' => 'Request Attendance Adjustments / Regularizations',
            'hcm.attendance.approve' => 'Approve Attendance Adjustments',
            'hcm.attendance.timesheet.view' => 'View Employee Timesheets',
            'hcm.attendance.timesheet.approve' => 'Approve Employee Timesheets',
            'hcm.attendance.overtime.view' => 'View Overtime Records & Requests',
            'hcm.attendance.overtime.approve' => 'Approve Overtime Requests',
            'hcm.attendance.device.view' => 'View Attendance Devices & Sync Logs',
            'hcm.attendance.device.manage' => 'Manage Attendance Devices',
            'hcm.attendance.device.sync' => 'Trigger Device Synchronization',
            'hcm.attendance.report.view' => 'View Attendance Reports & Analytics',
            'hcm.attendance.report.export' => 'Export Attendance Reports',
            'hcm.attendance.payroll.export' => 'Export Approved Time for Payroll',
            'hcm.attendance.period.lock' => 'Lock Attendance Periods',
            'hcm.attendance.period.reopen' => 'Reopen Locked Attendance Periods',
        ];

        foreach ($permissions as $name => $label) {
            Permission::query()->firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'label' => $label,
                    'description' => $label,
                ]
            );
        }
    }
}
