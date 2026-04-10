<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class SorterField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'sorter');
    }

    /**
     * @param  array<int|string, mixed>  $options
     */
    public function options(array $options): static
    {
        return $this->attribute('options', $options);
    }

    /**
     * @param  array<int|string, mixed>  $groups
     */
    public function groups(array $groups): static
    {
        return $this->attribute('groups', $groups);
    }

    public function render(): string
    {
        $options = $this->normalizeOptions($this->getAttribute('options', []));
        if ($options === []) {
            return '<p class="description">No options provided</p>';
        }

        $columns = $this->resolveColumns();
        $sortedColumns = $this->resolveSortedColumns($columns, $options, $this->getValue());
        $name = esc_attr($this->name);

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $html .= '<div class="wp-field-sorter">';

        foreach ($sortedColumns as $columnKey => $column) {
            $html .= '<div class="wp-field-sorter-column">';
            $html .= sprintf('<h4>%s</h4>', esc_html($column['label']));
            $html .= sprintf('<ul class="wp-field-sorter-list" data-type="%s">', esc_attr($columnKey));

            foreach ($column['items'] as $itemKey => $itemLabel) {
                $html .= sprintf(
                    '<li data-value="%s"><span class="dashicons dashicons-menu"></span><span>%s</span><input type="hidden" name="%s[%s][]" value="%s"></li>',
                    esc_attr($itemKey),
                    esc_html($itemLabel),
                    $name,
                    esc_attr($columnKey),
                    esc_attr($itemKey),
                );
            }

            $html .= '</ul></div>';
        }

        $html .= '</div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return [];
        }

        $sanitized = [];
        foreach ($value as $group => $items) {
            if (! is_string($group) || ! is_array($items)) {
                continue;
            }

            $sanitized[$group] = [];
            foreach ($items as $item) {
                if (! is_scalar($item)) {
                    continue;
                }

                $sanitized[$group][] = sanitize_text_field((string) $item);
            }

            $sanitized[$group] = array_values(array_unique($sanitized[$group]));
        }

        return $sanitized;
    }

    /**
     * @return array<string, string>
     */
    private function normalizeOptions(mixed $options): array
    {
        if (! is_array($options)) {
            return [];
        }

        $normalized = [];
        foreach ($options as $key => $label) {
            if (! is_scalar($label)) {
                continue;
            }

            $normalized[(string) $key] = (string) $label;
        }

        return $normalized;
    }

    /**
     * @return array<string, string>
     */
    private function resolveColumns(): array
    {
        $groups = $this->getAttribute('groups', []);
        if (! is_array($groups) || $groups === []) {
            return [
                'enabled' => (string) esc_html__('Enabled', 'wp-field'),
                'disabled' => (string) esc_html__('Disabled', 'wp-field'),
            ];
        }

        $columns = [];
        foreach ($groups as $key => $group) {
            $groupKey = (string) $key;
            if (is_scalar($group)) {
                $columns[$groupKey] = (string) $group;

                continue;
            }

            if (is_array($group)) {
                $label = isset($group['label']) && is_scalar($group['label'])
                    ? (string) $group['label']
                    : ucfirst($groupKey);
                $columns[$groupKey] = $label;
            }
        }

        return $columns === []
            ? [
                'enabled' => (string) esc_html__('Enabled', 'wp-field'),
                'disabled' => (string) esc_html__('Disabled', 'wp-field'),
            ]
            : $columns;
    }

    /**
     * @param  array<string, string>  $columns
     * @param  array<string, string>  $options
     * @return array<string, array{label: string, items: array<string, string>}>
     */
    private function resolveSortedColumns(array $columns, array $options, mixed $value): array
    {
        $resolved = [];
        foreach ($columns as $key => $label) {
            $resolved[$key] = ['label' => $label, 'items' => []];
        }

        $valueMap = is_array($value) ? $value : [];

        foreach (array_keys($columns) as $columnKey) {
            $keys = $valueMap[$columnKey] ?? [];
            if (! is_array($keys)) {
                continue;
            }

            foreach ($keys as $itemKey) {
                if (! is_scalar($itemKey)) {
                    continue;
                }

                $normalizedKey = (string) $itemKey;
                if (array_key_exists($normalizedKey, $options)) {
                    $resolved[$columnKey]['items'][$normalizedKey] = $options[$normalizedKey];
                }
            }
        }

        $leftovers = [];
        foreach ($options as $key => $label) {
            $placed = false;
            foreach ($resolved as $column) {
                if (array_key_exists($key, $column['items'])) {
                    $placed = true;
                    break;
                }
            }

            if (! $placed) {
                $leftovers[$key] = $label;
            }
        }

        if ($leftovers === []) {
            return $resolved;
        }

        $fallbackColumn = array_key_exists('disabled', $resolved)
            ? 'disabled'
            : array_key_last($resolved);

        if ($fallbackColumn === null) {
            return $resolved;
        }

        foreach ($leftovers as $key => $label) {
            $resolved[$fallbackColumn]['items'][$key] = $label;
        }

        return $resolved;
    }
}
