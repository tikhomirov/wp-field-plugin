<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class TypographyField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'typography');
    }

    /**
     * @param  array<int|string, mixed>  $options
     */
    public function options(array $options): static
    {
        return $this->attribute('options', $options);
    }

    /**
     * @param  array<int|string, mixed>  $default
     */
    public function defaultValue(array $default): static
    {
        return $this->attribute('default', $default);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('typography');
    }
}
