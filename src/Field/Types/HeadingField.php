<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class HeadingField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'heading');
    }

    public function tag(string $tag): static
    {
        return $this->attribute('tag', $tag);
    }

    public function render(): string
    {
        $tag = $this->stringify($this->getAttribute('tag', 'h3'), 'h3');
        $class = $this->getAttribute('class', 'wp-field-heading');
        $escapedTag = function_exists('tag_escape') ? tag_escape($tag) : htmlspecialchars($tag, ENT_QUOTES);
        $label = $this->getAttribute('label', '');

        return sprintf(
            '<%s class="%s">%s</%s>',
            $escapedTag,
            esc_attr(is_string($class) && $class !== '' ? $class : 'wp-field-heading'),
            esc_html($this->stringify($label)),
            $escapedTag,
        );
    }
}
