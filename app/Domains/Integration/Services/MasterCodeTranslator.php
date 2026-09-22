<?php

declare(strict_types=1);

namespace App\Domains\Integration\Services;

use App\Domains\WorkforceGovernance\Models\HcmGovMasterMapping;

class MasterCodeTranslator
{
    /**
     * Translate an external code to internal HCM standard code.
     */
    public function translate(
        string $tenantId,
        string $entityType,
        string $sourceSystem,
        string $sourceCode,
        string $targetSystem = 'smarthcm'
    ): ?string {
        $mapping = HcmGovMasterMapping::query()
            ->where('tenant_id', $tenantId)
            ->where('entity_type', $entityType)
            ->where('source_system', $sourceSystem)
            ->where('source_code', $sourceCode)
            ->where('target_system', $targetSystem)
            ->where(function ($q) {
                $q->whereNull('mapping_status')->orWhere('mapping_status', 'active');
            })
            ->first();

        return $mapping ? $mapping->target_code : null;
    }

    /**
     * Reverse translate internal HCM code to external system code.
     */
    public function reverseTranslate(
        string $tenantId,
        string $entityType,
        string $targetCode,
        string $targetSystem,
        string $sourceSystem = 'smarthcm'
    ): ?string {
        $mapping = HcmGovMasterMapping::query()
            ->where('tenant_id', $tenantId)
            ->where('entity_type', $entityType)
            ->where('target_code', $targetCode)
            ->where('target_system', $targetSystem)
            ->where('source_system', $sourceSystem)
            ->first();

        return $mapping ? $mapping->source_code : null;
    }

    /**
     * Record or update master code mapping.
     */
    public function recordMapping(
        string $tenantId,
        string $entityType,
        string $sourceSystem,
        string $sourceCode,
        string $targetSystem,
        string $targetCode,
        ?string $sourceId = null,
        ?string $targetId = null
    ): HcmGovMasterMapping {
        return HcmGovMasterMapping::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'entity_type' => $entityType,
                'source_system' => $sourceSystem,
                'source_code' => $sourceCode,
                'target_system' => $targetSystem,
            ],
            [
                'target_code' => $targetCode,
                'source_id' => $sourceId,
                'target_id' => $targetId,
                'mapping_status' => 'active',
                'effective_from' => now()->toDateString(),
            ]
        );
    }
}
