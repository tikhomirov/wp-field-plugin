<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class SpinnerField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'spinner');
    }

    public function min(int|float $min): static
    {
        $this->validationRules['min'] = $min;
        $this->attributes['min'] = $min;

        return $this;
    }

    public function max(int|float $max): static
    {
        $this->validationRules['max'] = $max;
        $this->attributes['max'] = $max;

        return $this;
    }

    public function step(int|float $step): static
    {
        return $this->attribute('step', $step);
    }

    public function unit(string $unit): static
    {
        return $this->attribute('unit', $unit);
    }

    public function render(): string
    {
        $rawValue = $this->getValue();
        $value = esc_attr($this->stringify($rawValue));
        $name = esc_attr($this->name);
        $min = esc_attr($this->attributeString('min', '0'));
        $max = esc_attr($this->attributeString('max', '100'));
        $step = esc_attr($this->attributeString('step', '1'));
        $unit = $this->attributeString('unit');
        $disabled = $this->getAttribute('disabled', false) ? ' disabled' : '';
        $readonly = $this->getAttribute('readonly', false) ? ' readonly' : '';
        $required = $this->isRequired() ? ' required' : '';
        $wrapperClass = 'wp-field-spinner';
        $customClass = $this->getAttribute('class', '');
        if (is_string($customClass) && $customClass !== '') {
            $wrapperClass .= ' '.trim($customClass);
        }

        $html = sprintf('<div class="%s">', esc_attr($wrapperClass));
        $html .= sprintf(
            '<button type="button" class="wp-field-spinner-btn wp-field-spinner-down" data-step="%s">◄</button>',
            $step,
        );
        $html .= '<div class="wp-field-spinner-input-wrap">';
        $html .= sprintf(
            '<input type="number" name="%s" value="%s" min="%s" max="%s" step="%s"%s%s%s />',
            $name,
            $value,
            $min,
            $max,
            $step,
            $disabled,
            $readonly,
            $required,
        );

        if (is_string($unit) && $unit !== '') {
            $html .= sprintf('<span class="wp-field-spinner-unit">%s</span>', esc_html($unit));
        }

        $html .= '</div>';
        $html .= sprintf(
            '<button type="button" class="wp-field-spinner-btn wp-field-spinner-up" data-step="%s">►</button>',
            $step,
        );
        $html .= '</div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }
}
