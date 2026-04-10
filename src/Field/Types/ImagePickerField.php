<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class ImagePickerField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'image_picker');
    }

    /**
     * @param  array<int|string, mixed>  $options
     */
    public function options(array $options): static
    {
        return $this->attribute('options', $options);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $id = esc_attr($this->attributeString('id', $this->name));
        $value = $this->getValue();
        $selectedValue = is_scalar($value) ? (string) $value : '';
        $options = $this->getAttribute('options', []);
        $options = is_array($options) ? $options : [];

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $html .= sprintf('<select class="wp-field-image-picker" id="%s" name="%s">', $id, $name);

        foreach ($options as $optionValue => $optionData) {
            $selected = $selectedValue === (string) $optionValue ? ' selected' : '';
            $image = '';
            $optionLabel = (string) $optionValue;

            if (is_array($optionData)) {
                $image = isset($optionData['src']) && is_scalar($optionData['src']) ? (string) $optionData['src'] : '';
                $optionLabel = isset($optionData['label']) && is_scalar($optionData['label'])
                    ? (string) $optionData['label']
                    : $optionLabel;
            } elseif (is_scalar($optionData)) {
                $image = (string) $optionData;
            }

            $html .= sprintf(
                '<option value="%s" data-img-src="%s"%s>%s</option>',
                esc_attr((string) $optionValue),
                esc_url($image),
                $selected,
                esc_html($optionLabel),
            );
        }

        $html .= '</select>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }
}
