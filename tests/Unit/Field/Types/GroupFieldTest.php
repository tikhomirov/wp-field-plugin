<?php

declare(strict_types=1);

use WpField\Field\Types\GroupField;
use WpField\Field\Types\TextField;

beforeEach(function (): void {
    $this->group = new GroupField('test_group');
});

it('can add fields to group', function (): void {
    $field1 = new TextField('field1');
    $field2 = new TextField('field2');

    $this->group->fields([$field1, $field2]);

    expect($this->group->getFields())->toHaveCount(2);
});

it('can add single field', function (): void {
    $field = new TextField('single_field');

    $this->group->addField($field);

    expect($this->group->getFields())->toHaveCount(1)
        ->and($this->group->getFields()[0])->toBe($field);
});

it('filters non-FieldInterface objects', function (): void {
    $field = new TextField('valid_field');

    $this->group->fields([$field, 'invalid', 123, null]);

    expect($this->group->getFields())->toHaveCount(1)
        ->and($this->group->getFields()[0]->getName())->toBe('valid_field');
});

it('fields method is chainable', function (): void {
    $field = new TextField('field');

    $result = $this->group->fields([$field]);

    expect($result)->toBe($this->group);
});

it('addField method is chainable', function (): void {
    $field = new TextField('field');

    $result = $this->group->addField($field);

    expect($result)->toBe($this->group);
});

it('sanitizes array values for nested fields', function (): void {
    $text1 = new TextField('text1');
    $text2 = new TextField('text2');
    $this->group->fields([$text1, $text2]);

    $input = [
        'text1' => 'value1',
        'text2' => '<script>alert("xss")</script>',
    ];

    $sanitized = $this->group->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized['text1'])->toBe('value1')
        ->and($sanitized['text2'])->not->toContain('<script>');
});

it('returns empty array for non-array input in sanitize', function (): void {
    $sanitized = $this->group->sanitize('invalid');

    expect($sanitized)->toBe([]);
});

it('validates nested fields', function (): void {
    $text1 = new TextField('text1');
    $text2 = new TextField('text2');
    $this->group->fields([$text1, $text2]);

    $value = ['text1' => 'valid', 'text2' => 'valid'];

    expect($this->group->validate($value))->toBeTrue();
});

it('returns false if nested field validation fails', function (): void {
    $text1 = new TextField('text1');
    $text1->required();
    $text2 = new TextField('text2');
    $this->group->fields([$text1, $text2]);

    $value = ['text1' => '', 'text2' => 'valid'];

    expect($this->group->validate($value))->toBeFalse();
});

it('returns false for non-array input when required', function (): void {
    $this->group->required();

    expect($this->group->validate('invalid'))->toBeFalse();
});

it('returns true for non-array input when not required', function (): void {
    expect($this->group->validate('invalid'))->toBeTrue();
});

it('renders group with nested fields', function (): void {
    $text1 = new TextField('text1');
    $text1->label('Text 1');
    $text2 = new TextField('text2');
    $this->group->fields([$text1, $text2]);

    $html = $this->group->render();

    expect($html)->toBeString()
        ->and($html)->toContain('wp-field-group')
        ->and($html)->toContain('test_group[text1]')
        ->and($html)->toContain('test_group[text2]')
        ->and($html)->toContain('Text 1');
});

it('renders with custom class', function (): void {
    $this->group->class('custom-group-class');
    $field = new TextField('field');
    $this->group->fields([$field]);

    $html = $this->group->render();

    expect($html)->toContain('custom-group-class');
});

it('renders with label', function (): void {
    $this->group->label('Group Label');
    $field = new TextField('field');
    $this->group->fields([$field]);

    $html = $this->group->render();

    expect($html)->toContain('Group Label')
        ->and($html)->toContain('wp-field-group-label');
});

it('renders with description', function (): void {
    $this->group->description('Group Description');
    $field = new TextField('field');
    $this->group->fields([$field]);

    $html = $this->group->render();

    expect($html)->toContain('Group Description');
});

it('renders nested fields with values', function (): void {
    $text1 = new TextField('text1');
    $text2 = new TextField('text2');
    $this->group->fields([$text1, $text2]);
    $this->group->value(['text1' => 'value1', 'text2' => 'value2']);

    $html = $this->group->render();

    expect($html)->toContain('value1')
        ->and($html)->toContain('value2');
});

it('toArray includes nested fields', function (): void {
    $text1 = new TextField('text1');
    $text2 = new TextField('text2');
    $this->group->fields([$text1, $text2]);

    $array = $this->group->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('fields')
        ->and($array['fields'])->toBeArray()
        ->and($array['fields'])->toHaveCount(2);
});
