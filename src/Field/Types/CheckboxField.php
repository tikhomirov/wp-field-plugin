<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class CheckboxField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'checkbox');
    }

    public function checkedValue(string $value): static
    {
        return $this->attribute('checked_value', $value);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);

        $rawId = $this->getAttribute('id', $this->name);
        $id = is_string($rawId) ? esc_attr($rawId) : esc_attr($this->name);

        $checkedValueRaw = $this->getAttribute('checked_value', '1');
        $checkedValue = is_scalar($checkedValueRaw) ? (string) $checkedValueRaw : '1';

        $rawValue = $this->getValue();
        $isChecked = false;
        if (is_bool($rawValue)) {
            $isChecked = $rawValue;
        } elseif (is_scalar($rawValue)) {
            $isChecked = (string) $rawValue === $checkedValue;
        }

        $checked = $isChecked ? ' checked' : '';
        $disabled = $this->getAttribute('disabled', false) ? ' disabled' : '';

        $labelText = $this->getAttribute('label');
        $label = is_string($labelText) ? $labelText : '';

        $html = sprintf(
            '<label for="%s"><input type="checkbox" name="%s" id="%s" value="%s"%s%s /> %s</label>',
            $id,
            $name,
            $id,
            esc_attr($checkedValue),
            $checked,
            $disabled,
            esc_html($label),
        );

        $rawDescription = $this->getAttribute('description');
        if ($rawDescription !== null && is_string($rawDescription)) {
            $html .= sprintf('<p class="description">%s</p>', esc_html($rawDescription));
        }

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        if (! is_scalar($value)) {
            return '';
        }

        return sanitize_text_field((string) $value);
    }
}
