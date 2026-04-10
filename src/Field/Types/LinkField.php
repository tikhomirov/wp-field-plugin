<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class LinkField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'link');
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $value = $this->normalizeValue($this->getValue());

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s[url]">%s</label>', $id, esc_html($label));
        }

        $html .= '<div class="wp-field-link">';

        $html .= sprintf(
            '<div class="wp-field-link-item"><label>%s</label><input type="url" name="%s[url]" value="%s" placeholder="https://" class="regular-text"></div>',
            esc_html__('URL', 'wp-field'),
            $name,
            esc_attr($value['url']),
        );

        $html .= sprintf(
            '<div class="wp-field-link-item"><label>%s</label><input type="text" name="%s[text]" value="%s" placeholder="%s" class="regular-text"></div>',
            esc_html__('Link Text', 'wp-field'),
            $name,
            esc_attr($value['text']),
            esc_attr(__('Click here', 'wp-field')),
        );

        $html .= sprintf(
            '<div class="wp-field-link-item"><label>%s</label><select name="%s[target]"><option value="_self" %s>%s</option><option value="_blank" %s>%s</option></select></div>',
            esc_html__('Target', 'wp-field'),
            $name,
            $value['target'] === '_self' ? 'selected' : '',
            esc_html__('Same window', 'wp-field'),
            $value['target'] === '_blank' ? 'selected' : '',
            esc_html__('New window', 'wp-field'),
        );

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

        return [
            'url' => filter_var($normalized['url'], FILTER_SANITIZE_URL) ?: '',
            'text' => sanitize_text_field($normalized['text']),
            'target' => $normalized['target'] === '_blank' ? '_blank' : '_self',
        ];
    }

    /**
     * @return array{url: string, text: string, target: string}
     */
    private function normalizeValue(mixed $value): array
    {
        if (! is_array($value)) {
            return ['url' => '', 'text' => '', 'target' => '_self'];
        }

        $url = isset($value['url']) && is_scalar($value['url']) ? (string) $value['url'] : '';
        $text = isset($value['text']) && is_scalar($value['text']) ? (string) $value['text'] : '';
        $target = isset($value['target']) && is_scalar($value['target']) ? (string) $value['target'] : '_self';

        return [
            'url' => $url,
            'text' => $text,
            'target' => $target,
        ];
    }
}
