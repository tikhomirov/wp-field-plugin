<?php

declare(strict_types=1);

use WpField\Field\FieldInterface;
use WpField\Legacy\LegacyAdapter;

it('creates field with minimal config', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
    ];

    $field = LegacyAdapter::make($config);

    expect($field)->toBeInstanceOf(FieldInterface::class)
        ->and($field->getName())->toBe('test_field');
});

it('creates field with name instead of id', function (): void {
    $config = [
        'type' => 'text',
        'name' => 'test_field',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getName())->toBe('test_field');
});

it('creates field with name when id is not set', function (): void {
    $config = [
        'type' => 'text',
        'name' => 'field_from_name',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getName())->toBe('field_from_name');
});

it('throws exception when name is empty', function (): void {
    $config = [
        'type' => 'text',
        'id' => '',
    ];

    expect(fn () => LegacyAdapter::make($config))->toThrow(InvalidArgumentException::class);
});

it('throws exception when name is 0', function (): void {
    $config = [
        'type' => 'text',
        'id' => '0',
    ];

    expect(fn () => LegacyAdapter::make($config))->toThrow(InvalidArgumentException::class);
});

it('applies label from config', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'label' => 'Test Label',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('label'))->toBe('Test Label');
});

it('applies title as label when label is not set', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'title' => 'Test Title',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('label'))->toBe('Test Title');
});

it('label takes precedence over title', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'label' => 'Test Label',
        'title' => 'Test Title',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('label'))->toBe('Test Label');
});

it('applies placeholder', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'placeholder' => 'Enter text',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('placeholder'))->toBe('Enter text');
});

it('applies description', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'description' => 'Test description',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('description'))->toBe('Test description');
});

it('applies desc as description alias', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'desc' => 'Test description',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('description'))->toBe('Test description');
});

it('description takes precedence over desc', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'description' => 'Description',
        'desc' => 'Desc',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('description'))->toBe('Description');
});

it('applies default value', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'default' => 'default_value',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('default'))->toBe('default_value');
});

it('applies value from config', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'value' => 'current_value',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getValue())->toBe('current_value');
});

it('applies val as value alias', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'val' => 'current_value',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getValue())->toBe('current_value');
});

it('value takes precedence over val', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'value' => 'value',
        'val' => 'val',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getValue())->toBe('value');
});

it('applies class', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'class' => 'custom-class',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('class'))->toBe('custom-class');
});

it('applies required flag', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'required' => true,
    ];

    $field = LegacyAdapter::make($config);

    expect($field->isRequired())->toBeTrue();
});

it('applies readonly flag', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'readonly' => true,
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('readonly'))->toBeTrue();
});

it('applies disabled flag', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'disabled' => true,
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('disabled'))->toBeTrue();
});

it('applies attributes from config', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'attributes' => [
            'data-test' => 'value',
            'maxlength' => 100,
        ],
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('data-test'))->toBe('value')
        ->and($field->getAttribute('maxlength'))->toBe(100);
});

it('applies attr as attributes alias', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'attr' => [
            'data-test' => 'value',
        ],
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('data-test'))->toBe('value');
});

it('applies atts as attributes alias', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'atts' => [
            'data-test' => 'value',
        ],
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getAttribute('data-test'))->toBe('value');
});

it('applies validation as string', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => 'required|email|min:5',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->isRequired())->toBeTrue();
});

it('applies validation as array', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => [
            'required',
            'email',
            'min' => 5,
        ],
    ];

    $field = LegacyAdapter::make($config);

    expect($field->isRequired())->toBeTrue();
});

it('applies conditional_logic', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'conditional_logic' => [
            [
                'field' => 'other_field',
                'operator' => '==',
                'value' => 'test',
            ],
        ],
    ];

    $field = LegacyAdapter::make($config);

    expect($field->hasConditions())->toBeTrue();
});

it('applies conditions as alias', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'conditions' => [
            [
                'field' => 'other_field',
                'operator' => '==',
                'value' => 'test',
            ],
        ],
    ];

    $field = LegacyAdapter::make($config);

    expect($field->hasConditions())->toBeTrue();
});

