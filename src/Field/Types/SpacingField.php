<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class SpacingField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'spacing');
    }

    /**
     * @param  array<int, string>  $units
     */
    public function units(array $units): static
    {
        return $this->attribute('units', $units);
    }

    /**
     * @param  array<int, string>  $sides
     */
    public function sides(array $sides): static
    {
        return $this->attribute('sides', $sides);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('spacing');
    }
}
