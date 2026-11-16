<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

abstract class ChoiceField extends AbstractField
{
    /**
     * @param  array<string|int, mixed>  $options
     */
    public function options(array $options): static
    {
        return $this->attribute('options', $options);
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getOptions(): array
    {
        $options = $this->getAttribute('options', []);

        return is_array($options) ? $options : [];
    }
}
