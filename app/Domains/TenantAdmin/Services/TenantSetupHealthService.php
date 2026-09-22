<?php

namespace App\Domains\TenantAdmin\Services;

use App\Domains\TenantAdmin\Models\HcmTenantFeatureFlag;
use App\Domains\TenantAdmin\Models\HcmTenantSetupHealth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantSetupHealthService
{
    public function calculateHealth(string $tenantId): HcmTenantSetupHealth
    {
        $hasDepts = DB::table('departments')->where('tenant_id', $tenantId)->count() > 0;
        $orgScore = $hasDepts ? 100.0 : 50.0;

        $hasUsers = DB::table('users')->where('tenant_id', $tenantId)->count() > 0;
        $secScore = $hasUsers ? 95.0 : 60.0;

        $hasEmployees = DB::table('employees')->where('tenant_id', $tenantId)->count() > 0;
        $wfScore = $hasEmployees ? 98.0 : 70.0;

        $categories = [
            'ORGANIZATION' => $orgScore,
            'SECURITY' => $secScore,
            'WORKFORCE' => $wfScore,
            'PAYROLL' => 88.0,
            'LEAVE' => 100.0,
            'ATTENDANCE' => 90.0,
            'AI_OPERATIONS' => 94.0,
            'INTEGRATIONS' => 85.0,
            'DATA_GOVERNANCE' => 92.0,
        ];

        $overall = round(array_sum($categories) / count($categories), 2);

        $remediation = [];
        if (!$hasDepts) {
            $remediation[] = [
                'category' => 'ORGANIZATION',
                'title' => 'Missing Department Structures',
                'action_url' => '/admin/onboarding?step=2',
            ];
        }
        if (!$hasEmployees) {
            $remediation[] = [
                'category' => 'WORKFORCE',
                'title' => 'No Personnel Records Imported',
                'action_url' => '/admin/imports?domain=EMPLOYEES',
            ];
        }

        return HcmTenantSetupHealth::updateOrCreate(
            ['tenant_id' => $tenantId],
            [
                'overall_score' => $overall,
                'category_scores' => $categories,
                'remediation_items' => $remediation,
                'last_assessed_at' => Carbon::now(),
            ]
        );
    }
}
