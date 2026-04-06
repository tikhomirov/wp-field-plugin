<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class GalleryField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'gallery');
    }

    public function addButton(string $text): static
    {
        return $this->attribute('add_button', $text);
    }

    public function editButton(string $text): static
    {
        return $this->attribute('edit_button', $text);
    }

    public function clearButton(string $text): static
    {
        return $this->attribute('clear_button', $text);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('gallery');
    }
}
