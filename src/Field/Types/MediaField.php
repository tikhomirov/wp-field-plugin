<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class MediaField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'media');
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
        $fileUrl = $value;
        $fileName = $value !== '' ? basename($value) : '';

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $placeholder = $this->attributeString('placeholder', __('Не выбрано', 'wp-field'));
        $buttonText = $this->attributeString('button_text', __('Загрузить', 'wp-field'));
        $library = $this->attributeString('library');
        $showUrl = (bool) $this->getAttribute('url', true);
        $showPreview = (bool) $this->getAttribute('preview', true);

        $html .= '<div class="wp-field-media-wrapper">';

        if ($showUrl) {
            $html .= sprintf(
                '<input type="text" class="regular-text wp-field-media-url" value="%s" placeholder="%s" readonly>',
                esc_attr($fileUrl),
                esc_attr($placeholder),
            );
        }

        $html .= sprintf(
            '<input type="hidden" id="%s" name="%s" value="%s" class="wp-field-media-id">',
            $id,
            $name,
            esc_attr($value),
        );

        $html .= sprintf(
            '<button type="button" class="button wp-field-media-button" data-field-id="%s" data-library="%s">%s</button>',
            $id,
            esc_attr($library),
            esc_html($buttonText),
        );

        if ($showPreview && $fileUrl !== '') {
            $html .= '<div class="wp-field-media-preview">';
            $html .= sprintf('<span class="wp-field-media-filename">%s</span>', esc_html($fileName));
            $html .= '</div>';
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
