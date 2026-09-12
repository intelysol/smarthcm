<?php

namespace App\Domains\Learning\Services;

use App\Domains\Learning\Models\EmployeeLearningRecord;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Learning\Models\LearningRequirementAssignment;
use App\Domains\Learning\Models\LearningTrainingCost;
use Illuminate\Database\Eloquent\Collection;

class LearningReportService
{
    /**
     * Training Completion Report
     */
    public function completionReport(string $tenantId, array $filters = []): Collection
    {
        return EmployeeLearningRecord::query()
            ->where('tenant_id', $tenantId)
            ->when($filters['course_id'] ?? null, fn ($q, $id) => $q->where('course_id', $id))
            ->when($filters['employee_id'] ?? null, fn ($q, $id) => $q->where('employee_id', $id))
            ->when($filters['start_date'] ?? null, fn ($q, $date) => $q->where('completion_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($q, $date) => $q->where('completion_date', '<=', $date))
            ->with(['employee', 'course', 'certificate', 'provider'])
            ->orderBy('completion_date', 'desc')
            ->get();
    }

    /**
     * Mandatory Training Compliance Report
     */
    public function complianceReport(string $tenantId, array $filters = []): Collection
    {
        return LearningRequirementAssignment::query()
            ->where('tenant_id', $tenantId)
            ->when($filters['requirement_id'] ?? null, fn ($q, $id) => $q->where('requirement_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->with(['employee.department', 'course', 'requirement'])
            ->orderBy('due_at')
            ->get();
    }

    /**
     * Training Hours Report
     */
    public function hoursReport(string $tenantId, array $filters = []): Collection
    {
        return EmployeeLearningRecord::query()
            ->where('tenant_id', $tenantId)
            ->when($filters['employee_id'] ?? null, fn ($q, $id) => $q->where('employee_id', $id))
            ->with(['employee', 'course'])
            ->get();
    }

    /**
     * Training Cost Report
     */
    public function costReport(string $tenantId, array $filters = []): Collection
    {
        return LearningTrainingCost::query()
            ->where('tenant_id', $tenantId)
            ->when($filters['course_id'] ?? null, fn ($q, $id) => $q->where('course_id', $id))
            ->when($filters['provider_id'] ?? null, fn ($q, $id) => $q->where('provider_id', $id))
            ->with(['course', 'provider', 'employee', 'session'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Certification Status & Expiry Report
     */
    public function certificationReport(string $tenantId, array $filters = []): Collection
    {
        return LearningCertificate::query()
            ->where('tenant_id', $tenantId)
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->with(['employee', 'course', 'version'])
            ->orderBy('expiry_date')
            ->get();
    }
}
