<?php

declare(strict_types=1);

// Load vanilla WP_Field class for testing
require_once __DIR__.'/../../vanilla/WP_Field.php';

use WpField\Field\Field;
use WpField\Field\Types\LegacyWrapperField;

beforeEach(function (): void {
    // Ensure WP_Field class is loaded
    if (! class_exists('WP_Field', false)) {
        require_once __DIR__.'/../../vanilla/WP_Field.php';
    }
});

it('LegacyAdapterBridge uses real WP_Field class', function (): void {
    // Create a custom type that will use LegacyWrapperField
    $field = Field::make('my_custom_legacy_type', 'test_legacy');
    $field->label('Test Legacy');
    $field->value('test value');
    $field->required();

    $html = $field->render();

    // Should render using legacy WP_Field class
    expect($html)->toBeString()
        ->and($html)->toContain('wp-field-vanilla-fallback');
});

it('LegacyWrapperField builds config with real WP_Field class', function (): void {
    $wrapper = new LegacyWrapperField('test_wrapper', 'text');
    $wrapper->label('Test Label');
    $wrapper->value('test value');
    $wrapper->required();
    $wrapper->when('other_field', '==', 'test');

    // Access private method via reflection
    $reflection = new ReflectionClass($wrapper);
    $method = $reflection->getMethod('buildLegacyConfig');
    $method->setAccessible(true);

    $config = $method->invoke($wrapper);

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('id')
        ->and($config)->toHaveKey('name')
        ->and($config)->toHaveKey('type')
        ->and($config['label'])->toBe('Test Label')
        ->and($config['value'])->toBe('test value')
        ->and($config['required'])->toBeTrue()
        ->and($config)->toHaveKey('dependency');
});

it('LegacyWrapperField renders with real WP_Field class when available', function (): void {
    $wrapper = new LegacyWrapperField('test_wrapper', 'text');
    $wrapper->label('Test Label');
    $wrapper->value('test value');

    $html = $wrapper->render();

    expect($html)->toBeString();
});

it('LegacyWrapperField handles complex conditions with real WP_Field class', function (): void {
    $wrapper = new LegacyWrapperField('test_wrapper', 'text');
    $wrapper->when('field1', '==', 'val1');
    $wrapper->orWhen('field2', '==', 'val2');

    $reflection = new ReflectionClass($wrapper);
    $method = $reflection->getMethod('buildLegacyConfig');
    $method->setAccessible(true);

    $config = $method->invoke($wrapper);

    expect($config)->toHaveKey('dependency');
});
