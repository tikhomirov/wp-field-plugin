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

if (! function_exists('get_post_meta')) {
    function get_post_meta(int $post_id, string $key = '', bool $single = false): mixed
    {
        static $meta = [];
        if (! isset($meta[$post_id][$key])) {
            return $single ? '' : [];
        }

        return $single ? $meta[$post_id][$key] : [$meta[$post_id][$key]];
    }
}

if (! function_exists('update_post_meta')) {
    function update_post_meta(int $post_id, string $meta_key, mixed $meta_value): bool
    {
        static $meta = [];
        $meta[$post_id][$meta_key] = $meta_value;

        return true;
    }
}

if (! function_exists('delete_post_meta')) {
    function delete_post_meta(int $post_id, string $meta_key): bool
    {
        static $meta = [];
        unset($meta[$post_id][$meta_key]);

        return true;
    }
}

if (! function_exists('metadata_exists')) {
    function metadata_exists(string $meta_type, int $object_id, string $meta_key): bool
    {
        return false;
    }
}

if (! function_exists('get_term_meta')) {
    function get_term_meta(int $term_id, string $key = '', bool $single = false): mixed
    {
        static $meta = [];
        if (! isset($meta[$term_id][$key])) {
            return $single ? '' : [];
        }

        return $single ? $meta[$term_id][$key] : [$meta[$term_id][$key]];
    }
}

if (! function_exists('update_term_meta')) {
    function update_term_meta(int $term_id, string $meta_key, mixed $meta_value): bool
    {
        static $meta = [];
        $meta[$term_id][$meta_key] = $meta_value;

        return true;
    }
}

if (! function_exists('delete_term_meta')) {
    function delete_term_meta(int $term_id, string $meta_key): bool
    {
        static $meta = [];
        unset($meta[$term_id][$meta_key]);

        return true;
    }
}

if (! function_exists('get_user_meta')) {
    function get_user_meta(int $user_id, string $key = '', bool $single = false): mixed
    {
        static $meta = [];
        if (! isset($meta[$user_id][$key])) {
            return $single ? '' : [];
        }

        return $single ? $meta[$user_id][$key] : [$meta[$user_id][$key]];
    }
}

if (! function_exists('update_user_meta')) {
    function update_user_meta(int $user_id, string $meta_key, mixed $meta_value): bool
    {
        static $meta = [];
        $meta[$user_id][$meta_key] = $meta_value;

        return true;
    }
}

if (! function_exists('delete_user_meta')) {
    function delete_user_meta(int $user_id, string $meta_key): bool
    {
        static $meta = [];
        unset($meta[$user_id][$meta_key]);

        return true;
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

require_once __DIR__.'/../vendor/autoload.php';
