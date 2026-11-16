<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class ImageField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'image');
    }

    public function buttonText(string $text): static
    {
        return $this->attribute('button_text', $text);
    }

    public function removeText(string $text): static
    {
        return $this->attribute('remove_text', $text);
    }

    public function preview(bool $enabled = true): static
    {
        return $this->attribute('preview', $enabled);
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
        $removeText = $this->attributeString('remove_text', __('Удалить', 'wp-field'));
        $showUrl = (bool) $this->getAttribute('url', true);
        $showPreview = (bool) $this->getAttribute('preview', true);

        $html .= '<div class="wp-field-image-wrapper">';

        if ($showUrl) {
            $html .= sprintf(
                '<input type="text" class="regular-text wp-field-image-url" value="%s" placeholder="%s" readonly>',
                esc_attr($value),
                esc_attr($placeholder),
            );
        }

        $html .= sprintf(
            '<input type="hidden" id="%s" name="%s" value="%s" class="wp-field-image-id">',
            $id,
            $name,
            esc_attr($value),
        );

        $html .= sprintf('<button type="button" class="button wp-field-image-button" data-field-id="%s">%s</button>', $id, esc_html($buttonText));

        if ($value !== '') {
            $html .= sprintf('<button type="button" class="button wp-field-image-remove" data-field-id="%s">%s</button>', $id, esc_html($removeText));
        }

        if ($showPreview && $value !== '') {
            $html .= '<div class="wp-field-image-preview-wrapper">';
            $html .= sprintf('<img src="%s" alt="" class="wp-field-image-preview">', esc_url($value));
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
