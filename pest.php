<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit\Framework\TestCase class.
| By default, that will be PestPHP\Pest\TestCase. Of course, you may need to bind a different class for certain
| file or directories. You can do so here.
|
*/

use PHPUnit\Framework\TestCase;

uses(TestCase::class)->in('tests');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of expectations that you can apply to your values.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Feel free to use this space to define helper functions that you find yourself
| regularly needing within your tests. They're shared across every test file in your
| application, giving you a convenient place to reference them.
|
*/

function createField(array $config = []): array
{
    return array_merge([
        'id' => 'test_field',
        'type' => 'text',
        'label' => 'Test Field',
    ], $config);
}

function createWPField(array $config = [], string $storage_type = 'options'): \WP_Field
{
    return new \WP_Field(createField($config), $storage_type);
}

/*
|--------------------------------------------------------------------------
| WordPress Mock Functions for Testing
|--------------------------------------------------------------------------
*/

// Global metadata storage for all meta functions
global $wp_test_meta_storage;
$wp_test_meta_storage = [
    'post' => [],
    'term' => [],
    'user' => [],
    'comment' => [],
    'options' => [],
];

if (! function_exists('plugin_dir_url')) {
    function plugin_dir_url($file)
    {
        return 'http://example.com/wp-content/plugins/woo2iiko/lib/wp-field/';
    }
}

if (! function_exists('wp_kses_post')) {
    function wp_kses_post($data)
    {
        return $data;
    }
}

if (! function_exists('esc_attr')) {
    function esc_attr($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_html')) {
    function esc_html($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('__')) {
    function __($text, $domain = 'default')
    {
        return $text;
    }
}

if (! function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default')
    {
        return esc_html(__($text, $domain));
    }
}

if (! function_exists('esc_textarea')) {
    function esc_textarea($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_url')) {
    function esc_url($url)
    {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('tag_escape')) {
    function tag_escape($tag)
    {
        return htmlspecialchars($tag, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('absint')) {
    function absint($maybeint)
    {
        return abs((int) $maybeint);
    }
}

if (! function_exists('get_option')) {
    function get_option($option, $default = false)
    {
        global $wp_test_meta_storage;

        return $wp_test_meta_storage['options'][$option] ?? $default;
    }
}

if (! function_exists('get_post_meta')) {
    function get_post_meta($post_id, $key, $single = false)
    {
        global $wp_test_meta_storage;
        if (! isset($wp_test_meta_storage['post'][$post_id][$key])) {
            return $single ? '' : [];
        }

        return $single ? $wp_test_meta_storage['post'][$post_id][$key] : [$wp_test_meta_storage['post'][$post_id][$key]];
    }
}

if (! function_exists('get_term_meta')) {
    function get_term_meta($term_id, $key, $single = false): void {}
}

if (! function_exists('get_user_meta')) {
    function get_user_meta($user_id, $key, $single = false): void {}
}

if (! function_exists('get_comment_meta')) {
    function get_comment_meta($comment_id, $key, $single = false): void {}
}

if (! function_exists('is_admin')) {
    function is_admin()
    {
        return false;
    }
}

if (! function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false)
    {
        return true;
    }
}

if (! function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all')
    {
        return true;
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters($tag, $value, ...$args)
    {
        return $value;
    }
}

if (! function_exists('get_the_ID')) {
    function get_the_ID()
    {
        return 1;
    }
}

if (! function_exists('sanitize_text_field')) {
    function sanitize_text_field($str)
    {
        return strip_tags($str);
    }
}

if (! function_exists('is_email')) {
    function is_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (! function_exists('update_post_meta')) {
    function update_post_meta($post_id, $meta_key, $meta_value)
    {
        global $wp_test_meta_storage;
        $wp_test_meta_storage['post'][$post_id][$meta_key] = $meta_value;

        return true;
    }
}

if (! function_exists('delete_post_meta')) {
    function delete_post_meta($post_id, $meta_key)
    {
        global $wp_test_meta_storage;
        unset($wp_test_meta_storage['post'][$post_id][$meta_key]);

        return true;
    }
}

if (! function_exists('metadata_exists')) {
    function metadata_exists($meta_type, $object_id, $meta_key)
    {
        global $wp_test_meta_storage;

        return isset($wp_test_meta_storage[$meta_type][$object_id][$meta_key]);
    }
}

if (! function_exists('update_term_meta')) {
    function update_term_meta($term_id, $meta_key, $meta_value)
    {
        return true;
    }
}

if (! function_exists('delete_term_meta')) {
    function delete_term_meta($term_id, $meta_key)
    {
        return true;
    }
}

if (! function_exists('update_user_meta')) {
    function update_user_meta($user_id, $meta_key, $meta_value)
    {
        return true;
    }
}

if (! function_exists('delete_user_meta')) {
    function delete_user_meta($user_id, $meta_key)
    {
        return true;
    }
}

if (! function_exists('update_option')) {
    function update_option($option, $value)
    {
        global $wp_test_meta_storage;
        $wp_test_meta_storage['options'][$option] = $value;

        return true;
    }
}

if (! function_exists('delete_option')) {
    function delete_option($option)
    {
        return true;
    }
}
