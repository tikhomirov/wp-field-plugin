<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class SwitcherField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'switcher');
    }

    public function textOn(string $text): static
    {
        return $this->attribute('text_on', $text);
    }

    public function textOff(string $text): static
    {
        return $this->attribute('text_off', $text);
    }

    public function checkedValue(string $value): static
    {
        return $this->attribute('checked_value', $value);
    }

    public function render(): string
    {
        $rawValue = $this->getValue();
        $checkedValueRaw = $this->getAttribute('checked_value', '1');
        $checkedValue = is_scalar($checkedValueRaw) ? (string) $checkedValueRaw : '1';
        $isChecked = false;

        if (is_bool($rawValue)) {
            $isChecked = $rawValue;
        } elseif (is_scalar($rawValue)) {
            $isChecked = (string) $rawValue === $checkedValue;
        } elseif ($rawValue !== null) {
            $isChecked = true;
        }

        $checked = $isChecked ? ' checked' : '';
        $disabled = $this->getAttribute('disabled', false) ? ' disabled' : '';
        $readonly = $this->getAttribute('readonly', false) ? ' readonly' : '';
        $name = esc_attr($this->name);
        $value = esc_attr($checkedValue);
        $textOn = $this->getAttribute('text_on', 'On');
        $textOff = $this->getAttribute('text_off', 'Off');
        $wrapperClass = 'wp-field-switcher';
        $customClass = $this->getAttribute('class', '');
        if (is_string($customClass) && $customClass !== '') {
            $wrapperClass .= ' '.trim($customClass);
        }

        $html = sprintf(
            '<label class="%s"><input type="checkbox" name="%s" value="%s"%s%s%s /><span class="wp-field-switcher-slider"><span class="wp-field-switcher-on">%s</span><span class="wp-field-switcher-off">%s</span></span></label>',
            esc_attr($wrapperClass),
            $name,
            $value,
            $checked,
            $disabled,
            $readonly,
            esc_html(is_string($textOn) ? $textOn : 'On'),
            esc_html(is_string($textOff) ? $textOff : 'Off'),
        );

        $rawDescription = $this->getAttribute('description');
        if ($rawDescription !== null && is_string($rawDescription)) {
            $html .= sprintf('<p class="description">%s</p>', esc_html($rawDescription));
        }

        return $html;
    }
}
