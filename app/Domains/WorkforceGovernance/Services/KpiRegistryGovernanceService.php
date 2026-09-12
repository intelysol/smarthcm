<?php

namespace App\Domains\WorkforceGovernance\Services;

use App\Domains\WorkforceGovernance\Models\HcmGovKpiRegistry;
use Carbon\Carbon;
use Illuminate\Support\Str;

class KpiRegistryGovernanceService
{
    public function registerKpi(array $data): HcmGovKpiRegistry
    {
        return HcmGovKpiRegistry::updateOrCreate(
            [
                'tenant_id' => $data['tenant_id'],
                'kpi_code' => $data['kpi_code'],
            ],
            [
                'name' => $data['name'],
                'business_definition' => $data['business_definition'],
                'technical_formula' => $data['technical_formula'],
                'business_owner' => $data['business_owner'],
                'technical_owner' => $data['technical_owner'],
                'source_module' => $data['source_module'],
                'unit' => $data['unit'] ?? 'count',
                'frequency' => $data['frequency'] ?? 'MONTHLY',
                'version' => $data['version'] ?? 1,
                'lifecycle_status' => $data['lifecycle_status'] ?? 'PUBLISHED',
                'certification_status' => $data['certification_status'] ?? 'UNCERTIFIED',
                'security_classification' => $data['security_classification'] ?? 'INTERNAL',
            ]
        );
    }

    public function certifyKpi(string $kpiId, string $userId): HcmGovKpiRegistry
    {
        $kpi = HcmGovKpiRegistry::findOrFail($kpiId);
        $kpi->update([
            'certification_status' => 'CERTIFIED',
            'certified_by_user_id' => $userId,
            'certified_at' => Carbon::now(),
        ]);

        return $kpi;
    }

    public function listKpis(string $tenantId, ?string $certificationStatus = null): array
    {
        $q = HcmGovKpiRegistry::where('tenant_id', $tenantId);
        if ($certificationStatus) {
            $q->where('certification_status', $certificationStatus);
        }

        return $q->orderBy('name')->get()->toArray();
    }
}
