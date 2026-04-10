<?php

declare(strict_types=1);

namespace WpField\Field\Types;

class ImageSelectField extends ChoiceField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'image_select');
    }

    public function render(): string
    {
        $options = $this->getOptions();
        if ($options === []) {
            return '<p class="description">No options provided</p>';
        }

        $value = $this->getValue();
        $name = esc_attr($this->name);
        $wrapperClass = 'wp-field-image-select';
        $customClass = $this->getAttribute('class', '');
        if (is_string($customClass) && $customClass !== '') {
            $wrapperClass .= ' '.trim($customClass);
        }

        $html = '';
        $rawLabel = $this->getAttribute('label');
        if ($rawLabel !== null && is_string($rawLabel) && $rawLabel !== '') {
            $html .= sprintf('<p class="wp-field-image-select-label">%s</p>', esc_html($this->stringify($rawLabel)));
        }

        $html .= sprintf('<div class="%s">', esc_attr($wrapperClass));

        foreach ($options as $key => $option) {
            $imageSrc = is_array($option) ? $this->stringify($option['src'] ?? '') : $this->stringify($option);
            $imageLabel = is_array($option) ? $this->stringify($option['label'] ?? $key) : $this->stringify($key);
            $checked = is_scalar($value) ? (string) $value === (string) $key : false;
            $itemClass = $checked ? ' selected' : '';

            $html .= sprintf(
                '<label class="wp-field-image-select-item%s"><input type="radio" name="%s" value="%s"%s /> <img src="%s" alt="%s" /><span>%s</span></label>',
                $itemClass,
                $name,
                esc_attr((string) $key),
                $checked ? ' checked' : '',
                esc_url($imageSrc),
                esc_attr($imageLabel),
                esc_html($this->stringify($imageLabel)),
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
