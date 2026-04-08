<?php

declare(strict_types=1);

namespace WpField\Field\Types;

class ButtonSetField extends ChoiceField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'button_set');
    }

    public function multiple(bool $multiple = true): static
    {
        return $this->attribute('multiple', $multiple);
    }

    public function render(): string
    {
        $options = $this->getOptions();
        if ($options === []) {
            return '<p class="description">No options provided</p>';
        }

        $name = esc_attr($this->name);
        $multiple = (bool) $this->getAttribute('multiple', false);
        $value = $this->getValue();
        $selectedValues = $multiple
            ? (is_array($value) ? array_map('strval', $value) : (is_scalar($value) ? [(string) $value] : []))
            : [(is_scalar($value) ? (string) $value : '')];
        $wrapperClass = 'wp-field-button-set';
        $customClass = $this->getAttribute('class', '');
        if (is_string($customClass) && $customClass !== '') {
            $wrapperClass .= ' '.trim($customClass);
        }

        $html = sprintf('<div class="%s">', esc_attr($wrapperClass));

        foreach ($options as $key => $label) {
            $keyValue = (string) $key;
            $checked = in_array($keyValue, $selectedValues, true);
            $inputType = $multiple ? 'checkbox' : 'radio';
            $inputName = $multiple ? $name.'[]' : $name;
            $itemClass = $checked ? ' active' : '';

            $html .= sprintf(
                '<label class="wp-field-button-set-item%s"><input type="%s" name="%s" value="%s"%s /><span>%s</span></label>',
                $itemClass,
                $inputType,
                esc_attr($inputName),
                esc_attr($keyValue),
                $checked ? ' checked' : '',
                esc_html($this->stringify($label)),
            );
        }

        $html .= '</div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }
}
