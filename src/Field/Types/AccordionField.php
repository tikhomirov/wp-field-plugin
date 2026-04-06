<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class AccordionField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'accordion');
        $this->label($name);
    }

    /**
     * @param  array<int|string, mixed>  $sections
     */
    public function sections(array $sections): static
    {
        return $this->attribute('sections', $sections);
    }

    /**
     * @param  array<int|string, mixed>  $items
     */
    public function items(array $items): static
    {
        return $this->attribute('items', $items);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('accordion');
    }
}
