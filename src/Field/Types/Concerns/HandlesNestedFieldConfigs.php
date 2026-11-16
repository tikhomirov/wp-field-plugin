<?php

declare(strict_types=1);

namespace WpField\Field\Types\Concerns;

use WpField\Field\Field;
use WpField\Field\FieldInterface;

trait HandlesNestedFieldConfigs
{
    /**
     * @return array<int, mixed>
     */
    protected function normalizeNestedFields(mixed $fields): array
    {
        return is_array($fields) ? array_values($fields) : [];
    }

    /**
     * @param  array<int, mixed>  $fields
     */
    protected function renderNestedFields(array $fields): string
    {
        $html = '';

        foreach ($fields as $field) {
            $fieldObject = $this->normalizeNestedFieldObject($field);
            if (! $fieldObject instanceof FieldInterface) {
                continue;
            }

            $html .= $fieldObject->render();
        }

        return $html;
    }

    protected function normalizeNestedFieldObject(mixed $field): ?FieldInterface
    {
        if ($field instanceof FieldInterface) {
            return clone $field;
        }

        if (! is_array($field)) {
            return null;
        }

        $type = isset($field['type']) && is_scalar($field['type']) ? (string) $field['type'] : '';
        $name = isset($field['name']) && is_scalar($field['name']) ? (string) $field['name'] : '';

        if ($name === '') {
            $name = isset($field['id']) && is_scalar($field['id']) ? (string) $field['id'] : '';
        }

        if ($type === '' || $name === '') {
            return null;
        }

        $fieldObject = Field::make($type, $name);

        foreach ($field as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if ($key === 'type' || $key === 'id' || $key === 'name') {
                continue;
            }

            if ($key === 'value') {
                $fieldObject->value($value);

                continue;
            }

            $fieldObject->attribute($key, $value);
        }

        return $fieldObject;
    }
}
