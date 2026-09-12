<?php

namespace App\Domains\WorkforceGovernance\Contracts;

use App\Domains\WorkforceGovernance\Models\HcmGovAsset;
use App\Domains\WorkforceGovernance\Models\HcmGovQualityIssue;
use App\Domains\WorkforceGovernance\Models\HcmGovQualityRun;

interface WorkforceDataGovernanceInterface
{
    public function getGovernanceDashboard(string $tenantId): array;

    public function registerAsset(array $data): HcmGovAsset;

    public function executeQualityRun(string $tenantId, ?string $domain = null): HcmGovQualityRun;

    public function assignIssue(string $issueId, string $stewardId): HcmGovQualityIssue;

    public function resolveIssue(string $issueId, string $userId, ?string $explanation = null): HcmGovQualityIssue;

    public function getLineageGraph(string $tenantId, string $nodeCode): array;
}
