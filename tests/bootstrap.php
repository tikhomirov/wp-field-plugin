<?php

declare(strict_types=1);

// Load WordPress functions for testing
if (! function_exists('sanitize_text_field')) {
    function sanitize_text_field(string $str): string
    {
        return strip_tags($str);
    }
}

if (! function_exists('esc_attr')) {
    function esc_attr(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_html')) {
    function esc_html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (! function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string
    {
        return esc_html(__($text, $domain));
    }
}

if (! function_exists('is_email')) {
    function is_email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (! function_exists('get_option')) {
    function get_option(string $option, mixed $default = false): mixed
    {
        static $options = [];

        return $options[$option] ?? $default;
    }
}

if (! function_exists('update_option')) {
    function update_option(string $option, mixed $value): bool
    {
        static $options = [];
        $options[$option] = $value;

        return true;
    }
}

if (! function_exists('delete_option')) {
    function delete_option(string $option): bool
    {
        static $options = [];
        unset($options[$option]);

        return true;
    }
}

if (! function_exists('maybe_serialize')) {
    function maybe_serialize(mixed $data): string
    {
        if (is_array($data) || is_object($data)) {
            return serialize($data);
        }

        return (string) $data;
    }
}

if (! function_exists('maybe_unserialize')) {
    function maybe_unserialize(string $data): mixed
    {
        $unserialized = @unserialize($data);

        return $unserialized !== false ? $unserialized : $data;
    }
}

if (! function_exists('wp_json_encode')) {
    function wp_json_encode(mixed $value, int $flags = 0, int $depth = 512): string|false
    {
        return json_encode($value, $flags, $depth);
    }
}

if (! function_exists('wp_kses_post')) {
    function wp_kses_post(string $content): string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_url')) {
    function esc_url(string $url): string
    {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('sanitize_key')) {
    function sanitize_key(string $key): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '', $key) ?? '');
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (! isset($GLOBALS['wp_test_filters'][$hook])) {
            return $value;
        }

        return $GLOBALS['wp_test_filters'][$hook]($value, ...$args);
    }
}

if (! function_exists('add_action')) {
    function add_action(string $hook, callable $callback): void
    {
        $GLOBALS['wp_test_actions'] ??= [];
        $GLOBALS['wp_test_actions'][] = ['hook' => $hook, 'callback' => $callback];
    }
}

if (! function_exists('plugin_dir_url')) {
    function plugin_dir_url(string $file): string
    {
        return 'http://example.com/wp-field/'.trim(str_replace('\\', '/', dirname($file)), '/').'/';
    }
}

if (! function_exists('plugin_dir_path')) {
    function plugin_dir_path(string $file): string
    {
        return dirname($file).'/';
    }
}

if (! function_exists('wp_script_add_data')) {
    function wp_script_add_data(string $handle, string $key, mixed $value): void
    {
        $GLOBALS['wp_test_script_data'] ??= [];
        $GLOBALS['wp_test_script_data'][$handle][$key] = $value;
    }
}

if (! function_exists('wp_script_is')) {
    function wp_script_is(string $handle, string $status = 'enqueued'): bool
    {
        return (bool) ($GLOBALS['wp_test_script_is'][$handle] ?? false);
    }
}

if (! function_exists('wp_style_is')) {
    function wp_style_is(string $handle, string $status = 'enqueued'): bool
    {
        return (bool) ($GLOBALS['wp_test_style_is'][$handle] ?? false);
    }
}

if (! function_exists('wp_enqueue_media')) {
    function wp_enqueue_media(): void
    {
        $GLOBALS['wp_test_media_enqueued'] = true;
    }
}

if (! function_exists('wp_enqueue_editor')) {
    function wp_enqueue_editor(): void
    {
        $GLOBALS['wp_test_editor_enqueued'] = true;
    }
}

if (! function_exists('wp_enqueue_code_editor')) {
    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    function wp_enqueue_code_editor(array $settings = []): array
    {
        $GLOBALS['wp_test_code_editor_settings'] = $settings;

        return $settings;
    }
}

if (! function_exists('wp_enqueue_style')) {
    function wp_enqueue_style(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false): void
    {
        $GLOBALS['wp_test_styles'] ??= [];
        $GLOBALS['wp_test_styles'][$handle] = compact('src', 'deps', 'ver');
    }
}

if (! function_exists('wp_enqueue_script')) {
    function wp_enqueue_script(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false, bool $in_footer = false): void
    {
        $GLOBALS['wp_test_scripts'] ??= [];
        $GLOBALS['wp_test_scripts'][$handle] = compact('src', 'deps', 'ver', 'in_footer');
    }
}

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        return (bool) ($GLOBALS['wp_test_is_admin'] ?? false);
    }
}

