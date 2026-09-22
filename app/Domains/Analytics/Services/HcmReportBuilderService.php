<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HcmReportBuilderService
{
    public function __construct(
        protected ?AuditService $auditService = null
    ) {}

    public function executeReportQuery(string $tenantId, string $dataset, array $config, ?User $actor = null): array
    {
        // Permission Gating on Sensitive Report Datasets
        if ($actor !== null) {
            $sensitiveDatasets = ['payroll', 'payroll_register', 'DP_PAYROLL', 'compensation'];
            if (in_array($dataset, $sensitiveDatasets)) {
                $hasPayroll = false;
                if (method_exists($actor, 'hasPermission') && $actor->hasPermission('payroll.view')) {
                    $hasPayroll = true;
                } elseif (method_exists($actor, 'can') && $actor->can('payroll.view')) {
                    $hasPayroll = true;
                } elseif ($actor->is_platform_admin ?? false) {
                    $hasPayroll = true;
                } elseif (method_exists($actor, 'hasRole') && ($actor->hasRole('admin') || $actor->hasRole('super-admin') || $actor->hasRole('hr-admin'))) {
                    $hasPayroll = true;
                }

                if (!$hasPayroll) {
                    throw new \Illuminate\Auth\Access\AuthorizationException("User is not authorized to execute sensitive report dataset [{$dataset}].");
                }
            }
        }

        $dimensions = $config['dimensions'] ?? ['department', 'branch'];
        $metrics = $config['metrics'] ?? ['headcount', 'fte'];
        $filters = $config['filters'] ?? [];
        $grouping = $config['grouping'] ?? $dimensions;

        // 1. WORKFORCE DATASET (Operational & Management)
        if (in_array($dataset, ['workforce', 'DP_WORKFORCE', 'employees', 'employee_directory'])) {
            $query = DB::table('employees')
                ->where('employees.tenant_id', $tenantId)
                ->whereNull('employees.deleted_at');

            if (Schema::hasTable('departments')) {
                $query->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                      ->addSelect('departments.department_name as department');
            }
            if (Schema::hasTable('positions')) {
                $query->leftJoin('positions', 'employees.current_position_id', '=', 'positions.id')
                      ->addSelect('positions.title as position_title');
            }

            $query->addSelect([
                'employees.id',
                'employees.employee_number',
                'employees.first_name',
                'employees.last_name',
                'employees.official_email',
                'employees.employment_status',
                'employees.joining_date',
                'employees.department_id',
            ]);

            if (!empty($filters['department_id'])) {
                $query->where('employees.department_id', $filters['department_id']);
            }
            if (!empty($filters['status'])) {
                $query->where('employees.employment_status', $filters['status']);
            }

            $records = $query->get();

            if (!empty($config['raw_records'])) {
                $rows = $records->map(fn ($e) => [
                    'employee_number' => $e->employee_number,
                    'full_name' => trim("{$e->first_name} {$e->last_name}"),
                    'official_email' => $e->official_email,
                    'department' => $e->department ?? 'Corporate',
                    'position' => $e->position_title ?? 'Staff',
                    'joining_date' => $e->joining_date,
                    'status' => $e->employment_status,
                ])->toArray();

                return [
                    'dataset' => $dataset,
                    'total_records_analyzed' => $records->count(),
                    'grouped_rows_count' => count($rows),
                    'rows' => $rows,
                ];
            }

            // Perform Grouping & Aggregations
            $grouped = $records->groupBy(function ($emp) use ($dimensions) {
                $keys = [];
                foreach ($dimensions as $dim) {
                    if ($dim === 'department') $keys[] = $emp->department ?? 'Corporate';
                    elseif ($dim === 'status') $keys[] = $emp->employment_status;
                }
                return empty($keys) ? 'All' : implode(' | ', $keys);
            });

            $rows = [];
            foreach ($grouped as $groupKey => $empList) {
                $rows[] = [
                    'group' => $groupKey,
                    'headcount' => $empList->count(),
                    'active_count' => $empList->where('employment_status', 'active')->count(),
                    'fte' => (float) $empList->count(),
                ];
            }

            return [
                'dataset' => $dataset,
                'total_records_analyzed' => $records->count(),
                'grouped_rows_count' => count($rows),
                'rows' => $rows,
            ];
        }

        // 2. ATTENDANCE & OVERTIME DATASET (Operational Exceptions)
        if (in_array($dataset, ['attendance', 'attendance_exceptions', 'DP_ATTENDANCE', 'timesheets'])) {
            $query = DB::table('timesheets')
                ->where('tenant_id', $tenantId)
                ->whereNull('deleted_at');

            if (!empty($filters['employee_id'])) {
                $query->where('employee_id', $filters['employee_id']);
            }
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            $records = $query->get();
            $rows = $records->map(fn ($t) => [
                'timesheet_id' => $t->id,
                'employee_id' => $t->employee_id,
                'start_date' => $t->start_date,
                'end_date' => $t->end_date,
                'regular_hours' => round(((int)$t->total_regular_minutes) / 60, 2),
                'overtime_hours' => round(((int)$t->total_approved_overtime_minutes) / 60, 2),
                'status' => $t->status,
            ])->toArray();

            return [
                'dataset' => $dataset,
                'total_records_analyzed' => $records->count(),
                'grouped_rows_count' => count($rows),
                'rows' => $rows,
            ];
        }

        // 3. LEAVE & ABSENCE DATASET (Operational Queue)
        if (in_array($dataset, ['leave', 'leave_requests', 'DP_LEAVE', 'leave_applications'])) {
            $query = DB::table('leave_applications')
                ->where('tenant_id', $tenantId);

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['employee_id'])) {
                $query->where('employee_id', $filters['employee_id']);
            }

            $records = $query->get();
            $rows = $records->map(fn ($l) => [
                'leave_id' => $l->id,
                'employee_id' => $l->employee_id,
                'leave_type_id' => $l->leave_type_id,
                'start_date' => $l->start_date,
                'end_date' => $l->end_date,
                'duration' => (float) $l->duration,
                'status' => $l->status,
                'reason' => $l->reason,
            ])->toArray();

            return [
                'dataset' => $dataset,
                'total_records_analyzed' => $records->count(),
                'grouped_rows_count' => count($rows),
                'rows' => $rows,
            ];
        }

        // 4. PAYROLL & COMPENSATION DATASET (Management Cost Summary)
        if (in_array($dataset, ['payroll', 'payroll_register', 'DP_PAYROLL', 'compensation'])) {
            if (Schema::hasTable('employee_compensations')) {
                $query = DB::table('employee_compensations')
                    ->where('tenant_id', $tenantId)
                    ->where('is_active', 1);

                if (!empty($filters['employee_id'])) {
                    $query->where('employee_id', $filters['employee_id']);
                }

                $records = $query->get();
                $rows = $records->map(fn ($c) => [
                    'compensation_id' => $c->id,
                    'employee_id' => $c->employee_id,
                    'base_salary' => (float) $c->base_salary,
                    'currency' => $c->currency ?? 'USD',
                    'pay_frequency' => $c->pay_frequency ?? 'monthly',
                    'status' => $c->status,
                ])->toArray();

                return [
                    'dataset' => $dataset,
                    'total_records_analyzed' => $records->count(),
                    'grouped_rows_count' => count($rows),
                    'rows' => $rows,
                ];
            }
        }

        // 5. REGULATORY & COMPLIANCE DATASET (Statutory Training & Certifications)
        if (in_array($dataset, ['compliance', 'certifications', 'DP_COMPLIANCE', 'learning'])) {
            if (Schema::hasTable('course_enrollments')) {
                $query = DB::table('course_enrollments')
                    ->where('course_enrollments.tenant_id', $tenantId);

                if (Schema::hasTable('courses')) {
                    $query->leftJoin('courses', 'course_enrollments.course_id', '=', 'courses.id')
                          ->addSelect('courses.name as course_name', 'courses.is_mandatory');
                }

                $query->addSelect([
                    'course_enrollments.id',
                    'course_enrollments.employee_id',
                    'course_enrollments.course_id',
                    'course_enrollments.status',
                    'course_enrollments.progress',
                    'course_enrollments.completed_at',
                ]);

                if (!empty($filters['status'])) {
                    $query->where('course_enrollments.status', $filters['status']);
                }

                $records = $query->get();
                $rows = $records->map(fn ($e) => [
                    'enrollment_id' => $e->id,
                    'employee_id' => $e->employee_id,
                    'course_name' => $e->course_name ?? 'Training Module',
                    'status' => $e->status,
                    'progress' => (int) $e->progress,
                    'completed_at' => $e->completed_at,
                    'is_mandatory' => (bool) ($e->is_mandatory ?? false),
                ])->toArray();

                return [
                    'dataset' => $dataset,
                    'total_records_analyzed' => $records->count(),
                    'grouped_rows_count' => count($rows),
                    'rows' => $rows,
                ];
            }
        }

        // Generic fallback response
        return [
            'dataset' => $dataset,
            'total_records_analyzed' => 0,
            'grouped_rows_count' => 0,
            'rows' => [],
        ];
    }

    /**
     * Just-In-Time Security Gate for Scheduled Report Delivery
     */
    public function validateScheduledDelivery(string $tenantId, int $recipientUserId, string $reportId): array
    {
        // 1. Verify Tenant
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        if (!$tenant || ($tenant->status ?? 'active') !== 'active') {
            return ['authorized' => false, 'reason' => 'Tenant is inactive or suspended.'];
        }

        // 2. Verify Recipient User
        $user = User::query()->where('id', $recipientUserId)->where('tenant_id', $tenantId)->first();
        if (!$user || ($user->status ?? 'active') !== 'active') {
            return ['authorized' => false, 'reason' => 'Recipient user account is inactive or revoked.'];
        }

        // 3. Domain Gating for Sensitive Reports
        $sensitivePayrollReports = ['REP-MGT-003', 'REP-OPS-004', 'payroll_register', 'compensation_report'];
        if (in_array($reportId, $sensitivePayrollReports)) {
            $hasPayrollPermission = (method_exists($user, 'hasPermission') && $user->hasPermission('payroll.view'))
                || (method_exists($user, 'can') && ($user->can('payroll.view') || $user->can('payroll.admin')))
                || ($user->is_platform_admin ?? false)
                || (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('super-admin') || $user->hasRole('hr-admin')));

            if (!$hasPayrollPermission) {
                return ['authorized' => false, 'reason' => 'Recipient lacks required payroll.view permission.'];
            }
        }

        return ['authorized' => true, 'reason' => null];
    }

    /**
     * Sanitize and generate CSV export with spreadsheet command injection neutralization
     */
    public function generateCsvExport(array $reportResult): string
    {
        $rows = $reportResult['rows'] ?? [];
        if (empty($rows)) {
            return "No data available\n";
        }

        $headers = array_keys($rows[0]);
        $csv = implode(',', $headers) . "\n";

        foreach ($rows as $row) {
            $sanitizedValues = [];
            foreach ($row as $val) {
                $strVal = (string) $val;
                // Neutralize spreadsheet command injection triggers (=, +, -, @)
                if (in_array($strVal[0] ?? '', ['=', '+', '-', '@', "\t", "\r"])) {
                    $strVal = "'" . $strVal;
                }
                $sanitizedValues[] = '"' . str_replace('"', '""', $strVal) . '"';
            }
            $csv .= implode(',', $sanitizedValues) . "\n";
        }

        return $csv;
    }
}
