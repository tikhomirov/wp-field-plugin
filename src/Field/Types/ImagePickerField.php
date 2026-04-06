<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class ImagePickerField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'image_picker');
    }

    /**
     * @param  array<int|string, mixed>  $options
     */
    public function options(array $options): static
    {
        return $this->attribute('options', $options);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('image_picker');
    }
}
