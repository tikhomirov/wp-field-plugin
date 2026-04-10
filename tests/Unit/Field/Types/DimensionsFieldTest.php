<?php

declare(strict_types=1);

use WpField\Field\Types\DimensionsField;

beforeEach(function (): void {
    $this->field = new DimensionsField('test_dimensions');
});

it('can set units', function (): void {
    $units = ['px', 'em', 'rem'];
    $this->field->units($units);

    expect($this->field->getAttribute('units'))->toBe($units);
});

it('units is chainable', function (): void {
    $result = $this->field->units(['px', '%']);

    expect($result)->toBe($this->field);
});

it('renders dimensions with default units', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-dimensions')
        ->and($html)->toContain('px')
        ->and($html)->toContain('em')
        ->and($html)->toContain('rem');
});

it('renders dimensions with custom units', function (): void {
    $this->field->units(['px', '%', 'vh']);
    $html = $this->field->render();

    expect($html)->toContain('px')
        ->and($html)->toContain('%')
        ->and($html)->toContain('vh');
});

it('renders dimensions with label', function (): void {
    $this->field->label('Dimensions');
    $html = $this->field->render();

    expect($html)->toContain('Dimensions')
        ->and($html)->toContain('<label');
});

it('renders dimensions with description', function (): void {
    $this->field->description('Set width and height');
    $html = $this->field->render();

    expect($html)->toContain('Set width and height');
});

it('renders dimensions with values', function (): void {
    $this->field->value(['width' => '100', 'height' => '200', 'unit' => 'px']);
    $html = $this->field->render();

    expect($html)->toContain('value="100"')
        ->and($html)->toContain('value="200"')
        ->and($html)->toContain('selected');
});

it('sanitizes dimensions array', function (): void {
    $input = ['width' => '100', 'height' => '200', 'unit' => 'px'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized['width'])->toBe('100')
        ->and($sanitized['height'])->toBe('200')
        ->and($sanitized['unit'])->toBe('px');
});

it('sanitizes invalid unit to default', function (): void {
    $input = ['width' => '100', 'height' => '200', 'unit' => 'invalid'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['unit'])->toBe('px');
});

it('sanitizes invalid width to empty string', function (): void {
    $input = ['width' => 'invalid', 'height' => '200', 'unit' => 'px'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['width'])->toBe('');
});

it('sanitizes invalid height to empty string', function (): void {
    $input = ['width' => '100', 'height' => 'invalid', 'unit' => 'px'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['height'])->toBe('');
});

it('sanitizes non-array to defaults', function (): void {
    $sanitized = $this->field->sanitize('invalid');

    expect($sanitized)->toBeArray()
        ->and($sanitized['width'])->toBe('')
        ->and($sanitized['height'])->toBe('');
});

it('validates valid dimensions array', function (): void {
    $input = ['width' => '100', 'height' => '200', 'unit' => 'px'];

    expect($this->field->validate($input))->toBeTrue();
});

it('validates empty array when not required', function (): void {
    expect($this->field->validate([]))->toBeTrue();
});

it('validates non-array when not required', function (): void {
    expect($this->field->validate('invalid'))->toBeTrue();
});

it('validates non-array when required', function (): void {
    $this->field->required();

    expect($this->field->validate('invalid'))->toBeFalse();
});

it('validates invalid width', function (): void {
    $input = ['width' => 'invalid', 'height' => '200', 'unit' => 'px'];

    expect($this->field->validate($input))->toBeFalse();
});

it('validates invalid height', function (): void {
    $input = ['width' => '100', 'height' => 'invalid', 'unit' => 'px'];

    expect($this->field->validate($input))->toBeFalse();
});

it('validates invalid unit', function (): void {
    $input = ['width' => '100', 'height' => '200', 'unit' => 'invalid'];

    expect($this->field->validate($input))->toBeFalse();
});

it('resolves units from attribute', function (): void {
    $this->field->units(['px', 'em']);
    $sanitized = $this->field->sanitize(['width' => '100', 'height' => '200', 'unit' => 'em']);

    expect($sanitized['unit'])->toBe('em');
});

it('handles empty units array with defaults', function (): void {
    $this->field->units([]);
    $html = $this->field->render();

    expect($html)->toContain('px');
});

it('trims unit values', function (): void {
    $this->field->units(['  px  ', '  em  ']);
    $sanitized = $this->field->sanitize(['width' => '100', 'height' => '200', 'unit' => 'px']);

    expect($sanitized['unit'])->toBe('px');
});
