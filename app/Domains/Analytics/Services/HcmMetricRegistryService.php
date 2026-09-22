<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Models\HcmAnalyticsMetric;
use App\Domains\Analytics\Models\HcmAnalyticsMetricVersion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HcmMetricRegistryService
{
    public function getCatalog(string $tenantId, ?string $category = null): Collection
    {
        $query = HcmAnalyticsMetric::query()
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->where('is_active', true)
            ->with(['versions' => fn ($q) => $q->orderBy('version_number', 'desc')]);

        if ($category) {
            $query->where('category', $category);
        }

        return $query->orderBy('name', 'asc')->get();
    }

    public function findMetric(string $tenantId, string $code): ?HcmAnalyticsMetric
    {
        return HcmAnalyticsMetric::query()
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->where('code', $code)
            ->with(['versions'])
            ->first();
    }

    public function createMetric(string $tenantId, array $data, ?User $creator = null): HcmAnalyticsMetric
    {
        return DB::transaction(function () use ($tenantId, $data, $creator) {
            $metric = HcmAnalyticsMetric::create([
                'tenant_id' => $tenantId,
                'code' => strtoupper($data['code']),
                'name' => $data['name'],
                'category' => $data['category'],
                'description' => $data['description'] ?? null,
                'formula' => $data['formula'] ?? null,
                'unit' => $data['unit'] ?? 'count',
                'aggregation' => $data['aggregation'] ?? 'sum',
                'sensitivity' => $data['sensitivity'] ?? 'public',
                'refresh_frequency' => $data['refresh_frequency'] ?? 'daily',
                'current_version' => 1,
                'owner' => $data['owner'] ?? 'HCM Analytics',
                'data_sources' => $data['data_sources'] ?? [],
                'is_active' => true,
            ]);

            HcmAnalyticsMetricVersion::create([
                'tenant_id' => $tenantId,
                'hcm_analytics_metric_id' => $metric->id,
                'version_number' => 1,
                'effective_from' => $data['effective_from'] ?? now()->toDateString(),
                'calculation_definition' => $data['calculation_definition'] ?? ['formula' => $metric->formula, 'aggregation' => $metric->aggregation],
                'change_summary' => 'Initial metric definition',
                'created_by' => $creator?->id,
            ]);

            return $metric->fresh(['versions']);
        });
    }

    public function createNewVersion(HcmAnalyticsMetric $metric, array $newDefinition, string $effectiveFrom, string $changeSummary, ?User $creator = null): HcmAnalyticsMetricVersion
    {
        return DB::transaction(function () use ($metric, $newDefinition, $effectiveFrom, $changeSummary, $creator) {
            $latestVersion = $metric->versions()->orderBy('version_number', 'desc')->first();
            $newVersionNumber = ($latestVersion?->version_number ?? 0) + 1;

            // Close old version's effective window if active
            if ($latestVersion && ! $latestVersion->effective_to) {
                $latestVersion->update([
                    'effective_to' => Carbon::parse($effectiveFrom)->subDay()->toDateString(),
                ]);
            }

            $version = HcmAnalyticsMetricVersion::create([
                'tenant_id' => $metric->tenant_id,
                'hcm_analytics_metric_id' => $metric->id,
                'version_number' => $newVersionNumber,
                'effective_from' => $effectiveFrom,
                'effective_to' => null,
                'calculation_definition' => $newDefinition,
                'change_summary' => $changeSummary,
                'created_by' => $creator?->id,
            ]);

            $metric->update([
                'current_version' => $newVersionNumber,
                'formula' => $newDefinition['formula'] ?? $metric->formula,
            ]);

            return $version;
        });
    }

    public function getVersionForDate(HcmAnalyticsMetric $metric, string $date): ?HcmAnalyticsMetricVersion
    {
        return $metric->versions()
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date);
            })
            ->first() ?? $metric->versions()->orderBy('version_number', 'asc')->first();
    }

    public function getKpiExplanation(string $tenantId, string $code, ?string $asOfDate = null): ?array
    {
        $metric = $this->findMetric($tenantId, $code);
        if (!$metric) {
            return null;
        }

        $date = $asOfDate ?? now()->toDateString();
        $version = $this->getVersionForDate($metric, $date);

        return [
            'kpi_code' => $metric->code,
            'name' => $metric->name,
            'category' => $metric->category,
            'description' => $metric->description,
            'owner' => $metric->owner ?? 'HCM Analytics',
            'status' => $metric->is_active ? 'CERTIFIED' : 'DRAFT',
            'certification_status' => $metric->is_active ? 'CERTIFIED' : 'DRAFT',
            'unit' => $metric->unit,
            'version' => $version?->version_number ?? $metric->current_version,
            'effective_version' => $version?->version_number ?? $metric->current_version,
            'formula' => $version?->calculation_definition['formula'] ?? $metric->formula,
            'as_of_date' => $date,
            'data_sources' => !empty($metric->data_sources) ? $metric->data_sources : ['employees', 'employee_terminations'],
            'lineage' => [
                'data_sources' => !empty($metric->data_sources) ? $metric->data_sources : ['employees', 'employee_terminations'],
            ],
            'last_refreshed_at' => now()->toIso8601String(),
        ];
    }
}
