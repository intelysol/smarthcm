<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpensePolicy;
use App\Domains\Expenses\Models\ExpensePolicyAssignment;
use App\Domains\Expenses\Models\ExpensePolicyVersion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpensePolicyService
{
    public function createPolicy(array $data): ExpensePolicy
    {
        return DB::transaction(function () use ($data) {
            $policy = ExpensePolicy::create([
                'tenant_id' => $data['tenant_id'],
                'policy_code' => $data['policy_code'] ?? ('POL-' . strtoupper(uniqid())),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'effective_from' => $data['effective_from'] ?? now()->toDateString(),
                'effective_to' => $data['effective_to'] ?? null,
                'current_version' => 1,
                'status' => $data['status'] ?? 'active',
            ]);

            $policy->versions()->create([
                'tenant_id' => $policy->tenant_id,
                'version_number' => 1,
                'effective_from' => $policy->effective_from,
                'effective_to' => $policy->effective_to,
                'daily_meal_limit' => $data['daily_meal_limit'] ?? 5000.0000,
                'daily_hotel_limit' => $data['daily_hotel_limit'] ?? 20000.0000,
                'receipt_required_threshold' => $data['receipt_required_threshold'] ?? 2000.0000,
                'allow_policy_override' => $data['allow_policy_override'] ?? true,
                'rules_configuration' => $data['rules_configuration'] ?? null,
                'is_active' => true,
            ]);

            return $policy->fresh(['versions']);
        });
    }

    public function createNewVersion(ExpensePolicy $policy, array $versionData): ExpensePolicyVersion
    {
        return DB::transaction(function () use ($policy, $versionData) {
            $nextVersionNumber = (int) $policy->current_version + 1;

            $version = $policy->versions()->create([
                'tenant_id' => $policy->tenant_id,
                'version_number' => $nextVersionNumber,
                'effective_from' => $versionData['effective_from'] ?? now()->toDateString(),
                'effective_to' => $versionData['effective_to'] ?? null,
                'daily_meal_limit' => $versionData['daily_meal_limit'] ?? null,
                'daily_hotel_limit' => $versionData['daily_hotel_limit'] ?? null,
                'receipt_required_threshold' => $versionData['receipt_required_threshold'] ?? 0.0000,
                'allow_policy_override' => $versionData['allow_policy_override'] ?? true,
                'rules_configuration' => $versionData['rules_configuration'] ?? null,
                'is_active' => true,
            ]);

            $policy->update(['current_version' => $nextVersionNumber]);

            return $version;
        });
    }

    public function assignPolicy(ExpensePolicy $policy, string $scopeType, ?string $scopeId = null, int $priority = 10): ExpensePolicyAssignment
    {
        return ExpensePolicyAssignment::create([
            'tenant_id' => $policy->tenant_id,
            'expense_policy_id' => $policy->id,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'priority' => $priority,
        ]);
    }

    public function resolvePolicyForEmployee(Employee $employee, ?string $travelType = null): ?ExpensePolicy
    {
        $tenantId = $employee->tenant_id;

        // Check specific employee assignment first
        $assignment = ExpensePolicyAssignment::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($employee, $travelType) {
                $q->where(fn ($sq) => $sq->where('scope_type', 'employee')->where('scope_id', $employee->id))
                  ->orWhere(fn ($sq) => $sq->where('scope_type', 'department')->where('scope_id', $employee->department_id))
                  ->orWhere(fn ($sq) => $sq->where('scope_type', 'job_grade')->where('scope_id', $employee->job_grade_id))
                  ->orWhere(fn ($sq) => $sq->where('scope_type', 'travel_type')->where('scope_id', $travelType))
                  ->orWhere(fn ($sq) => $sq->where('scope_type', 'global'));
            })
            ->orderBy('priority', 'asc')
            ->first();

        if ($assignment && $assignment->policy && $assignment->policy->status === 'active') {
            return $assignment->policy;
        }

        // Fallback to active global policy for tenant
        return ExpensePolicy::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('created_at', 'asc')
            ->first();
    }

    public function getActiveVersionForDate(ExpensePolicy $policy, ?string $date = null): ?ExpensePolicyVersion
    {
        $queryDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        return $policy->versions()
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $queryDate)
            ->where(function ($q) use ($queryDate) {
                $q->whereNull('effective_to')
                  ->orWhereDate('effective_to', '>=', $queryDate);
            })
            ->orderBy('version_number', 'desc')
            ->first();
    }
}
