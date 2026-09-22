<?php

declare(strict_types=1);

namespace App\Domains\Integration\Services;

use App\Domains\Integration\Models\IntegrationMapping;
use App\Domains\Rules\Services\ExpressionEvaluator;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Throwable;

class MappingEngine
{
    public function __construct(
        protected MasterCodeTranslator $masterCodeTranslator,
        protected ExpressionEvaluator $expressionEvaluator
    ) {}

    /**
     * Transform a payload according to an IntegrationMapping definition.
     */
    public function transform(
        array $sourceData,
        IntegrationMapping $mapping,
        string $direction = 'inbound'
    ): array {
        $fieldMappings = $mapping->mapping['field_mappings'] ?? $mapping->mapping ?? [];
        $transformRules = $mapping->validation_rules['transform_rules'] ?? $mapping->mapping['transform_rules'] ?? [];
        $tenantId = $mapping->connection?->tenant_id ?? 'global';
        $connection = $mapping->connection;
        $sourceSystem = $connection?->connector?->key ?? 'external';

        $result = [];

        foreach ($fieldMappings as $targetField => $rule) {
            // Rule can be a simple string (source field name) or an array with details
            if (is_string($rule)) {
                $value = Arr::get($sourceData, $rule);
                Arr::set($result, $targetField, $value);
                continue;
            }

            if (!is_array($rule)) {
                continue;
            }

            $sourceKey = $rule['source_field'] ?? $rule['source'] ?? $targetField;
            $value = Arr::get($sourceData, $sourceKey, $rule['default'] ?? null);

            // Apply field-level transforms
            $transform = $rule['transform'] ?? null;
            if ($transform) {
                $value = $this->applyTransform($value, $transform, $sourceData, $tenantId, $sourceSystem, $targetField);
            }

            // Apply master code mapping if specified
            if (!empty($rule['master_code_entity'])) {
                $entityType = $rule['master_code_entity'];
                $translated = $direction === 'inbound'
                    ? $this->masterCodeTranslator->translate($tenantId, $entityType, $sourceSystem, (string) $value)
                    : $this->masterCodeTranslator->reverseTranslate($tenantId, $entityType, (string) $value, $sourceSystem);

                if ($translated !== null) {
                    $value = $translated;
                }
            }

            // Apply default if null
            if ($value === null && array_key_exists('default', $rule)) {
                $value = $rule['default'];
            }

            Arr::set($result, $targetField, $value);
        }

        // Apply additional transform rules (conditions, formulas)
        foreach ($transformRules as $ruleDef) {
            if (isset($ruleDef['when'])) {
                $conditionMet = $this->expressionEvaluator->evaluate($ruleDef['when'], ['data' => $result]);
                if (!$conditionMet) {
                    continue;
                }
            }

            if (isset($ruleDef['set'])) {
                foreach ($ruleDef['set'] as $setField => $setValue) {
                    Arr::set($result, $setField, $setValue);
                }
            }
        }

        return $result;
    }

    /**
     * Apply individual transformation rule.
     */
    protected function applyTransform(
        mixed $value,
        string|array $transform,
        array $context,
        string $tenantId,
        string $sourceSystem,
        string $targetField
    ): mixed {
        if ($value === null) {
            return null;
        }

        if (is_string($transform)) {
            if ($transform === 'lowercase' || $transform === 'lower') {
                return strtolower((string) $value);
            }
            if ($transform === 'uppercase' || $transform === 'upper') {
                return strtoupper((string) $value);
            }
            if ($transform === 'trim') {
                return trim((string) $value);
            }
            if ($transform === 'integer' || $transform === 'int') {
                return (int) $value;
            }
            if ($transform === 'float') {
                return (float) $value;
            }
            if ($transform === 'boolean' || $transform === 'bool') {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
            if (str_starts_with($transform, 'date:')) {
                $format = substr($transform, 5);
                try {
                    return Carbon::parse((string) $value)->format($format);
                } catch (Throwable) {
                    return $value;
                }
            }
        }

        if (is_array($transform) && isset($transform['formula'])) {
            try {
                return $this->expressionEvaluator->evaluate($transform['formula'], ['data' => $context]);
            } catch (Throwable) {
                return $value;
            }
        }

        return $value;
    }
}
