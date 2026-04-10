<?php

declare(strict_types=1);

use WpField\Field\Types\SorterField;

beforeEach(function (): void {
    $this->field = new SorterField('test_sorter');
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

it('can set groups', function (): void {
    $groups = ['enabled' => 'Enabled', 'disabled' => 'Disabled'];
    $this->field->groups($groups);

    expect($this->field->getAttribute('groups'))->toBe($groups);
});

it('groups is chainable', function (): void {
    $result = $this->field->groups(['group1' => 'Group 1']);

    expect($result)->toBe($this->field);
});

it('renders message when no options provided', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('No options provided');
});

it('renders sorter with default columns', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-sorter')
        ->and($html)->toContain('Enabled')
        ->and($html)->toContain('Disabled')
        ->and($html)->toContain('Option 1');
});

it('renders sorter with custom groups', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->groups(['group1' => 'Group 1', 'group2' => 'Group 2']);
    $html = $this->field->render();

    expect($html)->toContain('Group 1')
        ->and($html)->toContain('Group 2');
});

it('renders sorter with label', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->label('Sort Options');
    $html = $this->field->render();

    expect($html)->toContain('Sort Options')
        ->and($html)->toContain('<label');
});

it('renders sorter with description', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->description('Drag and drop to sort');
    $html = $this->field->render();

    expect($html)->toContain('Drag and drop to sort');
});

it('renders items in correct columns based on value', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $this->field->value(['enabled' => ['opt1'], 'disabled' => ['opt2']]);
    $html = $this->field->render();

    expect($html)->toContain('Option 1')
        ->and($html)->toContain('Option 2');
});

it('renders hidden inputs for each item', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $html = $this->field->render();

    expect($html)->toContain('type="hidden"')
        ->and($html)->toContain('name="test_sorter');
});

it('sanitizes array value', function (): void {
    $input = [
        'enabled' => ['opt1', 'opt2'],
        'disabled' => ['opt3'],
    ];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized)->toHaveCount(2)
        ->and($sanitized['enabled'])->toContain('opt1');
});

it('sanitizes removes duplicates', function (): void {
    $input = [
        'enabled' => ['opt1', 'opt1', 'opt2'],
    ];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['enabled'])->toHaveCount(2);
});

it('sanitizes filters non-scalar items', function (): void {
    $input = [
        'enabled' => ['opt1', ['invalid'], 'opt2'],
    ];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['enabled'])->toHaveCount(2);
});

it('sanitizes filters non-string groups', function (): void {
    $input = [
        'enabled' => ['opt1'],
        123 => ['opt2'],
    ];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toHaveKey('enabled')
        ->and($sanitized)->not->toHaveKey(123);
});

it('sanitizes returns empty array for non-array input', function (): void {
    $sanitized = $this->field->sanitize('invalid');

    expect($sanitized)->toBe([]);
});

it('sanitizes filters HTML from items', function (): void {
    $input = [
        'enabled' => ['<script>alert("xss")</script>'],
    ];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['enabled'][0])->not->toContain('<script>');
});

it('renders with dashicons menu', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $html = $this->field->render();

    expect($html)->toContain('dashicons-menu');
});

it('renders with column labels', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-sorter-column')
        ->and($html)->toContain('wp-field-sorter-list');
});

it('renders with groups as array with label', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->groups(['group1' => ['label' => 'Custom Label']]);
    $html = $this->field->render();

    expect($html)->toContain('Custom Label');
});

it('renders with groups as array without label uses ucfirst', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->groups(['group1' => ['items' => []]]);
    $html = $this->field->render();

    expect($html)->toContain('Group1');
});

it('renders with empty groups array uses default columns', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->groups([]);
    $html = $this->field->render();

    expect($html)->toContain('Enabled')
        ->and($html)->toContain('Disabled');
});

it('sanitizes handles non-array value in value map', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->value(['enabled' => 'not_an_array']);
    $html = $this->field->render();

    expect($html)->toContain('Option 1');
});

it('sanitizes handles non-scalar item key in value', function (): void {
    $input = [
        'enabled' => ['opt1', ['invalid'], 'opt2'],
    ];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['enabled'])->toHaveCount(2);
});

it('renders with leftover items in disabled column', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $this->field->value(['enabled' => ['opt1']]);
    $html = $this->field->render();

    expect($html)->toContain('Option 2');
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

it('resolves columns returns default when empty after filtering', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->groups([]);
    $html = $this->field->render();

    expect($html)->toContain('Enabled')
        ->and($html)->toContain('Disabled');
});

it('resolves sorted columns filters non-scalar item keys', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $this->field->value(['enabled' => ['opt1', ['invalid'], 'opt2']]);
    $html = $this->field->render();

    expect($html)->toContain('Option 1')
        ->and($html)->toContain('Option 2');
});

it('resolves sorted columns returns empty when resolved array is empty', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->value([]);
    $html = $this->field->render();

    expect($html)->toContain('Option 1');
});

it('resolves columns fallback to default when all groups filtered out', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->groups([]);
    $html = $this->field->render();

    expect($html)->toContain('Enabled')
        ->and($html)->toContain('Disabled');
});
