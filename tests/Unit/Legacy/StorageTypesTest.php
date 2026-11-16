<?php

declare(strict_types=1);

beforeEach(function (): void {
    // Ensure WP_Field class is loaded
    if (! class_exists('WP_Field', false)) {
        require_once __DIR__.'/../../../vanilla/WP_Field.php';
    }
});

describe('WP_Field storage types', function (): void {
    it('supports post storage type', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'post', 123);

        expect($wpField->storage_type)->toBe('post')
            ->and($wpField->storage_id)->toBe(123);
    });

    it('supports options storage type', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'options');

        expect($wpField->storage_type)->toBe('options');
    });

    it('supports term storage type', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'term', 456);

        expect($wpField->storage_type)->toBe('term')
            ->and($wpField->storage_id)->toBe(456);
    });

    it('supports user storage type', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'user', 789);

        expect($wpField->storage_type)->toBe('user')
            ->and($wpField->storage_id)->toBe(789);
    });

    it('supports comment storage type', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'comment', 101);

        expect($wpField->storage_type)->toBe('comment')
            ->and($wpField->storage_id)->toBe(101);
    });

    it('supports nav_menu_item storage type', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'nav_menu_item', 202);

        expect($wpField->storage_type)->toBe('nav_menu_item')
            ->and($wpField->storage_id)->toBe(202);
    });

    it('supports site_option storage type', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'site_option');

        expect($wpField->storage_type)->toBe('site_option');
    });

    it('supports attachment storage type', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'attachment', 303);

        expect($wpField->storage_type)->toBe('attachment')
            ->and($wpField->storage_id)->toBe(303);
    });

    it('supports custom_table storage type', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
            'table' => 'wp_custom_meta',
            'object_id_column' => 'object_id',
            'meta_key_column' => 'meta_key',
            'meta_value_column' => 'meta_value',
        ];

        $wpField = new WP_Field($field, 'custom_table', 404);

        expect($wpField->storage_type)->toBe('custom_table')
            ->and($wpField->storage_id)->toBe(404);
    });
});

describe('WP_Field::get_value for different storage types', function (): void {
    it('handles post storage type', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'post', 99999);

        // Should return empty string for non-existent meta
        $value = $wpField->get_value('test_field', 99999);

        expect($value)->toBe('');
    });

    it('handles options storage type', function (): void {
        $field = [
            'id' => 'test_option',
            'type' => 'text',
            'label' => 'Test Option',
        ];

        $wpField = new WP_Field($field, 'options');

        // Should return null for non-existent option
        $value = $wpField->get_value('test_option');

        expect($value)->toBeNull();
    });

    it('handles term storage type', function (): void {
        $field = [
            'id' => 'test_term_meta',
            'type' => 'text',
            'label' => 'Test Term Meta',
        ];

        $wpField = new WP_Field($field, 'term', 99999);

        // Should return empty string for non-existent term meta
        $value = $wpField->get_value('test_term_meta', 99999);

        expect($value)->toBe('');
    });

    it('handles user storage type', function (): void {
        $field = [
            'id' => 'test_user_meta',
            'type' => 'text',
            'label' => 'Test User Meta',
        ];

        $wpField = new WP_Field($field, 'user', 99999);

        // Should return empty string for non-existent user meta
        $value = $wpField->get_value('test_user_meta', 99999);

        expect($value)->toBe('');
    });

    it('handles comment storage type', function (): void {
        $field = [
            'id' => 'test_comment_meta',
            'type' => 'text',
            'label' => 'Test Comment Meta',
        ];

        $wpField = new WP_Field($field, 'comment', 99999);

        // Should return null for non-existent comment meta
        $value = $wpField->get_value('test_comment_meta', 99999);

        expect($value)->toBeNull();
    });

    it('handles site_option storage type', function (): void {
        $field = [
            'id' => 'test_site_option',
            'type' => 'text',
            'label' => 'Test Site Option',
        ];

        $wpField = new WP_Field($field, 'site_option');

        // Should return false for non-existent site option
        $value = $wpField->get_value('test_site_option');

        expect($value)->toBeFalse();
    });

    it('respects wp_field_get_value filter', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'post', 123);

        if (function_exists('add_filter')) {
            add_filter('wp_field_get_value', function ($value, $storage_type, $key, $id, $field_data) {
                if ($key === 'test_field') {
                    return 'filtered_value';
                }

                return $value;
            }, 10, 5);

            $value = $wpField->get_value('test_field', 123);

            expect($value)->toBe('filtered_value');

            remove_all_filters('wp_field_get_value');
        }
    });
});

describe('WP_Field custom table storage', function (): void {
    it('uses default table name when not specified', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
        ];

        $wpField = new WP_Field($field, 'custom_table', 123);

        $reflection = new ReflectionClass($wpField);
        $property = $reflection->getProperty('field');
        $property->setAccessible(true);

        $fieldData = $property->getValue($wpField);

        expect($fieldData)->toBeArray();
    });

    it('uses custom table name when specified', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
            'table' => 'wp_my_custom_table',
        ];

        $wpField = new WP_Field($field, 'custom_table', 123);

        expect($wpField->field['table'])->toBe('wp_my_custom_table');
    });

    it('uses custom column names when specified', function (): void {
        $field = [
            'id' => 'test_field',
            'type' => 'text',
            'label' => 'Test Field',
            'table' => 'wp_custom_table',
            'object_id_column' => 'custom_id',
            'meta_key_column' => 'custom_key',
            'meta_value_column' => 'custom_value',
        ];

        $wpField = new WP_Field($field, 'custom_table', 123);

        expect($wpField->field['object_id_column'])->toBe('custom_id')
            ->and($wpField->field['meta_key_column'])->toBe('custom_key')
            ->and($wpField->field['meta_value_column'])->toBe('custom_value');
    });
});
