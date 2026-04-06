<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class ColorField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'color');
        $this->label($name);
    }

    public function alpha(bool $enabled = true): static
    {
        return $this->attribute('alpha', $enabled);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $id = esc_attr($this->attributeString('id', $this->name));
        $value = $this->normalizeValue($this->getValue());
        $default = $this->attributeString('default', '#000000');
        $alpha = $this->getAttribute('alpha', true) === false ? 'false' : 'true';

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $html .= sprintf(
            '<input type="text" id="%s" name="%s" value="%s" class="wp-color-picker-field" data-default-color="%s" data-alpha="%s">',
            $id,
            $name,
            esc_attr($value),
            esc_attr($default),
            esc_attr($alpha),
        );

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        return $this->normalizeValue($value);
    }

    private function normalizeValue(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return trim(sanitize_text_field((string) $value));
    }
}
