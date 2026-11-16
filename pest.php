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

function createWPField(array $config = [], string $storage_type = 'options'): WP_Field
{
    return new WP_Field(createField($config), $storage_type);
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

if (! function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default')
    {
        return esc_attr(__($text, $domain));
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
    function get_term_meta($term_id, $key, $single = false)
    {
        global $wp_test_meta_storage;
        if (! isset($wp_test_meta_storage['term'][$term_id][$key])) {
            return $single ? '' : [];
        }

        return $single ? $wp_test_meta_storage['term'][$term_id][$key] : [$wp_test_meta_storage['term'][$term_id][$key]];
    }
}

if (! function_exists('get_user_meta')) {
    function get_user_meta($user_id, $key, $single = false)
    {
        global $wp_test_meta_storage;
        if (! isset($wp_test_meta_storage['user'][$user_id][$key])) {
            return $single ? '' : [];
        }

        return $single ? $wp_test_meta_storage['user'][$user_id][$key] : [$wp_test_meta_storage['user'][$user_id][$key]];
    }
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
        global $wp_test_meta_storage;
        $wp_test_meta_storage['term'][$term_id][$meta_key] = $meta_value;

        return true;
    }
}

if (! function_exists('delete_term_meta')) {
    function delete_term_meta($term_id, $meta_key)
    {
        global $wp_test_meta_storage;
        unset($wp_test_meta_storage['term'][$term_id][$meta_key]);

        return true;
    }
}

if (! function_exists('update_user_meta')) {
    function update_user_meta($user_id, $meta_key, $meta_value)
    {
        global $wp_test_meta_storage;
        $wp_test_meta_storage['user'][$user_id][$meta_key] = $meta_value;

        return true;
    }
}

if (! function_exists('delete_user_meta')) {
    function delete_user_meta($user_id, $meta_key)
    {
        global $wp_test_meta_storage;
        unset($wp_test_meta_storage['user'][$user_id][$meta_key]);

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

if (! function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
    {
        $GLOBALS['wp_test_actions'] ??= [];
        $GLOBALS['wp_test_actions'][] = ['hook' => $hook, 'callback' => $callback];
    }
}

if (! function_exists('add_meta_box')) {
    function add_meta_box($id, $title, $callback, $screen, $context = 'advanced', $priority = 'default', $callback_args = null)
    {
        $GLOBALS['wp_test_meta_boxes'] ??= [];
        $GLOBALS['wp_test_meta_boxes'][] = compact('id', 'title', 'callback', 'screen', 'context', 'priority', 'callback_args');
    }
}

if (! function_exists('wp_nonce_field')) {
    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true)
    {
        $nonce = 'test_nonce';
        if ($echo) {
            echo '<input type="hidden" name="'.$name.'" value="'.$nonce.'" />';
        }

        return $nonce;
    }
}

if (! function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1)
    {
        return $GLOBALS['wp_test_verify_nonce'] ?? true;
    }
}

if (! function_exists('current_user_can')) {
    function current_user_can($capability, ...$args)
    {
        $can = $GLOBALS['wp_test_current_user_can'] ?? true;
        if ($capability === 'edit_post' && isset($args[0]) && $args[0] === 999999) {
            return false;
        }

        return $can;
    }
}

if (! function_exists('add_menu_page')) {
    function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon = '', $position = null)
    {
        $GLOBALS['wp_test_menu_pages'] ??= [];
        $GLOBALS['wp_test_menu_pages'][] = compact('page_title', 'menu_title', 'capability', 'menu_slug', 'icon', 'position');

        return $menu_slug;
    }
}

if (! function_exists('add_submenu_page')) {
    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '')
    {
        $GLOBALS['wp_test_submenu_pages'] ??= [];
        $GLOBALS['wp_test_submenu_pages'][] = compact('parent_slug', 'page_title', 'menu_title', 'capability', 'menu_slug');

        return $menu_slug;
    }
}

if (! function_exists('register_setting')) {
    function register_setting($option_group, $option_name, $args = [])
    {
        $GLOBALS['wp_test_registered_settings'] ??= [];
        $GLOBALS['wp_test_registered_settings'][] = compact('option_group', 'option_name', 'args');
    }
}

if (! function_exists('settings_fields')) {
    function settings_fields($option_group)
    {
        echo '<input type="hidden" name="option_page" value="'.$option_group.'" />';
    }
}

if (! function_exists('submit_button')) {
    function submit_button($text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = [])
    {
        echo '<button type="'.$type.'" name="'.$name.'">'.($text ?? 'Save Changes').'</button>';
    }
}

if (! class_exists('WP_Term')) {
    class WP_Term
    {
        public $term_id;

        public $name;

        public $slug;

        public $term_group;

        public $term_taxonomy_id;

        public $taxonomy;

        public $description;

        public $parent;

        public $count;

        public function __construct($term_id)
        {
            $this->term_id = $term_id;
            $this->name = 'Test Term';
            $this->slug = 'test-term';
            $this->term_group = 0;
            $this->term_taxonomy_id = $term_id;
            $this->taxonomy = 'category';
            $this->description = '';
            $this->parent = 0;
            $this->count = 0;
        }
    }
}
