<?php

declare(strict_types=1);

use WpField\Field\Types\TypographyField;

beforeEach(function (): void {
    $this->field = new TypographyField('test_typography');
});

it('can set options', function (): void {
    $options = ['custom' => 'Custom Font'];
    $this->field->options($options);

    expect($this->field->getAttribute('options'))->toBe($options);
});

it('options is chainable', function (): void {
    $result = $this->field->options(['opt1' => 'Option 1']);

    expect($result)->toBe($this->field);
});

it('can set default value', function (): void {
    $default = ['font_family' => 'Arial', 'font_size' => '16'];
    $this->field->defaultValue($default);

    expect($this->field->getAttribute('default'))->toBe($default);
});

it('defaultValue is chainable', function (): void {
    $result = $this->field->defaultValue(['font_family' => 'Arial']);

    expect($result)->toBe($this->field);
});

it('renders typography field with all controls', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-typography')
        ->and($html)->toContain('font_family')
        ->and($html)->toContain('font_size')
        ->and($html)->toContain('font_weight')
        ->and($html)->toContain('line_height')
        ->and($html)->toContain('text_align')
        ->and($html)->toContain('text_transform')
        ->and($html)->toContain('color');
});

it('renders typography field with label', function (): void {
    $this->field->label('Typography Settings');
    $html = $this->field->render();

    expect($html)->toContain('Typography Settings')
        ->and($html)->toContain('<label');
});

it('renders typography field with description', function (): void {
    $this->field->description('Configure typography settings');
    $html = $this->field->render();

    expect($html)->toContain('Configure typography settings');
});

it('renders typography field with values', function (): void {
    $this->field->value([
        'font_family' => 'Arial',
        'font_size' => '16',
        'font_weight' => '400',
        'line_height' => '1.5',
        'text_align' => 'center',
        'text_transform' => 'uppercase',
        'color' => '#ff0000',
    ]);
    $html = $this->field->render();

    expect($html)->toContain('Arial')
        ->and($html)->toContain('16')
        ->and($html)->toContain('400')
        ->and($html)->toContain('1.5')
        ->and($html)->toContain('#ff0000');
});

it('sanitizes typography array', function (): void {
    $input = [
        'font_family' => 'Arial',
        'font_size' => '16',
        'font_weight' => '400',
        'line_height' => '1.5',
        'text_align' => 'center',
        'text_transform' => 'uppercase',
        'color' => '#ff0000',
    ];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized['font_family'])->toBe('Arial')
        ->and($sanitized['font_size'])->toBe('16');
});

it('sanitizes invalid font family to empty string', function (): void {
    $input = ['font_family' => 'Invalid Font'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['font_family'])->toBe('');
});

it('sanitizes invalid font weight to empty string', function (): void {
    $input = ['font_weight' => '999'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['font_weight'])->toBe('');
});

it('sanitizes invalid text align to empty string', function (): void {
    $input = ['text_align' => 'invalid'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['text_align'])->toBe('');
});

it('sanitizes invalid text transform to empty string', function (): void {
    $input = ['text_transform' => 'invalid'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['text_transform'])->toBe('');
});

it('sanitizes numeric font size', function (): void {
    $input = ['font_size' => '18'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['font_size'])->toBe('18');
});

it('sanitizes invalid font size to empty string', function (): void {
    $input = ['font_size' => 'invalid'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['font_size'])->toBe('');
});

it('sanitizes numeric line height', function (): void {
    $input = ['line_height' => '1.8'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['line_height'])->toBe('1.8');
});

it('sanitizes invalid line height to empty string', function (): void {
    $input = ['line_height' => 'invalid'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['line_height'])->toBe('');
});

it('sanitizes trims color value', function (): void {
    $input = ['color' => '  #ff0000  '];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['color'])->toBe('#ff0000');
});

it('sanitizes filters HTML from color', function (): void {
    $input = ['color' => '<script>alert("xss")</script>#ff0000'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['color'])->not->toContain('<script>');
});

it('sanitizes non-array value to defaults', function (): void {
    $sanitized = $this->field->sanitize('invalid');

    expect($sanitized)->toBeArray()
        ->and($sanitized['font_family'])->toBe('')
        ->and($sanitized['font_size'])->toBe('');
});

it('validates valid typography array', function (): void {
    $input = [
        'font_family' => 'Arial',
        'font_size' => '16',
        'font_weight' => '400',
        'line_height' => '1.5',
        'text_align' => 'center',
        'text_transform' => 'uppercase',
    ];

    expect($this->field->validate($input))->toBeTrue();
});

it('validates empty array when not required', function (): void {
    expect($this->field->validate([]))->toBeTrue();
});

it('validates empty array when required', function (): void {
    $this->field->required();

    expect($this->field->validate([]))->toBeTrue();
});

it('validates non-array when not required', function (): void {
    expect($this->field->validate('invalid'))->toBeTrue();
});

it('validates non-array when required', function (): void {
    $this->field->required();

    expect($this->field->validate('invalid'))->toBeFalse();
});

it('validates invalid font family', function (): void {
    $input = ['font_family' => 'Invalid Font'];

    expect($this->field->validate($input))->toBeFalse();
});

it('validates invalid font weight', function (): void {
    $input = ['font_weight' => '999'];

    expect($this->field->validate($input))->toBeFalse();
});

it('validates invalid text align', function (): void {
    $input = ['text_align' => 'invalid'];

    expect($this->field->validate($input))->toBeFalse();
});

it('validates invalid text transform', function (): void {
    $input = ['text_transform' => 'invalid'];

    expect($this->field->validate($input))->toBeFalse();
});

it('validates invalid font size', function (): void {
    $input = ['font_size' => 'invalid'];

    expect($this->field->validate($input))->toBeFalse();
});

it('validates invalid line height', function (): void {
    $input = ['line_height' => 'invalid'];

    expect($this->field->validate($input))->toBeFalse();
});
