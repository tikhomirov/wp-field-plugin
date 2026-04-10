<?php

declare(strict_types=1);

if (! class_exists('WP_Field')) {
    require_once __DIR__.'/WP_Field.php';
}

// Legacy fallback assets bootstrap.
if (function_exists('add_action')) {
    add_action('admin_enqueue_scripts', static function (?string $hook = null): void {
        if ($hook === 'tools_page_wp-field-components') {
            return;
        }

        $base_url = defined('WP_FIELD_PLUGIN_URL') ? WP_FIELD_PLUGIN_URL : plugin_dir_url(__FILE__);
        $base_dir = defined('WP_FIELD_PLUGIN_DIR') ? WP_FIELD_PLUGIN_DIR : plugin_dir_path(__FILE__);

        $css_rel = 'vanilla/assets/css/wp-field.css';
        $js_rel = 'vanilla/assets/js/wp-field.js';

        $css_ver = file_exists($base_dir.$css_rel) ? (string) filemtime($base_dir.$css_rel) : '4.0.0';
        $js_ver = file_exists($base_dir.$js_rel) ? (string) filemtime($base_dir.$js_rel) : '4.0.0';

        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_media();

        if (! wp_script_is('wp-field-main', 'enqueued')) {
            wp_enqueue_script(
                'wp-field-main',
                $base_url.$js_rel,
                ['jquery'],
                $js_ver,
                true,
            );
        }

        if (! wp_style_is('wp-field-main', 'enqueued')) {
            wp_enqueue_style(
                'wp-field-main',
                $base_url.$css_rel,
                [],
                $css_ver,
            );
        }
    });
}
