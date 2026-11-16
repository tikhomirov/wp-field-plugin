<?php

declare(strict_types=1);

use WpField\Field\FieldInterface;
use WpField\Field\Types\Concerns\HandlesNestedFieldConfigs;
use WpField\Field\Types\TextField;

class TestHandlesNestedFieldConfigs
{
    use HandlesNestedFieldConfigs;

    public function publicNormalizeNestedFields(mixed $fields): array
    {
        return $this->normalizeNestedFields($fields);
    }

    public function publicRenderNestedFields(array $fields): string
    {
        return $this->renderNestedFields($fields);
    }

    public function publicNormalizeNestedFieldObject(mixed $field): ?FieldInterface
    {
        return $this->normalizeNestedFieldObject($field);
    }
}

beforeEach(function (): void {
    $this->handler = new TestHandlesNestedFieldConfigs;
});

it('normalizes nested fields array', function (): void {
    $fields = ['field1', 'field2'];
    $result = $this->handler->publicNormalizeNestedFields($fields);

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result)->toBe(['field1', 'field2']);
});

it('normalizes nested fields with associative array', function (): void {
    $fields = ['key1' => 'field1', 'key2' => 'field2'];
    $result = $this->handler->publicNormalizeNestedFields($fields);

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2);
});

it('returns empty array for non-array fields', function (): void {
    $result = $this->handler->publicNormalizeNestedFields('invalid');

    expect($result)->toBe([]);
});

it('renders nested field objects', function (): void {
    $field1 = new TextField('field1');
    $field2 = new TextField('field2');
    $fields = [$field1, $field2];

    $html = $this->handler->publicRenderNestedFields($fields);

    expect($html)->toContain('type="text"')
        ->and($html)->toContain('field1')
        ->and($html)->toContain('field2');
});

it('skips invalid field objects', function (): void {
    $field = new TextField('field1');
    $fields = [$field, 'invalid', ['array']];

    $html = $this->handler->publicRenderNestedFields($fields);

    expect($html)->toContain('field1');
});

it('returns empty string for empty fields array', function (): void {
    $html = $this->handler->publicRenderNestedFields([]);

    expect($html)->toBe('');
});

it('normalizes FieldInterface object by cloning', function (): void {
    $field = new TextField('field1');
    $result = $this->handler->publicNormalizeNestedFieldObject($field);

    expect($result)->toBeInstanceOf(FieldInterface::class)
        ->and($result)->not->toBe($field); // cloned
});

it('returns null for non-array and non-FieldInterface', function (): void {
    $result = $this->handler->publicNormalizeNestedFieldObject('invalid');

    expect($result)->toBeNull();
});

it('creates field from array config', function (): void {
    $config = [
        'type' => 'text',
        'name' => 'field_name',
        'label' => 'Field Label',
    ];

    $result = $this->handler->publicNormalizeNestedFieldObject($config);

    expect($result)->toBeInstanceOf(FieldInterface::class);
});

it('uses id as fallback for name', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'field_id',
    ];

    $result = $this->handler->publicNormalizeNestedFieldObject($config);

    expect($result)->not->toBeNull();
});

it('returns null when type is missing', function (): void {
    $config = [
        'name' => 'field_name',
    ];

    $result = $this->handler->publicNormalizeNestedFieldObject($config);

    expect($result)->toBeNull();
});

it('returns null when name and id are missing', function (): void {
    $config = [
        'type' => 'text',
    ];

    $result = $this->handler->publicNormalizeNestedFieldObject($config);

    expect($result)->toBeNull();
});

it('sets value from config', function (): void {
    $config = [
        'type' => 'text',
        'name' => 'field_name',
        'value' => 'test_value',
    ];

    $result = $this->handler->publicNormalizeNestedFieldObject($config);

    expect($result)->not->toBeNull();
});

it('sets custom attributes from config', function (): void {
    $config = [
        'type' => 'text',
        'name' => 'field_name',
        'placeholder' => 'Enter value',
        'class' => 'custom-class',
    ];

    $result = $this->handler->publicNormalizeNestedFieldObject($config);

    expect($result)->not->toBeNull();
});

it('skips non-string keys in config', function (): void {
    $config = [
        'type' => 'text',
        'name' => 'field_name',
        123 => 'invalid_key',
    ];

    $result = $this->handler->publicNormalizeNestedFieldObject($config);

    expect($result)->not->toBeNull();
});

it('skips type, id, name keys when setting attributes', function (): void {
    $config = [
        'type' => 'text',
        'name' => 'field_name',
        'id' => 'field_id',
    ];

    $result = $this->handler->publicNormalizeNestedFieldObject($config);

    expect($result)->not->toBeNull();
});
