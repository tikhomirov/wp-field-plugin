<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class SortableField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'sortable');
    }

    /**
     * @param  array<int|string, mixed>  $options
     */
    public function options(array $options): static
    {
        return $this->attribute('options', $options);
    }

    public function render(): string
    {
        $options = $this->normalizeOptions($this->getAttribute('options', []));

        if ($options === []) {
            return '<p class="description">No options provided</p>';
        }

        $name = esc_attr($this->name);
        $ordered = $this->resolveOrderedOptions($options, $this->getValue());

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $html .= '<ul class="wp-field-sortable">';

        foreach ($ordered as $key => $itemLabel) {
            $html .= sprintf(
                '<li data-value="%s"><span class="dashicons dashicons-menu"></span><span>%s</span><input type="hidden" name="%s[]" value="%s"></li>',
                esc_attr((string) $key),
                esc_html((string) $itemLabel),
                $name,
                esc_attr((string) $key),
            );
        }

        $html .= '</ul>';

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
        foreach ($value as $item) {
            if (! is_scalar($item)) {
                continue;
            }

            $sanitized[] = sanitize_text_field((string) $item);
        }

        return array_values(array_unique($sanitized));
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
     * @param  array<string, string>  $options
     * @return array<string, string>
     */
    private function resolveOrderedOptions(array $options, mixed $value): array
    {
        $ordered = [];
        $selected = is_array($value) ? $value : [];

        foreach ($selected as $item) {
            if (! is_scalar($item)) {
                continue;
            }

            $key = (string) $item;
            if (array_key_exists($key, $options)) {
                $ordered[$key] = $options[$key];
            }
        }

        foreach ($options as $key => $label) {
            if (! array_key_exists($key, $ordered)) {
                $ordered[$key] = $label;
            }
        }

        return $ordered;
    }
}
