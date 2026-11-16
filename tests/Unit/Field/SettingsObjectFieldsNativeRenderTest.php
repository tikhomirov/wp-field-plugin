<?php

declare(strict_types=1);

use WpField\Field\Field;

it('renders and sanitizes typography as native settings object', function (): void {
    $field = Field::make('typography', 'font')->value([
        'font_family' => 'Arial',
        'font_size' => '16',
        'font_weight' => '700',
        'line_height' => '1.5',
        'text_align' => 'center',
        'text_transform' => 'uppercase',
        'color' => ' <b>#ffffff</b> ',
    ]);

    $html = $field->render();
    $sanitized = $field->sanitize($field->getValue());

    expect($html)
        ->toContain('wp-field-typography')
        ->toContain('name="font[font_family]"')
        ->toContain('name="font[color]"')
        ->and($sanitized)->toMatchArray([
            'font_family' => 'Arial',
            'font_size' => '16',
            'font_weight' => '700',
            'line_height' => '1.5',
            'text_align' => 'center',
            'text_transform' => 'uppercase',
            'color' => '#ffffff',
        ])
        ->and($field->validate(['font_size' => 'abc']))->toBeFalse()
        ->and($field->validate(['font_size' => '12']))->toBeTrue();
});

it('renders spacing with composite shape and handles partial values', function (): void {
    $field = Field::make('spacing', 'padding')
        ->attribute('units', ['px', 'rem'])
        ->value([
            'top' => '10',
            'left' => 'oops',
            'unit' => 'rem',
        ]);

    $html = $field->render();
    $sanitized = $field->sanitize($field->getValue());

    expect($html)
        ->toContain('wp-field-spacing')
        ->toContain('name="padding[top]"')
        ->toContain('name="padding[unit]"')
        ->and($sanitized)->toMatchArray([
            'top' => '10',
            'left' => '',
            'unit' => 'rem',
        ])
        ->and($field->validate(['top' => 'x', 'unit' => 'px']))->toBeFalse()
        ->and($field->validate(['top' => '8', 'unit' => 'px']))->toBeTrue();
});

it('renders dimensions and validates numeric sub keys', function (): void {
    $field = Field::make('dimensions', 'box')->value([
        'width' => '100',
        'height' => '50',
        'unit' => '%',
    ]);

    $html = $field->render();

    expect($html)
        ->toContain('wp-field-dimensions')
        ->toContain('name="box[width]"')
        ->toContain('name="box[height]"')
        ->and($field->sanitize(['width' => ' 120 ', 'height' => 'abc', 'unit' => 'px']))->toMatchArray([
            'width' => '120',
            'height' => '',
            'unit' => 'px',
        ])
        ->and($field->validate(['width' => 'wide', 'height' => '40', 'unit' => 'px']))->toBeFalse()
        ->and($field->validate(['width' => '10', 'height' => '', 'unit' => 'px']))->toBeTrue();
});

it('renders border and enforces style options', function (): void {
    $field = Field::make('border', 'frame')->attribute('styles', ['solid', 'dashed']);

    $html = $field->render();
    $sanitized = $field->sanitize([
        'style' => 'double',
        'width' => '3',
        'color' => ' <i>#000</i> ',
    ]);

    expect($html)
        ->toContain('wp-field-border')
        ->toContain('name="frame[style]"')
        ->and($sanitized)->toMatchArray([
            'style' => 'solid',
            'width' => '3',
            'color' => '#000',
        ])
        ->and($field->validate(['style' => 'double', 'width' => '2']))->toBeFalse()
        ->and($field->validate(['style' => 'solid', 'width' => '2']))->toBeTrue();
});

it('renders background and sanitizes canonical keys', function (): void {
    $field = Field::make('background', 'hero_bg')->attribute('background_fields', [
        'color' => true,
        'image' => true,
        'position' => true,
        'size' => true,
        'repeat' => true,
        'attachment' => true,
    ]);

    $html = $field->render();
    $sanitized = $field->sanitize([
        'color' => ' <b>#fff</b> ',
        'image' => ' 44 ',
        'position' => 'center center',
        'size' => 'contain',
        'repeat' => 'repeat-x',
        'attachment' => 'fixed',
    ]);

    expect($html)
        ->toContain('wp-field-background')
        ->toContain('name="hero_bg[color]"')
        ->toContain('name="hero_bg[position]"')
        ->and($sanitized)->toMatchArray([
            'color' => '#fff',
            'image' => '44',
            'position' => 'center center',
            'size' => 'contain',
            'repeat' => 'repeat-x',
            'attachment' => 'fixed',
        ])
        ->and($field->validate(['position' => 'invalid']))->toBeFalse()
        ->and($field->validate(['position' => 'left top', 'size' => 'auto', 'repeat' => 'repeat', 'attachment' => 'scroll']))->toBeTrue();
});

it('renders link_color with configurable states and keeps composite shape', function (): void {
    $field = Field::make('link_color', 'links')
        ->required()
        ->attribute('states', ['normal', 'hover', 'visited']);

    $html = $field->render();
    $sanitized = $field->sanitize([
        'normal' => ' <b>#111</b> ',
        'hover' => '#222',
        'visited' => '#333',
    ]);

    expect($html)
        ->toContain('wp-field-link-color')
        ->toContain('name="links[normal]"')
        ->toContain('name="links[visited]"')
        ->and($sanitized)->toMatchArray([
            'normal' => '#111',
            'hover' => '#222',
            'visited' => '#333',
        ])
        ->and($field->validate('bad'))->toBeFalse()
        ->and($field->validate(['normal' => '#111']))->toBeTrue();
});
