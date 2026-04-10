<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class CodeEditorField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'code_editor');
    }

    public function mode(string $mode): static
    {
        return $this->attribute('mode', $mode);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $id = esc_attr($this->attributeString('id', $this->name));
        $value = $this->normalizeValue($this->getValue());
        $mode = $this->attributeString('mode', 'text/html');
        $rowsAttr = $this->getAttribute('rows', 12);
        $rows = max(1, is_numeric($rowsAttr) ? (int) $rowsAttr : 12);

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $html .= sprintf(
            '<textarea id="%s" name="%s" rows="%d" class="wp-field-code-editor" data-mode="%s">%s</textarea>',
            $id,
            $name,
            $rows,
            esc_attr($mode),
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
