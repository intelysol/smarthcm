<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HcmEnterpriseDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tenant Creation
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'apex-global'],
            [
                'name' => 'Apex Global Technologies',
                'timezone' => 'America/New_York',
                'currency' => 'USD',
            ]
        );
        $tenantId = (string) $tenant->id;

        // 2. Company & Organizational Hierarchy
        $companyId = (string) Str::uuid();
        $existingCompany = DB::table('companies')->where('tenant_id', $tenantId)->first();
        if ($existingCompany) {
            $companyId = $existingCompany->id;
        } else {
            DB::table('companies')->insert([
                'id' => $companyId,
                'tenant_id' => $tenantId,
                'name' => 'Apex Global Technologies Inc.',
                'legal_name' => 'Apex Global Technologies International Corp.',
                'timezone' => 'America/New_York',
                'currency' => 'USD',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $buId = (string) Str::uuid();
        $existingBu = DB::table('business_units')->where('tenant_id', $tenantId)->where('code', 'BU-ENG')->first();
        if ($existingBu) {
            $buId = $existingBu->id;
        } else {
            DB::table('business_units')->insert([
                'id' => $buId,
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'code' => 'BU-ENG',
                'name' => 'Enterprise Engineering & Technology',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $deptEngId = (string) Str::uuid();
        $existingDeptEng = DB::table('departments')->where('tenant_id', $tenantId)->where('department_code', 'DEP-ENG')->first();
        if ($existingDeptEng) {
            $deptEngId = $existingDeptEng->id;
        } else {
            DB::table('departments')->insert([
                'id' => $deptEngId,
                'tenant_id' => $tenantId,
                'business_unit_id' => $buId,
                'department_code' => 'DEP-ENG',
                'department_name' => 'Core Platform Engineering',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $deptHrId = (string) Str::uuid();
        $existingDeptHr = DB::table('departments')->where('tenant_id', $tenantId)->where('department_code', 'DEP-HR')->first();
        if ($existingDeptHr) {
            $deptHrId = $existingDeptHr->id;
        } else {
            DB::table('departments')->insert([
                'id' => $deptHrId,
                'tenant_id' => $tenantId,
                'business_unit_id' => $buId,
                'department_code' => 'DEP-HR',
                'department_name' => 'People Operations & Talent',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Job Grades & Positions (Job Architecture)
        $gradeEngSeniorId = (string) Str::uuid();
        $posLeadArchId = (string) Str::uuid();
        $existingPos = DB::table('positions')->where('tenant_id', $tenantId)->where('code', 'POS-ENG-001')->first();
        if ($existingPos) {
            $posLeadArchId = $existingPos->id;
        } else {
            DB::table('positions')->insert([
                'id' => $posLeadArchId,
                'tenant_id' => $tenantId,
                'code' => 'POS-ENG-001',
                'title' => 'Principal Software Architect',
                'department_id' => $deptEngId,
                'headcount' => 3,
                'filled_headcount' => 1,
                'status' => 'active',
                'salary_min' => 140000.00,
                'salary_max' => 185000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $posSeniorDevId = (string) Str::uuid();
        $existingPos2 = DB::table('positions')->where('tenant_id', $tenantId)->where('code', 'POS-ENG-002')->first();
        if ($existingPos2) {
            $posSeniorDevId = $existingPos2->id;
        } else {
            DB::table('positions')->insert([
                'id' => $posSeniorDevId,
                'tenant_id' => $tenantId,
                'code' => 'POS-ENG-002',
                'title' => 'Senior Fullstack Engineer',
                'department_id' => $deptEngId,
                'headcount' => 5,
                'filled_headcount' => 1,
                'status' => 'active',
                'salary_min' => 105000.00,
                'salary_max' => 135000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Users & Employees (Core HR)
        $users = [
            [
                'email' => 'alex.mercer@apexglobal.test',
                'first_name' => 'Alex',
                'last_name' => 'Mercer',
                'emp_num' => 'EMP-APEX-001',
                'pos_id' => $posLeadArchId,
                'dept_id' => $deptEngId,
                'salary' => 165000.00,
            ],
            [
                'email' => 'elena.rostova@apexglobal.test',
                'first_name' => 'Elena',
                'last_name' => 'Rostova',
                'emp_num' => 'EMP-APEX-002',
                'pos_id' => $posSeniorDevId,
                'dept_id' => $deptEngId,
                'salary' => 125000.00,
            ],
        ];

        $employeeIds = [];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['first_name'] . ' ' . $u['last_name'],
                    'password' => Hash::make('Secret123!'),
                    'tenant_id' => $tenantId,
                    'status' => 'active',
                ]
            );

            $emp = DB::table('employees')->where('tenant_id', $tenantId)->where('employee_number', $u['emp_num'])->first();
            if ($emp) {
                $employeeIds[$u['emp_num']] = $emp->id;
            } else {
                $empId = (string) Str::uuid();
                DB::table('employees')->insert([
                    'id' => $empId,
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'employee_number' => $u['emp_num'],
                    'employee_code' => $u['emp_num'],
                    'first_name' => $u['first_name'],
                    'last_name' => $u['last_name'],
                    'official_email' => $u['email'],
                    'personal_email' => $u['email'],
                    'department_id' => $u['dept_id'],
                    'current_position_id' => $u['pos_id'],
                    'employment_status' => 'active',
                    'joining_date' => Carbon::now()->subMonths(18)->toDateString(),
                    'original_hire_date' => Carbon::now()->subMonths(18)->toDateString(),
                    'portal_access' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Compensation
                DB::table('employee_compensations')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'employee_id' => $empId,
                    'currency' => 'USD',
                    'pay_frequency' => 'monthly',
                    'base_salary' => $u['salary'],
                    'gross_salary' => $u['salary'],
                    'effective_from' => Carbon::now()->subMonths(18)->toDateString(),
                    'reason_for_change' => 'Initial Placement',
                    'status' => 'approved',
                    'is_active' => 1,
                    'approved_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Position Occupancy
                DB::table('position_occupancies')->insert([
                    'id' => (string) Str::uuid(),
                    'position_id' => $u['pos_id'],
                    'employee_id' => $empId,
                    'starts_on' => Carbon::now()->subMonths(18)->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $employeeIds[$u['emp_num']] = $empId;
            }
        }

        // Set manager hierarchy: Alex Mercer manages Elena Rostova
        if (isset($employeeIds['EMP-APEX-001']) && isset($employeeIds['EMP-APEX-002'])) {
            DB::table('employees')->where('id', $employeeIds['EMP-APEX-002'])->update([
                'current_manager_employee_id' => $employeeIds['EMP-APEX-001'],
                'reporting_manager_id' => $employeeIds['EMP-APEX-001'],
            ]);
        }

        // 5. Leave Types & Balances (Absence Management)
        $leaveTypeId = (string) Str::uuid();
        $existingLeaveType = DB::table('leave_types')->where('tenant_id', $tenantId)->first();
        if ($existingLeaveType) {
            $leaveTypeId = $existingLeaveType->id;
        } else {
            DB::table('leave_types')->insert([
                'id' => $leaveTypeId,
                'tenant_id' => $tenantId,
                'name' => 'Annual Paid Time Off',
                'code' => 'PTO',
                'is_paid' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($employeeIds as $empId) {
            $hasBal = DB::table('leave_balances')->where('tenant_id', $tenantId)->where('employee_id', $empId)->where('leave_type_id', $leaveTypeId)->exists();
            if (!$hasBal) {
                DB::table('leave_balances')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'employee_id' => $empId,
                    'leave_type_id' => $leaveTypeId,
                    'year' => (int) date('Y'),
                    'entitled' => 25.0,
                    'earned' => 25.0,
                    'used' => 5.0,
                    'pending' => 0.0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 6. Workforce Scheduling & Shift
        $shiftId = (string) Str::uuid();
        $existingShift = DB::table('shifts')->where('tenant_id', $tenantId)->first();
        if ($existingShift) {
            $shiftId = $existingShift->id;
        } else {
            DB::table('shifts')->insert([
                'id' => $shiftId,
                'tenant_id' => $tenantId,
                'shift_name' => 'Standard Corporate Schedule',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'status' => 'active',
                'weekly_off' => json_encode(['Saturday', 'Sunday']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 7. LMS Course & Skills
        $courseId = (string) Str::uuid();
        $existingCourse = DB::table('courses')->where('tenant_id', $tenantId)->where('code', 'CRS-ARCH-2026')->first();
        if ($existingCourse) {
            $courseId = $existingCourse->id;
        } else {
            DB::table('courses')->insert([
                'id' => $courseId,
                'tenant_id' => $tenantId,
                'code' => 'CRS-ARCH-2026',
                'name' => 'Cloud Distributed Architecture & Security',
                'description' => 'Advanced enterprise architectural patterns and security standards.',
                'delivery_type' => 'online',
                'level' => 'advanced',
                'passing_score' => 80,
                'is_mandatory' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 8. Performance Cycles & Talent Pools
        $cycleId = (string) Str::uuid();
        $existingCycle = DB::table('performance_cycles')->where('tenant_id', $tenantId)->first();
        if ($existingCycle) {
            $cycleId = $existingCycle->id;
        } else {
            DB::table('performance_cycles')->insert([
                'id' => $cycleId,
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'Annual Performance Review 2026',
                'cycle_type' => 'annual',
                'start_date' => Carbon::now()->startOfYear()->toDateString(),
                'end_date' => Carbon::now()->endOfYear()->toDateString(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $poolId = (string) Str::uuid();
        $existingPool = DB::table('talent_pools')->where('tenant_id', $tenantId)->where('code', 'POOL-HIGH-POTENTIAL')->first();
        if ($existingPool) {
            $poolId = $existingPool->id;
        } else {
            DB::table('talent_pools')->insert([
                'id' => $poolId,
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'code' => 'POOL-HIGH-POTENTIAL',
                'name' => 'Enterprise High-Potential Leaders',
                'description' => 'Employees identified for strategic leadership succession.',
                'status' => 'active',
                'version' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
