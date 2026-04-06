<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class ContentField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'content');
    }

    public function content(string $content): static
    {
        return $this->attribute('content', $content);
    }

    public function render(): string
    {
        $content = $this->getAttribute('content', $this->getAttribute('label', ''));
        $content = is_scalar($content) ? (string) $content : '';

        return function_exists('wp_kses_post') ? wp_kses_post($content) : $content;
    }
}
