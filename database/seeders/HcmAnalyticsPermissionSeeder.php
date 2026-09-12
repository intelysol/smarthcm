<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HcmAnalyticsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['code' => 'hcm.analytics.view', 'name' => 'View HCM Analytics', 'module' => 'analytics', 'description' => 'View analytics home and standard dashboards'],
            ['code' => 'hcm.analytics.manage', 'name' => 'Manage HCM Analytics', 'module' => 'analytics', 'description' => 'Configure metrics, targets, alerts and data quality rules'],
            ['code' => 'hcm.dashboard.view', 'name' => 'View Dashboards', 'module' => 'analytics', 'description' => 'Access executive and operational dashboards'],
            ['code' => 'hcm.dashboard.manage', 'name' => 'Manage Dashboards', 'module' => 'analytics', 'description' => 'Create and customize dashboard layouts and widgets'],
            ['code' => 'hcm.report.view', 'name' => 'View Reports', 'module' => 'analytics', 'description' => 'Browse and run operational reports'],
            ['code' => 'hcm.report.manage', 'name' => 'Manage Reports', 'module' => 'analytics', 'description' => 'Build and edit custom report definitions'],
            ['code' => 'hcm.report.export', 'name' => 'Export Reports', 'module' => 'analytics', 'description' => 'Export analytics data to CSV, XLSX, and PDF'],
            ['code' => 'hcm.report.schedule', 'name' => 'Schedule Reports', 'module' => 'analytics', 'description' => 'Configure automated scheduled report delivery'],
            ['code' => 'hcm.analytics.payroll.view', 'name' => 'View Payroll Analytics', 'module' => 'analytics', 'description' => 'Access confidential payroll and compensation cost analytics'],
            ['code' => 'hcm.analytics.sensitive.view', 'name' => 'View Sensitive Analytics', 'module' => 'analytics', 'description' => 'Access sensitive demographic, medical benefit and talent analytics'],
            ['code' => 'hcm.analytics.er.aggregate.view', 'name' => 'View ER Aggregates', 'module' => 'analytics', 'description' => 'View high-level Employee Relations case trends and aging aggregates'],
            ['code' => 'hcm.analytics.workforce.view', 'name' => 'View Workforce Analytics', 'module' => 'analytics', 'description' => 'View headcount, movement, and turnover statistics'],
            ['code' => 'hcm.analytics.attendance.view', 'name' => 'View Attendance Analytics', 'module' => 'analytics', 'description' => 'View punctuality, absenteeism, and overtime statistics'],
            ['code' => 'hcm.analytics.leave.view', 'name' => 'View Leave Analytics', 'module' => 'analytics', 'description' => 'View leave utilization and absence trends'],
            ['code' => 'hcm.analytics.recruitment.view', 'name' => 'View Recruitment Analytics', 'module' => 'analytics', 'description' => 'View recruitment pipeline, funnel, and time-to-hire metrics'],
            ['code' => 'hcm.analytics.performance.view', 'name' => 'View Performance Analytics', 'module' => 'analytics', 'description' => 'View performance distribution and goal achievement trends'],
            ['code' => 'hcm.analytics.talent.view', 'name' => 'View Talent Analytics', 'module' => 'analytics', 'description' => 'View succession bench strength and skill gap analytics'],
            ['code' => 'hcm.analytics.engagement.view', 'name' => 'View Engagement Analytics', 'module' => 'analytics', 'description' => 'View anonymized engagement scores and eNPS distributions'],
        ];

        foreach ($permissions as $perm) {
            $exists = DB::table('permissions')->where('code', $perm['code'])->first();
            if (! $exists) {
                DB::table('permissions')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => $perm['code'],
                    'name' => $perm['name'],
                    'module' => $perm['module'],
                    'description' => $perm['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
