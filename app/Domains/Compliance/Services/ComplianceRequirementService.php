<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceAudit;
use App\Domains\Compliance\Models\HcmComplianceRequirement;
use App\Domains\Compliance\Models\HcmComplianceRequirementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ComplianceRequirementService
{
    /**
     * Create or retrieve requirement type.
     */
    public function getOrCreateType(string $tenantId, string $code, string $name, ?string $description = null): HcmComplianceRequirementType
    {
        return HcmComplianceRequirementType::firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => $code],
            ['name' => $name, 'description' => $description, 'is_active' => true]
        );
    }

    /**
     * List all requirements for tenant.
     */
    public function getRequirements(string $tenantId, bool $activeOnly = true): Collection
    {
        $query = HcmComplianceRequirement::with(['type', 'legalEntity', 'department', 'position'])
            ->where('tenant_id', $tenantId);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Create a new compliance requirement definition.
     */
    public function createRequirement(string $tenantId, array $data, ?User $actor = null): HcmComplianceRequirement
    {
        return DB::transaction(function () use ($tenantId, $data, $actor) {
            $requirement = HcmComplianceRequirement::create(array_merge($data, [
                'tenant_id' => $tenantId,
                'version' => 1,
                'is_active' => $data['is_active'] ?? true,
                'warning_periods' => $data['warning_periods'] ?? [90, 60, 30, 14, 7, 1],
            ]));

            HcmComplianceAudit::create([
                'tenant_id' => $tenantId,
                'action' => 'requirement.created',
                'entity_type' => HcmComplianceRequirement::class,
                'entity_id' => $requirement->id,
                'actor_id' => $actor?->id,
                'details' => ['name' => $requirement->name, 'code' => $requirement->code],
            ]);

            return $requirement;
        });
    }

    /**
     * Update requirement with versioning.
     */
    public function updateRequirement(string $requirementId, array $data, ?User $actor = null): HcmComplianceRequirement
    {
        return DB::transaction(function () use ($requirementId, $data, $actor) {
            $requirement = HcmComplianceRequirement::findOrFail($requirementId);

            $data['version'] = $requirement->version + 1;
            $requirement->update($data);

            HcmComplianceAudit::create([
                'tenant_id' => $requirement->tenant_id,
                'action' => 'requirement.updated',
                'entity_type' => HcmComplianceRequirement::class,
                'entity_id' => $requirement->id,
                'actor_id' => $actor?->id,
                'details' => ['version' => $requirement->version, 'changes' => array_keys($data)],
            ]);

            return $requirement->fresh(['type']);
        });
    }

    /**
     * Deactivate requirement.
     */
    public function deactivateRequirement(string $requirementId, ?User $actor = null): bool
    {
        $requirement = HcmComplianceRequirement::findOrFail($requirementId);
        $result = $requirement->update(['is_active' => false]);

        HcmComplianceAudit::create([
            'tenant_id' => $requirement->tenant_id,
            'action' => 'requirement.deactivated',
            'entity_type' => HcmComplianceRequirement::class,
            'entity_id' => $requirement->id,
            'actor_id' => $actor?->id,
        ]);

        return $result;
    }
}
