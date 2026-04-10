<?php

declare(strict_types=1);

use WpField\Field\Types\SortableField;

beforeEach(function (): void {
    $this->field = new SortableField('test_sortable');
});

it('can set options', function (): void {
    $options = ['option1' => 'Option 1', 'option2' => 'Option 2'];
    $this->field->options($options);

    expect($this->field->getAttribute('options'))->toBe($options);
});

it('options is chainable', function (): void {
    $result = $this->field->options(['opt1' => 'Option 1']);

    expect($result)->toBe($this->field);
});

it('renders message when no options provided', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('No options provided');
});

it('renders sortable with options', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-sortable')
        ->and($html)->toContain('Option 1')
        ->and($html)->toContain('Option 2');
});

it('renders sortable with label', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->label('Sort Options');
    $html = $this->field->render();

    expect($html)->toContain('Sort Options')
        ->and($html)->toContain('<label');
});

it('renders sortable with description', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->description('Drag and drop to sort');
    $html = $this->field->render();

    expect($html)->toContain('Drag and drop to sort');
});

it('renders items in order based on value', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $this->field->value(['opt2', 'opt1']);
    $html = $this->field->render();

    expect($html)->toContain('Option 1')
        ->and($html)->toContain('Option 2');
});

it('renders hidden inputs for each item', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $html = $this->field->render();

    expect($html)->toContain('type="hidden"')
        ->and($html)->toContain('name="test_sortable[]"');
});

it('renders dashicons menu for drag handle', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $html = $this->field->render();

    expect($html)->toContain('dashicons-menu');
});

it('sanitizes array value', function (): void {
    $input = ['opt1', 'opt2', 'opt3'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized)->toHaveCount(3)
        ->and($sanitized)->toContain('opt1');
});

it('sanitizes removes duplicates', function (): void {
    $input = ['opt1', 'opt1', 'opt2', 'opt2'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toHaveCount(2);
});

it('sanitizes filters non-scalar items', function (): void {
    $input = ['opt1', ['invalid'], 'opt2', 123];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toHaveCount(3);
});

it('sanitizes returns empty array for non-array input', function (): void {
    $sanitized = $this->field->sanitize('invalid');

    expect($sanitized)->toBe([]);
});

it('sanitizes filters HTML from items', function (): void {
    $input = ['<script>alert("xss")</script>', 'valid'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized[0])->not->toContain('<script>');
});

it('normalizes options returns empty array when not array', function (): void {
    $this->field->attribute('options', 'invalid');
    $html = $this->field->render();

    expect($html)->toContain('No options provided');
});

it('normalizes options filters non-scalar labels', function (): void {
    $this->field->options(['opt1' => 'Valid', 'opt2' => ['invalid'], 'opt3' => 'Also Valid']);
    $html = $this->field->render();

    expect($html)->toContain('Valid')
        ->and($html)->toContain('Also Valid');
});

it('resolves ordered options filters non-scalar items in value', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $this->field->value(['opt1', ['invalid'], 'opt2']);
    $html = $this->field->render();

    expect($html)->toContain('Option 1')
        ->and($html)->toContain('Option 2');
});
