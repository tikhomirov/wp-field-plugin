<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class BackupField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'backup');
    }

    public function render(): string
    {
        return $this->renderViaLegacy('backup');
    }
}
