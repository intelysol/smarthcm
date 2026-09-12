<?php

namespace App\Domains\Rules\Events;

use App\Domains\Rules\Models\RuleExecution;
use Illuminate\Foundation\Events\Dispatchable;

class RuleExecuted
{
    use Dispatchable;
    public function __construct(public readonly RuleExecution $execution) {}
}
