<?php

declare(strict_types=1);

use WpField\Field\Types\PaletteField;

beforeEach(function (): void {
    $this->field = new PaletteField('test_palette');
});

it('can set options', function (): void {
    $options = ['palette1' => ['#ff0000', '#00ff00', '#0000ff']];
    $this->field->options($options);

    expect($this->field->getAttribute('options'))->toBe($options);
});

it('options is chainable', function (): void {
    $result = $this->field->options(['palette1' => ['#ff0000']]);

    expect($result)->toBe($this->field);
});

it('can set palettes', function (): void {
    $palettes = ['palette1' => ['#ff0000', '#00ff00']];
    $this->field->palettes($palettes);

    expect($this->field->getAttribute('palettes'))->toBe($palettes);
});

it('palettes is chainable', function (): void {
    $result = $this->field->palettes(['palette1' => ['#ff0000']]);

    expect($result)->toBe($this->field);
});

it('renders message when no palettes provided', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('No palettes provided');
});

it('renders palette with palettes attribute', function (): void {
    $this->field->palettes(['palette1' => ['#ff0000', '#00ff00', '#0000ff']]);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-palette')
        ->and($html)->toContain('wp-field-palette-color')
        ->and($html)->toContain('background-color:#ff0000');
});

it('renders palette with options attribute as fallback', function (): void {
    $this->field->options(['palette1' => ['#ff0000', '#00ff00']]);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-palette')
        ->and($html)->toContain('background-color:#ff0000');
});

it('renders palette with label', function (): void {
    $this->field->palettes(['palette1' => ['#ff0000']]);
    $this->field->label('Color Palette');
    $html = $this->field->render();

    expect($html)->toContain('Color Palette')
        ->and($html)->toContain('<label');
});

it('renders palette with description', function (): void {
    $this->field->palettes(['palette1' => ['#ff0000']]);
    $this->field->description('Select a color palette');
    $html = $this->field->render();

    expect($html)->toContain('Select a color palette');
});

it('renders selected palette as checked', function (): void {
    $this->field->palettes(['palette1' => ['#ff0000'], 'palette2' => ['#00ff00']]);
    $this->field->value('palette2');
    $html = $this->field->render();

    expect($html)->toContain('value="palette2"')
        ->and($html)->toContain('checked');
});

it('renders selected palette with selected class', function (): void {
    $this->field->palettes(['palette1' => ['#ff0000']]);
    $this->field->value('palette1');
    $html = $this->field->render();

    expect($html)->toContain('selected');
});

it('renders radio inputs for palettes', function (): void {
    $this->field->palettes(['palette1' => ['#ff0000']]);
    $html = $this->field->render();

    expect($html)->toContain('type="radio"')
        ->and($html)->toContain('name="test_palette"');
});

it('handles single color in palette', function (): void {
    $this->field->palettes(['palette1' => '#ff0000']);
    $html = $this->field->render();

    expect($html)->toContain('background-color:#ff0000');
});

it('handles array of colors in palette', function (): void {
    $this->field->palettes(['palette1' => ['#ff0000', '#00ff00', '#0000ff']]);
    $html = $this->field->render();

    expect($html)->toContain('background-color:#ff0000')
        ->and($html)->toContain('background-color:#00ff00')
        ->and($html)->toContain('background-color:#0000ff');
});

it('skips non-scalar colors in palette', function (): void {
    $this->field->palettes(['palette1' => ['#ff0000', ['invalid'], '#00ff00']]);
    $html = $this->field->render();

    expect($html)->toContain('background-color:#ff0000')
        ->and($html)->toContain('background-color:#00ff00');
});
