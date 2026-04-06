<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class BootstrapFilesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__).'/bootstrap.php';
        $GLOBALS['wp_test_actions'] = [];
        $GLOBALS['wp_test_scripts'] = [];
        $GLOBALS['wp_test_styles'] = [];
        $GLOBALS['wp_test_script_is'] = [];
        $GLOBALS['wp_test_style_is'] = [];
        $GLOBALS['wp_test_filters'] = [];
        $GLOBALS['wp_test_media_enqueued'] = false;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function bootstrap_and_legacy_loaders_cover_guards_and_hooks(): void
    {
        $result = include dirname(__DIR__, 2).'/wp-field.php';
        $this->assertNull($result);
        $this->assertFalse(defined('WP_FIELD_PLUGIN_FILE'));

        define('ABSPATH', __DIR__);
        define('WP_DEBUG', false);

        $GLOBALS['wp_test_filters']['wp_field_enable_legacy'] = static fn (bool $enabled): bool => false;

        include dirname(__DIR__, 2).'/wp-field.php';

        $this->assertTrue(defined('WP_FIELD_PLUGIN_FILE'));
        $this->assertTrue(defined('WP_FIELD_PLUGIN_DIR'));
        $this->assertTrue(defined('WP_FIELD_PLUGIN_URL'));

        include dirname(__DIR__, 2) . '/vanilla/bootstrap.php';

        $this->assertNotEmpty($GLOBALS['wp_test_actions']);
        $hooks = array_column($GLOBALS['wp_test_actions'], 'hook');
        $this->assertContains('admin_enqueue_scripts', $hooks);
        $callbacks = array_column($GLOBALS['wp_test_actions'], 'callback');
        $this->assertNotEmpty($callbacks);

        $GLOBALS['wp_test_script_is']['wp-field-main'] = true;
        $GLOBALS['wp_test_style_is']['wp-field-main'] = true;
        foreach ($callbacks as $callback) {
            if (is_callable($callback)) {
                $callback();
            }
        }

        $this->assertTrue($GLOBALS['wp_test_media_enqueued']);
        $this->assertArrayNotHasKey('wp-field-main', $GLOBALS['wp_test_scripts']);
        $this->assertArrayNotHasKey('wp-field-main', $GLOBALS['wp_test_styles']);

        include dirname(__DIR__, 2).'/WP_Field.php';

        $this->assertTrue(class_exists('WP_Field'));
    }
}
