<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class BackgroundField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'background');
    }

    /**
     * @param  array<int|string, mixed>  $fields
     */
    public function backgroundFields(array $fields): static
    {
        return $this->attribute('background_fields', $fields);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('background');
    }
}
