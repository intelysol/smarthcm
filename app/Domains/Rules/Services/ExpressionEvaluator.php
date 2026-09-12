<?php

namespace App\Domains\Rules\Services;

class ExpressionEvaluator
{
    /** @param array<string, mixed> $node @param array<string, mixed> $context */
    public function evaluate(array $node, array $context): bool
    {
        if (isset($node['all'])) { foreach ($node['all'] as $child) { if (! $this->evaluate($child, $context)) { return false; } } return true; }
        if (isset($node['any'])) { foreach ($node['any'] as $child) { if ($this->evaluate($child, $context)) { return true; } } return false; }
        if (isset($node['not'])) { return ! $this->evaluate($node['not'], $context); }
        $left = array_key_exists('formula', $node) ? $this->formula($node['formula'], $context) : $this->resolve($node['field'] ?? '', $context);
        $right = array_key_exists('value_from', $node) ? $this->resolve($node['value_from'], $context) : (array_key_exists('value_formula', $node) ? $this->formula($node['value_formula'], $context) : ($node['value'] ?? null));
        return match ($node['operator'] ?? 'equals') {
            'equals' => $left === $right, 'not_equals' => $left !== $right,
            'greater_than' => $left !== null && $right !== null && $left > $right, 'less_than' => $left !== null && $right !== null && $left < $right,
            'between' => is_array($right) && $left !== null && $left >= ($right[0] ?? null) && $left <= ($right[1] ?? null),
            'contains' => is_string($left) ? str_contains($left, (string) $right) : (is_array($left) && in_array($right, $left, true)),
            'starts_with' => is_string($left) && str_starts_with($left, (string) $right), 'ends_with' => is_string($left) && str_ends_with($left, (string) $right),
            'is_empty' => $left === null || $left === '' || $left === [], 'is_not_empty' => $left !== null && $left !== '' && $left !== [],
            'in_list' => is_array($right) && in_array($left, $right, true), 'not_in_list' => is_array($right) && ! in_array($left, $right, true),
            'matches' => is_string($left) && is_string($right) && @preg_match($right, $left) === 1, default => false,
        };
    }
    /** @param array<string, mixed> $context */
    private function resolve(string $path, array $context): mixed { $value = $context; foreach (explode('.', $path) as $segment) { if (! is_array($value) || ! array_key_exists($segment, $value)) { return null; } $value = $value[$segment]; } return $value; }

    private function formula(mixed $expression, array $context): mixed
    {
        if (is_scalar($expression) || $expression === null) return $expression;
        if (is_string($expression)) return $this->resolve($expression, $context);
        $name = strtolower((string) ($expression['function'] ?? ''));
        $args = array_map(fn (mixed $arg): mixed => $this->formula($arg, $context), $expression['args'] ?? []);
        return match ($name) {
            'add' => ($args[0] ?? 0) + ($args[1] ?? 0), 'subtract' => ($args[0] ?? 0) - ($args[1] ?? 0), 'multiply' => ($args[0] ?? 0) * ($args[1] ?? 0),
            'divide' => ($args[1] ?? 0) == 0 ? null : ($args[0] ?? 0) / $args[1], 'lower' => strtolower((string) ($args[0] ?? '')), 'upper' => strtoupper((string) ($args[0] ?? '')),
            'length' => is_countable($args[0] ?? null) ? count($args[0]) : strlen((string) ($args[0] ?? '')), 'contains' => is_string($args[0] ?? null) && str_contains($args[0], (string) ($args[1] ?? '')),
            'coalesce' => collect($args)->first(fn (mixed $value): bool => $value !== null), 'in' => in_array($args[0] ?? null, $args[1] ?? [], true), default => null,
        };
    }
}
