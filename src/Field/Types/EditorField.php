<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class EditorField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'editor');
    }

    public function rows(int $rows): static
    {
        return $this->attribute('rows', $rows);
    }

    public function mediaButtons(bool $enabled = true): static
    {
        return $this->attribute('media_buttons', $enabled);
    }

    public function teeny(bool $enabled = true): static
    {
        return $this->attribute('teeny', $enabled);
    }

    public function wpautop(bool $enabled = true): static
    {
        return $this->attribute('wpautop', $enabled);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('editor');
    }
}