if (! function_exists('get_current_screen')) {
    function get_current_screen(): ?object
    {
        return $GLOBALS['wp_test_current_screen'] ?? null;
    }
}

if (! function_exists('add_meta_box')) {
    function add_meta_box(string $id, string $title, callable $callback, string $screen, string $context = 'advanced', string $priority = 'default', ?array $callback_args = null): void
    {
        $GLOBALS['wp_test_meta_boxes'] ??= [];
        $GLOBALS['wp_test_meta_boxes'][] = compact('id', 'title', 'callback', 'screen', 'context', 'priority', 'callback_args');
    }
}

if (! function_exists('wp_nonce_field')) {
    function wp_nonce_field(string $action = '_wpnonce', string $name = '_wpnonce', bool $referer = true, bool $echo = true): string
    {
        $nonce = 'test_nonce';
        if ($echo) {
            echo '<input type="hidden" name="'.$name.'" value="'.$nonce.'" />';
        }

        return $nonce;
    }
}

if (! function_exists('wp_verify_nonce')) {
    function wp_verify_nonce(string $nonce, string $action = '_wpnonce'): bool
    {
        return $GLOBALS['wp_test_verify_nonce'] ?? true;
    }
}

if (! function_exists('current_user_can')) {
    function current_user_can(string $capability, ...$args): bool
    {
        return $GLOBALS['wp_test_current_user_can'] ?? true;
    }
}

if (! function_exists('add_menu_page')) {
    function add_menu_page(string $page_title, string $menu_title, string $capability, string $menu_slug, ?callable $callback = null, string $icon = '', ?int $position = null): string
    {
        $GLOBALS['wp_test_menu_pages'] ??= [];
        $GLOBALS['wp_test_menu_pages'][] = compact('page_title', 'menu_title', 'capability', 'menu_slug', 'icon', 'position');

        return $menu_slug;
    }
}

if (! function_exists('add_submenu_page')) {
    function add_submenu_page(string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, ?callable $callback = null): string
    {
        $GLOBALS['wp_test_submenu_pages'] ??= [];
        $GLOBALS['wp_test_submenu_pages'][] = compact('parent_slug', 'page_title', 'menu_title', 'capability', 'menu_slug');

        return $menu_slug;
    }
}

if (! function_exists('register_setting')) {
    function register_setting(string $option_group, string $option_name, array $args = []): void
    {
        $GLOBALS['wp_test_registered_settings'] ??= [];
        $GLOBALS['wp_test_registered_settings'][] = compact('option_group', 'option_name', 'args');
    }
}

if (! function_exists('settings_fields')) {
    function settings_fields(string $option_group): void
    {
        echo '<input type="hidden" name="option_page" value="'.$option_group.'" />';
    }
}

if (! function_exists('submit_button')) {
    function submit_button(?string $text = null, string $type = 'primary', string $name = 'submit', bool $wrap = true, array $other_attributes = []): void
    {
        echo '<button type="'.$type.'" name="'.$name.'">'.($text ?? 'Save Changes').'</button>';
    }
}

require_once __DIR__.'/../vendor/autoload.php';
