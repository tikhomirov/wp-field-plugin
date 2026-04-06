<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class CodeEditorField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'code_editor');
    }

    public function mode(string $mode): static
    {
        return $this->attribute('mode', $mode);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('code_editor');
    }
}
