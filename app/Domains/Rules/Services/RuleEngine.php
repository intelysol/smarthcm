<?php

namespace App\Domains\Rules\Services;

use App\Domains\Rules\DTOs\RuleExecutionContext;
use App\Domains\Rules\Events\RuleExecuted;
use App\Domains\Rules\Models\BusinessRule;
use App\Domains\Rules\Models\RuleExecution;
use Illuminate\Support\Carbon;

class RuleEngine
{
    public function __construct(private readonly ExpressionEvaluator $expressions) {}
    /** @return array<string, mixed> */
    public function execute(BusinessRule $rule, RuleExecutionContext $context, bool $dryRun = false): array
    {
        $started = hrtime(true);
        $trace = [];
        try {
            $matched = $this->expressions->evaluate($rule->conditions, ['data' => $context->data, 'variables' => $context->variables]);
            $trace[] = ['stage' => 'conditions', 'matched' => $matched];
            $output = ['matched' => $matched, 'changes' => [], 'intents' => []];
            if ($matched) { foreach ($rule->actions as $action) { $this->applyAction($action, $output, $context, $trace); } }
            $execution = $this->record($rule, $context, 'succeeded', $output, $trace, $started, $dryRun ? 'dry_run' : $rule->execution_mode);
            RuleExecuted::dispatch($execution);
            return $output + ['execution_id' => $execution->id, 'trace' => $trace];
        } catch (\Throwable $exception) {
            $execution = $this->record($rule, $context, 'failed', [], $trace, $started, $dryRun ? 'dry_run' : $rule->execution_mode, $exception->getMessage());
            RuleExecuted::dispatch($execution);
            throw $exception;
        }
    }
    /** @param array<string, mixed> $action @param array<string, mixed> $output @param list<array<string, mixed>> $trace */
    private function applyAction(array $action, array &$output, RuleExecutionContext $context, array &$trace): void
    {
        $type = $action['type'] ?? 'log_event';
        if ($type === 'set_field_value') { $output['changes'][$action['field']] = $action['value'] ?? null; }
        elseif (in_array($type, ['show_field', 'hide_field', 'enable_field', 'disable_field', 'require_field', 'remove_requirement'], true)) { $output['changes']['ui'][] = $action; }
        elseif ($type === 'raise_exception') { throw new \DomainException((string) ($action['message'] ?? 'Rule rejected execution.')); }
        else { $output['intents'][] = $action; }
        $trace[] = ['stage' => 'action', 'type' => $type];
    }
    /** @param array<string, mixed> $output @param list<array<string, mixed>> $trace */
    private function record(BusinessRule $rule, RuleExecutionContext $context, string $status, array $output, array $trace, int $started, string $mode, ?string $error = null): RuleExecution
    {
        return RuleExecution::query()->create(['tenant_id' => $context->tenantId, 'business_rule_id' => $rule->id, 'subject_type' => $context->subjectType, 'subject_id' => $context->subjectId, 'status' => $status, 'execution_mode' => $mode, 'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000), 'input' => ['data' => $context->data, 'variables' => $context->variables], 'output' => $output, 'trace' => $trace, 'error_message' => $error, 'correlation_id' => $context->correlationId, 'executed_at' => Carbon::now()]);
    }
}
