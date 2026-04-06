<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class SorterField extends AbstractField
{
    use LegacyAdapterBridge;

    public function __construct(string $name)
    {
        parent::__construct($name, 'sorter');
    }

    /**
     * @param  array<int|string, mixed>  $options
     */
    public function options(array $options): static
    {
        return $this->attribute('options', $options);
    }

    /**
     * @param  array<int|string, mixed>  $groups
     */
    public function groups(array $groups): static
    {
        return $this->attribute('groups', $groups);
    }

    public function render(): string
    {
        return $this->renderViaLegacy('sorter');
    }
}