it('makes multiple fields', function (): void {
    $fields = [
        ['type' => 'text', 'id' => 'field1'],
        ['type' => 'text', 'id' => 'field2'],
        ['type' => 'text', 'id' => 'field3'],
    ];

    $result = LegacyAdapter::makeMultiple($fields);

    expect($result)->toHaveCount(3)
        ->and($result[0]->getName())->toBe('field1')
        ->and($result[1]->getName())->toBe('field2')
        ->and($result[2]->getName())->toBe('field3');
});

it('skips non-array configs in makeMultiple', function (): void {
    $fields = [
        ['type' => 'text', 'id' => 'field1'],
        'invalid',
        ['type' => 'text', 'id' => 'field2'],
    ];

    $result = LegacyAdapter::makeMultiple($fields);

    expect($result)->toHaveCount(2);
});

it('renders field', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
    ];

    $html = LegacyAdapter::render($config);

    expect($html)->toBeString();
});

it('renders multiple fields', function (): void {
    $fields = [
        ['type' => 'text', 'id' => 'field1'],
        ['type' => 'text', 'id' => 'field2'],
    ];

    $html = LegacyAdapter::renderMultiple($fields);

    expect($html)->toBeString();
});

it('applies email validation rule', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => 'email',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getValidationRules()['email'])->toBeTrue();
});

it('applies url validation rule', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => 'url',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getValidationRules()['url'])->toBeTrue();
});

it('applies min validation rule', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => 'min:10',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getValidationRules()['min'])->toBe(10);
});

it('applies max validation rule', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => 'max:100',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getValidationRules()['max'])->toBe(100);
});

it('applies pattern validation rule', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => 'pattern:[a-z]+',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getValidationRules()['pattern'])->toBe('[a-z]+');
});

it('applies validation as associative array', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => [
            'min' => 5,
            'max' => 100,
        ],
    ];

    $field = LegacyAdapter::make($config);

    expect($field->getValidationRules()['min'])->toBe(5)
        ->and($field->getValidationRules()['max'])->toBe(100);
});

it('ignores unknown validation rules', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => 'unknown_rule',
    ];

    $field = LegacyAdapter::make($config);

    expect($field)->toBeInstanceOf(FieldInterface::class);
});

it('handles non-numeric min validation gracefully', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => 'min:abc',
    ];

    $field = LegacyAdapter::make($config);

    expect($field)->toBeInstanceOf(FieldInterface::class);
});

it('handles non-numeric max validation gracefully', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => 'max:xyz',
    ];

    $field = LegacyAdapter::make($config);

    expect($field)->toBeInstanceOf(FieldInterface::class);
});

it('handles non-string pattern validation gracefully', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => [
            'pattern' => 123,
        ],
    ];

    $field = LegacyAdapter::make($config);

    expect($field)->toBeInstanceOf(FieldInterface::class);
});

it('skips conditional logic with empty field', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'conditional_logic' => [
            [
                'field' => '',
                'operator' => '==',
                'value' => 'test',
            ],
        ],
    ];

    $field = LegacyAdapter::make($config);

    expect($field->hasConditions())->toBeFalse();
});

it('skips conditional logic with field 0', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'conditional_logic' => [
            [
                'field' => '0',
                'operator' => '==',
                'value' => 'test',
            ],
        ],
    ];

    $field = LegacyAdapter::make($config);

    expect($field->hasConditions())->toBeFalse();
});

it('skips conditional logic with non-string field', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'conditional_logic' => [
            [
                'field' => 123,
                'operator' => '==',
                'value' => 'test',
            ],
        ],
    ];

    $field = LegacyAdapter::make($config);

    expect($field->hasConditions())->toBeFalse();
});

it('ignores non-array validation config', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'validation' => 123,
    ];

    $field = LegacyAdapter::make($config);

    expect($field)->toBeInstanceOf(FieldInterface::class);
});

it('ignores non-array conditions config', function (): void {
    $config = [
        'type' => 'text',
        'id' => 'test_field',
        'conditions' => 'invalid',
    ];

    $field = LegacyAdapter::make($config);

    expect($field->hasConditions())->toBeFalse();
});
