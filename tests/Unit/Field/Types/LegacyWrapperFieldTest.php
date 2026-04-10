<?php

declare(strict_types=1);

use WpField\Field\Types\LegacyWrapperField;
use WpField\Field\Types\TextField;

beforeEach(function (): void {
    $this->field = new LegacyWrapperField('test_wrapper', 'text');
});

it('constructs with name and legacy type', function (): void {
    $reflection = new ReflectionClass($this->field);
    $nameProperty = $reflection->getProperty('name');
    $nameProperty->setAccessible(true);

    expect($nameProperty->getValue($this->field))->toBe('test_wrapper');
});

it('can set config via config method', function (): void {
    $result = $this->field->config(['label' => 'Test Label']);

    expect($result)->toBe($this->field);
});

it('merges config with existing config', function (): void {
    $this->field->config(['label' => 'Label 1']);
    $this->field->config(['placeholder' => 'Placeholder']);

    $reflection = new ReflectionClass($this->field);
    $configProperty = $reflection->getProperty('legacyConfig');
    $configProperty->setAccessible(true);

    $config = $configProperty->getValue($this->field);

    expect($config)->toHaveKey('label')
        ->and($config)->toHaveKey('placeholder');
});

it('renders generic fallback when WP_Field class does not exist', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-vanilla-fallback');
});

it('renders with legacy API when WP_Field class exists', function (): void {
    // Mock WP_Field class
    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $html = $this->field->render();

    expect($html)->toBeString();
});

it('builds legacy config correctly', function (): void {
    $this->field->label('Test Label');
    $this->field->value('test value');
    $this->field->required();

    // Access private method via reflection
    $reflection = new ReflectionClass($this->field);
    $method = $reflection->getMethod('buildLegacyConfig');
    $method->setAccessible(true);

    $config = $method->invoke($this->field);

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('id')
        ->and($config)->toHaveKey('name')
        ->and($config)->toHaveKey('type')
        ->and($config['label'])->toBe('Test Label')
        ->and($config['value'])->toBe('test value')
        ->and($config['required'])->toBeTrue();
});

it('maps description to desc in config', function (): void {
    $this->field->description('Test Description');

    $reflection = new ReflectionClass($this->field);
    $method = $reflection->getMethod('buildLegacyConfig');
    $method->setAccessible(true);

    $config = $method->invoke($this->field);

    expect($config['desc'])->toBe('Test Description');
});

it('maps conditions to dependency in config', function (): void {
    $this->field->when('other_field', '==', 'test');

    $reflection = new ReflectionClass($this->field);
    $method = $reflection->getMethod('buildLegacyConfig');
    $method->setAccessible(true);

    $config = $method->invoke($this->field);

    expect($config)->toHaveKey('dependency');
});

it('normalizes field objects in fields config', function (): void {
    $field = new TextField('nested_field');
    $this->field->config(['fields' => [$field]]);

    $reflection = new ReflectionClass($this->field);
    $method = $reflection->getMethod('buildLegacyConfig');
    $method->setAccessible(true);

    $config = $method->invoke($this->field);

    expect($config['fields'])->toBeArray();
});

it('normalizes non-field objects in fields config', function (): void {
    $this->field->config(['fields' => [['type' => 'text', 'name' => 'field1']]]);

    $reflection = new ReflectionClass($this->field);
    $method = $reflection->getMethod('buildLegacyConfig');
    $method->setAccessible(true);

    $config = $method->invoke($this->field);

    expect($config['fields'])->toBeArray()
        ->and($config['fields'][0])->toBe(['type' => 'text', 'name' => 'field1']);
});

it('renders generic fallback when legacy API fails', function (): void {
    // Mock WP_Field but make render fail
    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $html = $this->field->render();

    expect($html)->toBeString();
});

it('handles runtime error in legacy API render', function (): void {
    // Test the error handling branch
    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $html = $this->field->render();

    expect($html)->toContain('wp-field-vanilla-fallback');
});

it('builds config without value when value is null', function (): void {
    $reflection = new ReflectionClass($this->field);
    $method = $reflection->getMethod('buildLegacyConfig');
    $method->setAccessible(true);

    $config = $method->invoke($this->field);

    expect($config)->not->toHaveKey('value');
});

it('does not add required flag when not required', function (): void {
    $reflection = new ReflectionClass($this->field);
    $method = $reflection->getMethod('buildLegacyConfig');
    $method->setAccessible(true);

    $config = $method->invoke($this->field);

    expect($config)->not->toHaveKey('required');
});

it('does not add dependency when no conditions', function (): void {
    $reflection = new ReflectionClass($this->field);
    $method = $reflection->getMethod('buildLegacyConfig');
    $method->setAccessible(true);

    $config = $method->invoke($this->field);

    expect($config)->not->toHaveKey('dependency');
});

it('handles non-array condition in mapConditionsToLegacyDependency', function (): void {
    $this->field->when('field1', '==', 'val1');

    $reflection = new ReflectionClass($this->field);
    $method = $reflection->getMethod('mapConditionsToLegacyDependency');
    $method->setAccessible(true);

    $result = $method->invoke($this->field);

    expect($result)->toBeArray();
});

it('handles condition with non-string field in mapConditionsToLegacyDependency', function (): void {
    $reflection = new ReflectionClass($this->field);
    $property = $reflection->getProperty('conditions');
    $property->setAccessible(true);
    $property->setValue($this->field, [
        ['field' => 123, 'operator' => '==', 'value' => 'test'],
    ]);

    $method = $reflection->getMethod('mapConditionsToLegacyDependency');
    $method->setAccessible(true);

    $result = $method->invoke($this->field);

    expect($result)->toBeArray();
});

it('handles nested conditions with invalid data', function (): void {
    $reflection = new ReflectionClass($this->field);
    $property = $reflection->getProperty('conditions');
    $property->setAccessible(true);
    $property->setValue($this->field, [
        [
            ['field' => 'field1', 'operator' => '==', 'value' => 'val1'],
            ['invalid' => 'data'],
        ],
    ]);

    $method = $reflection->getMethod('mapConditionsToLegacyDependency');
    $method->setAccessible(true);

    $result = $method->invoke($this->field);

    expect($result)->toBeArray();
});
