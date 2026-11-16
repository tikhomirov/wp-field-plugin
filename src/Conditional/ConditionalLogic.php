<?php

declare(strict_types=1);

namespace WpField\Conditional;

class ConditionalLogic
{
    /**
     * @param  array<array{field: string, operator: string, value: mixed}>  $conditions
     * @param  array<string, mixed>  $values
     */
    public static function evaluate(array $conditions, array $values, string $relation = 'AND'): bool
    {
        if ($conditions === []) {
            return true;
        }

        $results = [];
        foreach ($conditions as $condition) {
            $results[] = self::evaluateCondition($condition, $values);
        }

        return $relation === 'AND'
            ? ! in_array(false, $results, true)
            : in_array(true, $results, true);
    }

    /**
     * @param  array{field: string, operator: string, value: mixed}  $condition
     * @param  array<string, mixed>  $values
     */
    protected static function evaluateCondition(array $condition, array $values): bool
    {
        $fieldName = $condition['field'];
        $operator = $condition['operator'];
        $expectedValue = $condition['value'];

        $actualValue = $values[$fieldName] ?? null;

        return match ($operator) {
            '==' => $actualValue == $expectedValue,
            '!=' => $actualValue != $expectedValue,
            '===' => $actualValue === $expectedValue,
            '!==' => $actualValue !== $expectedValue,
            '>' => is_numeric($actualValue) && is_numeric($expectedValue) && $actualValue > $expectedValue,
            '>=' => is_numeric($actualValue) && is_numeric($expectedValue) && $actualValue >= $expectedValue,
            '<' => is_numeric($actualValue) && is_numeric($expectedValue) && $actualValue < $expectedValue,
            '<=' => is_numeric($actualValue) && is_numeric($expectedValue) && $actualValue <= $expectedValue,
            'contains' => is_string($actualValue) && is_string($expectedValue) && str_contains($actualValue, $expectedValue),
            'not_contains' => is_string($actualValue) && is_string($expectedValue) && ! str_contains($actualValue, $expectedValue),
            'starts_with' => is_string($actualValue) && is_string($expectedValue) && str_starts_with($actualValue, $expectedValue),
            'ends_with' => is_string($actualValue) && is_string($expectedValue) && str_ends_with($actualValue, $expectedValue),
            'in' => is_array($expectedValue) && in_array($actualValue, $expectedValue, true),
            'not_in' => is_array($expectedValue) && ! in_array($actualValue, $expectedValue, true),
            'empty' => empty($actualValue),
            'not_empty' => ! empty($actualValue),
            default => false,
        };
    }

    /**
     * @param  array<array{field: string, operator: string, value: mixed}>  $conditions
     * @param  array<string, mixed>  $values
     */
    public static function shouldDisplay(array $conditions, array $values, string $relation = 'AND'): bool
    {
        return self::evaluate($conditions, $values, $relation);
    }

    /**
     * @param  array<array{field: string, operator: string, value: mixed}>  $conditions
     * @param  array<string, mixed>  $values
     */
    public static function shouldSave(array $conditions, array $values, string $relation = 'AND'): bool
    {
        return self::evaluate($conditions, $values, $relation);
    }
}
