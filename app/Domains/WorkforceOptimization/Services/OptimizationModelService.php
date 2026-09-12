<?php

namespace App\Domains\WorkforceOptimization\Services;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationModel;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationModelVersion;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationObjective;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationConstraint;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class OptimizationModelService
{
    /**
     * Create or update an optimization model template.
     */
    public function createModel(array $data): HcmWorkforceOptimizationModel
    {
        return HcmWorkforceOptimizationModel::create([
            'tenant_id' => $data['tenant_id'],
            'name' => $data['name'],
            'model_type' => $data['model_type'] ?? ($data['model_code'] ?? 'balanced'),
            'default_solver' => $data['default_solver'] ?? 'weighted_scoring',
            'status' => $data['status'] ?? 'active',
            'current_version' => $data['current_version'] ?? 1,
            'is_active' => $data['is_active'] ?? true,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Create a new version for a model with objectives and constraints.
     */
    public function createModelVersion(
        HcmWorkforceOptimizationModel $model,
        array $versionData,
        array $objectives = [],
        array $constraints = []
    ): HcmWorkforceOptimizationModelVersion {
        $versionNumber = ($model->versions()->max('version') ?? 0) + 1;

        if (!empty($versionData['is_default']) || !empty($versionData['is_current'])) {
            $model->versions()->update(['is_current' => false]);
        }

        $version = HcmWorkforceOptimizationModelVersion::create([
            'tenant_id' => $model->tenant_id,
            'model_id' => $model->id,
            'version' => $versionNumber,
            'configuration' => $versionData['configuration'] ?? $versionData['solver_configuration'] ?? [],
            'effective_from' => $versionData['effective_from'] ?? now()->toDateString(),
            'effective_to' => $versionData['effective_to'] ?? null,
            'is_current' => $versionData['is_current'] ?? $versionData['is_default'] ?? true,
            'change_notes' => $versionData['change_notes'] ?? ($versionData['name'] ?? "v{$versionNumber}"),
        ]);

        foreach ($objectives as $obj) {
            $this->addObjective($model, $obj);
        }

        foreach ($constraints as $con) {
            $this->addConstraint($model, $con);
        }

        return $version->fresh();
    }

    public function addObjective(
        HcmWorkforceOptimizationModel $model,
        array $data
    ): HcmWorkforceOptimizationObjective {
        return HcmWorkforceOptimizationObjective::create([
            'tenant_id' => $model->tenant_id,
            'model_id' => $model->id,
            'code' => $data['code'] ?? ($data['objective_code'] ?? 'OBJ_'.uniqid()),
            'name' => $data['name'],
            'direction' => strtoupper($data['direction'] ?? 'MINIMIZE'),
            'target_metric' => $data['target_metric'],
            'weight' => $data['weight'] ?? 1.0,
            'priority' => $data['priority'] ?? 1,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function addConstraint(
        HcmWorkforceOptimizationModel $model,
        array $data
    ): HcmWorkforceOptimizationConstraint {
        return HcmWorkforceOptimizationConstraint::create([
            'tenant_id' => $model->tenant_id,
            'model_id' => $model->id,
            'constraint_type' => $data['constraint_type'] ?? 'working_hours',
            'name' => $data['name'],
            'is_hard_constraint' => $data['is_hard_constraint'] ?? (($data['constraint_type'] ?? '') === 'HARD'),
            'parameters' => $data['parameters'] ?? ($data['rule_definition'] ?? []),
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Get active models for tenant.
     */
    public function getActiveModels(?string $tenantId = null): Collection
    {
        $query = HcmWorkforceOptimizationModel::with(['currentVersion', 'objectives', 'constraints'])
            ->where('is_active', true);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get();
    }
}
