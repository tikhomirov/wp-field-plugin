<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class IconField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'icon');
    }

    public function library(string $library): static
    {
        return $this->attribute('library', $library);
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $id = esc_attr($this->attributeString('id', $this->name));
        $value = $this->normalizeValue($this->getValue());
        $library = $this->attributeString('library', 'dashicons');
        $buttonText = $this->attributeString('button_text', __('Выбрать иконку', 'wp-field'));

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $html .= '<div class="wp-field-icon-wrapper wp-field-icon-picker">';
        $html .= sprintf('<input type="text" id="%s" name="%s" value="%s" class="regular-text wp-field-icon-value" data-library="%s">', $id, $name, esc_attr($value), esc_attr($library));
        $html .= sprintf('<button type="button" class="button wp-field-icon-button" data-field-id="%s">%s</button>', $id, esc_html($buttonText));
        $html .= sprintf('<span class="wp-field-icon-preview %s" aria-hidden="true"></span>', esc_attr($value));
        $html .= '<div class="wp-field-icon-modal" style="display:none;">';
        $html .= '<div class="wp-field-icon-modal-header">';
        $html .= sprintf('<input type="text" class="wp-field-icon-search" placeholder="%s">', esc_attr__('Search icons...', 'wp-field'));
        $html .= sprintf('<button type="button" class="button wp-field-icon-close">%s</button>', esc_html('×'));
        $html .= '</div>';
        $html .= '<div class="wp-field-icon-grid">';

        foreach ($this->getIconLibrary($library) as $icon) {
            $html .= sprintf('<span class="%s %s" data-icon="%s" title="%s"></span>', esc_attr($library), esc_attr($icon), esc_attr($icon), esc_attr($icon));
        }

        $html .= '</div></div></div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    public function sanitize(mixed $value): string
    {
        return $this->normalizeValue($value);
    }

    private function normalizeValue(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return trim(sanitize_text_field((string) $value));
    }

    /**
     * @return array<int, string>
     */
    private function getIconLibrary(string $library): array
    {
        if ($library === 'dashicons') {
            return [
                'dashicons-admin-site', 'dashicons-dashboard', 'dashicons-admin-post', 'dashicons-admin-media',
                'dashicons-admin-links', 'dashicons-admin-page', 'dashicons-admin-comments', 'dashicons-admin-appearance',
                'dashicons-admin-plugins', 'dashicons-admin-users', 'dashicons-admin-tools', 'dashicons-admin-settings',
                'dashicons-admin-network', 'dashicons-admin-home', 'dashicons-admin-generic', 'dashicons-admin-collapse',
                'dashicons-filter', 'dashicons-admin-customizer', 'dashicons-admin-multisite', 'dashicons-welcome-write-blog',
                'dashicons-welcome-add-page', 'dashicons-welcome-view-site', 'dashicons-welcome-widgets-menus', 'dashicons-welcome-comments',
                'dashicons-welcome-learn-more', 'dashicons-format-aside', 'dashicons-format-image', 'dashicons-format-gallery',
                'dashicons-format-video', 'dashicons-format-status', 'dashicons-format-quote', 'dashicons-format-chat',
                'dashicons-format-audio', 'dashicons-camera', 'dashicons-images-alt', 'dashicons-images-alt2',
                'dashicons-video-alt', 'dashicons-video-alt2', 'dashicons-video-alt3', 'dashicons-media-archive',
                'dashicons-media-audio', 'dashicons-media-code', 'dashicons-media-default', 'dashicons-media-document',
                'dashicons-media-interactive', 'dashicons-media-spreadsheet', 'dashicons-media-text', 'dashicons-media-video',
                'dashicons-playlist-audio', 'dashicons-playlist-video', 'dashicons-controls-play', 'dashicons-controls-pause',
            ];
        }

        return apply_filters('wp_field_icon_library', [], $library);
    }
}
