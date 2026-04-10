<?php

declare(strict_types=1);

use WpField\Field\Types\CheckboxGroupField;

beforeEach(function (): void {
    $this->field = new CheckboxGroupField('test_checkbox_group');
});

it('renders checkbox group with options', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-checkbox-group')
        ->and($html)->toContain('Option 1')
        ->and($html)->toContain('Option 2');
});

it('renders checkbox group with label', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->label('Select Options');
    $html = $this->field->render();

    expect($html)->toContain('Select Options')
        ->and($html)->toContain('wp-field-checkbox-group-label');
});

it('renders checkbox group with description', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->description('Choose one or more options');
    $html = $this->field->render();

    expect($html)->toContain('Choose one or more options');
});

it('renders checkboxes with correct names', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $html = $this->field->render();

    expect($html)->toContain('name="test_checkbox_group[]"');
});

it('renders checkboxes with unique IDs', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $html = $this->field->render();

    expect($html)->toContain('id="test_checkbox_group_opt1"')
        ->and($html)->toContain('id="test_checkbox_group_opt2"');
});

it('renders checked checkboxes for selected values', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $this->field->value(['opt1']);
    $html = $this->field->render();

    expect($html)->toContain('checked');
});

it('renders all checkboxes checked when all values selected', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $this->field->value(['opt1', 'opt2']);
    $html = $this->field->render();

    expect($html)->toContain('checked');
});

it('sanitizes array of values', function (): void {
    $input = ['opt1', 'opt2', 'opt3'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized)->toHaveCount(3)
        ->and($sanitized)->toContain('opt1');
});

it('sanitizes filters HTML from values', function (): void {
    $input = ['<script>alert("xss")</script>valid'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized[0])->not->toContain('<script>');
});

it('sanitizes converts values to strings', function (): void {
    $input = [1, 2, 3];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized[0])->toBe('1');
});

it('sanitizes returns empty array for non-array input', function (): void {
    $sanitized = $this->field->sanitize('invalid');

    expect($sanitized)->toBe([]);
});

it('handles single scalar value as selected', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->value('opt1');
    $html = $this->field->render();

    expect($html)->toContain('checked');
});

it('handles empty value', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->value([]);
    $html = $this->field->render();

    expect($html)->not->toContain('checked');
});
