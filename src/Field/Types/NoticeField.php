<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class NoticeField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'notice');
    }

    public function noticeType(string $type): static
    {
        return $this->attribute('notice_type', $type);
    }

    public function render(): string
    {
        $type = $this->getAttribute('type_notice', $this->getAttribute('notice_type', 'info'));
        $type = is_string($type) ? $type : 'info';
        $safeType = function_exists('sanitize_html_class') ? sanitize_html_class($type) : preg_replace('/[^A-Za-z0-9_-]/', '', $type);
        $class = 'wp-field-notice wp-field-notice-'.($safeType ?: 'info');
        $label = $this->getAttribute('label', '');
        $content = function_exists('wp_kses_post') ? wp_kses_post(is_scalar($label) ? (string) $label : '') : (is_scalar($label) ? (string) $label : '');

        return sprintf('<div class="%s">%s</div>', esc_attr($class), $content);
    }
}
