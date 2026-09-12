<?php

namespace App\Domains\WorkforceGovernance\Services;

use App\Domains\WorkforceGovernance\Models\HcmGovMasterMapping;
use App\Domains\WorkforceGovernance\Models\HcmGovReconciliation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterDataReconciliationService
{
    public function createMapping(array $data): HcmGovMasterMapping
    {
        return HcmGovMasterMapping::updateOrCreate(
            [
                'tenant_id' => $data['tenant_id'],
                'entity_type' => $data['entity_type'],
                'source_system' => $data['source_system'],
                'source_code' => $data['source_code'],
            ],
            [
                'source_id' => $data['source_id'] ?? $data['source_code'],
                'target_system' => $data['target_system'] ?? 'HCM_CORE',
                'target_id' => $data['target_id'],
                'target_code' => $data['target_code'],
                'mapping_status' => $data['mapping_status'] ?? 'MAPPED',
                'effective_from' => $data['effective_from'] ?? Carbon::now()->toDateString(),
                'effective_to' => $data['effective_to'] ?? null,
                'verified_by_user_id' => $data['verified_by_user_id'] ?? null,
            ]
        );
    }

    public function reconcileEmployeeHeadcount(string $tenantId, array $externalRecords): HcmGovReconciliation
    {
        $hcmEmployees = DB::table('employees')
            ->where('tenant_id', $tenantId)
            ->where('employment_status', 'ACTIVE')
            ->get(['id', 'employee_code', 'employee_number']);

        $sourceCount = $hcmEmployees->count();
        $targetCount = count($externalRecords);
        $matched = 0;
        $unmatched = 0;
        $conflicts = 0;
        $discrepancies = [];

        $hcmCodes = $hcmEmployees->pluck('id', 'employee_code')->toArray();

        foreach ($externalRecords as $ext) {
            $extCode = $ext['employee_code'] ?? null;
            if ($extCode && isset($hcmCodes[$extCode])) {
                $matched++;
            } else {
                $unmatched++;
                $discrepancies[] = [
                    'type' => 'UNMATCHED_EXTERNAL_EMPLOYEE',
                    'external_code' => $extCode,
                    'external_name' => $ext['name'] ?? 'Unknown',
                    'reason' => 'Record present in external payroll/ERP but missing or inactive in HCM Core',
                ];
            }
        }

        $hcmUnmatched = $sourceCount - $matched;
        if ($hcmUnmatched > 0) {
            $discrepancies[] = [
                'type' => 'UNMATCHED_HCM_EMPLOYEE',
                'count' => $hcmUnmatched,
                'reason' => 'Active HCM employees not reported in external system payload',
            ];
        }

        $status = ($unmatched === 0 && $hcmUnmatched === 0) ? 'BALANCED' : 'DISCREPANCY_DETECTED';

        return HcmGovReconciliation::create([
            'tenant_id' => $tenantId,
            'reconciliation_code' => 'REC-EMP-' . Carbon::now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            'name' => 'Authoritative Core HR vs Payroll Headcount Reconciliation',
            'source_domain' => 'CORE_HR',
            'target_domain' => 'PAYROLL_EXTERNAL',
            'comparison_entity' => 'EMPLOYEE_HEADCOUNT',
            'source_record_count' => $sourceCount,
            'target_record_count' => $targetCount,
            'matched_count' => $matched,
            'unmatched_count' => $unmatched + $hcmUnmatched,
            'conflict_count' => $conflicts,
            'variance_amount' => 0.0,
            'status' => $status,
            'discrepancy_details' => $discrepancies,
            'reconciled_at' => Carbon::now(),
        ]);
    }
}
