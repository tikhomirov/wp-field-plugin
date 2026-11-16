<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class SliderField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'slider');
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

    public function showValue(bool $show = true): static
    {
        return $this->attribute('show_value', $show);
    }

    public function render(): string
    {
        $rawValue = $this->getValue();
        $value = esc_attr($this->stringify($rawValue));
        $name = esc_attr($this->name);
        $min = esc_attr($this->attributeString('min', '0'));
        $max = esc_attr($this->attributeString('max', '100'));
        $step = esc_attr($this->attributeString('step', '1'));
        $showValue = (bool) $this->getAttribute('show_value', false);
        $disabled = $this->getAttribute('disabled', false) ? ' disabled' : '';
        $readonly = $this->getAttribute('readonly', false) ? ' readonly' : '';
        $wrapperClass = 'wp-field-slider-wrapper';
        $customClass = $this->getAttribute('class', '');
        if (is_string($customClass) && $customClass !== '') {
            $wrapperClass .= ' '.trim($customClass);
        }

        $html = sprintf('<div class="%s">', esc_attr($wrapperClass));
        $html .= sprintf(
            '<input type="range" class="wp-field-slider" name="%s" value="%s" min="%s" max="%s" step="%s"%s%s />',
            $name,
            $value,
            $min,
            $max,
            $step,
            $disabled,
            $readonly,
        );

        if ($showValue) {
            $html .= sprintf('<div class="wp-field-slider-value">%s</div>', esc_html($this->stringify($rawValue)));
        }

        $html .= '</div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }
}
