<?php

namespace App\Domains\WorkforceGovernance\Services;

use App\Domains\WorkforceGovernance\Models\HcmGovAsset;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DataAssetCatalogService
{
    public function registerAsset(array $data): HcmGovAsset
    {
        return HcmGovAsset::create([
            'tenant_id' => $data['tenant_id'],
            'company_id' => $data['company_id'] ?? null,
            'asset_code' => $data['asset_code'] ?? 'AST-' . Str::upper(Str::random(6)),
            'name' => $data['name'],
            'domain' => $data['domain'],
            'business_definition' => $data['business_definition'],
            'technical_definition' => $data['technical_definition'] ?? null,
            'system_of_record' => $data['system_of_record'],
            'source_table' => $data['source_table'] ?? null,
            'business_owner' => $data['business_owner'],
            'technical_owner' => $data['technical_owner'],
            'data_steward' => $data['data_steward'] ?? null,
            'security_classification' => $data['security_classification'] ?? 'INTERNAL',
            'freshness_status' => 'FRESH',
            'expected_refresh_seconds' => $data['expected_refresh_seconds'] ?? 86400,
            'last_refreshed_at' => Carbon::now(),
            'current_quality_score' => 100.00,
            'status' => 'ACTIVE',
            'metadata' => $data['metadata'] ?? [],
        ]);
    }

    public function listAssets(string $tenantId, ?string $domain = null): array
    {
        $q = HcmGovAsset::where('tenant_id', $tenantId);
        if ($domain) {
            $q->where('domain', $domain);
        }

        return $q->orderBy('name')->get()->toArray();
    }
}
