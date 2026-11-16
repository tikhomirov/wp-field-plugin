<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class TypographyField extends AbstractField
{
    private const FONT_FAMILIES = ['Arial', 'Helvetica', 'Times New Roman', 'Georgia', 'Verdana'];

    private const FONT_WEIGHTS = ['300', '400', '600', '700'];

    private const TEXT_ALIGNS = ['left', 'center', 'right', 'justify'];

    private const TEXT_TRANSFORMS = ['none', 'uppercase', 'lowercase', 'capitalize'];

    public function __construct(string $name)
    {
        parent::__construct($name, 'typography');
    }

    /**
     * @param  array<int|string, mixed>  $options
     */
    public function options(array $options): static
    {
        return $this->attribute('options', $options);
    }

    /**
     * @param  array<int|string, mixed>  $default
     */
    public function defaultValue(array $default): static
    {
        return $this->attribute('default', $default);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $value = $this->normalizeValue($this->getValue());

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s[font_family]">%s</label>', $id, esc_html($label));
        }

        $html .= '<div class="wp-field-typography">';
        $html .= $this->renderSelect($name, 'font_family', 'Font Family', self::FONT_FAMILIES, $value['font_family'], true);
        $html .= $this->renderNumberInput($name, 'font_size', 'Font Size', $value['font_size'], ['min' => '8', 'max' => '72', 'placeholder' => '16']);
        $html .= $this->renderSelect($name, 'font_weight', 'Font Weight', self::FONT_WEIGHTS, $value['font_weight'], true);
        $html .= $this->renderNumberInput($name, 'line_height', 'Line Height', $value['line_height'], ['min' => '1', 'max' => '3', 'step' => '0.1', 'placeholder' => '1.5']);
        $html .= $this->renderSelect($name, 'text_align', 'Text Align', self::TEXT_ALIGNS, $value['text_align'], true);
        $html .= $this->renderSelect($name, 'text_transform', 'Text Transform', self::TEXT_TRANSFORMS, $value['text_transform'], true);
        $html .= sprintf(
            '<div class="wp-field-typography-item"><label>%s</label><input type="text" name="%s[color]" value="%s" class="wp-color-picker-field"></div>',
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

        return [
            'font_family' => in_array($normalized['font_family'], self::FONT_FAMILIES, true) ? $normalized['font_family'] : '',
            'font_size' => $this->sanitizeNumeric($normalized['font_size']),
            'font_weight' => in_array($normalized['font_weight'], self::FONT_WEIGHTS, true) ? $normalized['font_weight'] : '',
            'line_height' => $this->sanitizeNumeric($normalized['line_height']),
            'text_align' => in_array($normalized['text_align'], self::TEXT_ALIGNS, true) ? $normalized['text_align'] : '',
            'text_transform' => in_array($normalized['text_transform'], self::TEXT_TRANSFORMS, true) ? $normalized['text_transform'] : '',
            'color' => trim(sanitize_text_field($normalized['color'])),
        ];
    }

    public function validate(mixed $value): bool
    {
        if (! is_array($value)) {
            return ! $this->isRequired();
        }

        $normalized = $this->normalizeValue($value);

        return $this->isAllowedOrEmpty($normalized['font_family'], self::FONT_FAMILIES)
            && $this->isAllowedOrEmpty($normalized['font_weight'], self::FONT_WEIGHTS)
            && $this->isAllowedOrEmpty($normalized['text_align'], self::TEXT_ALIGNS)
            && $this->isAllowedOrEmpty($normalized['text_transform'], self::TEXT_TRANSFORMS)
            && $this->isNumericOrEmpty($normalized['font_size'])
            && $this->isNumericOrEmpty($normalized['line_height']);
    }

    /**
     * @return array{font_family: string, font_size: string, font_weight: string, line_height: string, text_align: string, text_transform: string, color: string}
     */
    private function normalizeValue(mixed $value): array
    {
        if (! is_array($value)) {
            return [
                'font_family' => '',
                'font_size' => '',
                'font_weight' => '',
                'line_height' => '',
                'text_align' => '',
                'text_transform' => '',
                'color' => '',
            ];
        }

        return [
            'font_family' => isset($value['font_family']) && is_scalar($value['font_family']) ? (string) $value['font_family'] : '',
            'font_size' => isset($value['font_size']) && is_scalar($value['font_size']) ? (string) $value['font_size'] : '',
            'font_weight' => isset($value['font_weight']) && is_scalar($value['font_weight']) ? (string) $value['font_weight'] : '',
            'line_height' => isset($value['line_height']) && is_scalar($value['line_height']) ? (string) $value['line_height'] : '',
            'text_align' => isset($value['text_align']) && is_scalar($value['text_align']) ? (string) $value['text_align'] : '',
            'text_transform' => isset($value['text_transform']) && is_scalar($value['text_transform']) ? (string) $value['text_transform'] : '',
            'color' => isset($value['color']) && is_scalar($value['color']) ? (string) $value['color'] : '',
        ];
    }

    /**
     * @param  array<int, string>  $options
     */
    private function renderSelect(string $name, string $key, string $label, array $options, string $selectedValue, bool $withDefault = false): string
    {
        $html = sprintf('<div class="wp-field-typography-item"><label>%s</label><select name="%s[%s]">', esc_html__($label, 'wp-field'), $name, esc_attr($key));

        if ($withDefault) {
            $html .= sprintf('<option value="" %s>%s</option>', $selectedValue === '' ? 'selected' : '', esc_html__('Default', 'wp-field'));
        }

        foreach ($options as $option) {
            $optionValue = (string) $option;
            $isSelected = $selectedValue === $optionValue ? 'selected' : '';
            $html .= sprintf('<option value="%s" %s>%s</option>', esc_attr($optionValue), $isSelected, esc_html($optionValue));
        }

        $html .= '</select></div>';

        return $html;
    }

    /**
     * @param  array<string, string>  $attrs
     */
    private function renderNumberInput(string $name, string $key, string $label, string $value, array $attrs = []): string
    {
        $attrsHtml = '';
        foreach ($attrs as $attr => $attrValue) {
            $attrsHtml .= sprintf(' %s="%s"', esc_attr($attr), esc_attr($attrValue));
        }

        return sprintf(
            '<div class="wp-field-typography-item"><label>%s</label><input type="number" name="%s[%s]" value="%s"%s></div>',
            esc_html__($label, 'wp-field'),
            $name,
            esc_attr($key),
            esc_attr($value),
            $attrsHtml,
        );
    }

    /**
     * @param  array<int, string>  $allowed
     */
    private function isAllowedOrEmpty(string $value, array $allowed): bool
    {
        return $value === '' || in_array($value, $allowed, true);
    }

    private function isNumericOrEmpty(string $value): bool
    {
        return $value === '' || is_numeric($value);
    }

    private function sanitizeNumeric(string $value): string
    {
        $value = trim(sanitize_text_field($value));

        return is_numeric($value) ? $value : '';
    }
}
