<?php

/**
 * WP_Field Components — Modern API Documentation
 *
 * React-powered documentation page for Field::make() fluent API.
 * No jQuery, no built-in WP scripts required for the page itself.
 * PHP server-renders field HTML; JS bundle provides sidebar navigation,
 * search, and scroll tracking.
 *
 * Slug: wp-field-components
 * Assets: examples/components/assets/
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

require_once dirname(__DIR__) . '/shared-catalog.php';

class WP_Field_Components
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_menu_page(): void
    {
        add_submenu_page(
            'tools.php',
            'WP_Field Components',
            'WP_Field Components',
            'manage_options',
            'wp-field-components',
            [$this, 'render_page']
        );
    }

    public function enqueue_assets(string $hook): void
    {
        if ($hook !== 'tools_page_wp-field-components') {
            return;
        }

        $cssPath = __DIR__ . '/assets/wp-field-components.css';
        if (file_exists($cssPath)) {
            wp_enqueue_style(
                'wp-field-components',
                plugin_dir_url(__FILE__) . 'assets/wp-field-components.css',
                [],
                (string) filemtime($cssPath),
            );
        }

        $jsPath = __DIR__ . '/assets/wp-field-components.js';
        if (file_exists($jsPath)) {
            wp_enqueue_script(
                'wp-field-components',
                plugin_dir_url(__FILE__) . 'assets/wp-field-components.js',
                [],
                (string) filemtime($jsPath),
                true,
            );
        }
    }

    public function render_page(): void
    {
        $catalog = wp_field_get_demo_catalog();
        $fieldCount = 0;
        foreach ($catalog as $section) {
            $fieldCount += count($section['fields']);
        }

        $navJson = wp_json_encode(array_map(static function (array $section): array {
            return [
                'id'    => $section['id'],
                'title' => $section['title'],
                'count' => count($section['fields']),
            ];
        }, $catalog));
        ?>
        <div class="wrap wfc-page">
            <header class="wfc-header">
                <h1>WP_Field Components</h1>
                <p class="wfc-header__lead">
                    Modern <code>Field::make()</code> API — no jQuery.
                    <strong><?php echo esc_html((string) $fieldCount); ?></strong> field examples.
                </p>
            </header>

            <div class="wfc-layout" id="wfc-root" data-nav="<?php echo esc_attr($navJson); ?>">
                <aside class="wfc-sidebar" id="wfc-sidebar">
                    <input type="search" class="wfc-sidebar__search" placeholder="Search fields..." id="wfc-search" />
                    <nav class="wfc-sidebar__nav" id="wfc-nav">
                        <?php foreach ($catalog as $section): ?>
                            <a href="#<?php echo esc_attr($section['id']); ?>" class="wfc-sidebar__link" data-section="<?php echo esc_attr($section['id']); ?>">
                                <?php echo esc_html($section['title']); ?>
                                <span class="wfc-sidebar__count"><?php echo count($section['fields']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </aside>

                <main class="wfc-content">
                    <?php foreach ($catalog as $section): ?>
                        <section class="wfc-section" id="<?php echo esc_attr($section['id']); ?>">
                            <div class="wfc-section__header">
                                <h2><?php echo esc_html($section['title']); ?></h2>
                                <p><?php echo esc_html($section['description']); ?></p>
                            </div>
                            <div class="wfc-grid">
                                <?php foreach ($section['fields'] as $fieldDef): ?>
                                    <?php echo $this->render_card($fieldDef); ?>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </main>
            </div>
        </div>
        <?php
    }

    private function render_card(array $fieldDef): string
    {
        $type  = esc_html($fieldDef['type']);
        $title = esc_html($fieldDef['title']);
        $desc  = esc_html($fieldDef['description']);
        $code  = esc_html($fieldDef['code'] ?? '');
        $props = $fieldDef['props'] ?? [];

        $renderedField = $fieldDef['field']->render();

        $propsHtml = '';
        if ($props) {
            $propsHtml = '<div class="wfc-card__props">';
            foreach ($props as $prop) {
                $propsHtml .= '<span class="wfc-card__prop">' . esc_html($prop) . '</span>';
            }
            $propsHtml .= '</div>';
        }

        return <<<HTML
        <article class="wfc-card" data-type="{$type}">
            <header class="wfc-card__header">
                <div>
                    <h3>{$title}</h3>
                    <p>{$desc}</p>
                </div>
                <code class="wfc-card__type">{$type}</code>
            </header>
            <div class="wfc-card__preview">{$renderedField}</div>
            {$propsHtml}
            <details class="wfc-card__code">
                <summary>Code example</summary>
                <pre><code>{$code}</code></pre>
            </details>
        </article>
        HTML;
    }
}

if (is_admin()) {
    new WP_Field_Components;
}
