<?php

namespace App\Domains\EmployeeExperience\Services;

use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeTaskService
{
    public function getPendingTasks(Employee $employee): array
    {
        $tenantId = $employee->tenant_id;
        $employeeId = $employee->id;
        $tasks = [];

        // 1. Policy & Document Acknowledgements
        // 1. Policy & Document Acknowledgements
        if (Schema::hasTable('employee_document_requirements')) {
            try {
                $unacknowledged = DB::table('employee_document_requirements')
                    ->where('tenant_id', $tenantId)
                    ->where('employee_id', $employeeId)
                    ->where('status', 'required')
                    ->get();

                foreach ($unacknowledged as $req) {
                    $docTitle = 'Mandatory Policy';
                    if (Schema::hasTable('hcm_document_types')) {
                        $type = DB::table('hcm_document_types')->where('id', $req->document_type_id)->first();
                        if ($type) {
                            $docTitle = $type->name ?? $docTitle;
                        }
                    }

                    $tasks[] = [
                        'id' => 'req_' . $req->id,
                        'title' => 'Acknowledge Policy: ' . $docTitle,
                        'due_date' => $req->due_date ?? Carbon::now()->addDays(3)->toDateString(),
                        'priority' => 'HIGH',
                        'source_module' => 'DOCUMENTS',
                        'status' => 'PENDING',
                        'action_url' => '/portal/documents?req=' . $req->id,
                        'action_label' => 'Review & Sign',
                    ];
                }
            } catch (\Throwable) {
            }
        }

        // 2. Performance Review Self-Evaluation
        if (Schema::hasTable('performance_reviews')) {
            try {
                $pendingReviews = DB::table('performance_reviews')
                    ->where('tenant_id', $tenantId)
                    ->where('employee_id', $employeeId)
                    ->whereIn('status', ['draft', 'self_review', 'pending_self_review'])
                    ->get();

                foreach ($pendingReviews as $rev) {
                    $tasks[] = [
                        'id' => 'perf_' . $rev->id,
                        'title' => 'Complete Self Performance Review',
                        'due_date' => Carbon::now()->addDays(5)->toDateString(),
                        'priority' => 'HIGH',
                        'source_module' => 'PERFORMANCE',
                        'status' => 'PENDING',
                        'action_url' => '/portal/growth',
                        'action_label' => 'Start Review',
                    ];
                }
            } catch (\Throwable) {
            }
        }

        // 3. Learning & Training Due
        if (Schema::hasTable('course_enrollments')) {
            try {
                $courses = DB::table('course_enrollments')
                    ->where('tenant_id', $tenantId)
                    ->where('employee_id', $employeeId)
                    ->whereIn('status', ['assigned', 'in_progress', 'overdue'])
                    ->get();

                foreach ($courses as $c) {
                    $tasks[] = [
                        'id' => 'learn_' . $c->id,
                        'title' => 'Complete Training Module',
                        'due_date' => $c->due_date ?? Carbon::now()->addDays(7)->toDateString(),
                        'priority' => ($c->status ?? '') === 'overdue' ? 'URGENT' : 'MEDIUM',
                        'source_module' => 'LEARNING',
                        'status' => strtoupper($c->status ?? 'PENDING'),
                        'action_url' => '/portal/growth',
                        'action_label' => 'Continue Learning',
                    ];
                }
            } catch (\Throwable) {
            }
        }

        // 4. Pending Timesheets
        if (Schema::hasTable('timesheets')) {
            try {
                $timesheets = DB::table('timesheets')
                    ->where('tenant_id', $tenantId)
                    ->where('employee_id', $employeeId)
                    ->whereIn('status', ['draft', 'rejected'])
                    ->get();

                foreach ($timesheets as $ts) {
                    $tasks[] = [
                        'id' => 'timesheet_' . $ts->id,
                        'title' => 'Submit Timesheet for Period',
                        'due_date' => Carbon::now()->endOfWeek()->toDateString(),
                        'priority' => 'MEDIUM',
                        'source_module' => 'ATTENDANCE',
                        'status' => strtoupper($ts->status ?? 'DRAFT'),
                        'action_url' => '/portal/work',
                        'action_label' => 'Submit Timesheet',
                    ];
                }
            } catch (\Throwable) {
            }
        }

        // 5. Profile Completion Task
        $missingFields = [];
        if (empty($employee->emergency_phone)) $missingFields[] = 'Emergency Contact';
        if (empty($employee->personal_email)) $missingFields[] = 'Personal Email';
        if (empty($employee->present_address)) $missingFields[] = 'Address Details';

        if (!empty($missingFields)) {
            $tasks[] = [
                'id' => 'profile_completion_' . $employeeId,
                'title' => 'Complete Profile: Add ' . implode(', ', $missingFields),
                'due_date' => Carbon::now()->addDays(14)->toDateString(),
                'priority' => 'LOW',
                'source_module' => 'CORE_HR',
                'status' => 'PENDING',
                'action_url' => '/portal/profile',
                'action_label' => 'Update Profile',
            ];
        }

        return $tasks;
    }
}
