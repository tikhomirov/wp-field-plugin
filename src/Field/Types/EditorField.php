<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class EditorField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'editor');
    }

    public function rows(int $rows): static
    {
        return $this->attribute('rows', $rows);
    }

    public function mediaButtons(bool $enabled = true): static
    {
        return $this->attribute('media_buttons', $enabled);
    }

    public function teeny(bool $enabled = true): static
    {
        return $this->attribute('teeny', $enabled);
    }

    public function wpautop(bool $enabled = true): static
    {
        return $this->attribute('wpautop', $enabled);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $id = esc_attr($this->attributeString('id', $this->name));
        $value = $this->normalizeValue($this->getValue());
        $rowsAttr = $this->getAttribute('rows', 10);
        $rows = max(1, is_numeric($rowsAttr) ? (int) $rowsAttr : 10);

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $html .= sprintf(
            '<textarea id="%s" name="%s" rows="%d" class="wp-editor-area" data-media-buttons="%s" data-teeny="%s" data-wpautop="%s">%s</textarea>',
            $id,
            $name,
            $rows,
            $this->getAttribute('media_buttons', false) ? '1' : '0',
            $this->getAttribute('teeny', false) ? '1' : '0',
            $this->getAttribute('wpautop', false) ? '1' : '0',
            esc_html($value),
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
