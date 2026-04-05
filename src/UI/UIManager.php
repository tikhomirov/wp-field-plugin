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

        if (self::isReactMode()) {
            self::enqueueReactAssets($pluginUrl, $pluginPath);
        } else {
            self::enqueueVanillaAssets($pluginUrl, $pluginPath);
        }

        self::$assetsEnqueued = true;
    }

    protected static function enqueueReactAssets(string $pluginUrl, string $pluginPath): void
    {
        $repeaterJs = $pluginUrl . 'assets/dist/repeater.js';
        $flexibleJs = $pluginUrl . 'assets/dist/flexible-content.js';

        if (file_exists($pluginPath . '/assets/dist/repeater.js')) {
            $version = filemtime($pluginPath . '/assets/dist/repeater.js');
            wp_enqueue_script(
                'wp-field-repeater-react',
                $repeaterJs,
                ['react', 'react-dom'],
                $version === false ? false : (string) $version,
                true,
            );
        }

        if (file_exists($pluginPath . '/assets/dist/flexible-content.js')) {
            $version = filemtime($pluginPath . '/assets/dist/flexible-content.js');
            wp_enqueue_script(
                'wp-field-flexible-react',
                $flexibleJs,
                ['react', 'react-dom'],
                $version === false ? false : (string) $version,
                true,
            );
        }

        wp_enqueue_script('react', 'https://unpkg.com/react@18/umd/react.production.min.js', [], '18.2.0', true);
        wp_enqueue_script('react-dom', 'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js', ['react'], '18.2.0', true);

        self::enqueueCommonStyles($pluginUrl, $pluginPath);
    }

    protected static function enqueueVanillaAssets(string $pluginUrl, string $pluginPath): void
    {
        $repeaterJs = $pluginUrl . 'assets/js/repeater.js';
        $flexibleJs = $pluginUrl . 'assets/js/flexible-content.js';

        if (file_exists($pluginPath . '/assets/js/repeater.js')) {
            $version = filemtime($pluginPath . '/assets/js/repeater.js');
            wp_enqueue_script(
                'wp-field-repeater',
                $repeaterJs,
                ['jquery'],
                $version === false ? false : (string) $version,
                true,
            );
        }

        if (file_exists($pluginPath . '/assets/js/flexible-content.js')) {
            $version = filemtime($pluginPath . '/assets/js/flexible-content.js');
            wp_enqueue_script(
                'wp-field-flexible',
                $flexibleJs,
                ['jquery'],
                $version === false ? false : (string) $version,
                true,
            );
        }

        self::enqueueCommonStyles($pluginUrl, $pluginPath);
    }

    protected static function enqueueCommonStyles(string $pluginUrl, string $pluginPath): void
    {
        $cssFile = $pluginUrl . 'assets/css/wp-field.css';

        if (file_exists($pluginPath . '/assets/css/wp-field.css')) {
            $version = filemtime($pluginPath . '/assets/css/wp-field.css');
            wp_enqueue_style(
                'wp-field-styles',
                $cssFile,
                [],
                $version === false ? false : (string) $version,
            );
        }
    }

    public static function init(): void
    {
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAssets']);
    }
}
