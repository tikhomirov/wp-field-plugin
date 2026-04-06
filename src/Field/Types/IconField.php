<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class IconField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'icon');
    }

    public function library(string $library): static
    {
        return $this->attribute('library', $library);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $id = esc_attr($this->attributeString('id', $this->name));
        $value = $this->normalizeValue($this->getValue());
        $library = $this->attributeString('library', 'dashicons');
        $buttonText = $this->attributeString('button_text', __('Выбрать иконку', 'wp-field'));

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $html .= '<div class="wp-field-icon-wrapper">';
        $html .= sprintf('<input type="text" id="%s" name="%s" value="%s" class="regular-text wp-field-icon-value" data-library="%s">', $id, $name, esc_attr($value), esc_attr($library));
        $html .= sprintf('<button type="button" class="button wp-field-icon-button" data-field-id="%s">%s</button>', $id, esc_html($buttonText));
        $html .= sprintf('<span class="wp-field-icon-preview %s" aria-hidden="true"></span>', esc_attr($value));
        $html .= '</div>';

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
