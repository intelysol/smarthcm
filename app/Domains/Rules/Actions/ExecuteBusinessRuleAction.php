<?php

namespace App\Domains\Rules\Actions;

use App\Domains\Rules\DTOs\RuleExecutionContext;
use App\Domains\Rules\Models\BusinessRule;
use App\Domains\Rules\Services\RuleEngine;

class ExecuteBusinessRuleAction
{
    public function __construct(private readonly RuleEngine $engine) {}
    /** @return array<string, mixed> */
    public function execute(BusinessRule $rule, RuleExecutionContext $context, bool $dryRun = false): array { return $this->engine->execute($rule, $context, $dryRun); }
}
