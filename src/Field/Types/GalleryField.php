<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class GalleryField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'gallery');
    }

    public function addButton(string $text): static
    {
        return $this->attribute('add_button', $text);
    }

    public function editButton(string $text): static
    {
        return $this->attribute('edit_button', $text);
    }

    public function clearButton(string $text): static
    {
        return $this->attribute('clear_button', $text);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $id = esc_attr($this->attributeString('id', $this->name));
        $ids = $this->normalizeIds($this->getValue());

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $addText = $this->attributeString('add_button', __('Добавить галерею', 'wp-field'));
        $editText = $this->attributeString('edit_button', __('Редактировать галерею', 'wp-field'));
        $clearText = $this->attributeString('clear_button', __('Сброс', 'wp-field'));

        $html .= '<div class="wp-field-gallery-wrapper">';
        $html .= sprintf('<input type="hidden" id="%s" name="%s" value="%s" class="wp-field-gallery-ids">', $id, $name, esc_attr(implode(',', $ids)));

        $html .= '<div class="wp-field-gallery-preview">';
        foreach ($ids as $galleryId) {
            $html .= sprintf('<div class="wp-field-gallery-item" data-id="%s"><span class="wp-field-gallery-remove" data-id="%s">×</span></div>', esc_attr($galleryId), esc_attr($galleryId));
        }
        $html .= '</div>';

        $html .= '<div class="wp-field-gallery-buttons">';
        $html .= sprintf('<button type="button" class="button wp-field-gallery-add" data-field-id="%s">%s</button>', $id, esc_html($addText));
        $html .= sprintf('<button type="button" class="button wp-field-gallery-edit" data-field-id="%s">%s</button>', $id, esc_html($editText));
        $html .= sprintf('<button type="button" class="button wp-field-gallery-clear" data-field-id="%s">%s</button>', $id, esc_html($clearText));
        $html .= '</div></div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        return implode(',', $this->normalizeIds($value));
    }

    public function validate(mixed $value): bool
    {
        if (! is_array($value) && ! is_string($value)) {
            return ! $this->isRequired();
        }

        return true;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeIds(mixed $value): array
    {
        $items = [];

        if (is_array($value)) {
            $items = $value;
        } elseif (is_scalar($value)) {
            $items = explode(',', (string) $value);
        }

        $normalized = [];
        foreach ($items as $item) {
            if (! is_scalar($item)) {
                continue;
            }

            $id = trim((string) $item);
            if ($id === '') {
                continue;
            }

            $normalized[] = $id;
        }

        return array_values(array_unique($normalized));
    }
}
