<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class BackgroundField extends AbstractField
{
    private const DEFAULT_POSITION = ['left top', 'center top', 'right top', 'left center', 'center center', 'right center', 'left bottom', 'center bottom', 'right bottom'];

    private const DEFAULT_SIZE = ['auto', 'cover', 'contain'];

    private const DEFAULT_REPEAT = ['no-repeat', 'repeat', 'repeat-x', 'repeat-y'];

    private const DEFAULT_ATTACHMENT = ['scroll', 'fixed'];

    public function __construct(string $name)
    {
        parent::__construct($name, 'background');
    }

    /**
     * @param  array<int|string, mixed>  $fields
     */
    public function backgroundFields(array $fields): static
    {
        return $this->attribute('background_fields', $fields);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $value = $this->normalizeValue($this->getValue());
        $enabled = $this->resolveEnabledFields();

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s[color]">%s</label>', $id, esc_html($label));
        }

        $html .= '<div class="wp-field-background">';

        if ($enabled['color']) {
            $html .= sprintf('<div class="wp-field-background-item"><label>%s</label><input type="text" name="%s[color]" value="%s" class="wp-color-picker-field"></div>', esc_html__('Background Color', 'wp-field'), $name, esc_attr($value['color']));
        }

        if ($enabled['image']) {
            $html .= sprintf(
                '<div class="wp-field-background-item"><label>%s</label><input type="hidden" name="%s[image]" value="%s" class="wp-field-background-image-id"><button type="button" class="button wp-field-background-image-button" data-field-name="%s">%s</button></div>',
                esc_html__('Background Image', 'wp-field'),
                $name,
                esc_attr($value['image']),
                $name,
                esc_html__('Choose Image', 'wp-field'),
            );
        }

        if ($enabled['position']) {
            $html .= $this->renderSelect('Position', $name, 'position', self::DEFAULT_POSITION, $value['position']);
        }

        if ($enabled['size']) {
            $html .= $this->renderSelect('Size', $name, 'size', self::DEFAULT_SIZE, $value['size']);
        }

        if ($enabled['repeat']) {
            $html .= $this->renderSelect('Repeat', $name, 'repeat', self::DEFAULT_REPEAT, $value['repeat']);
        }

        if ($enabled['attachment']) {
            $html .= $this->renderSelect('Attachment', $name, 'attachment', self::DEFAULT_ATTACHMENT, $value['attachment']);
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
        $normalized = $this->normalizeValue($value);

        return [
            'color' => trim(sanitize_text_field($normalized['color'])),
            'image' => trim(sanitize_text_field($normalized['image'])),
            'position' => in_array($normalized['position'], self::DEFAULT_POSITION, true) ? $normalized['position'] : 'center center',
            'size' => in_array($normalized['size'], self::DEFAULT_SIZE, true) ? $normalized['size'] : 'cover',
            'repeat' => in_array($normalized['repeat'], self::DEFAULT_REPEAT, true) ? $normalized['repeat'] : 'no-repeat',
            'attachment' => in_array($normalized['attachment'], self::DEFAULT_ATTACHMENT, true) ? $normalized['attachment'] : 'scroll',
        ];
    }

    public function validate(mixed $value): bool
    {
        if (! is_array($value)) {
            return ! $this->isRequired();
        }

        $normalized = $this->normalizeValue($value);

        return in_array($normalized['position'], self::DEFAULT_POSITION, true)
            && in_array($normalized['size'], self::DEFAULT_SIZE, true)
            && in_array($normalized['repeat'], self::DEFAULT_REPEAT, true)
            && in_array($normalized['attachment'], self::DEFAULT_ATTACHMENT, true);
    }

    /**
     * @return array{color: string, image: string, position: string, size: string, repeat: string, attachment: string}
     */
    private function normalizeValue(mixed $value): array
    {
        if (! is_array($value)) {
            return [
                'color' => '',
                'image' => '',
                'position' => 'center center',
                'size' => 'cover',
                'repeat' => 'no-repeat',
                'attachment' => 'scroll',
            ];
        }

        return [
            'color' => isset($value['color']) && is_scalar($value['color']) ? (string) $value['color'] : '',
            'image' => isset($value['image']) && is_scalar($value['image']) ? (string) $value['image'] : '',
            'position' => isset($value['position']) && is_scalar($value['position']) ? (string) $value['position'] : 'center center',
            'size' => isset($value['size']) && is_scalar($value['size']) ? (string) $value['size'] : 'cover',
            'repeat' => isset($value['repeat']) && is_scalar($value['repeat']) ? (string) $value['repeat'] : 'no-repeat',
            'attachment' => isset($value['attachment']) && is_scalar($value['attachment']) ? (string) $value['attachment'] : 'scroll',
        ];
    }

    /**
     * @return array{color: bool, image: bool, position: bool, size: bool, repeat: bool, attachment: bool}
     */
    private function resolveEnabledFields(): array
    {
        $defaults = [
            'color' => true,
            'image' => true,
            'position' => true,
            'size' => true,
            'repeat' => true,
            'attachment' => true,
        ];

        $fields = $this->getAttribute('background_fields', []);
        if (! is_array($fields) || $fields === []) {
            return $defaults;
        }

        foreach (array_keys($defaults) as $key) {
            if (array_key_exists($key, $fields)) {
                $defaults[$key] = (bool) $fields[$key];
            }
        }

        return $defaults;
    }

    /**
     * @param  array<int, string>  $options
     */
    private function renderSelect(string $label, string $name, string $key, array $options, string $selectedValue): string
    {
        $html = sprintf('<div class="wp-field-background-item"><label>%s</label><select name="%s[%s]">', esc_html__($label, 'wp-field'), $name, esc_attr($key));

        foreach ($options as $option) {
            $selected = $selectedValue === $option ? 'selected' : '';
            $html .= sprintf('<option value="%s" %s>%s</option>', esc_attr($option), $selected, esc_html(ucwords($option)));
        }

        $html .= '</select></div>';

        return $html;
    }
}
