<?php

namespace App\Domains\Rules\Listeners;

use App\Domains\Rules\Events\RuleExecuted;
use Illuminate\Support\Facades\Log;

class RecordRuleExecution
{
    public function handle(RuleExecuted $event): void { Log::info('business_rule.executed', ['execution_id' => $event->execution->id, 'rule_id' => $event->execution->business_rule_id, 'tenant_id' => $event->execution->tenant_id, 'status' => $event->execution->status, 'duration_ms' => $event->execution->duration_ms]); }
}
