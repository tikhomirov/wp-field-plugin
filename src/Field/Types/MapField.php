<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class MapField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'map');
    }

    public function zoom(int $zoom): static
    {
        return $this->attribute('zoom', $zoom);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('map');
    }
}
