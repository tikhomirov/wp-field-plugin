<?php

declare(strict_types=1);

use WpField\Field\Types\BorderField;

beforeEach(function (): void {
    $this->field = new BorderField('test_border');
});

it('can set styles', function (): void {
    $styles = ['solid', 'dashed', 'dotted'];
    $this->field->styles($styles);

    expect($this->field->getAttribute('styles'))->toBe($styles);
});

it('renders border with default styles', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-border')
        ->and($html)->toContain('Solid')
        ->and($html)->toContain('Dashed');
});

it('renders border with custom styles', function (): void {
    $this->field->styles(['solid', 'double']);
    $html = $this->field->render();

    expect($html)->toContain('Solid')
        ->and($html)->toContain('Double');
});

it('sanitizes border array', function (): void {
    $input = ['style' => 'solid', 'width' => '2', 'color' => '#ff0000'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized['style'])->toBe('solid');
});

it('validates valid border array', function (): void {
    $input = ['style' => 'solid', 'width' => '2', 'color' => '#ff0000'];

    expect($this->field->validate($input))->toBeTrue();
});

it('renders with label', function (): void {
    $this->field->label('Border Style');
    $html = $this->field->render();

    expect($html)->toContain('Border Style')
        ->and($html)->toContain('<label');
});

it('renders with description', function (): void {
    $this->field->description('Choose border style');
    $html = $this->field->render();

    expect($html)->toContain('Choose border style');
});

it('validates non-array value when not required', function (): void {
    expect($this->field->validate('invalid'))->toBeTrue();
});

it('validates non-array value when required', function (): void {
    $this->field->required();
    expect($this->field->validate('invalid'))->toBeFalse();
});

it('resolves styles returns default when empty array', function (): void {
    $this->field->styles([]);
    $html = $this->field->render();

    expect($html)->toContain('Solid')
        ->and($html)->toContain('Dashed');
});

it('resolves styles filters non-scalar values', function (): void {
    $this->field->styles(['solid', ['invalid'], 'dashed']);
    $html = $this->field->render();

    expect($html)->toContain('Solid')
        ->and($html)->toContain('Dashed');
});

it('sanitizes non-array value to defaults', function (): void {
    $sanitized = $this->field->sanitize('invalid');

    expect($sanitized)->toBeArray()
        ->and($sanitized['style'])->toBe('solid')
        ->and($sanitized['width'])->toBe('')
        ->and($sanitized['color'])->toBe('');
});
