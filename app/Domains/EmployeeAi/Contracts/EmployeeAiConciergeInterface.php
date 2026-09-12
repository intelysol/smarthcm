<?php

namespace App\Domains\EmployeeAi\Contracts;

use App\Domains\EmployeeAi\Models\HcmAiConciergeAction;
use App\Domains\EmployeeAi\Models\HcmAiConciergeMessage;
use App\Domains\EmployeeAi\Models\HcmAiConciergeSession;

interface EmployeeAiConciergeInterface
{
    public function startSession(string $tenantId, string $userId, ?string $employeeId = null, string $persona = 'EMPLOYEE'): HcmAiConciergeSession;

    public function chat(string $sessionId, string $prompt, string $tenantId, string $userId, ?string $employeeId = null): array;

    public function confirmAction(string $actionId, string $userId, ?string $comment = null): HcmAiConciergeAction;

    public function getMyHrSummary(string $tenantId, string $employeeId): array;

    public function getProactiveSuggestions(string $tenantId, string $employeeId): array;
}
