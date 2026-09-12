<?php

namespace App\Domains\WorkforceGovernance\Services;

use App\Domains\WorkforceGovernance\Models\HcmGovContract;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DataContractGovernanceService
{
    public function publishContract(array $data): HcmGovContract
    {
        return HcmGovContract::updateOrCreate(
            [
                'tenant_id' => $data['tenant_id'],
                'contract_code' => $data['contract_code'],
            ],
            [
                'name' => $data['name'],
                'producer_module' => $data['producer_module'],
                'consumer_modules' => $data['consumer_modules'] ?? [],
                'version' => $data['version'] ?? 1,
                'schema_contract' => $data['schema_contract'] ?? [],
                'status' => 'HEALTHY',
                'last_validated_at' => Carbon::now(),
                'breaking_change_notes' => $data['breaking_change_notes'] ?? null,
            ]
        );
    }

    public function validateCompatibility(string $tenantId, string $contractCode, array $incomingPayload): array
    {
        $contract = HcmGovContract::where('tenant_id', $tenantId)->where('contract_code', $contractCode)->first();
        if (!$contract) {
            return ['status' => 'CONTRACT_NOT_FOUND', 'compatible' => false];
        }

        $schema = $contract->schema_contract['required_fields'] ?? [];
        $missing = [];

        foreach ($schema as $field) {
            if (!array_key_exists($field, $incomingPayload)) {
                $missing[] = $field;
            }
        }

        $isCompatible = count($missing) === 0;

        return [
            'status' => $isCompatible ? 'COMPATIBLE' : 'BREAKING_CHANGE_DETECTED',
            'compatible' => $isCompatible,
            'missing_required_fields' => $missing,
            'contract_version' => $contract->version,
        ];
    }
}
