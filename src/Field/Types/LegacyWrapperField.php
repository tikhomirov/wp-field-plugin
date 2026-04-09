<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;
use WpField\Field\FieldInterface;

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
     * Merge additional vanilla config options
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
        $config = $this->buildLegacyConfig();

        $legacyHtml = $this->renderWithLegacyApi($config);
        if ($legacyHtml !== '') {
            return $legacyHtml;
        }

        return $this->renderGenericFallback($config);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLegacyConfig(): array
    {
        $config = [
            'id' => $this->name,
            'name' => $this->name,
            'type' => $this->type,
        ];

        $config = array_merge($config, $this->attributes, $this->legacyConfig);

        if ($this->getValue() !== null && ! array_key_exists('value', $config)) {
            $config['value'] = $this->getValue();
        }

        if ($this->isRequired()) {
            $config['required'] = true;
        }

        if (array_key_exists('description', $config) && ! array_key_exists('desc', $config)) {
            $config['desc'] = $config['description'];
        }

        $dependency = $this->mapConditionsToLegacyDependency();
        if ($dependency !== [] && ! array_key_exists('dependency', $config)) {
            $config['dependency'] = $dependency;
        }

        if (isset($config['fields']) && is_array($config['fields'])) {
            $config['fields'] = array_map(function ($field) {
                if ($field instanceof FieldInterface) {
                    return $field->toArray();
                }

                return $field;
            }, $config['fields']);
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function renderWithLegacyApi(array $config): string
    {
        if (! class_exists('\WP_Field')) {
            return '';
        }

        $hasRuntimeError = false;

        ob_start();

        set_error_handler(static function () use (&$hasRuntimeError): bool {
            $hasRuntimeError = true;

            return true;
        });

        try {
            $legacyField = new \WP_Field($config);
            $legacyField->render();
        } catch (\Throwable) {
            $hasRuntimeError = true;
        } finally {
            restore_error_handler();
        }

        $html = trim((string) ob_get_clean());

        if ($hasRuntimeError) {
            return '';
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function renderGenericFallback(array $config): string
    {
        $id = isset($config['id']) && is_scalar($config['id']) ? (string) $config['id'] : $this->name;
        $name = isset($config['name']) && is_scalar($config['name']) ? (string) $config['name'] : $id;
        $label = isset($config['label']) && is_scalar($config['label']) ? (string) $config['label'] : '';
        $description = isset($config['desc']) && is_scalar($config['desc'])
            ? (string) $config['desc']
            : (isset($config['description']) && is_scalar($config['description']) ? (string) $config['description'] : '');
        $placeholder = isset($config['placeholder']) && is_scalar($config['placeholder']) ? (string) $config['placeholder'] : '';
        $value = $config['value'] ?? null;
        $required = empty($config['required']) ? '' : ' required';
        $readonly = empty($config['readonly']) ? '' : ' readonly';
        $disabled = empty($config['disabled']) ? '' : ' disabled';
        $class = isset($config['class']) && is_scalar($config['class']) ? (string) $config['class'] : '';

        $html = sprintf(
            '<div class="wp-field-vanilla-fallback" data-vanilla-fallback="1" data-vanilla-type="%s">',
            esc_attr($this->type),
        );

        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', esc_attr($id), esc_html($label));
        }

        $options = $config['options'] ?? null;
        if (is_array($options) && $options !== []) {
            $multiple = ! empty($config['multiple']);
            $currentValues = is_array($value) ? $value : [$value];
            $selectName = $multiple ? $name.'[]' : $name;

            $html .= sprintf('<select id="%s" name="%s" class="%s"%s%s%s%s>', esc_attr($id), esc_attr($selectName), esc_attr($class), $multiple ? ' multiple' : '', $required, $readonly, $disabled);
            foreach ($options as $optionValue => $optionLabel) {
                $isSelected = in_array((string) $optionValue, array_map(static fn ($v) => is_scalar($v) ? (string) $v : '', $currentValues), true)
                    ? ' selected'
                    : '';
                $html .= sprintf('<option value="%s"%s>%s</option>', esc_attr((string) $optionValue), $isSelected, esc_html(is_scalar($optionLabel) ? (string) $optionLabel : (string) $optionValue));
            }
            $html .= '</select>';
        } else {
            $inputValue = is_scalar($value) ? (string) $value : '';
            $inputClass = trim('regular-text '.$class);

            $html .= sprintf(
                '<input type="text" id="%s" name="%s" value="%s" class="%s" placeholder="%s"%s%s%s>',
                esc_attr($id),
                esc_attr($name),
                esc_attr($inputValue),
                esc_attr($inputClass),
                esc_attr($placeholder),
                $required,
                $readonly,
                $disabled,
            );
        }

        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        $html .= '</div>';

        return $html;
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
