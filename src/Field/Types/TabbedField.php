<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class TabbedField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'tabbed');
        $this->label($name);
    }

    /**
     * @param  array<int|string, mixed>  $tabs
     */
    public function tabs(array $tabs): static
    {
        return $this->attribute('tabs', $tabs);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('tabbed');
    }
}
