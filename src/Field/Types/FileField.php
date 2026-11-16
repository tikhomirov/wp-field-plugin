<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class FileField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'file');
    }

    public function buttonText(string $text): static
    {
        return $this->attribute('button_text', $text);
    }

    public function library(string $type): static
    {
        return $this->attribute('library', $type);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $id = esc_attr($this->attributeString('id', $this->name));
        $value = $this->normalizeValue($this->getValue());

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $placeholder = $this->attributeString('placeholder', __('Не выбрано', 'wp-field'));
        $buttonText = $this->attributeString('button_text', __('Загрузить', 'wp-field'));
        $library = $this->attributeString('library');
        $showUrl = (bool) $this->getAttribute('url', true);

        $html .= '<div class="wp-field-file-wrapper">';

        if ($showUrl) {
            $html .= sprintf(
                '<input type="text" class="regular-text wp-field-file-url" value="%s" placeholder="%s" readonly>',
                esc_attr($value),
                esc_attr($placeholder),
            );
        }

        $html .= sprintf('<input type="hidden" id="%s" name="%s" value="%s" class="wp-field-file-id">', $id, $name, esc_attr($value));
        $html .= sprintf('<button type="button" class="button wp-field-file-button" data-field-id="%s" data-library="%s">%s</button>', $id, esc_attr($library), esc_html($buttonText));

        if ($value !== '') {
            $html .= sprintf('<span class="wp-field-file-name">%s</span>', esc_html(basename($value)));
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
