<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;
use WpField\Field\Types\Concerns\HandlesNestedFieldConfigs;

class AccordionField extends AbstractField
{
    use HandlesNestedFieldConfigs;

    public function __construct(string $name)
    {
        parent::__construct($name, 'accordion');
        $this->label($name);
    }

    /**
     * @param  array<int|string, mixed>  $sections
     */
    public function sections(array $sections): static
    {
        return $this->attribute('sections', $sections);
    }

    /**
     * @param  array<int|string, mixed>  $items
     */
    public function items(array $items): static
    {
        return $this->attribute('items', $items);
    }

    public function render(): string
    {
        $items = $this->normalizeItems();

        if ($items === []) {
            return '<p class="description">No items provided</p>';
        }

        $fieldId = esc_attr($this->attributeString('id', $this->name));
        $html = '';

        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $fieldId, esc_html($label));
        }

        $html .= sprintf('<div class="wp-field-accordion" data-field-id="%s">', $fieldId);

        foreach ($items as $index => $item) {
            $title = isset($item['title']) && is_scalar($item['title']) ? (string) $item['title'] : 'Item '.($index + 1);
            $content = isset($item['content']) && is_scalar($item['content']) ? (string) $item['content'] : '';
            $isOpen = ! empty($item['open']);
            $panelId = sprintf('%s-accordion-panel-%d', $fieldId, $index);

            $html .= sprintf(
                '<div class="wp-field-accordion-item %s" data-index="%d">',
                $isOpen ? 'is-open' : '',
                $index,
            );
            $html .= sprintf(
                '<button type="button" class="wp-field-accordion-header" aria-expanded="%s" aria-controls="%s">',
                $isOpen ? 'true' : 'false',
                esc_attr($panelId),
            );
            $html .= sprintf('<span class="wp-field-accordion-title">%s</span>', esc_html($title));
            $html .= '<span class="wp-field-accordion-indicator" aria-hidden="true"></span>';
            $html .= '</button>';
            $html .= sprintf(
                '<div id="%s" class="wp-field-accordion-content" role="region">',
                esc_attr($panelId),
            );

            if ($content !== '') {
                $html .= function_exists('wp_kses_post') ? wp_kses_post($content) : $content;
            }

            $fields = $this->normalizeNestedFields($item['fields'] ?? []);
            $html .= $this->renderNestedFields($fields);
            $html .= '</div></div>';
        }

        $html .= '</div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(): array
    {
        $items = $this->getAttribute('items', $this->getAttribute('sections', []));
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $normalized[] = $item;
        }

        return $normalized;
    }
}
