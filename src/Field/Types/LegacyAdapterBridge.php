<?php

declare(strict_types=1);

namespace WpField\Field\Types;

trait LegacyAdapterBridge
{
    protected function renderViaLegacy(string $legacyType): string
    {
        if (! class_exists('\WP_Field')) {
            return '';
        }

        $wrapper = new LegacyWrapperField($this->name, $legacyType);
        $wrapper->value($this->getValue());

        if ($this->isRequired()) {
            $wrapper->required();
        }

        foreach ($this->attributes as $key => $value) {
            $wrapper->attribute($key, $value);
        }

        foreach ($this->conditions as $condition) {
            if (is_array($condition) && isset($condition['field'], $condition['operator'])) {
                $this->applyConditionToWrapper($wrapper, $condition);

                continue;
            }

            if (is_array($condition)) {
                foreach ($condition as $nestedCondition) {
                    $this->applyConditionToWrapper($wrapper, $nestedCondition);
                }
            }
        }

        return $wrapper->render();
    }

    private function applyConditionToWrapper(LegacyWrapperField $wrapper, mixed $condition): void
    {
        if (! is_array($condition)) {
            return;
        }

        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? null;

        if (! is_string($field) || ! is_string($operator)) {
            return;
        }

        $value = $condition['value'] ?? null;
        $logic = $condition['logic'] ?? 'AND';

        if ($logic === 'OR') {
            $wrapper->orWhen($field, $operator, $value);

            return;
        }

        $wrapper->when($field, $operator, $value);
    }
}
