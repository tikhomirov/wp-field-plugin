<?php

declare(strict_types=1);

// Load plugin file to test constants and autoloading
require_once __DIR__.'/../../wp-field.php';

it('defines WP_FIELD_PLUGIN_FILE constant', function (): void {
    expect(defined('WP_FIELD_PLUGIN_FILE'))->toBeTrue();
});

it('defines WP_FIELD_PLUGIN_DIR constant', function (): void {
    expect(defined('WP_FIELD_PLUGIN_DIR'))->toBeTrue();
});

it('defines WP_FIELD_PLUGIN_URL constant', function (): void {
    expect(defined('WP_FIELD_PLUGIN_URL'))->toBeTrue();
});

it('WP_FIELD_PLUGIN_FILE points to correct file', function (): void {
    expect(WP_FIELD_PLUGIN_FILE)->toContain('wp-field.php');
});

it('WP_FIELD_PLUGIN_DIR points to existing directory', function (): void {
    expect(WP_FIELD_PLUGIN_DIR)->toEndWith('/')
        ->and(is_dir(WP_FIELD_PLUGIN_DIR))->toBeTrue();
});

it('WP_FIELD_PLUGIN_URL is a valid URL format', function (): void {
    expect(WP_FIELD_PLUGIN_URL)->toStartWith('http');
});

it('autoloader loads WpField classes', function (): void {
    $fieldClassExists = class_exists('WpField\Field\Field');
    $textFieldExists = class_exists('WpField\Field\Types\TextField');

    expect($fieldClassExists)->toBeTrue()
        ->and($textFieldExists)->toBeTrue();
});

it('vanilla WP_Field class is loaded when legacy is enabled', function (): void {
    $wpFieldClassExists = class_exists('WP_Field', false);

    expect($wpFieldClassExists)->toBeTrue();
});
