<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class ColorGroupField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'color_group');
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
        $colors = $this->resolveColors();
        if ($colors === []) {
            return '<p class="description">No options provided</p>';
        }

        $name = esc_attr($this->name);
        $value = $this->normalizeValue($this->getValue());

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $html .= '<div class="wp-field-color-group">';

        foreach ($colors as $key => $colorLabel) {
            $html .= sprintf(
                '<div class="wp-field-color-group-item"><label>%s</label><input type="text" name="%s[%s]" value="%s" class="wp-color-picker-field"></div>',
                esc_html($colorLabel),
                $name,
                esc_attr($key),
                esc_attr($value[$key] ?? ''),
            );
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
        foreach ($value as $key => $item) {
            if (! is_scalar($key) || ! is_scalar($item)) {
                continue;
            }

            $sanitized[(string) $key] = trim(sanitize_text_field((string) $item));
        }

        return $sanitized;
    }

    /**
     * @return array<string, string>
     */
    private function resolveColors(): array
    {
        $colors = $this->getAttribute('colors', $this->getAttribute('options', []));
        if (! is_array($colors)) {
            return [];
        }

        $normalized = [];
        foreach ($colors as $key => $label) {
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
    private function normalizeValue(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            if (! is_scalar($key) || ! is_scalar($item)) {
                continue;
            }

            $normalized[(string) $key] = (string) $item;
        }

        return $normalized;
    }
}
