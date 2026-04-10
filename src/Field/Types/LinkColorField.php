<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class LinkColorField extends AbstractField
{
    private const DEFAULT_STATES = ['normal', 'hover', 'active'];

    public function __construct(string $name)
    {
        parent::__construct($name, 'link_color');
    }

    /**
     * @param  array<int, string>  $states
     */
    public function states(array $states): static
    {
        return $this->attribute('states', $states);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $value = $this->normalizeValue($this->getValue());

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s[normal]">%s</label>', $id, esc_html($label));
        }

        $html .= '<div class="wp-field-link-color">';

        foreach ($this->resolveStates() as $state) {
            $html .= sprintf(
                '<div class="wp-field-link-color-item"><label>%s</label><input type="text" name="%s[%s]" value="%s" class="wp-color-picker-field"></div>',
                esc_html(ucfirst($state)),
                $name,
                esc_attr($state),
                esc_attr($value[$state] ?? ''),
            );
        }

        $html .= '</div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        $normalized = $this->normalizeValue($value);
        $sanitized = [];

        foreach ($this->resolveStates() as $state) {
            $sanitized[$state] = trim(sanitize_text_field($normalized[$state] ?? ''));
        }

        return $sanitized;
    }

    public function validate(mixed $value): bool
    {
        if (! is_array($value)) {
            return ! $this->isRequired();
        }

        $normalized = $this->normalizeValue($value);
        foreach ($this->resolveStates() as $state) {
            $stateValue = $normalized[$state] ?? '';
            if (! is_string($stateValue)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, string>
     */
    private function normalizeValue(mixed $value): array
    {
        if (! is_array($value)) {
            return [
                'normal' => '',
                'hover' => '',
                'active' => '',
            ];
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            if (! is_scalar($key) || ! is_scalar($item)) {
                continue;
            }

            $normalized[(string) $key] = (string) $item;
        }

        foreach (self::DEFAULT_STATES as $state) {
            $normalized[$state] = $normalized[$state] ?? '';
        }

        return $normalized;
    }

    /**
     * @return array<int, string>
     */
    private function resolveStates(): array
    {
        $states = $this->getAttribute('states', self::DEFAULT_STATES);

        if (! is_array($states) || $states === []) {
            return self::DEFAULT_STATES;
        }

        $normalized = [];
        foreach ($states as $state) {
            if (! is_scalar($state)) {
                continue;
            }

            $state = strtolower(trim((string) $state));
            if ($state !== '') {
                $normalized[] = $state;
            }
        }

        return $normalized === [] ? self::DEFAULT_STATES : array_values(array_unique($normalized));
    }
}
