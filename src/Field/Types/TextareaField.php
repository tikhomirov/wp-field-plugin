<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class TextareaField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'textarea');
    }

    public function render(): string
    {
        $rawValue = $this->getValue();
        $value = is_scalar($rawValue) ? esc_textarea((string) $rawValue) : '';

        $name = esc_attr($this->name);

        $rawId = $this->getAttribute('id', $this->name);
        $id = is_string($rawId) ? esc_attr($rawId) : esc_attr($this->name);

        $rawClass = $this->getAttribute('class', '');
        $class = is_string($rawClass) ? esc_attr($rawClass) : '';

        $rowsRaw = $this->getAttribute('rows', 4);
        $rows = is_numeric($rowsRaw) ? (int) $rowsRaw : 4;

        $disabled = $this->getAttribute('disabled', false) ? ' disabled' : '';
        $readonly = $this->getAttribute('readonly', false) ? ' readonly' : '';
        $required = $this->isRequired() ? ' required' : '';

        $html = sprintf(
            '<textarea name="%s" id="%s" class="%s" rows="%d"%s%s%s>%s</textarea>',
            $name,
            $id,
            $class,
            $rows,
            $disabled,
            $readonly,
            $required,
            $value,
        );

        $rawLabel = $this->getAttribute('label');
        if ($rawLabel !== null && is_string($rawLabel)) {
            $html = sprintf('<label for="%s">%s</label>', $id, esc_html($rawLabel)).$html;
        }

        $rawDescription = $this->getAttribute('description');
        if ($rawDescription !== null && is_string($rawDescription)) {
            $html .= sprintf('<p class="description">%s</p>', esc_html($rawDescription));
        }

        return $html;
    }
}
