<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class InputField extends AbstractField
{
    public function __construct(string $name, string $type = 'text')
    {
        parent::__construct($name, $type);
    }

    public function render(): string
    {
        $rawValue = $this->getValue();
        $value = is_scalar($rawValue) ? esc_attr((string) $rawValue) : '';

        $name = esc_attr($this->name);

        $rawId = $this->getAttribute('id', $this->name);
        $id = is_string($rawId) ? esc_attr($rawId) : esc_attr($this->name);

        $rawClass = $this->getAttribute('class', '');
        $class = is_string($rawClass) ? esc_attr($rawClass) : '';

        $rawPlaceholder = $this->getAttribute('placeholder', '');
        $placeholder = is_string($rawPlaceholder) ? esc_attr($rawPlaceholder) : '';

        $disabled = $this->getAttribute('disabled', false) ? ' disabled' : '';
        $readonly = $this->getAttribute('readonly', false) ? ' readonly' : '';
        $required = $this->isRequired() ? ' required' : '';

        $extraAttributes = $this->buildExtraAttributes();

        $html = sprintf(
            '<input type="%s" name="%s" id="%s" class="%s" value="%s" placeholder="%s"%s%s%s%s />',
            esc_attr($this->type),
            $name,
            $id,
            $class,
            $value,
            $placeholder,
            $disabled,
            $readonly,
            $required,
            $extraAttributes,
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

    private function buildExtraAttributes(): string
    {
        $skipKeys = [
            'label',
            'description',
            'placeholder',
            'default',
            'class',
            'id',
            'disabled',
            'readonly',
        ];

        $attributes = '';
        foreach ($this->getAttributes() as $key => $value) {
            if (in_array($key, $skipKeys, true)) {
                continue;
            }

            if (is_bool($value)) {
                if ($value) {
                    $attributes .= ' '.esc_attr($key);
                }

                continue;
            }

            if (is_scalar($value)) {
                $attributes .= sprintf(' %s="%s"', esc_attr($key), esc_attr((string) $value));
            }
        }

        return $attributes;
    }
}
