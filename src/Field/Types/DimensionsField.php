<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class DimensionsField extends AbstractField
{
    private const DEFAULT_UNITS = ['px', 'em', 'rem', '%', 'vh', 'vw'];

    public function __construct(string $name)
    {
        parent::__construct($name, 'dimensions');
    }

    /**
     * @param  array<int, string>  $units
     */
    public function units(array $units): static
    {
        return $this->attribute('units', $units);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $value = $this->normalizeValue($this->getValue());
        $units = $this->resolveUnits();

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s[width]">%s</label>', $id, esc_html($label));
        }

        $html .= '<div class="wp-field-dimensions">';
        $html .= sprintf('<div class="wp-field-dimensions-item"><label>%s</label><input type="number" name="%s[width]" value="%s" placeholder="0"></div>', esc_html__('Width', 'wp-field'), $name, esc_attr($value['width']));
        $html .= sprintf('<div class="wp-field-dimensions-item"><label>%s</label><input type="number" name="%s[height]" value="%s" placeholder="0"></div>', esc_html__('Height', 'wp-field'), $name, esc_attr($value['height']));
        $html .= sprintf('<div class="wp-field-dimensions-item"><label>%s</label><select name="%s[unit]">', esc_html__('Unit', 'wp-field'), $name);

        foreach ($units as $unit) {
            $selected = $value['unit'] === $unit ? 'selected' : '';
            $html .= sprintf('<option value="%s" %s>%s</option>', esc_attr($unit), $selected, esc_html($unit));
        }

        $html .= '</select></div></div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        $normalized = $this->normalizeValue($value);
        $units = $this->resolveUnits();

        return [
            'width' => $this->sanitizeNumeric($normalized['width']),
            'height' => $this->sanitizeNumeric($normalized['height']),
            'unit' => in_array($normalized['unit'], $units, true) ? $normalized['unit'] : $units[0],
        ];
    }

    public function validate(mixed $value): bool
    {
        if (! is_array($value)) {
            return ! $this->isRequired();
        }

        $normalized = $this->normalizeValue($value);

        return $this->isNumericOrEmpty($normalized['width'])
            && $this->isNumericOrEmpty($normalized['height'])
            && in_array($normalized['unit'], $this->resolveUnits(), true);
    }

    /**
     * @return array{width: string, height: string, unit: string}
     */
    private function normalizeValue(mixed $value): array
    {
        if (! is_array($value)) {
            return ['width' => '', 'height' => '', 'unit' => 'px'];
        }

        return [
            'width' => isset($value['width']) && is_scalar($value['width']) ? (string) $value['width'] : '',
            'height' => isset($value['height']) && is_scalar($value['height']) ? (string) $value['height'] : '',
            'unit' => isset($value['unit']) && is_scalar($value['unit']) ? (string) $value['unit'] : 'px',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolveUnits(): array
    {
        $units = $this->getAttribute('units', self::DEFAULT_UNITS);

        if (! is_array($units) || $units === []) {
            return self::DEFAULT_UNITS;
        }

        $normalized = [];
        foreach ($units as $unit) {
            if (! is_scalar($unit)) {
                continue;
            }

            $unit = trim((string) $unit);
            if ($unit !== '') {
                $normalized[] = $unit;
            }
        }

        return $normalized === [] ? self::DEFAULT_UNITS : array_values(array_unique($normalized));
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
