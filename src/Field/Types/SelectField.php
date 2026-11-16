<?php

declare(strict_types=1);

namespace WpField\Field\Types;

class SelectField extends ChoiceField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'select');
    }

    public function multiple(bool $multiple = true): static
    {
        return $this->attribute('multiple', $multiple);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $isMultiple = (bool) $this->getAttribute('multiple', false);
        $selectName = $isMultiple ? $name.'[]' : $name;

        $rawId = $this->getAttribute('id', $this->name);
        $id = is_string($rawId) ? esc_attr($rawId) : esc_attr($this->name);

        $rawClass = $this->getAttribute('class', '');
        $class = is_string($rawClass) ? esc_attr($rawClass) : '';

        $rawValue = $this->getValue();
        $selectedValues = $isMultiple
            ? (is_array($rawValue) ? array_map('strval', $rawValue) : [])
            : [is_scalar($rawValue) ? (string) $rawValue : ''];

        $multipleAttr = $isMultiple ? ' multiple="multiple"' : '';
        $required = $this->isRequired() ? ' required' : '';
        $disabled = $this->getAttribute('disabled', false) ? ' disabled' : '';

        $html = sprintf(
            '<select name="%s" id="%s" class="%s"%s%s%s>',
            esc_attr($selectName),
            $id,
            $class,
            $multipleAttr,
            $required,
            $disabled,
        );

        foreach ($this->getOptions() as $value => $label) {
            $valueStr = (string) $value;
            $selected = in_array($valueStr, $selectedValues, true) ? ' selected' : '';

            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($valueStr),
                $selected,
                esc_html($this->stringify($label)),
            );
        }

        $html .= '</select>';

        $label = $this->attributeString('label');
        if ($label !== '') {
            $html = sprintf('<label for="%s">%s</label>', $id, esc_html($label)).$html;
        }

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }
}
