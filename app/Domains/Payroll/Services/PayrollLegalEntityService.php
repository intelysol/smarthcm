<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Models\PayrollLegalEntity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PayrollLegalEntityService
{
    public function createLegalEntity(string $tenantId, array $data, ?User $actor = null): PayrollLegalEntity
    {
        return PayrollLegalEntity::query()->create([
            'tenant_id' => $tenantId,
            'company_id' => $data['company_id'] ?? null,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'currency' => strtoupper($data['currency'] ?? 'USD'),
            'country' => strtoupper($data['country'] ?? 'USA'),
            'region' => $data['region'] ?? null,
            'timezone' => $data['timezone'] ?? 'UTC',
            'default_pay_frequency' => $data['default_pay_frequency'] ?? 'monthly',
            'tax_configuration' => $data['tax_configuration'] ?? null,
            'bank_configuration' => $data['bank_configuration'] ?? null,
            'accounting_configuration' => $data['accounting_configuration'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $actor?->id,
            'updated_by' => $actor?->id,
        ]);
    }

    public function updateLegalEntity(PayrollLegalEntity $entity, array $data, ?User $actor = null): PayrollLegalEntity
    {
        $entity->update(array_merge($data, ['updated_by' => $actor?->id]));
        return $entity;
    }

    public function getLegalEntities(string $tenantId): Collection
    {
        return PayrollLegalEntity::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['company'])
            ->get();
    }
}
