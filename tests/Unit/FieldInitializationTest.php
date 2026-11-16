<?php

declare(strict_types=1);

beforeEach(function (): void {
    require_once dirname(__DIR__, 2).'/WP_Field.php';
});

it('initializes field types registry', function (): void {
    WP_Field::init_field_types();

    // Проверяем, что реестр инициализирован
    $reflection = new ReflectionClass(WP_Field::class);
    $property = $reflection->getProperty('field_types');
    $property->setAccessible(true);
    $types = $property->getValue();

    expect($types)
        ->not->toBeEmpty()
        ->and(isset($types['text']))->toBeTrue()
        ->and(isset($types['select']))->toBeTrue()
        ->and(isset($types['repeater']))->toBeTrue();
});

it('supports field aliases', function (): void {
    $field = new WP_Field([
        'id' => 'test',
        'type' => 'text',
        'title' => 'Test Field',  // alias для label
    ], 'options');

    expect($field->field['label'])->toBe('Test Field');
});

it('supports value alias', function (): void {
    $field = new WP_Field([
        'id' => 'test',
        'type' => 'text',
        'label' => 'Test',
        'val' => 'test value',  // alias для value
    ], 'options');

    expect($field->field['value'])->toBe('test value');
});

it('supports custom attributes aliases', function (): void {
    $field = new WP_Field([
        'id' => 'test',
        'type' => 'text',
        'label' => 'Test',
        'attributes' => ['data-test' => 'value'],  // alias для custom_attributes
    ], 'options');

    expect($field->field['custom_attributes'])->not->toBeNull();
});

it('creates field with static make', function (): void {
    $html = WP_Field::make([[
        'id' => 'test',
        'type' => 'text',
        'label' => 'Test',
    ], 'options'], false);

    expect($html)
        ->toBeString()
        ->toContain('wp-field');
});

it('creates field with make and output', function (): void {
    ob_start();
    $result = WP_Field::make([[
        'id' => 'test',
        'type' => 'text',
        'label' => 'Test',
    ], 'options'], true);
    $output = ob_get_clean();

    expect($result)->toBeNull()
        ->and($output)->toContain('wp-field');
});

// Skipping validation test - validate_field_data() uses trigger_error() by design, not exceptions

it('sets default storage type', function (): void {
    $field = new WP_Field([
        'id' => 'test',
        'type' => 'text',
        'label' => 'Test',
    ], 'post', 1);

    expect($field->storage_type)->toBe('post');
});

it('supports different storage types', function (): void {
    $types = ['post', 'options', 'term', 'user', 'comment'];

    foreach ($types as $type) {
        $field = new WP_Field([
            'id' => 'test',
            'type' => 'text',
            'label' => 'Test',
        ], $type, 1);

        expect($field->storage_type)->toBe($type);
    }
});

it('handles field with default value', function (): void {
    $field = new WP_Field([
        'id' => 'test',
        'type' => 'text',
        'label' => 'Test',
        'default' => 'default value',
    ], 'options');

    expect($field->field['default'])->toBe('default value');
});

it('handles field with explicit value', function (): void {
    $field = new WP_Field([
        'id' => 'test',
        'type' => 'text',
        'label' => 'Test',
        'value' => 'explicit value',
    ], 'options');

    expect($field->field['value'])->toBe('explicit value');
});

it('supports field with options', function (): void {
    $field = new WP_Field([
        'id' => 'test',
        'type' => 'select',
        'label' => 'Test',
        'options' => ['a' => 'Option A', 'b' => 'Option B'],
    ], 'options');

    expect($field->field['options'])->toHaveCount(2);
});

it('supports field with nested fields', function (): void {
    $field = new WP_Field([
        'id' => 'test',
        'type' => 'group',
        'label' => 'Test',
        'fields' => [
            ['id' => 'sub1', 'type' => 'text', 'label' => 'Sub 1'],
            ['id' => 'sub2', 'type' => 'text', 'label' => 'Sub 2'],
        ],
    ], 'options');

    expect($field->field['fields'])->toHaveCount(2);
});
