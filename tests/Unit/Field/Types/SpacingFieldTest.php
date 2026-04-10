<?php

declare(strict_types=1);

use WpField\Field\Types\SpacingField;

beforeEach(function (): void {
    $this->field = new SpacingField('test_spacing');
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

it('can set sides', function (): void {
    $sides = ['top', 'right', 'bottom'];
    $this->field->sides($sides);

    expect($this->field->getAttribute('sides'))->toBe($sides);
});

it('sides is chainable', function (): void {
    $result = $this->field->sides(['top', 'bottom']);

    expect($result)->toBe($this->field);
});

it('renders spacing with default sides', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-spacing')
        ->and($html)->toContain('Top')
        ->and($html)->toContain('Right')
        ->and($html)->toContain('Bottom')
        ->and($html)->toContain('Left');
});

it('renders spacing with custom sides', function (): void {
    $this->field->sides(['top', 'bottom']);
    $html = $this->field->render();

    expect($html)->toContain('Top')
        ->and($html)->toContain('Bottom');
});

it('renders spacing with default units', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('px')
        ->and($html)->toContain('em')
        ->and($html)->toContain('rem');
});

it('renders spacing with custom units', function (): void {
    $this->field->units(['px', '%', 'vh']);
    $html = $this->field->render();

    expect($html)->toContain('px')
        ->and($html)->toContain('%')
        ->and($html)->toContain('vh');
});

it('renders spacing with label', function (): void {
    $this->field->label('Spacing');
    $html = $this->field->render();

    expect($html)->toContain('Spacing')
        ->and($html)->toContain('<label');
});

it('renders spacing with description', function (): void {
    $this->field->description('Set spacing values');
    $html = $this->field->render();

    expect($html)->toContain('Set spacing values');
});

it('renders spacing with values', function (): void {
    $this->field->value(['top' => '10', 'right' => '20', 'bottom' => '30', 'left' => '40', 'unit' => 'px']);
    $html = $this->field->render();

    expect($html)->toContain('value="10"')
        ->and($html)->toContain('value="20"')
        ->and($html)->toContain('selected');
});

it('sanitizes spacing array', function (): void {
    $input = ['top' => '10', 'right' => '20', 'bottom' => '30', 'left' => '40', 'unit' => 'px'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized['top'])->toBe('10')
        ->and($sanitized['unit'])->toBe('px');
});

it('sanitizes invalid unit to default', function (): void {
    $input = ['top' => '10', 'unit' => 'invalid'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['unit'])->toBe('px');
});

it('sanitizes invalid side values to empty string', function (): void {
    $input = ['top' => 'invalid', 'unit' => 'px'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['top'])->toBe('');
});

it('sanitizes non-array to defaults', function (): void {
    $sanitized = $this->field->sanitize('invalid');

    expect($sanitized)->toBeArray()
        ->and($sanitized['top'])->toBe('');
});

it('validates valid spacing array', function (): void {
    $input = ['top' => '10', 'right' => '20', 'bottom' => '30', 'left' => '40', 'unit' => 'px'];

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

it('validates invalid unit', function (): void {
    $input = ['top' => '10', 'unit' => 'invalid'];

    expect($this->field->validate($input))->toBeFalse();
});

it('validates invalid side value', function (): void {
    $input = ['top' => 'invalid', 'unit' => 'px'];

    expect($this->field->validate($input))->toBeFalse();
});

it('filters invalid sides with defaults', function (): void {
    $this->field->sides(['invalid', 'top', 'bottom']);
    $html = $this->field->render();

    expect($html)->toContain('Top')
        ->and($html)->toContain('Bottom');
});

it('handles empty sides array with defaults', function (): void {
    $this->field->sides([]);
    $html = $this->field->render();

    expect($html)->toContain('Top');
});

it('resolves sides filters non-scalar elements', function (): void {
    $this->field->sides(['top', ['invalid'], 'bottom']);
    $html = $this->field->render();

    expect($html)->toContain('Top')
        ->and($html)->toContain('Bottom');
});

it('resolves units returns defaults when empty array', function (): void {
    $this->field->units([]);
    $html = $this->field->render();

    expect($html)->toContain('px');
});

it('resolves units filters non-scalar elements', function (): void {
    $this->field->units(['px', ['invalid'], 'em']);
    $html = $this->field->render();

    expect($html)->toContain('px')
        ->and($html)->toContain('em');
});
