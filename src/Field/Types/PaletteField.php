<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class PaletteField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'palette');
    }

    /**
     * @param  array<int|string, mixed>  $options
     */
    public function options(array $options): static
    {
        return $this->attribute('options', $options);
    }

    /**
     * @param  array<int|string, mixed>  $palettes
     */
    public function palettes(array $palettes): static
    {
        return $this->attribute('palettes', $palettes);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $palettes = $this->resolvePalettes();

        if ($palettes === []) {
            return '<p class="description">No palettes provided</p>';
        }

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $value = $this->getValue();
        $selectedValue = is_scalar($value) ? (string) $value : '';
        $html .= '<div class="wp-field-palette">';

        foreach ($palettes as $key => $palette) {
            $colors = is_array($palette) ? $palette : [$palette];
            $isSelected = $selectedValue === (string) $key;

            $html .= sprintf(
                '<label class="wp-field-palette-item %s"><input type="radio" name="%s" value="%s" %s><div class="wp-field-palette-colors">',
                $isSelected ? 'selected' : '',
                $name,
                esc_attr((string) $key),
                $isSelected ? 'checked' : '',
            );

            foreach ($colors as $color) {
                if (! is_scalar($color)) {
                    continue;
                }

                $html .= sprintf(
                    '<span class="wp-field-palette-color" style="background-color:%s;"></span>',
                    esc_attr((string) $color),
                );
            }

            $html .= '</div></label>';
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
    private function resolvePalettes(): array
    {
        $palettes = $this->getAttribute('palettes', []);
        if (is_array($palettes) && $palettes !== []) {
            return $palettes;
        }

        $options = $this->getAttribute('options', []);

        return is_array($options) ? $options : [];
    }
}
