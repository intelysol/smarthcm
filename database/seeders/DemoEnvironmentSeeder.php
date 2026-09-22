<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Position;
use App\Domains\Platform\Models\Role;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DemoEnvironmentSeeder extends Seeder
{
    /**
     * Run the demo environment seeder.
     */
    public function run(): void
    {
        // 1. Production Safety Guard
        if (app()->environment('production') && !env('SEED_DEMO_USERS', false)) {
            throw new RuntimeException("CRITICAL SAFETY BLOCK: Demo seeding is strictly prohibited in production without SEED_DEMO_USERS=true.");
        }

        $demoPassword = env('DEMO_USER_PASSWORD', 'Demo1234!@#$');
        $hashedPassword = Hash::make($demoPassword);

        // 2. Demo Tenant
        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => 'demo-organization'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Smart HCM Demo Organization',
                'timezone' => 'UTC',
                'currency' => 'USD',
                'status' => 'active',
            ]
        );
        $tenantId = (string) $tenant->id;

        // 3. Demo Company
        $company = Company::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'name' => 'Smart HCM Global Technologies Inc.'],
            [
                'id' => (string) Str::uuid(),
                'legal_name' => 'Smart HCM Enterprise Technologies Global Corp.',
                'industry' => 'Enterprise Software & Cloud',
                'company_size' => '500-1000',
                'timezone' => 'UTC',
                'currency' => 'USD',
            ]
        );

        // 3.1 Demo Business Unit
        $businessUnit = BusinessUnit::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'BU-GLOBAL'],
            [
                'id' => (string) Str::uuid(),
                'company_id' => $company->id,
                'name' => 'Global Technology & Operations',
                'description' => 'Global enterprise business unit',
                'status' => 'active',
            ]
        );
        $buId = (string) $businessUnit->id;

        // 4. Organizational Departments
        $deptHr = Department::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'department_code' => 'DEP-HR'],
            [
                'business_unit_id' => $buId,
                'department_name' => 'People Operations & Human Resources',
                'description' => 'Employee lifecycle, talent acquisition, total rewards, and compliance',
                'status' => 'active',
            ]
        );

        $deptEng = Department::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'department_code' => 'DEP-ENG'],
            [
                'business_unit_id' => $buId,
                'department_name' => 'Core Platform Engineering',
                'description' => 'Software engineering, architecture, cloud infrastructure, and DevOps',
                'status' => 'active',
            ]
        );

        $deptFin = Department::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'department_code' => 'DEP-FIN'],
            [
                'business_unit_id' => $buId,
                'department_name' => 'Finance & Accounting',
                'description' => 'Payroll execution, expense audits, budgeting, and commercial billing',
                'status' => 'active',
            ]
        );

        $deptOps = Department::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'department_code' => 'DEP-OPS'],
            [
                'business_unit_id' => $buId,
                'department_name' => 'Operations & Site Reliability',
                'description' => 'System operations, incident management, reliability engineering, and monitoring',
                'status' => 'active',
            ]
        );

        $deptExec = Department::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'department_code' => 'DEP-EXEC'],
            [
                'business_unit_id' => $buId,
                'department_name' => 'Executive & Strategic Administration',
                'description' => 'Executive leadership, corporate governance, and workforce intelligence',
                'status' => 'active',
            ]
        );

        // 5. Positions (Job Architecture)
        $posHrDir = Position::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'POS-HR-001'],
            [
                'company_id' => $company->id,
                'department_id' => $deptHr->id,
                'title' => 'HR Director & People Leader',
                'headcount' => 1,
                'filled_headcount' => 1,
                'status' => 'active',
            ]
        );

        $posEngMgr = Position::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'POS-ENG-MGR'],
            [
                'company_id' => $company->id,
                'department_id' => $deptEng->id,
                'title' => 'Engineering Manager',
                'headcount' => 2,
                'filled_headcount' => 1,
                'status' => 'active',
            ]
        );

        $posSeniorDev = Position::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'POS-ENG-001'],
            [
                'company_id' => $company->id,
                'department_id' => $deptEng->id,
                'title' => 'Senior Fullstack Software Engineer',
                'headcount' => 5,
                'filled_headcount' => 1,
                'status' => 'active',
            ]
        );

        // 6. Permission Groups & Standard Permissions
        $groupPlatform = PermissionGroup::query()->firstOrCreate(['name' => 'platform'], ['label' => 'Platform Control Plane']);
        $groupTenant = PermissionGroup::query()->firstOrCreate(['name' => 'tenant'], ['label' => 'Tenant Administration']);
        $groupHr = PermissionGroup::query()->firstOrCreate(['name' => 'hr'], ['label' => 'HR Operations']);
        $groupManager = PermissionGroup::query()->firstOrCreate(['name' => 'manager'], ['label' => 'Manager Workbench']);
        $groupEmployee = PermissionGroup::query()->firstOrCreate(['name' => 'employee'], ['label' => 'Employee Self-Service']);

        $permissionDefs = [
            // Platform Super Admin Permissions
            ['group' => $groupPlatform, 'name' => 'platform.view', 'label' => 'View platform control center'],
            ['group' => $groupPlatform, 'name' => 'platform.manage', 'label' => 'Manage platform configuration'],
            ['group' => $groupPlatform, 'name' => 'platform.tenants.view', 'label' => 'View all tenants'],
            ['group' => $groupPlatform, 'name' => 'platform.tenants.manage', 'label' => 'Manage and provision tenants'],
            ['group' => $groupPlatform, 'name' => 'platform.users.view', 'label' => 'View platform users'],
            ['group' => $groupPlatform, 'name' => 'platform.users.manage', 'label' => 'Manage platform users'],
            ['group' => $groupPlatform, 'name' => 'platform.roles.view', 'label' => 'View platform roles'],
            ['group' => $groupPlatform, 'name' => 'platform.roles.manage', 'label' => 'Manage platform roles'],
            ['group' => $groupPlatform, 'name' => 'platform.security.view', 'label' => 'View security & audit telemetry'],
            ['group' => $groupPlatform, 'name' => 'platform.operations.view', 'label' => 'View system operations & health'],

            // Tenant Admin Permissions
            ['group' => $groupTenant, 'name' => 'tenant.view', 'label' => 'View tenant overview'],
            ['group' => $groupTenant, 'name' => 'tenant.settings', 'label' => 'Manage tenant settings'],
            ['group' => $groupTenant, 'name' => 'tenant.users.view', 'label' => 'View tenant users'],
            ['group' => $groupTenant, 'name' => 'tenant.users.manage', 'label' => 'Create and modify tenant users'],
            ['group' => $groupTenant, 'name' => 'tenant.roles.assign', 'label' => 'Assign roles to tenant users'],
            ['group' => $groupTenant, 'name' => 'tenant.departments.manage', 'label' => 'Manage organizational departments'],
            ['group' => $groupTenant, 'name' => 'tenant.compliance.view', 'label' => 'View compliance governance'],

            // HR Admin Permissions
            ['group' => $groupHr, 'name' => 'hr.dashboard.view', 'label' => 'View HR command center'],
            ['group' => $groupHr, 'name' => 'hr.employees.view', 'label' => 'View all employees'],
            ['group' => $groupHr, 'name' => 'hr.employees.manage', 'label' => 'Manage employee master records'],
            ['group' => $groupHr, 'name' => 'hr.attendance.manage', 'label' => 'Manage employee attendance and shifts'],
            ['group' => $groupHr, 'name' => 'hr.leave.manage', 'label' => 'Manage employee leaves and quotas'],
            ['group' => $groupHr, 'name' => 'hr.documents.manage', 'label' => 'Manage corporate document templates'],
            ['group' => $groupHr, 'name' => 'hr.reports.view', 'label' => 'View HR analytics and reports'],

            // Manager Permissions
            ['group' => $groupManager, 'name' => 'manager.workbench.view', 'label' => 'View manager team workbench'],
            ['group' => $groupManager, 'name' => 'manager.team.view', 'label' => 'View direct reports team roster'],
            ['group' => $groupManager, 'name' => 'manager.approvals.manage', 'label' => 'Approve or reject team requests'],
            ['group' => $groupManager, 'name' => 'manager.attendance.view', 'label' => 'View team attendance and timesheets'],
            ['group' => $groupManager, 'name' => 'manager.performance.view', 'label' => 'Conduct team performance reviews'],

            // Employee Permissions
            ['group' => $groupEmployee, 'name' => 'employee.portal.access', 'label' => 'Access employee self-service portal'],
            ['group' => $groupEmployee, 'name' => 'employee.profile.view', 'label' => 'View personal profile'],
            ['group' => $groupEmployee, 'name' => 'employee.punch.record', 'label' => 'Record attendance punch'],
            ['group' => $groupEmployee, 'name' => 'employee.leave.submit', 'label' => 'Submit leave requests'],
            ['group' => $groupEmployee, 'name' => 'employee.documents.view', 'label' => 'View personal documents'],
            ['group' => $groupEmployee, 'name' => 'employee.privacy.manage', 'label' => 'Exercise privacy and data rights'],
        ];

        $permissionModels = [];
        foreach ($permissionDefs as $def) {
            $perm = Permission::query()->firstOrCreate(
                ['name' => $def['name']],
                [
                    'permission_group_id' => $def['group']->id,
                    'label' => $def['label'],
                    'status' => 'active',
                ]
            );
            $permissionModels[$def['name']] = $perm->id;
        }

        // 7. Standard Roles & Permission Mappings
        $roleSuperAdmin = Role::query()->firstOrCreate(
            ['name' => 'platform_admin'],
            [
                'uuid' => (string) Str::uuid(),
                'label' => 'Platform Super Admin',
                'code' => 'ROLE_SUPER_ADMIN',
                'description' => 'Global SaaS platform operator with full system and cross-tenant governance privileges',
                'type' => 'system',
                'status' => 'active',
                'priority' => 100,
                'is_system' => true,
            ]
        );
        $roleSuperAdmin->permissions()->sync(array_values($permissionModels));

        $roleTenantAdmin = Role::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'name' => 'tenant_admin'],
            [
                'uuid' => (string) Str::uuid(),
                'label' => 'Tenant Administrator',
                'code' => 'ROLE_TENANT_ADMIN',
                'description' => 'Organization administrator with authority over company settings, users, and organizational structure',
                'type' => 'tenant',
                'status' => 'active',
                'priority' => 80,
                'is_system' => false,
            ]
        );
        $tenantAdminPermNames = [
            'tenant.view', 'tenant.settings', 'tenant.users.view', 'tenant.users.manage',
            'tenant.roles.assign', 'tenant.departments.manage', 'tenant.compliance.view',
            'hr.employees.view', 'hr.reports.view', 'employee.portal.access',
        ];
        $roleTenantAdmin->permissions()->sync(array_intersect_key($permissionModels, array_flip($tenantAdminPermNames)));

        $roleHrAdmin = Role::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'name' => 'hr_admin'],
            [
                'uuid' => (string) Str::uuid(),
                'label' => 'HR Administrator',
                'code' => 'ROLE_HR_ADMIN',
                'description' => 'Human resources leader managing employee lifecycle, time & attendance, leaves, and documents',
                'type' => 'tenant',
                'status' => 'active',
                'priority' => 60,
                'is_system' => false,
            ]
        );
        $hrAdminPermNames = [
            'hr.dashboard.view', 'hr.employees.view', 'hr.employees.manage',
            'hr.attendance.manage', 'hr.leave.manage', 'hr.documents.manage', 'hr.reports.view',
            'employee.portal.access', 'employee.profile.view', 'employee.punch.record',
        ];
        $roleHrAdmin->permissions()->sync(array_intersect_key($permissionModels, array_flip($hrAdminPermNames)));

        $roleManager = Role::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'name' => 'manager'],
            [
                'uuid' => (string) Str::uuid(),
                'label' => 'People Manager',
                'code' => 'ROLE_MANAGER',
                'description' => 'Direct team lead with approval authorities, attendance oversight, and performance reviews',
                'type' => 'tenant',
                'status' => 'active',
                'priority' => 40,
                'is_system' => false,
            ]
        );
        $managerPermNames = [
            'manager.workbench.view', 'manager.team.view', 'manager.approvals.manage',
            'manager.attendance.view', 'manager.performance.view',
            'employee.portal.access', 'employee.profile.view', 'employee.punch.record',
            'employee.leave.submit', 'employee.documents.view', 'employee.privacy.manage',
        ];
        $roleManager->permissions()->sync(array_intersect_key($permissionModels, array_flip($managerPermNames)));

        $roleEmployee = Role::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'name' => 'employee'],
            [
                'uuid' => (string) Str::uuid(),
                'label' => 'Employee Self-Service',
                'code' => 'ROLE_EMPLOYEE',
                'description' => 'Individual contributor with self-service access to work schedule, leaves, profile, and documents',
                'type' => 'tenant',
                'status' => 'active',
                'priority' => 20,
                'is_system' => false,
            ]
        );
        $employeePermNames = [
            'employee.portal.access', 'employee.profile.view', 'employee.punch.record',
            'employee.leave.submit', 'employee.documents.view', 'employee.privacy.manage',
        ];
        $roleEmployee->permissions()->sync(array_intersect_key($permissionModels, array_flip($employeePermNames)));

        // 8. Demo Users Creation & Role Synchronization

        // A. Platform Super Admin
        $userSuperAdmin = User::query()->updateOrCreate(
            ['email' => 'superadmin@example.test'],
            [
                'name' => 'Platform Super Admin',
                'username' => 'superadmin',
                'password' => $hashedPassword,
                'is_platform_admin' => true,
                'tenant_id' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $userSuperAdmin->roles()->syncWithoutDetaching([$roleSuperAdmin->id => ['assigned_by' => $userSuperAdmin->id]]);

        // B. Tenant Admin
        $userTenantAdmin = User::query()->updateOrCreate(
            ['email' => 'admin@example.test'],
            [
                'name' => 'Demo Tenant Admin',
                'username' => 'tenantadmin',
                'password' => $hashedPassword,
                'is_platform_admin' => false,
                'tenant_id' => $tenantId,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $userTenantAdmin->roles()->syncWithoutDetaching([$roleTenantAdmin->id => ['assigned_by' => $userSuperAdmin->id]]);

        // C. HR Administrator
        $userHr = User::query()->updateOrCreate(
            ['email' => 'hr@example.test'],
            [
                'name' => 'Sarah Jenkins (HR Admin)',
                'username' => 'sarah.hr',
                'password' => $hashedPassword,
                'is_platform_admin' => false,
                'tenant_id' => $tenantId,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $userHr->roles()->syncWithoutDetaching([$roleHrAdmin->id => ['assigned_by' => $userTenantAdmin->id]]);

        $empHr = Employee::query()->updateOrCreate(
            ['tenant_id' => $tenantId, 'official_email' => 'hr@example.test'],
            [
                'company_id' => $company->id,
                'user_id' => $userHr->id,
                'department_id' => $deptHr->id,
                'current_position_id' => $posHrDir->id,
                'employee_number' => 'EMP-HR-001',
                'employee_code' => 'EMP-HR-001',
                'first_name' => 'Sarah',
                'last_name' => 'Jenkins',
                'personal_email' => 'sarah.jenkins@personal.test',
                'employment_status' => 'active',
                'joining_date' => Carbon::now()->subYears(3)->toDateString(),
                'portal_access' => true,
            ]
        );

        // D. Manager (People Manager)
        $userMgr = User::query()->updateOrCreate(
            ['email' => 'manager@example.test'],
            [
                'name' => 'Marcus Vance (Engineering Manager)',
                'username' => 'marcus.mgr',
                'password' => $hashedPassword,
                'is_platform_admin' => false,
                'tenant_id' => $tenantId,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $userMgr->roles()->syncWithoutDetaching([$roleManager->id => ['assigned_by' => $userTenantAdmin->id]]);

        $empMgr = Employee::query()->updateOrCreate(
            ['tenant_id' => $tenantId, 'official_email' => 'manager@example.test'],
            [
                'company_id' => $company->id,
                'user_id' => $userMgr->id,
                'department_id' => $deptEng->id,
                'current_position_id' => $posEngMgr->id,
                'employee_number' => 'EMP-ENG-001',
                'employee_code' => 'EMP-ENG-001',
                'first_name' => 'Marcus',
                'last_name' => 'Vance',
                'personal_email' => 'marcus.vance@personal.test',
                'employment_status' => 'active',
                'joining_date' => Carbon::now()->subYears(2)->toDateString(),
                'portal_access' => true,
            ]
        );

        // E. Employee (Individual Contributor reporting to Marcus Vance)
        $userEmp = User::query()->updateOrCreate(
            ['email' => 'employee@example.test'],
            [
                'name' => 'Alex Chen (Senior Software Engineer)',
                'username' => 'alex.chen',
                'password' => $hashedPassword,
                'is_platform_admin' => false,
                'tenant_id' => $tenantId,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $userEmp->roles()->syncWithoutDetaching([$roleEmployee->id => ['assigned_by' => $userTenantAdmin->id]]);

        $empDev = Employee::query()->updateOrCreate(
            ['tenant_id' => $tenantId, 'official_email' => 'employee@example.test'],
            [
                'company_id' => $company->id,
                'user_id' => $userEmp->id,
                'department_id' => $deptEng->id,
                'current_position_id' => $posSeniorDev->id,
                'reporting_manager_id' => $empMgr->id, // Demonstrates Manager -> Employee hierarchy
                'current_manager_employee_id' => $empMgr->id,
                'employee_number' => 'EMP-ENG-002',
                'employee_code' => 'EMP-ENG-002',
                'first_name' => 'Alex',
                'last_name' => 'Chen',
                'personal_email' => 'alex.chen@personal.test',
                'employment_status' => 'active',
                'joining_date' => Carbon::now()->subYear()->toDateString(),
                'portal_access' => true,
            ]
        );

        // 9. Sample Attendance & Privacy Data for Real-Data Verification
        if (DB::getSchemaBuilder()->hasTable('privacy_processing_activities')) {
            DB::table('privacy_processing_activities')->updateOrInsert(
                ['tenant_id' => $tenantId, 'name' => 'Core Employee Records Processing'],
                [
                    'id' => (string) Str::uuid(),
                    'purpose' => 'Employment lifecycle administration, payroll, and benefits distribution',
                    'business_owner' => 'People Operations',
                    'data_categories' => json_encode(['Identity', 'Contact Details', 'Compensation', 'Bank Details']),
                    'legal_basis' => 'Contractual Obligation',
                    'processing_location' => 'US-East / Frankfurt',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
