<?php

declare(strict_types=1);

beforeEach(function (): void {
    // Load vanilla WP_Field class for testing
    require_once __DIR__.'/../../vanilla/WP_Field.php';
});

it('vanilla WP_Field class is loaded', function (): void {
    $wpFieldClassExists = class_exists('WP_Field', false);

    expect($wpFieldClassExists)->toBeTrue();
});

it('vanilla WP_Field has field types registry', function (): void {
    $reflection = new ReflectionClass('WP_Field');
    $property = $reflection->getProperty('field_types');
    $property->setAccessible(true);

    expect($property->getValue())->toBeArray();
});

it('vanilla WP_Field creates text field', function (): void {
    $field = [
        'id' => 'test_field',
        'type' => 'text',
        'label' => 'Test Field',
        'value' => 'test value',
    ];

    $wpField = new WP_Field($field);

    expect($wpField)->toBeInstanceOf('WP_Field');
});
