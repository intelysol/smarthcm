<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Models\HcmProductivityAudit;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMetricDefinition;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMetricVersion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ProductivityMetricService
{
    /**
     * Create a new tenant-defined metric with version 1.
     */
    public function createMetric(array $data, ?int $userId = null): HcmProductivityMetricDefinition
    {
        $definition = HcmProductivityMetricDefinition::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $data['tenant_id'],
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'metric_type' => $data['metric_type'] ?? 'volume',
            'unit' => $data['unit'] ?? 'units_per_hour',
            'current_version' => 1,
            'status' => $data['status'] ?? 'active',
            'dimensions' => $data['dimensions'] ?? ['department', 'position', 'shift'],
            'is_system' => $data['is_system'] ?? false,
        ]);

        HcmProductivityMetricVersion::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $definition->tenant_id,
            'metric_definition_id' => $definition->id,
            'version' => 1,
            'formula_name' => $data['formula_name'] ?? ($definition->name . ' Formula v1'),
            'numerator_code' => $data['numerator_code'] ?? 'COMPLETED_OUTPUT',
            'denominator_code' => $data['denominator_code'] ?? 'PRODUCTIVE_HOURS',
            'calculation_expression' => $data['calculation_expression'] ?? 'output / productive_hours',
            'effective_from' => $data['effective_from'] ?? Carbon::now()->toDateString(),
            'effective_to' => null,
            'is_current' => true,
            'change_notes' => 'Initial version creation.',
        ]);

        HcmProductivityAudit::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $definition->tenant_id,
            'action' => 'CREATE_METRIC',
            'target_type' => HcmProductivityMetricDefinition::class,
            'target_id' => $definition->id,
            'user_id' => $userId,
            'changes' => ['code' => $definition->code, 'version' => 1],
            'created_at' => now(),
        ]);

        return $definition->load('currentVersion');
    }

    /**
     * Create a new immutable version of a metric formula without altering historical measurements.
     */
    public function createNewVersion(
        string $metricDefinitionId,
        array $versionData,
        ?int $userId = null
    ): HcmProductivityMetricVersion {
        $definition = HcmProductivityMetricDefinition::findOrFail($metricDefinitionId);

        // Mark previous current version as expired
        HcmProductivityMetricVersion::where('metric_definition_id', $definition->id)
            ->where('is_current', true)
            ->update([
                'is_current' => false,
                'effective_to' => Carbon::parse($versionData['effective_from'] ?? now())->subDay()->toDateString(),
            ]);

        $newVersionNumber = $definition->current_version + 1;

        $newVersion = HcmProductivityMetricVersion::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $definition->tenant_id,
            'metric_definition_id' => $definition->id,
            'version' => $newVersionNumber,
            'formula_name' => $versionData['formula_name'] ?? ($definition->name . " Formula v{$newVersionNumber}"),
            'numerator_code' => $versionData['numerator_code'],
            'denominator_code' => $versionData['denominator_code'],
            'calculation_expression' => $versionData['calculation_expression'] ?? null,
            'effective_from' => $versionData['effective_from'] ?? Carbon::now()->toDateString(),
            'effective_to' => null,
            'is_current' => true,
            'change_notes' => $versionData['change_notes'] ?? 'Updated formula definition',
        ]);

        $definition->update(['current_version' => $newVersionNumber]);

        HcmProductivityAudit::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $definition->tenant_id,
            'action' => 'UPDATE_METRIC_VERSION',
            'target_type' => HcmProductivityMetricDefinition::class,
            'target_id' => $definition->id,
            'user_id' => $userId,
            'changes' => ['new_version' => $newVersionNumber],
            'created_at' => now(),
        ]);

        return $newVersion;
    }

    public function listMetrics(string $tenantId): Collection
    {
        return HcmProductivityMetricDefinition::with('currentVersion')
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();
    }
}
