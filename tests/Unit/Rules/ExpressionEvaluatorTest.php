<?php

namespace Tests\Unit\Rules;

use App\Domains\Rules\Services\ExpressionEvaluator;
use Tests\TestCase;

class ExpressionEvaluatorTest extends TestCase
{
    public function test_it_evaluates_nested_condition_groups_without_executing_code(): void
    {
        $result = (new ExpressionEvaluator())->evaluate(['all' => [['field' => 'data.amount', 'operator' => 'greater_than', 'value' => 100], ['not' => ['field' => 'variables.department', 'operator' => 'equals', 'value' => 'Finance']]]], ['data' => ['amount' => 125], 'variables' => ['department' => 'Sales']]);
        $this->assertTrue($result);
    }
}
