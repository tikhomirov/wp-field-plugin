<?php

declare(strict_types=1);

use WpField\Field\Types\RepeaterField;
use WpField\Field\Types\TextField;

beforeEach(function (): void {
    $this->repeater = new RepeaterField('test_repeater');
});

it('can add subfields to repeater', function (): void {
    $field1 = new TextField('field1');
    $field2 = new TextField('field2');

    $this->repeater->fields([$field1, $field2]);

    expect($this->repeater->getFields())->toHaveCount(2);
});

it('can add single subfield', function (): void {
    $field = new TextField('single_field');

    $this->repeater->addField($field);

    expect($this->repeater->getFields())->toHaveCount(1)
        ->and($this->repeater->getFields()[0])->toBe($field);
});

it('sets min limit', function (): void {
    $this->repeater->min(2);

    $array = $this->repeater->toArray();

    expect($array['min'])->toBe(2);
});

it('sets max limit', function (): void {
    $this->repeater->max(5);

    $array = $this->repeater->toArray();

    expect($array['max'])->toBe(5);
});

it('sets custom button label', function (): void {
    $this->repeater->buttonLabel('Add New Item');

    $array = $this->repeater->toArray();

    expect($array['button_label'])->toBe('Add New Item');
});

it('sets valid layout', function (): void {
    $this->repeater->layout('block');

    $array = $this->repeater->toArray();

    expect($array['layout'])->toBe('block');
});

it('ignores invalid layout', function (): void {
    $this->repeater->layout('invalid');

    $array = $this->repeater->toArray();

    expect($array['layout'])->toBe('table'); // default
});

it('sanitizes array of rows', function (): void {
    $field1 = new TextField('text1');
    $field2 = new TextField('text2');
    $this->repeater->fields([$field1, $field2]);

    $input = [
        ['text1' => 'value1', 'text2' => 'value2'],
        ['text1' => 'value3', 'text2' => '<script>alert("xss")</script>'],
    ];

    $sanitized = $this->repeater->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized)->toHaveCount(2)
        ->and($sanitized[0]['text1'])->toBe('value1')
        ->and($sanitized[1]['text2'])->not->toContain('<script>');
});

it('returns empty array for non-array input in sanitize', function (): void {
    $sanitized = $this->repeater->sanitize('invalid');

    expect($sanitized)->toBe([]);
});

it('skips non-array rows in sanitize', function (): void {
    $field = new TextField('text');
    $this->repeater->fields([$field]);

    $input = [
        ['text' => 'valid'],
        'invalid',
        ['text' => 'valid2'],
    ];

    $sanitized = $this->repeater->sanitize($input);

    expect($sanitized)->toHaveCount(2);
});

it('validates min constraint', function (): void {
    $field = new TextField('text');
    $this->repeater->fields([$field]);
    $this->repeater->min(2);

    $value = [['text' => 'value']];

    expect($this->repeater->validate($value))->toBeFalse();
});

it('validates max constraint', function (): void {
    $field = new TextField('text');
    $this->repeater->fields([$field]);
    $this->repeater->max(2);

    $value = [
        ['text' => 'value1'],
        ['text' => 'value2'],
        ['text' => 'value3'],
    ];

    expect($this->repeater->validate($value))->toBeFalse();
});

it('validates nested field values', function (): void {
    $field = new TextField('text');
    $field->required();
    $this->repeater->fields([$field]);

    $value = [
        ['text' => 'valid'],
        ['text' => ''],
    ];

    expect($this->repeater->validate($value))->toBeFalse();
});

it('returns true for valid data within constraints', function (): void {
    $field = new TextField('text');
    $this->repeater->fields([$field]);
    $this->repeater->min(1)->max(5);

    $value = [
        ['text' => 'value1'],
        ['text' => 'value2'],
    ];

    expect($this->repeater->validate($value))->toBeTrue();
});

it('returns false for non-array rows in validation', function (): void {
    $field = new TextField('text');
    $this->repeater->fields([$field]);

    $value = ['invalid', 'data'];

    expect($this->repeater->validate($value))->toBeFalse();
});

it('renders repeater with table layout', function (): void {
    $field = new TextField('text');
    $this->repeater->fields([$field]);

    $html = $this->repeater->render();

    expect($html)->toContain('wp-field-repeater')
        ->and($html)->toContain('data-layout="table"')
        ->and($html)->toContain('wp-field-repeater-add')
        ->and($html)->toContain('wp-field-repeater-template');
});

it('renders with custom layout', function (): void {
    $field = new TextField('text');
    $this->repeater->fields([$field]);
    $this->repeater->layout('block');

    $html = $this->repeater->render();

    expect($html)->toContain('data-layout="block"');
});

it('renders rows with data', function (): void {
    $field = new TextField('text');
    $this->repeater->fields([$field]);
    $this->repeater->value([
        ['text' => 'value1'],
        ['text' => 'value2'],
    ]);

    $html = $this->repeater->render();

    expect($html)->toContain('value1')
        ->and($html)->toContain('value2');
});

it('renders min rows when no data and min > 0', function (): void {
    $field = new TextField('text');
    $this->repeater->fields([$field]);
    $this->repeater->min(2);

    $html = $this->repeater->render();

    expect($html)->toContain('data-index="0"')
        ->and($html)->toContain('data-index="1"');
});

it('renders with label and description', function (): void {
    $field = new TextField('text');
    $this->repeater->fields([$field]);
    $this->repeater->label('Repeater Label')->description('Repeater Description');

    $html = $this->repeater->render();

    expect($html)->toContain('Repeater Label')
        ->and($html)->toContain('Repeater Description');
});

it('toArray includes repeater config', function (): void {
    $field = new TextField('text');
    $this->repeater->fields([$field]);
    $this->repeater->min(1)->max(5)->buttonLabel('Add')->layout('row');

    $array = $this->repeater->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('fields')
        ->and($array)->toHaveKey('min')
        ->and($array)->toHaveKey('max')
        ->and($array)->toHaveKey('button_label')
        ->and($array)->toHaveKey('layout');
});
