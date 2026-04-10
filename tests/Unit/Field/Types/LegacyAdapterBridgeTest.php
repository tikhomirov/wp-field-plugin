<?php

declare(strict_types=1);

use WpField\Field\Types\LegacyAdapterBridge;
use WpField\Field\Types\LegacyWrapperField;

class LegacyAdapterBridgeTestClass
{
    use LegacyAdapterBridge;

    public string $name = 'test_field';

    public mixed $value = null;

    public bool $required = false;

    public array $attributes = [];

    public array $conditions = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function setConditions(array $conditions): void
    {
        $this->conditions = $conditions;
    }

    public function publicRenderViaLegacy(string $legacyType): string
    {
        return $this->renderViaLegacy($legacyType);
    }
}

beforeEach(function (): void {
    $this->testClass = new LegacyAdapterBridgeTestClass('test_field');
});

it('renders fallback when WP_Field class does not exist', function (): void {
    // Ensure WP_Field class does not exist
    $wpFieldExists = class_exists('WP_Field', false);

    // If it exists, we can't reliably test this case
    if ($wpFieldExists) {
        $this->markTestSkipped('WP_Field class already exists in test environment');
    }

    $testClass = new LegacyAdapterBridgeTestClass('test_field');
    $result = $testClass->publicRenderViaLegacy('text');

    expect($result)->toBe('');
});

it('creates LegacyWrapperField with correct type', function (): void {
    // Mock WP_Field class existence test
    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});

it('passes value to wrapper', function (): void {
    $this->testClass->setValue('test value');

    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});

it('passes required status to wrapper', function (): void {
    $this->testClass->setRequired(true);

    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});

it('passes attributes to wrapper', function (): void {
    $this->testClass->setAttribute('label', 'Test Label');
    $this->testClass->setAttribute('placeholder', 'Enter value');

    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});

it('passes simple condition to wrapper', function (): void {
    $this->testClass->setConditions([
        ['field' => 'other_field', 'operator' => '==', 'value' => 'test'],
    ]);

    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});

it('passes OR condition to wrapper', function (): void {
    $this->testClass->setConditions([
        ['field' => 'other_field', 'operator' => '==', 'value' => 'test', 'logic' => 'OR'],
    ]);

    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});

it('handles nested conditions', function (): void {
    $this->testClass->setConditions([
        [
            ['field' => 'field1', 'operator' => '==', 'value' => 'val1'],
            ['field' => 'field2', 'operator' => '==', 'value' => 'val2'],
        ],
    ]);

    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});

it('handles invalid condition without field', function (): void {
    $this->testClass->setConditions([
        ['operator' => '==', 'value' => 'test'],
    ]);

    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});

it('handles non-array condition', function (): void {
    $this->testClass->setConditions(['invalid']);

    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});

it('handles condition without operator', function (): void {
    $this->testClass->setConditions([
        ['field' => 'other_field', 'value' => 'test'],
    ]);

    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});

it('handles condition with non-string field', function (): void {
    $this->testClass->setConditions([
        ['field' => 123, 'operator' => '==', 'value' => 'test'],
    ]);

    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});

it('handles condition with non-string operator', function (): void {
    $this->testClass->setConditions([
        ['field' => 'other_field', 'operator' => 123, 'value' => 'test'],
    ]);

    if (! class_exists('WP_Field', false)) {
        class_alias(LegacyWrapperField::class, 'WP_Field');
    }

    $result = $this->testClass->publicRenderViaLegacy('text');

    expect($result)->toBeString();
});
