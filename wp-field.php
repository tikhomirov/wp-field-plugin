<?php

/**
 * Plugin Name: WP_Field — HTML Fields Library for WordPress
 * Plugin URI:  https://github.com/rwsite/wp-field-plugin
 * Description: HTML fields library for WordPress, designed as a foundation for custom frameworks, settings systems, and admin UI builders. Includes Fluent API, 52 unique field types (+4 aliases), and React/Vanilla UI.
 * Version:     3.0.0
 * Requires at least: 6.0
 * Tested up to: 7.0
 * Requires PHP: 8.3
 * Author:      Aleksei Tikhomirov
 * Author URI:  https://rwsite.ru
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-field
 * Domain Path: /lang/
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    return;
}

if (! defined('WP_FIELD_PLUGIN_FILE')) {
    define('WP_FIELD_PLUGIN_FILE', __FILE__);
}
if (! defined('WP_FIELD_PLUGIN_DIR')) {
    define('WP_FIELD_PLUGIN_DIR', __DIR__.'/');
}
if (! defined('WP_FIELD_PLUGIN_URL')) {
    define('WP_FIELD_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (file_exists(WP_FIELD_PLUGIN_DIR.'vendor/autoload.php')) {
    require_once WP_FIELD_PLUGIN_DIR.'vendor/autoload.php';
} else {
    // Fallback autoloader for environments without Composer
    spl_autoload_register(function ($class): void {
        $prefix = 'WpField\\';
        $base_dir = WP_FIELD_PLUGIN_DIR.'src/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir.str_replace('\\', '/', $relative_class).'.php';

        if (file_exists($file)) {
            require $file;
        }
    });
}

$legacy_enabled = function_exists('apply_filters')
    ? (bool) apply_filters('wp_field_enable_legacy', true)
    : true;

if ($legacy_enabled) {
    // Legacy class + isolated vanilla bootstrap.
    require_once WP_FIELD_PLUGIN_DIR.'vanilla/bootstrap.php';
}

// Loading demo pages strictly within WordPress admin debug context.
if (function_exists('is_admin') && is_admin() && defined('WP_DEBUG') && WP_DEBUG) {
    if ($legacy_enabled) {
        require_once WP_FIELD_PLUGIN_DIR.'vanilla/example.php';
    }

    // Modern demos: wp-field-components (React docs) + wp-field-ui-demo (Flux UI showcase).
    require_once WP_FIELD_PLUGIN_DIR.'examples/components/index.php';
    require_once WP_FIELD_PLUGIN_DIR.'examples/ui-demo/index.php';
}
