<?php

declare(strict_types=1);

namespace WpField\Legacy;

use WpField\Field\Field;
use WpField\Field\FieldInterface;

class LegacyAdapter
{
    /**
     * @param  array<string, mixed>  $config
     */
    public static function make(array $config): FieldInterface
    {
        $type = 'text';
        if (isset($config['type']) && is_string($config['type'])) {
            $type = $config['type'];
        }

        $name = '';
        if (isset($config['id']) && is_string($config['id'])) {
            $name = $config['id'];
        } elseif (isset($config['name']) && is_string($config['name'])) {
            $name = $config['name'];
        }

        if ($name === '' || $name === '0') {
            throw new \InvalidArgumentException('Field name/id is required');
        }

        $field = Field::make($type, $name);

        if (isset($config['label']) && is_string($config['label'])) {
            $field->label($config['label']);
        }

        if (isset($config['title']) && is_string($config['title']) && ! isset($config['label'])) {
            $field->label($config['title']);
        }

        if (isset($config['placeholder']) && is_string($config['placeholder'])) {
            $field->placeholder($config['placeholder']);
        }

        if (isset($config['description']) && is_string($config['description'])) {
            $field->description($config['description']);
        } elseif (isset($config['desc']) && is_string($config['desc'])) {
            $field->description($config['desc']);
        }

        if (isset($config['default'])) {
            $field->default($config['default']);
        }

        if (isset($config['value']) || isset($config['val'])) {
            $value = $config['value'] ?? $config['val'];
            $field->value($value);
        }

        if (isset($config['class']) && is_string($config['class'])) {
            $field->class($config['class']);
        }

        if (isset($config['required']) && $config['required']) {
            $field->required();
        }

        if (isset($config['readonly']) && $config['readonly']) {
            $field->readonly();
        }

        if (isset($config['disabled']) && $config['disabled']) {
            $field->disabled();
        }

        if (isset($config['attributes']) || isset($config['attr']) || isset($config['atts'])) {
            $attrs = $config['attributes'] ?? $config['attr'] ?? $config['atts'];
            if (is_array($attrs)) {
                foreach ($attrs as $key => $value) {
                    $field->attribute((string) $key, $value);
                }
            }
        }

        if (isset($config['validation'])) {
            $validation = $config['validation'];
            if (is_array($validation) || is_string($validation)) {
                self::applyValidation($field, $validation);
            }
        }

        if (isset($config['conditional_logic']) || isset($config['conditions'])) {
            $conditions = $config['conditional_logic'] ?? $config['conditions'];
            if (is_array($conditions)) {
                self::applyConditionalLogic($field, $conditions);
            }
        }

        return $field;
    }

    /**
     * @param  array<string, mixed>|string  $validation
     */
    protected static function applyValidation(FieldInterface $field, array|string $validation): void
    {
        if (is_string($validation)) {
            $rules = explode('|', $validation);
            foreach ($rules as $rule) {
                self::applyValidationRule($field, $rule);
            }
        } elseif (is_array($validation)) {
            foreach ($validation as $rule => $value) {
                if (is_numeric($rule)) {
                    if (is_string($value)) {
                        self::applyValidationRule($field, $value);
                    }
                } else {
                    self::applyValidationRule($field, $rule, $value);
                }
            }
        }
    }

    protected static function applyValidationRule(FieldInterface $field, string $rule, mixed $value = true): void
    {
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $ruleValue = $parts[1] ?? $value;

        match ($ruleName) {
            'required' => $field->required(),
            'email' => $field->email(),
            'url' => $field->url(),
            'min' => is_numeric($ruleValue) ? $field->min((int) $ruleValue) : null,
            'max' => is_numeric($ruleValue) ? $field->max((int) $ruleValue) : null,
            'pattern' => is_string($ruleValue) ? $field->pattern($ruleValue) : null,
            default => null,
        };
    }

    /**
     * @param  array<array<string, mixed>>  $conditions
     */
    protected static function applyConditionalLogic(FieldInterface $field, array $conditions): void
    {
        foreach ($conditions as $condition) {
            $targetField = is_string($condition['field'] ?? null) ? $condition['field'] : '';
            $operator = is_string($condition['operator'] ?? null) ? $condition['operator'] : '==';
            $value = $condition['value'] ?? '';

            if ($targetField !== '' && $targetField !== '0') {
                $field->when($targetField, $operator, $value);
            }
        }
    }

    /**
     * @param  array<array<string, mixed>>  $fields
     * @return array<FieldInterface>
     */
    public static function makeMultiple(array $fields): array
    {
        $result = [];
        foreach ($fields as $fieldConfig) {
            if (is_array($fieldConfig)) {
                $result[] = self::make($fieldConfig);
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function render(array $config): string
    {
        $field = self::make($config);

        return $field->render();
    }

    /**
     * @param  array<array<string, mixed>>  $fields
     */
    public static function renderMultiple(array $fields): string
    {
        $html = '';
        foreach ($fields as $fieldConfig) {
            if (is_array($fieldConfig)) {
                $html .= self::render($fieldConfig);
            }
        }

        return $html;
    }
}
