<?php

namespace App\Domains\WorkforceIntelligence\Contracts;

use App\Domains\WorkforceIntelligence\DTOs\AiQueryResultData;

interface WorkforceIntelligenceAiInterface
{
    public function ask(string $prompt, string $tenantId, ?string $userId = null, ?string $departmentId = null): AiQueryResultData;
}
