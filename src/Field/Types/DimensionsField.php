<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class DimensionsField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'dimensions');
    }

    /**
     * @param  array<int, string>  $units
     */
    public function units(array $units): static
    {
        return $this->attribute('units', $units);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('dimensions');
    }
}
