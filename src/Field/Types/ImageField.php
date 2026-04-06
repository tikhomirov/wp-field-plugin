<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class ImageField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'image');
    }

    public function buttonText(string $text): static
    {
        return $this->attribute('button_text', $text);
    }

    public function removeText(string $text): static
    {
        return $this->attribute('remove_text', $text);
    }

    public function preview(bool $enabled = true): static
    {
        return $this->attribute('preview', $enabled);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('image');
    }
}
