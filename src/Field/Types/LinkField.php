<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class LinkField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'link');
    }

    public function render(): string
    {
        return $this->renderViaLegacy('link');
    }
}
