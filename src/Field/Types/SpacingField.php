<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class SpacingField extends AbstractField
{
    private const DEFAULT_SIDES = ['top', 'right', 'bottom', 'left'];

    private const DEFAULT_UNITS = ['px', 'em', 'rem', '%'];

    public function __construct(string $name)
    {
        parent::__construct($name, 'spacing');
    }

    /**
     * @param  array<int, string>  $units
     */
    public function units(array $units): static
    {
        return $this->attribute('units', $units);
    }

    /**
     * @param  array<int, string>  $sides
     */
    public function sides(array $sides): static
    {
        return $this->attribute('sides', $sides);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $value = $this->normalizeValue($this->getValue());
        $sides = $this->resolveSides();
        $units = $this->resolveUnits();

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s[top]">%s</label>', $id, esc_html($label));
        }

        $spacingType = $this->attributeString('spacing_type', 'margin');

        $html .= '<div class="wp-field-spacing"><div class="wp-field-spacing-wrapper"><div class="wp-field-spacing-visual">';

        foreach ($sides as $side) {
            $html .= sprintf(
                '<div class="wp-field-spacing-side wp-field-spacing-%s"><label>%s</label><input type="number" name="%s[%s]" value="%s" placeholder="0" step="1"></div>',
                esc_attr($side),
                esc_html(ucfirst($side)),
                $name,
                esc_attr($side),
                esc_attr($value[$side]),
            );
        }

        $html .= sprintf('<div class="wp-field-spacing-center">%s</div>', esc_html($spacingType));
        $html .= '</div>';

        $html .= sprintf('<div class="wp-field-spacing-unit"><select name="%s[unit]">', $name);
        foreach ($units as $unit) {
            $selected = $value['unit'] === $unit ? 'selected' : '';
            $html .= sprintf('<option value="%s" %s>%s</option>', esc_attr($unit), $selected, esc_html($unit));
        }
        $html .= '</select></div>';

        $html .= '</div></div>';

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

        $sanitized = [
            'unit' => in_array($normalized['unit'], $units, true) ? $normalized['unit'] : $units[0],
        ];

        foreach ($this->resolveSides() as $side) {
            $sanitized[$side] = $this->sanitizeNumeric($normalized[$side]);
        }

        return $sanitized;
    }

    public function validate(mixed $value): bool
    {
        if (! is_array($value)) {
            return ! $this->isRequired();
        }

        $normalized = $this->normalizeValue($value);
        $units = $this->resolveUnits();

        if (! in_array($normalized['unit'], $units, true)) {
            return false;
        }

        foreach ($this->resolveSides() as $side) {
            if (! $this->isNumericOrEmpty($normalized[$side])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{top: string, right: string, bottom: string, left: string, unit: string}
     */
    private function normalizeValue(mixed $value): array
    {
        if (! is_array($value)) {
            return ['top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'unit' => 'px'];
        }

        return [
            'top' => isset($value['top']) && is_scalar($value['top']) ? (string) $value['top'] : '',
            'right' => isset($value['right']) && is_scalar($value['right']) ? (string) $value['right'] : '',
            'bottom' => isset($value['bottom']) && is_scalar($value['bottom']) ? (string) $value['bottom'] : '',
            'left' => isset($value['left']) && is_scalar($value['left']) ? (string) $value['left'] : '',
            'unit' => isset($value['unit']) && is_scalar($value['unit']) ? (string) $value['unit'] : 'px',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolveSides(): array
    {
        $sides = $this->getAttribute('sides', self::DEFAULT_SIDES);

        if (! is_array($sides) || $sides === []) {
            return self::DEFAULT_SIDES;
        }

        $normalized = [];
        foreach ($sides as $side) {
            if (! is_scalar($side)) {
                continue;
            }

            $side = strtolower(trim((string) $side));
            if (in_array($side, self::DEFAULT_SIDES, true)) {
                $normalized[] = $side;
            }
        }

        return $normalized === [] ? self::DEFAULT_SIDES : array_values(array_unique($normalized));
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
