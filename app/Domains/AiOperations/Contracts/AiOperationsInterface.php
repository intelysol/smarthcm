<?php

namespace App\Domains\AiOperations\Contracts;

use App\Domains\AiOperations\Models\HcmAiEvalRun;
use App\Domains\AiOperations\Models\HcmAiFeedback;
use App\Domains\AiOperations\Models\HcmAiImprovementItem;
use App\Domains\AiOperations\Models\HcmAiInteractionTelemetry;
use App\Domains\AiOperations\Models\HcmAiProductionReadiness;

interface AiOperationsInterface
{
    public function recordTelemetry(array $data): HcmAiInteractionTelemetry;

    public function recordFeedback(array $data): HcmAiFeedback;

    public function getOperationsDashboard(string $tenantId, ?string $period = '30d'): array;

    public function executeEvaluationRun(string $tenantId, string $datasetId, string $modelCode, ?string $userId = null): HcmAiEvalRun;

    public function detectRegressions(string $tenantId, string $evalRunId): array;

    public function createImprovementItem(array $data): HcmAiImprovementItem;

    public function calculateProductionReadiness(string $tenantId, string $useCaseCode): HcmAiProductionReadiness;

    public function checkBudgetStatus(string $tenantId, string $scope = 'TENANT', string $targetIdentifier = 'ALL'): array;

    public function compareModelPerformance(string $tenantId): array;
}
