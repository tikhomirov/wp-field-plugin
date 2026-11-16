<?php

declare(strict_types=1);

use WpField\Field\Field;

it('renders radio as native html without vanilla wrapper', function (): void {
    $field = Field::make('radio', 'delivery')
        ->label('Delivery')
        ->attribute('options', [
            'courier' => 'Courier',
            'pickup' => 'Pickup',
        ])
        ->value('pickup');

    $html = $field->render();

    expect($html)
        ->toContain('wp-field-radio-group')
        ->and($html)->toContain('type="radio"')
        ->and($html)->toContain('value="pickup"')
        ->and($html)->toContain('checked')
        ->and($html)->not->toContain('data-field-type="radio"');
});

it('renders fieldset with nested modern fields without vanilla wrapper', function (): void {
    $field = Field::make('fieldset', 'layout')
        ->attribute('legend', 'Layout settings')
        ->fields([
            Field::text('layout_title')->label('Title')->value('Hero'),
            [
                'id' => 'layout_enabled',
                'type' => 'checkbox',
                'label' => 'Enabled',
                'value' => '1',
            ],
        ]);

    $html = $field->render();

    expect($html)
        ->toContain('<fieldset')
        ->and($html)->toContain('Layout settings')
        ->and($html)->toContain('name="layout_title"')
        ->and($html)->toContain('name="layout_enabled"')
        ->and($html)->not->toContain('data-field-type="fieldset"');
});

it('renders image picker select with data image sources', function (): void {
    $field = Field::make('image_picker', 'skin')
        ->attribute('options', [
            'light' => ['src' => 'https://example.com/light.png', 'label' => 'Light'],
            'dark' => ['src' => 'https://example.com/dark.png', 'label' => 'Dark'],
        ])
        ->value('dark');

    $html = $field->render();

    expect($html)
        ->toContain('wp-field-image-picker')
        ->and($html)->toContain('data-img-src="https://example.com/dark.png"')
        ->and($html)->toContain('value="dark"')
        ->and($html)->toContain('selected')
        ->and($html)->not->toContain('data-field-type="image_picker"');
});

it('renders palette from palettes attribute and keeps selected value', function (): void {
    $field = Field::make('palette', 'theme_palette')
        ->attribute('palettes', [
            'warm' => ['#ff0000', '#ff9900'],
            'cold' => ['#0033ff', '#00ccff'],
        ])
        ->value('cold');

    $html = $field->render();

    expect($html)
        ->toContain('wp-field-palette')
        ->and($html)->toContain('wp-field-palette-color')
        ->and($html)->toContain('value="cold"')
        ->and($html)->toContain('checked')
        ->and($html)->not->toContain('data-field-type="palette"');
});

it('renders and sanitizes link value shape', function (): void {
    $field = Field::make('link', 'cta_link')->value([
        'url' => 'https://example.com/page',
        'text' => 'Open',
        'target' => '_blank',
    ]);

    $html = $field->render();
    $sanitized = $field->sanitize([
        'url' => 'https://example.com/page<script>',
        'text' => '<b>Open</b>',
        'target' => '_top',
    ]);

    expect($html)
        ->toContain('wp-field-link')
        ->and($html)->toContain('name="cta_link[url]"')
        ->and($html)->toContain('name="cta_link[text]"')
        ->and($html)->toContain('name="cta_link[target]"')
        ->and($html)->not->toContain('data-field-type="link"')
        ->and($sanitized)->toBe([
            'url' => 'https://example.com/page<script>',
            'text' => 'Open',
            'target' => '_self',
        ]);
});

it('renders backup import export ui and validates json payload', function (): void {
    $field = Field::make('backup', 'settings_backup')
        ->attribute('export_data', [
            'api_key' => 'demo',
            'enabled' => true,
        ]);

    $html = $field->render();

    expect($html)
        ->toContain('wp-field-backup')
        ->and($html)->toContain('wp-field-backup-export')
        ->and($html)->toContain('wp-field-backup-import')
        ->and($html)->not->toContain('data-field-type="backup"')
        ->and($field->validate('{"ok":true}'))->toBeTrue()
        ->and($field->validate('{broken json}'))->toBeFalse();
});
