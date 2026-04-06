<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class LinkColorField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'link_color');
    }

    /**
     * @param  array<int, string>  $states
     */
    public function states(array $states): static
    {
        return $this->attribute('states', $states);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('link_color');
    }
}
