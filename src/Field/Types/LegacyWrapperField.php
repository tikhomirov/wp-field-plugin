<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class LegacyWrapperField extends AbstractField
{
    /**
     * @var array<string, mixed>
     */
    private array $legacyConfig = [];

    public function __construct(string $name, string $legacyType)
    {
        parent::__construct($name, $legacyType);
    }

    /**
     * Merge additional legacy config options
     *
     * @param  array<string, mixed>  $config
     */
    public function config(array $config): static
    {
        $this->legacyConfig = array_merge($this->legacyConfig, $config);

        return $this;
    }

    public function render(): string
    {
        if (! class_exists('\WP_Field')) {
            return '';
        }

        $config = [
            'id' => $this->name,
            'type' => $this->type,
        ];

        // Map Fluent API properties to Legacy Array config
        if ($this->getAttribute('label')) {
            $config['label'] = $this->getAttribute('label');
        }

        if ($this->getAttribute('description')) {
            $config['desc'] = $this->getAttribute('description');
        }

        if ($this->getAttribute('placeholder')) {
            $config['placeholder'] = $this->getAttribute('placeholder');
        }

        if ($this->getAttribute('default')) {
            $config['default'] = $this->getAttribute('default');
        }

        if ($this->getValue() !== null) {
            $config['value'] = $this->getValue();
        }

        if ($this->isRequired()) {
            $config['required'] = true;
        }

        if ($this->getAttribute('class')) {
            $config['class'] = $this->getAttribute('class');
        }

        // Map conditions
        $dependency = $this->mapConditionsToLegacyDependency();
        if ($dependency !== []) {
            $config['dependency'] = $dependency;
        }

        // Add any explicitly set legacy config
        $config = array_merge($config, $this->legacyConfig);

        // Map specific field properties
        if ($this->type === 'radio' || $this->type === 'select' || $this->type === 'checkbox') {
            $options = $this->getAttribute('options');
            if ($options) {
                $config['options'] = $options;
            }
        }

        if ($this->type === 'accordion') {
            $sections = $this->getAttribute('sections') ?? $this->getAttribute('items');
            if (is_array($sections)) {
                $config['sections'] = $sections;
            }
        }

        if ($this->type === 'tabbed') {
            $tabs = $this->getAttribute('tabs');
            if (is_array($tabs)) {
                $config['tabs'] = $tabs;
            }
        }

        if ($this->type === 'fieldset') {
            $fields = $this->getAttribute('fields');
            if (is_array($fields)) {
                // Convert FieldInterface objects to legacy arrays if needed
                $config['fields'] = array_map(function ($field) {
                    if ($field instanceof \WpField\Field\FieldInterface) {
                        // We would need a toLegacyArray() method, but for now
                        // assume fieldsets are configured via the config() method
                        return $field->toArray();
                    }

                    return $field;
                }, $fields);
            }
        }

        // Render using the legacy API but return as string
        ob_start();
        $legacyField = new \WP_Field($config);
        $legacyField->render();
        $html = ob_get_clean();

        return $html ?: '';
    }

    /**
     * @return array<int|string, mixed>
     */
    private function mapConditionsToLegacyDependency(): array
    {
        if ($this->conditions === []) {
            return [];
        }

        $dependency = [];
        $relation = 'AND';

        $appendCondition = static function (mixed $condition) use (&$dependency, &$relation): void {
            if (! is_array($condition)) {
                return;
            }

            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? null;

            if (! is_string($field) || ! is_string($operator)) {
                return;
            }

            $dependency[] = [$field, $operator, $condition['value'] ?? null];

            $logic = $condition['logic'] ?? 'AND';
            if ($logic === 'OR') {
                $relation = 'OR';
            }
        };

        foreach ($this->conditions as $condition) {
            if (is_array($condition) && isset($condition['field'], $condition['operator'])) {
                $appendCondition($condition);

                continue;
            }

            if (is_array($condition)) {
                foreach ($condition as $nestedCondition) {
                    $appendCondition($nestedCondition);
                }
            }
        }

        if ($dependency === []) {
            return [];
        }

        if ($relation === 'OR' && count($dependency) > 1) {
            $dependency['relation'] = 'OR';
        }

        return $dependency;
    }
}
