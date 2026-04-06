<?php

/**
 * WP_Field v3.0 Demo Page
 *
 * Demonstrates modern Fluent API, Repeater, Flexible Content, Conditional Logic
 * with React/Vanilla UI mode switching
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

use WpField\Field\Field;

class WP_Field_V3_Demo
{
    private const UI_MODE_OPTION = 'wp_field_ui_mode';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'handle_ui_mode_switch']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_menu_page(): void
    {
        add_submenu_page(
            'tools.php',
            'WP_Field v3.0 Demo',
            'WP_Field v3.0 Demo',
            'manage_options',
            'wp-field-v3-demo',
            [$this, 'render_page']
        );
    }

    public function handle_ui_mode_switch(): void
    {
        if (! isset($_POST['wp_field_ui_mode_nonce'])) {
            return;
        }

        if (! wp_verify_nonce($_POST['wp_field_ui_mode_nonce'], 'wp_field_ui_mode')) {
            return;
        }

        if (! current_user_can('manage_options')) {
            return;
        }

        $mode = sanitize_text_field($_POST['ui_mode'] ?? 'vanilla');
        if (! in_array($mode, ['react', 'vanilla'], true)) {
            return;
        }

        if (! $this->isLegacyEnabled() && $mode === 'vanilla') {
            $mode = 'react';
            add_settings_error('wp_field_v3_demo', 'ui_mode_forced',
                'Vanilla mode requires legacy runtime. Mode forced to React.', 'warning');
        }

        update_option(self::UI_MODE_OPTION, $mode);
        add_settings_error('wp_field_v3_demo', 'ui_mode_updated',
            sprintf('UI Mode switched to: %s', ucfirst($mode)), 'updated');
    }

    public function enqueue_assets(string $hook): void
    {
        if ($hook !== 'tools_page_wp-field-v3-demo') {
            return;
        }

        $plugin_url = plugin_dir_url(dirname(__FILE__));
        $version = defined('WP_DEBUG') && WP_DEBUG ? time() : '3.0.0';
        $legacy_enabled = $this->isLegacyEnabled();

        if ($legacy_enabled) {
            wp_enqueue_script('jquery');
            wp_enqueue_media();
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');

            // Legacy JS/CSS for vanilla mode and legacy fallbacks.
            wp_enqueue_script(
                'wp-field-main',
                $plugin_url.'legacy/assets/js/wp-field.js',
                ['jquery', 'wp-color-picker'],
                $version,
                true
            );

            wp_enqueue_style(
                'wp-field-main',
                $plugin_url.'legacy/assets/css/wp-field.css',
                ['wp-color-picker'],
                $version
            );
        }

        $current_mode = get_option(self::UI_MODE_OPTION, 'vanilla');
        if (! $legacy_enabled && $current_mode === 'vanilla') {
            $current_mode = 'react';
        }

        if ($current_mode === 'react' && $this->hasReactBuild()) {
            wp_enqueue_script(
                'wp-field-react-repeater',
                $plugin_url.'assets/dist/repeater.js',
                [],
                $version,
                true
            );
            wp_script_add_data('wp-field-react-repeater', 'type', 'module');

            wp_enqueue_script(
                'wp-field-react-flexible',
                $plugin_url.'assets/dist/flexible-content.js',
                [],
                $version,
                true
            );
            wp_script_add_data('wp-field-react-flexible', 'type', 'module');
        }
    }

    public function render_page(): void
    {
        $current_mode = get_option(self::UI_MODE_OPTION, 'vanilla');
        $react_available = $this->hasReactBuild();
        $legacy_enabled = $this->isLegacyEnabled();

        if (! $legacy_enabled && $current_mode === 'vanilla') {
            $current_mode = 'react';
            update_option(self::UI_MODE_OPTION, 'react');
        }

        echo '<div class="wrap">';
        echo '<h1>WP_Field v3.0 — Modern API Demo</h1>';

        echo '<div class="notice notice-info">';
        echo '<p><strong>v3.0 Features:</strong> Fluent API, Repeater, Flexible Content, Conditional Logic, React UI</p>';
        echo '<p><strong>Current UI Mode:</strong> '.esc_html(ucfirst($current_mode));
        if (! $react_available) {
            echo ' <span style="color: #d63638;">(React build not found - using Vanilla fallback)</span>';
        }
        echo '</p>';
        echo '<p><strong>Legacy Runtime:</strong> '.($legacy_enabled ? 'enabled' : 'disabled').'</p></div>';

        settings_errors('wp_field_v3_demo');

        // UI Mode Switcher
        echo '<div class="card" style="max-width: 600px; margin: 20px 0;">';
        echo '<h2>UI Mode Switcher</h2>';
        echo '<form method="post" action="">';
        wp_nonce_field('wp_field_ui_mode', 'wp_field_ui_mode_nonce');
        echo '<p>';
        echo '<label><input type="radio" name="ui_mode" value="react" '.checked($current_mode, 'react', false).'> ';
        echo 'React UI '.($react_available ? '✓' : '(build required)').'</label><br>';
        echo '<label><input type="radio" name="ui_mode" value="vanilla" '.checked($current_mode, 'vanilla', false).' '.disabled(! $legacy_enabled, true, false).'> ';
        echo 'Vanilla JS UI '.($legacy_enabled ? '✓' : '(legacy disabled)').'</label>';
        echo '</p><p>';
        echo '<button type="submit" class="button button-primary">Switch Mode</button>';
        if (! $react_available) {
            echo ' <span class="description">Run <code>npm run build</code> to enable React UI</span>';
        }
        echo '</p></form></div>';

        // Demo Sections
        echo '<div class="wp-field-v3-demos">';

        // 1. Fluent API
        echo '<div class="card">';
        echo '<h2>1. Fluent API — Basic Example</h2>';
        echo '<p class="description">Modern Laravel-style chaining methods</p>';
        echo '<div class="demo-code"><pre><code class="language-php">use WpField\Field\Field;

$field = Field::text(\'email\')
    ->label(\'Email Address\')
    ->placeholder(\'user@example.com\')
    ->required()
    ->email()
    ->class(\'regular-text\');

echo $field->render();</code></pre></div>';

        echo '<div class="demo-preview"><h3>Preview:</h3>';
        echo Field::text('demo_fluent_email')
            ->label('Email Address')
            ->placeholder('user@example.com')
            ->required()
            ->email()
            ->class('regular-text')
            ->render();
        echo '</div></div>';

        // 2. Repeater
        echo '<div class="card">';
        echo '<h2>2. Repeater Field</h2>';
        echo '<p class="description">Infinite nesting with min/max constraints</p>';
        echo '<div class="demo-code"><pre><code class="language-php">$repeater = Field::repeater(\'team_members\')
    ->label(\'Team Members\')
    ->fields([
        Field::text(\'name\')->label(\'Name\')->required(),
        Field::text(\'position\')->label(\'Position\'),
        Field::make(\'email\', \'email\')->label(\'Email\'),
    ])
    ->min(1)
    ->max(10)
    ->buttonLabel(\'Add Member\')
    ->layout(\'table\');

echo $repeater->render();</code></pre></div>';

        echo '<div class="demo-preview"><h3>Preview:</h3>';
        if ($current_mode === 'react' && $react_available) {
            $repeater_config = [
                'name' => 'demo_repeater',
                'layout' => 'table',
                'buttonLabel' => 'Add Member',
                'min' => 1,
                'max' => 5,
                'value' => [
                    [
                        'name' => 'Alex',
                        'position' => 'Developer',
                        'email' => 'alex@example.com',
                    ],
                ],
                'fields' => [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => true],
                    ['name' => 'position', 'type' => 'text', 'label' => 'Position'],
                    ['name' => 'email', 'type' => 'email', 'label' => 'Email'],
                ],
            ];

            printf(
                '<div data-wp-field-repeater="%s"></div>',
                esc_attr((string) wp_json_encode($repeater_config))
            );
        } else {
            echo Field::repeater('demo_team')
                ->label('Team Members')
                ->fields([
                    Field::text('name')->label('Name')->required(),
                    Field::text('position')->label('Position'),
                    Field::make('email', 'email')->label('Email'),
                ])
                ->min(1)
                ->max(5)
                ->buttonLabel('Add Member')
                ->layout('table')
                ->render();
        }
        echo '</div></div>';

        // 3. Flexible Content
        echo '<div class="card">';
        echo '<h2>3. Flexible Content Field</h2>';
        echo '<p class="description">ACF-style layout builder with multiple block types</p>';
        echo '<div class="demo-code"><pre><code class="language-php">$flexible = Field::flexibleContent(\'page_sections\')
    ->label(\'Page Sections\')
    ->addLayout(\'text_block\', \'Text Block\', [
        Field::text(\'heading\')->label(\'Heading\'),
        Field::make(\'textarea\', \'content\')->label(\'Content\'),
    ])
    ->addLayout(\'image\', \'Image\', [
        Field::make(\'image\', \'image_url\')->label(\'Image\'),
        Field::text(\'caption\')->label(\'Caption\'),
    ])
    ->min(1)
    ->buttonLabel(\'Add Section\');

echo $flexible->render();</code></pre></div>';

        echo '<div class="demo-preview"><h3>Preview:</h3>';
        if ($current_mode === 'react' && $react_available) {
            $flexible_config = [
                'name' => 'demo_flexible',
                'buttonLabel' => 'Add Section',
                'min' => 1,
                'max' => 5,
                'value' => [
                    [
                        'acf_fc_layout' => 'text_block',
                        'heading' => 'Welcome',
                        'content' => 'Demo content block',
                    ],
                ],
                'layouts' => [
                    'text_block' => [
                        'label' => 'Text Block',
                        'fields' => [
                            ['name' => 'heading', 'type' => 'text', 'label' => 'Heading'],
                            ['name' => 'content', 'type' => 'text', 'label' => 'Content'],
                        ],
                    ],
                    'image' => [
                        'label' => 'Image',
                        'fields' => [
                            ['name' => 'image_url', 'type' => 'url', 'label' => 'Image URL'],
                            ['name' => 'caption', 'type' => 'text', 'label' => 'Caption'],
                        ],
                    ],
                ],
            ];

            printf(
                '<div data-wp-field-flexible="%s"></div>',
                esc_attr((string) wp_json_encode($flexible_config))
            );
        } else {
            echo Field::flexibleContent('demo_flexible_fallback')
                ->label('Page Sections')
                ->addLayout('text_block', 'Text Block', [
                    Field::text('heading')->label('Heading'),
                    Field::make('textarea', 'content')->label('Content'),
                ])
                ->addLayout('image', 'Image', [
                    Field::make('url', 'image_url')->label('Image URL'),
                    Field::text('caption')->label('Caption'),
                ])
                ->min(1)
                ->buttonLabel('Add Section')
                ->render();
        }
        echo '</div></div>';

        // 4. Conditional Logic
        echo '<div class="card">';
        echo '<h2>4. Conditional Logic</h2>';
        echo '<p class="description">14 operators with AND/OR relations</p>';
        echo '<div class="demo-code"><pre><code class="language-php">$field = Field::text(\'courier_address\')
    ->label(\'Delivery Address\')
    ->when(\'delivery_type\', \'==\', \'courier\');

$field = Field::text(\'special_field\')
    ->when(\'field1\', \'==\', \'value1\')
    ->when(\'field2\', \'!=\', \'value2\');</code></pre></div>';

        echo '<div class="demo-preview"><h3>Preview:</h3>';
        echo Field::make('select', 'demo_delivery_type')
            ->label('Delivery Type')
            ->attribute('options', [
                'pickup' => 'Pickup',
                'courier' => 'Courier',
                'mail' => 'Mail',
            ])
            ->render();

        echo Field::text('demo_courier_address')
            ->label('Courier Address')
            ->when('demo_delivery_type', '==', 'courier')
            ->render();

        echo '<p style="color:#666; margin-top:8px;">Conditional rules are attached via fluent API (`when` / `orWhen`).</p>';
        echo '</div></div>';

        // 5. Legacy API
        echo '<div class="card">';
        echo '<h2>5. Legacy API (v2.x) — 100% Compatible</h2>';
        echo '<p class="description">Old array-based API still works</p>';
        echo '<div class="demo-code"><pre><code class="language-php">$field = WP_Field::make([
    \'id\' => \'shop_name\',
    \'type\' => \'text\',
    \'label\' => \'Shop Name\',
    \'required\' => true,
]);

echo $field->render();</code></pre></div>';

        echo '<div class="demo-preview"><h3>Preview:</h3>';
        if (class_exists('WP_Field')) {
            $shop_html = WP_Field::make([
                'id' => 'demo_shop_name',
                'type' => 'text',
                'label' => 'Shop Name (Legacy API)',
                'storage_type' => 'options',
                'placeholder' => 'Enter shop name',
                'required' => true,
            ], false);

            if (is_string($shop_html)) {
                echo $shop_html;
            }
        } else {
            echo '<p style="color: #666;">Legacy runtime is disabled by <code>wp_field_enable_legacy</code>.</p>';
        }
        echo '</div></div>';

        echo '</div>'; // wp-field-v3-demos

        // Styles
        echo '<style>
            .wp-field-v3-demos .card { margin: 20px 0; padding: 20px; }
            .wp-field-v3-demos h2 { margin-top: 0; color: #0073aa; }
            .demo-code { background: #f5f5f5; padding: 15px; border-radius: 4px; margin: 15px 0; overflow-x: auto; }
            .demo-code pre { margin: 0; }
            .demo-code code { font-family: "Courier New", monospace; font-size: 13px; line-height: 1.6; }
            .demo-preview { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin: 15px 0; }
            .demo-preview h3 { margin-top: 0; font-size: 14px; color: #666; text-transform: uppercase; }
        </style>';

        echo '</div>'; // wrap
    }

    private function hasReactBuild(): bool
    {
        $plugin_path = dirname(__FILE__, 2);

        return file_exists($plugin_path.'/assets/dist/client.js')
            && file_exists($plugin_path.'/assets/dist/repeater.js')
            && file_exists($plugin_path.'/assets/dist/flexible-content.js');
    }

    private function isLegacyEnabled(): bool
    {
        return (bool) apply_filters('wp_field_enable_legacy', true);
    }
}

// Initialize only in admin
if (is_admin()) {
    new WP_Field_V3_Demo;
}
