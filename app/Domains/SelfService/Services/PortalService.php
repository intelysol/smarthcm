<?php

namespace App\Domains\SelfService\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Holiday;
use App\Domains\SelfService\Models\Announcement;
use App\Domains\SelfService\Models\ApprovalRequest;
use App\Domains\SelfService\Models\PortalNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PortalService
{
    public function employeeFor(User $user): Employee
    {
        return Employee::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->with(['department', 'designation', 'branch', 'reportingManager', 'documents'])
            ->firstOrFail();
    }

    public function dashboard(Employee $employee): array
    {
        $now = now();
        $announcements = $this->announcementsFor($employee)->limit(5)->get();

        return [
            'employee' => $employee,
            'profile_completion' => $this->profileCompletion($employee),
            'attendance_summary' => ['available' => false],
            'leave_balance' => ['available' => false],
            'upcoming_holidays' => $this->holidaysFor($employee)->limit(5)->get(),
            'upcoming_birthdays' => Employee::query()->where('tenant_id', $employee->tenant_id)->whereNotNull('date_of_birth')->get(['id', 'first_name', 'last_name', 'date_of_birth'])->filter(fn (Employee $record) => $record->date_of_birth?->format('m-d') >= $now->format('m-d'))->sortBy(fn (Employee $record) => $record->date_of_birth?->format('m-d'))->take(5)->values(),
            'announcements' => $announcements,
            'pending_approvals' => ApprovalRequest::query()->where('requester_employee_id', $employee->id)->where('status', 'pending')->count(),
            'recent_notifications' => PortalNotification::query()->where('employee_id', $employee->id)->whereNull('archived_at')->latest()->limit(5)->get(),
            'recent_documents' => $employee->documents()->latest()->limit(5)->get(),
            'quick_actions' => ['leave', 'attendance', 'expense', 'help-desk', 'payslip', 'letter', 'assets'],
        ];
    }

    public function managerDashboard(Employee $manager): array
    {
        $team = $this->teamFor($manager);

        return [
            'team_size' => $team->count(),
            'direct_reports' => $team,
            'pending_approvals' => ApprovalRequest::query()->where('current_approver_employee_id', $manager->id)->where('status', 'pending')->count(),
            'new_joiners' => $team->filter(fn (Employee $employee) => $employee->joining_date?->greaterThanOrEqualTo(now()->subDays(30)))->values(),
            'probation_reviews' => $team->filter(fn (Employee $employee) => $employee->probation_end_date?->between(now(), now()->addDays(30)))->values(),
            'contract_expiries' => $team->filter(fn (Employee $employee) => $employee->contract_end_date?->between(now(), now()->addDays(60)))->values(),
            'team_birthdays' => $team->filter(fn (Employee $employee) => $employee->date_of_birth !== null)->sortBy(fn (Employee $employee) => $employee->date_of_birth?->format('m-d'))->values(),
            'attendance_summary' => ['available' => false],
            'leave_summary' => ['available' => false],
        ];
    }

    /** @return Collection<int, Employee> */
    public function teamFor(Employee $manager): Collection
    {
        return Employee::query()->where('tenant_id', $manager->tenant_id)->where('reporting_manager_id', $manager->id)->with(['department', 'designation', 'branch'])->orderBy('first_name')->get();
    }

    public function announcementsFor(Employee $employee)
    {
        return Announcement::query()
            ->where('tenant_id', $employee->tenant_id)
            ->whereNotNull('published_at')->where('published_at', '<=', now())
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->where(fn ($query) => $query->whereNull('branch_id')->orWhere('branch_id', $employee->branch_id))
            ->where(fn ($query) => $query->whereNull('department_id')->orWhere('department_id', $employee->department_id))
            ->latest('published_at');
    }

    private function holidaysFor(Employee $employee)
    {
        return Holiday::query()->where('tenant_id', $employee->tenant_id)->where('holiday_calendar_id', $employee->holiday_calendar_id)->whereDate('holiday_date', '>=', today())->orderBy('holiday_date');
    }

    private function profileCompletion(Employee $employee): int
    {
        $fields = ['first_name', 'last_name', 'date_of_birth', 'gender', 'personal_email', 'mobile', 'present_address', 'nationality', 'photo_path'];
        $completed = collect($fields)->filter(fn (string $field) => filled($employee->{$field}))->count();

        return (int) round(($completed / count($fields)) * 100);
    }
}
