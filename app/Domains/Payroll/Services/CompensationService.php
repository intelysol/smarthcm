<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\CompensationComponent;
use App\Domains\Payroll\Models\CompensationStructure;
use App\Domains\Payroll\Models\EmployeeCompensation;
use App\Domains\Payroll\Models\EmployeeCompensationComponent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CompensationService
{
    public function createComponent(string $tenantId, array $data, ?User $actor = null): CompensationComponent
    {
        return CompensationComponent::query()->create([
            'tenant_id' => $tenantId,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'component_type' => $data['component_type'],
            'calculation_type' => $data['calculation_type'] ?? 'fixed',
            'default_amount' => $data['default_amount'] ?? null,
            'percentage' => $data['percentage'] ?? null,
            'formula' => $data['formula'] ?? null,
            'is_taxable' => $data['is_taxable'] ?? true,
            'is_pensionable' => $data['is_pensionable'] ?? false,
            'is_overtime_eligible' => $data['is_overtime_eligible'] ?? false,
            'is_recurring' => $data['is_recurring'] ?? true,
            'is_statutory' => $data['is_statutory'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'priority_order' => $data['priority_order'] ?? 10,
            'description' => $data['description'] ?? null,
            'created_by' => $actor?->id,
            'updated_by' => $actor?->id,
        ]);
    }

    public function createStructure(string $tenantId, array $data, array $components = [], ?User $actor = null): CompensationStructure
    {
        /** @var CompensationStructure $structure */
        $structure = CompensationStructure::query()->create([
            'tenant_id' => $tenantId,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'currency' => strtoupper($data['currency'] ?? 'USD'),
            'description' => $data['description'] ?? null,
            'version' => 1,
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $actor?->id,
            'updated_by' => $actor?->id,
        ]);

        foreach ($components as $seq => $c) {
            $structure->structureComponents()->create([
                'tenant_id' => $tenantId,
                'compensation_component_id' => $c['component_id'],
                'calculation_type' => $c['calculation_type'] ?? 'fixed',
                'default_amount' => $c['default_amount'] ?? null,
                'percentage' => $c['percentage'] ?? null,
                'formula' => $c['formula'] ?? null,
                'sequence' => $seq + 1,
            ]);
        }

        return $structure;
    }

    /**
     * Assign compensation package to an employee with effective dating and historical preservation.
     */
    public function assignCompensation(Employee $employee, array $data, array $components, ?User $actor = null): EmployeeCompensation
    {
        $tenantId = $employee->tenant_id;
        $effectiveFrom = CarbonImmutable::parse($data['effective_from']);

        // Mark previously active compensation as superseded or set effective_to
        EmployeeCompensation::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->where(function ($q) use ($effectiveFrom) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $effectiveFrom->toDateString());
            })
            ->update([
                'effective_to' => $effectiveFrom->subDay()->toDateString(),
                'is_active' => false,
            ]);

        $baseSalary = (float) ($data['base_salary'] ?? 0);
        $totalGross = $baseSalary;

        /** @var EmployeeCompensation $comp */
        $comp = EmployeeCompensation::query()->create([
            'tenant_id' => $tenantId,
            'employee_id' => $employee->id,
            'compensation_structure_id' => $data['compensation_structure_id'] ?? null,
            'currency' => strtoupper($data['currency'] ?? 'USD'),
            'pay_frequency' => $data['pay_frequency'] ?? 'monthly',
            'base_salary' => $baseSalary,
            'gross_salary' => $baseSalary, // computed below
            'effective_from' => $effectiveFrom->toDateString(),
            'effective_to' => $data['effective_to'] ?? null,
            'reason_for_change' => $data['reason_for_change'] ?? 'hire',
            'status' => 'approved',
            'is_active' => true,
            'approved_by' => $actor?->id,
            'approved_at' => now(),
            'created_by' => $actor?->id,
            'updated_by' => $actor?->id,
        ]);

        foreach ($components as $item) {
            $componentModel = CompensationComponent::query()->where('id', $item['component_id'])->first();
            $amount = (float) ($item['amount'] ?? 0);
            $pct = isset($item['percentage']) ? (float) $item['percentage'] : null;

            if ($pct !== null && $componentModel?->calculation_type === 'percentage_of_basic') {
                $amount = round($baseSalary * ($pct / 100), 4);
            }

            if ($componentModel?->isEarning() && $componentModel->code !== 'BASIC') {
                $totalGross += $amount;
            }

            $comp->components()->create([
                'tenant_id' => $tenantId,
                'compensation_component_id' => $item['component_id'],
                'calculation_type' => $item['calculation_type'] ?? ($componentModel?->calculation_type ?? 'fixed'),
                'amount' => $amount,
                'percentage' => $pct,
                'formula' => $item['formula'] ?? null,
                'is_active' => true,
            ]);
        }

        $comp->update(['gross_salary' => $totalGross]);

        return $comp->fresh(['components.component', 'structure']);
    }

    /**
     * Resolve the active compensation for an employee as of a specific date.
     */
    public function getActiveCompensationForDate(Employee $employee, string $date): ?EmployeeCompensation
    {
        return EmployeeCompensation::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date);
            })
            ->with(['components.component', 'structure.structureComponents.component'])
            ->orderByDesc('effective_from')
            ->first();
    }
}
