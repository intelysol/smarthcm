<?php

namespace App\Domains\ResponsibleAi\Contracts;

use App\Domains\ResponsibleAi\Models\HcmAiGovAssessment;
use App\Domains\ResponsibleAi\Models\HcmAiGovIncident;
use App\Domains\ResponsibleAi\Models\HcmAiGovKillSwitch;
use App\Domains\ResponsibleAi\Models\HcmAiGovModel;
use App\Domains\ResponsibleAi\Models\HcmAiGovUseCase;

interface ResponsibleAiGovernanceInterface
{
    public function registerUseCase(array $data): HcmAiGovUseCase;

    public function registerModel(array $data): HcmAiGovModel;

    public function createImpactAssessment(array $data): HcmAiGovAssessment;

    public function triggerKillSwitch(string $tenantId, string $scope, string $targetIdentifier, string $reason, string $userId): HcmAiGovKillSwitch;

    public function checkExecutionEligibility(string $tenantId, string $useCaseCode, ?string $modelCode = null): array;

    public function logIncident(array $data): HcmAiGovIncident;

    public function getGovernanceDashboard(string $tenantId): array;
}
