<?php

declare(strict_types=1);

namespace WpField\Field\Types;

class CheckboxGroupField extends ChoiceField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'checkbox_group');
    }

    public function render(): string
    {
        $name = esc_attr($this->name);

        $rawValue = $this->getValue();
        $selectedValues = is_array($rawValue)
            ? array_map('strval', $rawValue)
            : (is_scalar($rawValue) ? [(string) $rawValue] : []);

        $rawLabel = $this->getAttribute('label');
        $label = is_string($rawLabel) ? $rawLabel : '';

        $html = '';
        if ($label !== '') {
            $html .= sprintf('<p class="wp-field-checkbox-group-label">%s</p>', esc_html($this->stringify($label)));
        }

        $html .= '<div class="wp-field-checkbox-group">';

        foreach ($this->getOptions() as $value => $optionLabel) {
            $valueStr = (string) $value;
            $fieldId = esc_attr($this->name.'_'.$valueStr);
            $checked = in_array($valueStr, $selectedValues, true) ? ' checked' : '';

            $html .= sprintf(
                '<label for="%s"><input type="checkbox" name="%s[]" id="%s" value="%s"%s /> %s</label>',
                $fieldId,
                $name,
                $fieldId,
                esc_attr($valueStr),
                $checked,
                esc_html($this->stringify($optionLabel)),
            );
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
        if (! is_array($value)) {
            return [];
        }

        return array_map(static fn ($item): string => sanitize_text_field((string) $item), $value);
    }
}
