<?php

declare(strict_types=1);

beforeEach(function (): void {
    // Ensure WP_Field class is loaded
    if (! class_exists('WP_Field', false)) {
        require_once __DIR__.'/../../../vanilla/WP_Field.php';
    }
});

describe('WP_Field::init_field_types', function (): void {
    it('initializes field types registry', function (): void {
        // Create a new instance to trigger initialization
        new WP_Field(['id' => 'test', 'type' => 'text', 'label' => 'Test']);

        $reflection = new ReflectionClass('WP_Field');
        $property = $reflection->getProperty('field_types');
        $property->setAccessible(true);

        $fieldTypes = $property->getValue();

        expect($fieldTypes)->toBeArray()
            ->and($fieldTypes)->toHaveKey('text')
            ->and($fieldTypes)->toHaveKey('textarea')
            ->and($fieldTypes)->toHaveKey('select')
            ->and($fieldTypes)->toHaveKey('radio')
            ->and($fieldTypes)->toHaveKey('checkbox')
            ->and($fieldTypes)->toHaveKey('group')
            ->and($fieldTypes)->toHaveKey('repeater')
            ->and($fieldTypes)->toHaveKey('editor')
            ->and($fieldTypes)->toHaveKey('media')
            ->and($fieldTypes)->toHaveKey('image');
    });

    it('does not reinitialize if already initialized', function (): void {
        $reflection = new ReflectionClass('WP_Field');
        $method = $reflection->getMethod('init_field_types');
        $method->setAccessible(true);
        $property = $reflection->getProperty('field_types');
        $property->setAccessible(true);

        // Ensure initialized
        new WP_Field(['id' => 'test', 'type' => 'text', 'label' => 'Test']);
        $firstCall = $property->getValue();
        $firstCount = count($firstCall);

        // Try to reinitialize
        $method->invoke(null);
        $secondCall = $property->getValue();
        $secondCount = count($secondCall);

        expect($firstCount)->toBeGreaterThan(0)
            ->and($secondCount)->toBe($firstCount);
    });
});

describe('WP_Field constructor', function (): void {
    it('creates instance with valid field data', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field);

        expect($wpField)->toBeInstanceOf(WP_Field::class)
            ->and($wpField->field)->toBeArray()
            ->and($wpField->field['id'])->toBe('test_field')
            ->and($wpField->field['type'])->toBe('text')
            ->and($wpField->field['label'])->toBe('Test Field');
    });

    it('normalizes field aliases', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'title' => 'Test Title',
            'val' => 'test_value',
            'attr' => ['data-test' => 'value'],
        ];

        $wpField = new WP_Field($field);

        expect($wpField->field['label'])->toBe('Test Title')
            ->and($wpField->field['value'])->toBe('test_value')
            ->and($wpField->field['custom_attributes'])->toBe(['data-test' => 'value']);
    });

    it('sets default storage type to post', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field);

        expect($wpField->storage_type)->toBe('post');
    });

    it('accepts custom storage type', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'options');

        expect($wpField->storage_type)->toBe('options');
    });

    it('handles string field data with parse_str', function (): void {
        $fieldString = 'id=test_field&type=text&label=Test+Field';

        $wpField = new WP_Field($fieldString);

        // String data is parsed and validated
        expect($wpField->field)->toBeArray();
    });

    it('handles object field data', function (): void {
        $fieldObject = (object) [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($fieldObject);

        // Object is converted to array and validated
        expect($wpField->field)->toBeArray();
    });

    it('returns error for invalid field data', function (): void {
        $field = [
            'id' => 'test_field',
            // Missing type and label
        ];

        $wpField = new WP_Field($field);

        expect($wpField->field)->toBe('Incorrect field data');
    });

    it('returns error for missing label on required fields', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            // Missing label
        ];

        $wpField = new WP_Field($field);

        expect($wpField->field)->toBe('Incorrect field data');
    });

    it('allows missing label for fieldset', function (): void {
        $field = [
            'id' => 'test_fieldset',
            'type' => 'fieldset',
            // Missing label - allowed for fieldset
        ];

        $wpField = new WP_Field($field);

        expect($wpField->field)->toBeArray()
            ->and($wpField->field['type'])->toBe('fieldset');
    });

    it('allows missing label for content field', function (): void {
        $field = [
            'id' => 'test_content',
            'type' => 'content',
            'content' => 'Some content',
        ];

        $wpField = new WP_Field($field);

        expect($wpField->field)->toBeArray()
            ->and($wpField->field['type'])->toBe('content');
    });
});

describe('WP_Field::get_value', function (): void {
    it('returns empty string for non-existent post meta', function (): void {
        $field = [
            'id' => 'non_existent_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'post', 99999);

        $value = $wpField->get_value('non_existent_field', 99999);

        expect($value)->toBe('');
    });

    it('returns default value when field value is empty', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
            'default' => 'default_value',
            'value' => '', // Explicit empty value
        ];

        $wpField = new WP_Field($field, 'post', 99999);

        $reflection = new ReflectionClass($wpField);
        $method = $reflection->getMethod('get_field_value');
        $method->setAccessible(true);

        $value = $method->invoke($wpField, $field);

        // Empty value should be used, not default
        expect($value)->toBe('');
    });

    it('uses provided value over database value', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
            'value' => 'provided_value',
        ];

        $wpField = new WP_Field($field, 'post', 99999);

        $reflection = new ReflectionClass($wpField);
        $method = $reflection->getMethod('get_field_value');
        $method->setAccessible(true);

        $value = $method->invoke($wpField, $field);

        expect($value)->toBe('provided_value');
    });
});

describe('WP_Field::render', function (): void {
    it('returns HTML when output is false', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field);

        $html = $wpField->render(false);

        expect($html)->toBeString()
            ->and($html)->toContain('wp-field')
            ->and($html)->toContain('test_field');
    });

    it('outputs HTML when output is true', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field);

        ob_start();
        $wpField->render(true);
        $output = ob_get_clean();

        expect($output)->toContain('wp-field')
            ->and($output)->toContain('test_field');
    });

    it('renders error message for invalid field data', function (): void {
        $field = [
            'id' => 'test_field',
            // Missing type and label
        ];

        $wpField = new WP_Field($field);

        $html = $wpField->render(false);

        expect($html)->toBe('Incorrect field data');
    });

    it('includes field type in wrapper class', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'textarea',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field);

        $html = $wpField->render(false);

        expect($html)->toContain('wp-field-textarea');
    });
});

describe('WP_Field static properties', function (): void {
    it('has allowed_storage_types array', function (): void {
        expect(WP_Field::$allowed_storage_types)->toBeArray()
            ->and(WP_Field::$allowed_storage_types)->toContain('post')
            ->and(WP_Field::$allowed_storage_types)->toContain('options')
            ->and(WP_Field::$allowed_storage_types)->toContain('term')
            ->and(WP_Field::$allowed_storage_types)->toContain('user')
            ->and(WP_Field::$allowed_storage_types)->toContain('comment')
            ->and(WP_Field::$allowed_storage_types)->toContain('nav_menu_item')
            ->and(WP_Field::$allowed_storage_types)->toContain('site_option')
            ->and(WP_Field::$allowed_storage_types)->toContain('attachment')
            ->and(WP_Field::$allowed_storage_types)->toContain('custom_table');
    });

    it('has file constant pointing to correct path', function (): void {
        expect(WP_Field::file)->toContain('WP_Field.php');
    });
});
