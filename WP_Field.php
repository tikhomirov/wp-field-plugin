<?php

declare(strict_types=1);

// Deprecated entrypoint notice for direct includes in WordPress context.
if (defined('ABSPATH') && ! defined('WP_FIELD_PLUGIN_FILE') && function_exists('add_action')) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-warning"><p><strong>WP_Field:</strong> подключение через <code>WP_Field.php</code> устарело. Используйте <code>wp-field.php</code> как основную точку входа.</p></div>';
    });
}

if (! defined('WP_FIELD_PLUGIN_FILE')) {
    define('WP_FIELD_PLUGIN_FILE', __DIR__.'/wp-field.php');
}
if (! defined('WP_FIELD_PLUGIN_DIR')) {
    define('WP_FIELD_PLUGIN_DIR', __DIR__.'/');
}
if (! defined('WP_FIELD_PLUGIN_URL')) {
    define('WP_FIELD_PLUGIN_URL', function_exists('plugin_dir_url') ? plugin_dir_url(WP_FIELD_PLUGIN_FILE) : '/wp-content/plugins/wp-field-plugin/');
}

if (file_exists(WP_FIELD_PLUGIN_DIR.'vendor/autoload.php')) {
    require_once WP_FIELD_PLUGIN_DIR.'vendor/autoload.php';
} else {
    // Fallback autoloader for environments without Composer.
    spl_autoload_register(function ($class): void {
        $prefix = 'WpField\\';
        $baseDir = WP_FIELD_PLUGIN_DIR.'src/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir.str_replace('\\', '/', $relativeClass).'.php';

        if (file_exists($file)) {
            require $file;
        }
    });
}

require_once WP_FIELD_PLUGIN_DIR.'legacy/WP_Field.php';
