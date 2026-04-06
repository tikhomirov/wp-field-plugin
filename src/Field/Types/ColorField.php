<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class ColorField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'color');
        $this->label($name);
    }

    public function alpha(bool $enabled = true): static
    {
        return $this->attribute('alpha', $enabled);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('color');
    }
}
