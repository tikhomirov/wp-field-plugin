<?php

declare(strict_types=1);

namespace WpField\UI;

class UIManager
{
    protected static string $mode = 'vanilla';

    protected static bool $assetsEnqueued = false;

    public static function setMode(string $mode): void
    {
        if (in_array($mode, ['vanilla', 'react'], true)) {
            self::$mode = $mode;
        }
    }

    public static function getMode(): string
    {
        return apply_filters('wp_field_ui_mode', self::$mode);
    }

    public static function isReactMode(): bool
    {
        return self::getMode() === 'react';
    }

    public static function enqueueAssets(): void
    {
        if (self::$assetsEnqueued) {
            return;
        }

        $pluginUrl = plugin_dir_url(dirname(__DIR__, 2));
        $pluginPath = dirname(__DIR__, 2);

        self::enqueueWordPressEnhancementAssets($pluginUrl, $pluginPath);

        if (self::isReactMode()) {
            self::enqueueReactAssets($pluginUrl, $pluginPath);
        } else {
            self::enqueueVanillaAssets($pluginUrl, $pluginPath);
        }

        self::$assetsEnqueued = true;
    }

    protected static function enqueueReactAssets(string $pluginUrl, string $pluginPath): void
    {
        self::enqueueScript($pluginUrl, $pluginPath, 'wp-field-repeater-react', 'assets/dist/repeater.js', [], true);
        self::enqueueScript($pluginUrl, $pluginPath, 'wp-field-flexible-react', 'assets/dist/flexible-content.js', [], true);
        self::enqueueScript($pluginUrl, $pluginPath, 'wp-field-admin-shell-react', 'assets/dist/admin-shell.js', [], false);
        self::enqueueScript($pluginUrl, $pluginPath, 'wp-field-wizard-react', 'assets/dist/wizard.js', [], false);

        self::enqueueCommonStyles($pluginUrl, $pluginPath);
    }

    protected static function enqueueVanillaAssets(string $pluginUrl, string $pluginPath): void
    {
        self::enqueueScript($pluginUrl, $pluginPath, 'wp-field-main', 'legacy/assets/js/wp-field.js', ['jquery'], false);

        self::enqueueCommonStyles($pluginUrl, $pluginPath);
    }

    protected static function enqueueWordPressEnhancementAssets(string $pluginUrl, string $pluginPath): void
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery-ui-slider');

        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        }

        if (function_exists('wp_enqueue_editor')) {
            wp_enqueue_editor();
        }

        if (function_exists('wp_enqueue_code_editor')) {
            wp_enqueue_code_editor(['type' => 'text/html']);
        }

        self::enqueueScript(
            $pluginUrl,
            $pluginPath,
            'wp-field-integrations',
            'assets/js/wp-field-integrations.js',
            ['jquery', 'wp-color-picker', 'jquery-ui-slider'],
            false,
        );
    }

    protected static function enqueueCommonStyles(string $pluginUrl, string $pluginPath): void
    {
        self::enqueueStyle($pluginUrl, $pluginPath, 'wp-field-styles', 'legacy/assets/css/wp-field.css');
        self::enqueueStyle($pluginUrl, $pluginPath, 'wp-field-admin-shell-styles', 'assets/css/admin-shell.css');
        self::enqueueStyle($pluginUrl, $pluginPath, 'wp-field-wizard-styles', 'assets/css/wizard.css');
    }

    /**
     * @param  array<int, string>  $dependencies
     */
    protected static function enqueueScript(
        string $pluginUrl,
        string $pluginPath,
        string $handle,
        string $relativePath,
        array $dependencies,
        bool $module,
    ): void {
        $fullPath = $pluginPath.'/'.$relativePath;
        if (! file_exists($fullPath)) {
            return;
        }

        $version = filemtime($fullPath);
        wp_enqueue_script(
            $handle,
            $pluginUrl.$relativePath,
            $dependencies,
            $version === false ? false : (string) $version,
            true,
        );

        if ($module) {
            wp_script_add_data($handle, 'type', 'module');
        }
    }

    protected static function enqueueStyle(string $pluginUrl, string $pluginPath, string $handle, string $relativePath): void
    {
        $fullPath = $pluginPath.'/'.$relativePath;
        if (! file_exists($fullPath)) {
            return;
        }

        $version = filemtime($fullPath);
        wp_enqueue_style(
            $handle,
            $pluginUrl.$relativePath,
            [],
            $version === false ? false : (string) $version,
        );
    }

    public static function init(): void
    {
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAssets']);
    }
}
