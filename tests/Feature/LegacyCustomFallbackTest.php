<?php

declare(strict_types=1);

use WpField\Field\Field;

it('renders generic fallback for unknown custom type', function (): void {
    $html = Field::make('my_custom_type', 'custom_payload')
        ->label('Custom Payload')
        ->placeholder('Enter value')
        ->description('Custom field fallback')
        ->value('abc')
        ->render();

    expect($html)
        ->toContain('wp-field-vanilla-fallback')
        ->toContain('data-vanilla-type="my_custom_type"')
        ->toContain('name="custom_payload"')
        ->toContain('value="abc"')
        ->toContain('Enter value')
        ->toContain('Custom field fallback');
});

it('supports custom options shape in fallback mode', function (): void {
    $html = Field::make('my_custom_select', 'custom_select')
        ->label('Custom Select')
        ->attribute('options', [
            'a' => 'Option A',
            'b' => 'Option B',
        ])
        ->value('b')
        ->render();

    expect($html)
        ->toContain('<select')
        ->toContain('name="custom_select"')
        ->toContain('value="b" selected');
});
