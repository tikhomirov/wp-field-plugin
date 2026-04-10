<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class SubheadingField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'subheading');
    }

    public function tag(string $tag): static
    {
        return $this->attribute('tag', $tag);
    }

    public function render(): string
    {
        $tag = $this->stringify($this->getAttribute('tag', 'h4'), 'h4');
        $class = $this->getAttribute('class', 'wp-field-subheading');
        $escapedTag = function_exists('tag_escape') ? tag_escape($tag) : htmlspecialchars($tag, ENT_QUOTES);
        $label = $this->getAttribute('label', '');

        return sprintf(
            '<%s class="%s">%s</%s>',
            $escapedTag,
            esc_attr(is_string($class) && $class !== '' ? $class : 'wp-field-subheading'),
            esc_html($this->stringify($label)),
            $escapedTag,
        );
    }
}
