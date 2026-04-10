<?php

declare(strict_types=1);

use WpField\Field\Types\BackgroundField;

beforeEach(function (): void {
    $this->field = new BackgroundField('test_background');
});

it('can set background fields', function (): void {
    $fields = ['color' => true, 'image' => false];
    $this->field->backgroundFields($fields);

    expect($this->field->getAttribute('background_fields'))->toBe($fields);
});

it('renders with label', function (): void {
    $this->field->label('Background');
    $html = $this->field->render();

    expect($html)->toContain('Background')
        ->and($html)->toContain('<label');
});

it('renders with description', function (): void {
    $this->field->description('Choose background style');
    $html = $this->field->render();

    expect($html)->toContain('Choose background style');
});

it('renders color field by default', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('Background Color')
        ->and($html)->toContain('[color]');
});

it('renders image field by default', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('Background Image')
        ->and($html)->toContain('[image]');
});

it('renders position field by default', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('Position')
        ->and($html)->toContain('[position]');
});

it('renders size field by default', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('Size')
        ->and($html)->toContain('[size]');
});

it('renders repeat field by default', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('Repeat')
        ->and($html)->toContain('[repeat]');
});

it('renders attachment field by default', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('Attachment')
        ->and($html)->toContain('[attachment]');
});

it('validates non-array value when not required', function (): void {
    expect($this->field->validate('invalid'))->toBeTrue();
});

it('validates non-array value when required', function (): void {
    $this->field->required();
    expect($this->field->validate('invalid'))->toBeFalse();
});

it('sanitizes to defaults when non-array', function (): void {
    $sanitized = $this->field->sanitize('invalid');

    expect($sanitized)->toBeArray()
        ->and($sanitized['color'])->toBe('')
        ->and($sanitized['image'])->toBe('')
        ->and($sanitized['position'])->toBe('center center')
        ->and($sanitized['size'])->toBe('cover')
        ->and($sanitized['repeat'])->toBe('no-repeat')
        ->and($sanitized['attachment'])->toBe('scroll');
});

it('resolves enabled fields returns defaults when empty array', function (): void {
    $this->field->backgroundFields([]);
    $html = $this->field->render();

    expect($html)->toContain('Background Color')
        ->and($html)->toContain('Background Image');
});

it('disables color field when background_fields set to false', function (): void {
    $this->field->backgroundFields(['color' => false]);
    $html = $this->field->render();

    expect($html)->not->toContain('Background Color');
});

it('disables image field when background_fields set to false', function (): void {
    $this->field->backgroundFields(['image' => false]);
    $html = $this->field->render();

    expect($html)->not->toContain('Background Image');
});
