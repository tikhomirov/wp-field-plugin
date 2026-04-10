<?php

declare(strict_types=1);

use WpField\Field\Types\CheckboxField;

beforeEach(function (): void {
    $this->field = new CheckboxField('test_checkbox');
});

it('can set custom checked value', function (): void {
    $this->field->checkedValue('yes');

    expect($this->field->getAttribute('checked_value'))->toBe('yes');
});

it('checkedValue is chainable', function (): void {
    $result = $this->field->checkedValue('custom');

    expect($result)->toBe($this->field);
});

it('renders checkbox with default checked value', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('type="checkbox"')
        ->and($html)->toContain('value="1"')
        ->and($html)->toContain('name="test_checkbox"');
});

it('renders checkbox with custom checked value', function (): void {
    $this->field->checkedValue('yes');
    $html = $this->field->render();

    expect($html)->toContain('value="yes"');
});

it('renders as checked when value matches checked value', function (): void {
    $this->field->value('1');
    $html = $this->field->render();

    expect($html)->toContain('checked');
});

it('renders as checked when boolean true', function (): void {
    $this->field->value(true);
    $html = $this->field->render();

    expect($html)->toContain('checked');
});

it('renders as unchecked when value does not match', function (): void {
    $this->field->value('0');
    $html = $this->field->render();

    expect($html)->not->toContain('checked');
});

it('renders as unchecked when boolean false', function (): void {
    $this->field->value(false);
    $html = $this->field->render();

    expect($html)->not->toContain('checked');
});

it('renders with disabled attribute', function (): void {
    $this->field->disabled();
    $html = $this->field->render();

    expect($html)->toContain('disabled');
});

it('renders with label', function (): void {
    $this->field->label('Check this box');
    $html = $this->field->render();

    expect($html)->toContain('Check this box');
});

it('renders with description', function (): void {
    $this->field->description('This is a description');
    $html = $this->field->render();

    expect($html)->toContain('This is a description');
});

it('sanitizes boolean true to "1"', function (): void {
    $sanitized = $this->field->sanitize(true);

    expect($sanitized)->toBe('1');
});

it('sanitizes boolean false to empty string', function (): void {
    $sanitized = $this->field->sanitize(false);

    expect($sanitized)->toBe('');
});

it('sanitizes scalar value', function (): void {
    $sanitized = $this->field->sanitize('test_value');

    expect($sanitized)->toBe('test_value');
});

it('sanitizes non-scalar value to empty string', function (): void {
    $sanitized = $this->field->sanitize(['invalid']);

    expect($sanitized)->toBe('');
});

it('sanitizes HTML tags', function (): void {
    $sanitized = $this->field->sanitize('<script>alert("xss")</script>');

    expect($sanitized)->not->toContain('<script>');
});
