<?php

declare(strict_types=1);

namespace WpField\Field\Types;

class TextField extends InputField
{
    public function __construct(string $name, string $type = 'text')
    {
        parent::__construct($name, $type);
    }
}
