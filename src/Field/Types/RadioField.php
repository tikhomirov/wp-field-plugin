<?php

declare(strict_types=1);

namespace WpField\Field\Types;

class RadioField extends ChoiceField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'radio');
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $id = esc_attr($this->attributeString('id', $this->name));
        $value = $this->getValue();
        $options = $this->resolveOptions();

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $id.'-0', esc_html($label));
        }

        $html .= '<div class="wp-field-radio-group">';

        $currentValue = is_scalar($value) ? (string) $value : '';

        $i = 0;
        foreach ($options as $index => $optionLabel) {
            $optionValue = (string) $index;
            $checked = $currentValue === $optionValue ? ' checked' : '';
            $disabled = $this->getAttribute('disabled', false) ? ' disabled' : '';

            $html .= sprintf(
                '<label><input type="radio" name="%s" value="%s" id="%s-%d"%s%s> %s</label>',
                $name,
                esc_attr($optionValue),
                $id,
                $i,
                $checked,
                $disabled,
                esc_html($this->stringify($optionLabel)),
            );

            $i++;
        }

        $html .= '</div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    /**
     * @return array<int|string, mixed>
     */
    private function resolveOptions(): array
    {
        $options = $this->getAttribute('options', []);

        if (is_string($options)) {
            return array_filter(array_map('trim', explode("\n", $options)));
        }

        return is_array($options) ? $options : [];
    }
}
