<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class BorderField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'border');
    }

    /**
     * @param  array<int, string>  $styles
     */
    public function styles(array $styles): static
    {
        return $this->attribute('styles', $styles);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('border');
    }
}
