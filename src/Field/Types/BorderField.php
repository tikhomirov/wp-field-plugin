<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class BorderField extends AbstractField
{
    private const DEFAULT_STYLES = ['none', 'solid', 'dashed', 'dotted', 'double'];

    public function __construct(string $name)
    {
        parent::__construct($name, 'border');
    }

    /**
     * @param  array<int, string>  $styles
     */
    public function styles(array $styles): static
    {
        return $this->attribute('styles', $styles);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $value = $this->normalizeValue($this->getValue());

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s[style]">%s</label>', $id, esc_html($label));
        }

        $html .= '<div class="wp-field-border">';
        $html .= sprintf('<div class="wp-field-border-item"><label>%s</label><select name="%s[style]">', esc_html__('Style', 'wp-field'), $name);

        foreach ($this->resolveStyles() as $style) {
            $selected = $value['style'] === $style ? 'selected' : '';
            $html .= sprintf('<option value="%s" %s>%s</option>', esc_attr($style), $selected, esc_html(ucfirst($style)));
        }

        $html .= '</select></div>';

        $html .= sprintf(
            '<div class="wp-field-border-item"><label>%s</label><input type="number" name="%s[width]" value="%s" min="0" max="20" placeholder="1"></div>',
            esc_html__('Width (px)', 'wp-field'),
            $name,
            esc_attr($value['width']),
        );

        $html .= sprintf(
            '<div class="wp-field-border-item"><label>%s</label><input type="text" name="%s[color]" value="%s" class="wp-color-picker-field"></div>',
            esc_html__('Color', 'wp-field'),
            $name,
            esc_attr($value['color']),
        );

        $html .= '</div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        $normalized = $this->normalizeValue($value);
        $styles = $this->resolveStyles();

        return [
            'style' => in_array($normalized['style'], $styles, true) ? $normalized['style'] : $styles[0],
            'width' => $this->sanitizeNumeric($normalized['width']),
            'color' => trim(sanitize_text_field($normalized['color'])),
        ];
    }

    public function validate(mixed $value): bool
    {
        if (! is_array($value)) {
            return ! $this->isRequired();
        }

        $normalized = $this->normalizeValue($value);

        return in_array($normalized['style'], $this->resolveStyles(), true)
            && $this->isNumericOrEmpty($normalized['width']);
    }

    /**
     * @return array{style: string, width: string, color: string}
     */
    private function normalizeValue(mixed $value): array
    {
        if (! is_array($value)) {
            return ['style' => 'solid', 'width' => '', 'color' => ''];
        }

        return [
            'style' => isset($value['style']) && is_scalar($value['style']) ? (string) $value['style'] : 'solid',
            'width' => isset($value['width']) && is_scalar($value['width']) ? (string) $value['width'] : '',
            'color' => isset($value['color']) && is_scalar($value['color']) ? (string) $value['color'] : '',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolveStyles(): array
    {
        $styles = $this->getAttribute('styles', self::DEFAULT_STYLES);

        if (! is_array($styles) || $styles === []) {
            return self::DEFAULT_STYLES;
        }

        $normalized = [];
        foreach ($styles as $style) {
            if (! is_scalar($style)) {
                continue;
            }

            $style = trim((string) $style);
            if ($style !== '') {
                $normalized[] = $style;
            }
        }

        return $normalized === [] ? self::DEFAULT_STYLES : array_values(array_unique($normalized));
    }

    private function sanitizeNumeric(string $value): string
    {
        $value = trim(sanitize_text_field($value));

        return is_numeric($value) ? $value : '';
    }

    private function isNumericOrEmpty(string $value): bool
    {
        return $value === '' || is_numeric($value);
    }
}
