<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class IconField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'icon');
    }

    public function library(string $library): static
    {
        return $this->attribute('library', $library);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('icon');
    }
}
