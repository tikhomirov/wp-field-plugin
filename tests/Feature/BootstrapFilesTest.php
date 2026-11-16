<?php

declare(strict_types=1);

beforeEach(function (): void {
    require_once dirname(__DIR__).'/bootstrap.php';
    $GLOBALS['wp_test_actions'] = [];
    $GLOBALS['wp_test_scripts'] = [];
    $GLOBALS['wp_test_styles'] = [];
    $GLOBALS['wp_test_script_is'] = [];
    $GLOBALS['wp_test_style_is'] = [];
    $GLOBALS['wp_test_filters'] = [];
    $GLOBALS['wp_test_media_enqueued'] = false;
});

it('bootstrap and legacy loaders cover guards and hooks', function (): void {
    $result = include dirname(__DIR__, 2).'/wp-field.php';
    expect($result)->toBeNull()
        ->and(defined('WP_FIELD_PLUGIN_FILE'))->toBeFalse();

    if (! defined('ABSPATH')) {
        define('ABSPATH', __DIR__);
    }
    if (! defined('WP_DEBUG')) {
        define('WP_DEBUG', false);
    }

    $GLOBALS['wp_test_filters']['wp_field_enable_legacy'] = static fn (bool $enabled): bool => false;

    include dirname(__DIR__, 2).'/wp-field.php';

    expect(defined('WP_FIELD_PLUGIN_FILE'))->toBeTrue()
        ->and(defined('WP_FIELD_PLUGIN_DIR'))->toBeTrue()
        ->and(defined('WP_FIELD_PLUGIN_URL'))->toBeTrue();

    include dirname(__DIR__, 2).'/vanilla/bootstrap.php';

    expect($GLOBALS['wp_test_actions'])->not->toBeEmpty();
    $hooks = array_column($GLOBALS['wp_test_actions'], 'hook');
    expect($hooks)->toContain('admin_enqueue_scripts');
    $callbacks = array_column($GLOBALS['wp_test_actions'], 'callback');
    expect($callbacks)->not->toBeEmpty();

    $GLOBALS['wp_test_script_is']['wp-field-main'] = true;
    $GLOBALS['wp_test_style_is']['wp-field-main'] = true;
    foreach ($callbacks as $callback) {
        if (is_callable($callback)) {
            $callback();
        }
    }

    expect($GLOBALS['wp_test_media_enqueued'])->toBeTrue()
        ->and($GLOBALS['wp_test_scripts'])->not->toHaveKey('wp-field-main')
        ->and($GLOBALS['wp_test_styles'])->not->toHaveKey('wp-field-main');

    include dirname(__DIR__, 2).'/WP_Field.php';

    expect(class_exists('WP_Field'))->toBeTrue();
});
