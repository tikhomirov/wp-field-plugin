<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;
use WpField\Field\Types\Concerns\HandlesNestedFieldConfigs;

class TabbedField extends AbstractField
{
    use HandlesNestedFieldConfigs;

    public function __construct(string $name)
    {
        parent::__construct($name, 'tabbed');
        $this->label($name);
    }

    /**
     * @param  array<int|string, mixed>  $tabs
     */
    public function tabs(array $tabs): static
    {
        return $this->attribute('tabs', $tabs);
    }

    public function render(): string
    {
        $tabs = $this->normalizeTabs();

        if ($tabs === []) {
            return '<p class="description">No tabs provided</p>';
        }

        $fieldId = esc_attr($this->attributeString('id', $this->name));
        $activeIndex = $this->resolveActiveIndex($tabs);

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $fieldId, esc_html($label));
        }

        $html .= sprintf('<div class="wp-field-tabbed" data-field-id="%s" data-default-tab="%d">', $fieldId, $activeIndex);
        $html .= '<div class="wp-field-tabbed-nav" role="tablist">';

        foreach ($tabs as $index => $tab) {
            $title = isset($tab['title']) && is_scalar($tab['title']) ? (string) $tab['title'] : 'Tab '.($index + 1);
            $icon = isset($tab['icon']) && is_scalar($tab['icon']) ? (string) $tab['icon'] : '';
            $isActive = $index === $activeIndex;
            $tabId = sprintf('%s-%d', $fieldId, $index);

            $html .= sprintf(
                '<button type="button" class="wp-field-tabbed-nav-item %s" role="tab" aria-selected="%s" aria-controls="tab-pane-%s" data-tab="%s">',
                $isActive ? 'active' : '',
                $isActive ? 'true' : 'false',
                esc_attr($tabId),
                esc_attr($tabId),
            );

            if ($icon !== '') {
                $html .= sprintf('<span class="wp-field-tabbed-icon">%s</span>', esc_html($icon));
            }

            $html .= sprintf('<span>%s</span></button>', esc_html($title));
        }

        $html .= '</div><div class="wp-field-tabbed-content">';

        foreach ($tabs as $index => $tab) {
            $tabId = sprintf('%s-%d', $fieldId, $index);
            $isActive = $index === $activeIndex;
            $content = isset($tab['content']) && is_scalar($tab['content']) ? (string) $tab['content'] : '';

            $html .= sprintf(
                '<div id="tab-pane-%s" class="wp-field-tabbed-pane %s" role="tabpanel" data-tab="%s">',
                esc_attr($tabId),
                $isActive ? 'active' : '',
                esc_attr($tabId),
            );

            if ($content !== '') {
                $html .= function_exists('wp_kses_post') ? wp_kses_post($content) : $content;
            }

            $fields = $this->normalizeNestedFields($tab['fields'] ?? []);
            $html .= $this->renderNestedFields($fields);
            $html .= '</div>';
        }

        $html .= '</div></div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeTabs(): array
    {
        $tabs = $this->getAttribute('tabs', []);
        if (! is_array($tabs)) {
            return [];
        }

        $normalized = [];
        foreach ($tabs as $tab) {
            if (! is_array($tab)) {
                continue;
            }

            $normalized[] = $tab;
        }

        return $normalized;
    }

    /**
     * @param  array<int, array<string, mixed>>  $tabs
     */
    private function resolveActiveIndex(array $tabs): int
    {
        foreach ($tabs as $index => $tab) {
            if (! empty($tab['active'])) {
                return $index;
            }
        }

        return 0;
    }
}
