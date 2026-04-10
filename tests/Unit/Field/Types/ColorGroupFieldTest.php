<?php

declare(strict_types=1);

use WpField\Field\Types\ColorGroupField;

beforeEach(function (): void {
    $this->field = new ColorGroupField('test_color_group');
});

it('can set options', function (): void {
    $options = ['primary' => 'Primary Color', 'secondary' => 'Secondary Color'];
    $this->field->options($options);

    expect($this->field->getAttribute('options'))->toBe($options);
});

it('options is chainable', function (): void {
    $result = $this->field->options(['color1' => 'Color 1']);

    expect($result)->toBe($this->field);
});

it('renders message when no options provided', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('No options provided');
});

it('renders color group with options', function (): void {
    $this->field->options(['primary' => 'Primary Color', 'secondary' => 'Secondary Color']);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-color-group')
        ->and($html)->toContain('Primary Color')
        ->and($html)->toContain('Secondary Color');
});

it('renders color group with label', function (): void {
    $this->field->options(['color1' => 'Color 1']);
    $this->field->label('Color Scheme');
    $html = $this->field->render();

    expect($html)->toContain('Color Scheme')
        ->and($html)->toContain('<label');
});

it('renders color group with description', function (): void {
    $this->field->options(['color1' => 'Color 1']);
    $this->field->description('Select colors for your theme');
    $html = $this->field->render();

    expect($html)->toContain('Select colors for your theme');
});

it('renders inputs with color picker class', function (): void {
    $this->field->options(['color1' => 'Color 1']);
    $html = $this->field->render();

    expect($html)->toContain('wp-color-picker-field');
});

it('renders inputs with correct names', function (): void {
    $this->field->options(['primary' => 'Primary']);
    $html = $this->field->render();

    expect($html)->toContain('name="test_color_group[primary]"');
});

it('renders inputs with values', function (): void {
    $this->field->options(['primary' => 'Primary']);
    $this->field->value(['primary' => '#ff0000']);
    $html = $this->field->render();

    expect($html)->toContain('value="#ff0000"');
});

it('sanitizes array of color values', function (): void {
    $input = ['primary' => '#ff0000', 'secondary' => '#00ff00'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized)->toHaveCount(2)
        ->and($sanitized['primary'])->toBe('#ff0000');
});

it('sanitizes trims values', function (): void {
    $input = ['primary' => '  #ff0000  '];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['primary'])->toBe('#ff0000');
});

it('sanitizes filters non-scalar keys', function (): void {
    $input = ['valid' => '#ff0000'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toHaveKey('valid');
});

it('sanitizes filters non-scalar values', function (): void {
    $input = ['primary' => ['invalid'], 'secondary' => '#00ff00'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toHaveKey('secondary')
        ->and($sanitized)->not->toHaveKey('primary');
});

it('sanitizes returns empty array for non-array input', function (): void {
    $sanitized = $this->field->sanitize('invalid');

    expect($sanitized)->toBe([]);
});

it('sanitizes filters HTML from values', function (): void {
    $input = ['primary' => '<script>alert("xss")</script>#ff0000'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['primary'])->not->toContain('<script>');
});

it('resolves colors returns empty array when not array', function (): void {
    $this->field->attribute('colors', 'invalid');
    $html = $this->field->render();

    expect($html)->toContain('No options provided');
});

it('resolves colors filters non-scalar labels', function (): void {
    $this->field->options(['color1' => 'Valid', 'color2' => ['invalid'], 'color3' => 'Also Valid']);
    $html = $this->field->render();

    expect($html)->toContain('Valid')
        ->and($html)->toContain('Also Valid')
        ->and($html)->not->toContain('invalid');
});

it('normalizes value filters non-scalar values', function (): void {
    $this->field->options(['color1' => 'Color 1', 'color2' => 'Color 2']);
    $this->field->value(['color1' => '#ff0000', 'color2' => ['invalid']]);
    $html = $this->field->render();

    expect($html)->toContain('value="#ff0000"')
        ->and($html)->toContain('value=""');
});
