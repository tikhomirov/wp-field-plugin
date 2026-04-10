<?php

/**
 * WP_Field Components — Modern API Documentation
 *
 * Documentation page for the fluent Field::make() API. The page itself
 * uses a small vanilla JS bundle for navigation and search, while the
 * field demos still rely on selected WordPress UI assets (media modal,
 * color picker, editor, sortable, code editor) where a field needs them.
 *
 * Slug: wp-field-components
 * Assets: examples/components/assets/
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

require_once dirname(__DIR__).'/shared-catalog.php';

class WP_Field_Components
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    private function is_russian_locale(): bool
    {
        $locale = function_exists('determine_locale') ? determine_locale() : get_locale();

        return strpos((string) $locale, 'ru_') === 0;
    }

    private function t(string $en, ?string $ru = null): string
    {
        if ($this->is_russian_locale() && $ru !== null) {
            return $ru;
        }

        return $en;
    }

    public function add_menu_page(): void
    {
        $pageTitle = $this->t('Field Components', 'Компоненты Field');

        add_submenu_page(
            'tools.php',
            $pageTitle,
            $pageTitle,
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

        wp_dequeue_script('wp-field-main');
        wp_dequeue_style('wp-field-main');

        $legacyCssPath = dirname(__DIR__, 2).'/vanilla/assets/css/wp-field.css';
        if (file_exists($legacyCssPath)) {
            wp_enqueue_style(
                'wp-field-legacy',
                WP_FIELD_PLUGIN_URL.'vanilla/assets/css/wp-field.css',
                [],
                (string) filemtime($legacyCssPath),
            );
        }

        $cssPath = __DIR__.'/assets/wp-field-components.css';
        if (file_exists($cssPath)) {
            $componentDeps = file_exists($legacyCssPath) ? ['wp-field-legacy'] : [];
            wp_enqueue_style(
                'wp-field-components',
                plugin_dir_url(__FILE__).'assets/wp-field-components.css',
                $componentDeps,
                (string) filemtime($cssPath),
            );
        }

        $jsPath = __DIR__.'/assets/wp-field-components.js';
        if (file_exists($jsPath)) {
            wp_enqueue_script(
                'wp-field-components',
                plugin_dir_url(__FILE__).'assets/wp-field-components.js',
                [],
                (string) filemtime($jsPath),
                true,
            );
        }

        $jsPath = dirname(__DIR__, 2).'/vanilla/assets/js/wp-field.js';
        if (file_exists($jsPath)) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_media();

            wp_enqueue_script(
                'wp-field-main',
                WP_FIELD_PLUGIN_URL.'vanilla/assets/js/wp-field.js',
                ['jquery', 'wp-color-picker', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'media-editor'],
                (string) filemtime($jsPath),
                true,
            );
        }

        wp_enqueue_style(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            [],
            '1.9.4',
        );
        wp_enqueue_script(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            [],
            '1.9.4',
            true,
        );

        if (function_exists('wp_enqueue_code_editor')) {
            wp_enqueue_code_editor(['type' => 'text/html']);
        }

        wp_enqueue_editor();
        wp_enqueue_media();
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
                'id' => $section['id'],
                'title' => $section['title'],
                'count' => count($section['fields']),
            ];
        }, $catalog));

        $pageTitle = $this->t('WP_Field Components', 'Компоненты WP_Field');
        $headerLead = $this->t(
            'Modern <code>Field::make()</code> API with WordPress-backed UI where it matters. <strong>%s</strong> field examples.',
            'Современный API <code>Field::make()</code> с WordPress UI там, где он действительно нужен. <strong>%s</strong> примеров полей.',
        );
        $tabExamples = $this->t('Examples', 'Примеры');
        $tabDocs = $this->t('Documentation', 'Документация');
        $docsTabAria = $this->t('WP_Field documentation tabs', 'Вкладки документации WP_Field');
        $searchLabel = $this->t('Search fields', 'Поиск полей');
        $searchPlaceholder = $this->t('Search fields...', 'Поиск полей...');
        ?>
        <div class="wrap wfc-page">
            <header class="wfc-header">
                <h1><?php echo esc_html($pageTitle); ?></h1>
                <p class="wfc-header__lead">
                    <?php printf(wp_kses_post($headerLead), esc_html((string) $fieldCount)); ?>
                </p>
            </header>

            <div class="wp-field-tabbed wfc-page-tabs" data-field-id="wfc-page-tabs" data-default-tab="0">
                <div class="wp-field-tabbed-nav" role="tablist" aria-label="<?php echo esc_attr($docsTabAria); ?>">
                    <button type="button" class="wp-field-tabbed-nav-item active" role="tab" aria-selected="true" aria-controls="tab-pane-wfc-page-tabs-0" data-tab="wfc-page-tabs-0"><?php echo esc_html($tabExamples); ?></button>
                    <button type="button" class="wp-field-tabbed-nav-item" role="tab" aria-selected="false" aria-controls="tab-pane-wfc-page-tabs-1" data-tab="wfc-page-tabs-1"><?php echo esc_html($tabDocs); ?></button>
                </div>

                <div class="wp-field-tabbed-content">
                    <div id="tab-pane-wfc-page-tabs-0" class="wp-field-tabbed-pane active" role="tabpanel" data-tab="wfc-page-tabs-0">
                        <div class="wfc-layout" id="wfc-root" data-nav="<?php echo esc_attr($navJson); ?>">
                            <aside class="wfc-sidebar" id="wfc-sidebar">
                                <label class="screen-reader-text" for="wfc-search"><?php echo esc_html($searchLabel); ?></label>
                                <input type="search" class="wfc-sidebar__search" placeholder="<?php echo esc_attr($searchPlaceholder); ?>" id="wfc-search" />
                                <nav class="wfc-sidebar__nav" id="wfc-nav">
                                    <?php foreach ($catalog as $section) { ?>
                                        <a href="#<?php echo esc_attr($section['id']); ?>" class="wfc-sidebar__link" data-section="<?php echo esc_attr($section['id']); ?>">
                                            <?php echo esc_html($section['title']); ?>
                                            <span class="wfc-sidebar__count"><?php echo count($section['fields']); ?></span>
                                        </a>
                                    <?php } ?>
                                </nav>
                            </aside>

                            <main class="wfc-content">
                                <?php foreach ($catalog as $section) { ?>
                                    <section class="wfc-section" id="<?php echo esc_attr($section['id']); ?>">
                                        <div class="wfc-section__header">
                                            <h2><?php echo esc_html($section['title']); ?></h2>
                                            <p><?php echo esc_html($section['description']); ?></p>
                                        </div>
                                        <div class="wfc-grid">
                                            <?php foreach ($section['fields'] as $fieldDef) { ?>
                                                <?php echo $this->render_card($fieldDef); ?>
                                            <?php } ?>
                                        </div>
                                    </section>
                                <?php } ?>
                            </main>
                        </div>
                    </div>

                    <div id="tab-pane-wfc-page-tabs-1" class="wp-field-tabbed-pane" role="tabpanel" data-tab="wfc-page-tabs-1">
                        <div class="wfc-docs">
                            <div class="wfc-docs__shell" id="wfc-docs-root">
                                <aside class="wfc-docs__sidebar" id="wfc-docs-sidebar">
                                    <div class="wfc-docs__sidebar-inner">
                                        <span class="wfc-docs__eyebrow">WP_Field documentation</span>
                                        <p></p>
                                        <nav class="wfc-docs__nav" id="wfc-docs-nav" aria-label="<?php echo esc_attr($this->t('Documentation sections', 'Разделы документации')); ?>">
                                            <a href="#wfc-docs-installation" class="wfc-docs__nav-link is-active" data-doc-section="wfc-docs-installation"><?php echo esc_html($this->t('Installation', 'Установка')); ?></a>
                                            <a href="#wfc-docs-quickstart" class="wfc-docs__nav-link" data-doc-section="wfc-docs-quickstart"><?php echo esc_html($this->t('Quick start', 'Быстрый старт')); ?></a>
                                            <a href="#wfc-docs-usage" class="wfc-docs__nav-link" data-doc-section="wfc-docs-usage"><?php echo esc_html($this->t('How to use it', 'Как использовать')); ?></a>
                                            <a href="#wfc-docs-field-types" class="wfc-docs__nav-link" data-doc-section="wfc-docs-field-types"><?php echo esc_html($this->t('Field types', 'Типы полей')); ?></a>
                                            <a href="#wfc-docs-wp-deps" class="wfc-docs__nav-link" data-doc-section="wfc-docs-wp-deps"><?php echo esc_html($this->t('WordPress dependencies', 'WordPress зависимости')); ?></a>
                                            <a href="#wfc-docs-containers" class="wfc-docs__nav-link" data-doc-section="wfc-docs-containers"><?php echo esc_html($this->t('Containers and storage', 'Контейнеры и storage')); ?></a>
                                            <a href="#wfc-docs-examples" class="wfc-docs__nav-link" data-doc-section="wfc-docs-examples"><?php echo esc_html($this->t('Practical examples', 'Практические примеры')); ?></a>
                                            <a href="#wfc-docs-developers" class="wfc-docs__nav-link" data-doc-section="wfc-docs-developers"><?php echo esc_html($this->t('Developer guide', 'Для разработчиков')); ?></a>
                                            <a href="#wfc-docs-troubleshooting" class="wfc-docs__nav-link" data-doc-section="wfc-docs-troubleshooting"><?php echo esc_html($this->t('Troubleshooting', 'Troubleshooting')); ?></a>
                                            <a href="#wfc-docs-faq" class="wfc-docs__nav-link" data-doc-section="wfc-docs-faq">FAQ</a>
                                        </nav>
                                    </div>
                                </aside>

                                <div class="wfc-docs__content">
                                    <section class="wfc-docs__hero wfc-docs__hero--rich">
                                        <div>
                                            <span class="wfc-docs__eyebrow">WP_Field documentation</span>
                                            <h2><?php echo esc_html($this->t('Modern API for fields, containers, and admin UI', 'Modern API для полей, контейнеров и admin UI')); ?></h2>
                                            <p><?php echo wp_kses_post($this->t('Use <code>Field::make()</code> and typed shortcuts to describe fields once, render them in WordPress admin, validate them on save, and reuse them across metaboxes, settings pages, taxonomy screens, user profiles, and custom admin pages.', 'Используйте <code>Field::make()</code> и typed shortcuts, чтобы описывать поля один раз, рендерить их в WordPress admin, валидировать при сохранении и повторно использовать в metaboxes, settings pages, taxonomy screens, user profiles и custom admin pages.')); ?></p>
                                        </div>
                                        <div class="wfc-docs__hero-actions">
                                            <a href="#wfc-docs-installation" class="button button-primary"><?php echo esc_html($this->t('Get started', 'Начать')); ?></a>
                                            <a href="#wfc-docs-developers" class="button"><?php echo esc_html($this->t('For developers', 'Для разработчиков')); ?></a>
                                        </div>
                                    </section>

                                    <section class="wfc-docs__quick-grid">
                                        <article><h3><?php echo esc_html($this->t('Who it is for', 'Для кого')); ?></h3><p><?php echo esc_html($this->t('For WordPress developers who need one API for simple, composite, and interactive fields.', 'Для разработчиков WordPress, которым нужен единый API для простых, составных и интерактивных полей.')); ?></p></article>
                                        <article><h3><?php echo esc_html($this->t('Where to use it', 'Где использовать')); ?></h3><p><?php echo esc_html($this->t('Metaboxes, settings pages, taxonomy forms, user meta, custom admin screens, and any server-rendered admin forms.', 'Metaboxes, settings pages, taxonomy forms, user meta, custom admin screens и любые server-rendered admin формы.')); ?></p></article>
                                        <article><h3><?php echo esc_html($this->t('What matters', 'Что важно')); ?></h3><p><?php echo esc_html($this->t('The Modern API is not a WordPress-free runtime. Interactive fields still depend on core WP assets.', 'Modern API не является WordPress-free runtime. Интерактивные поля зависят от штатных WP assets.')); ?></p></article>
                                    </section>

                                    <section class="wfc-docs__section" id="wfc-docs-installation">
                                        <div class="wfc-docs__section-head">
                                            <h3><?php echo esc_html($this->t('Installation', 'Установка')); ?></h3>
                                            <p><?php echo esc_html($this->t('Add the library to your WordPress plugin or your custom admin workflow.', 'Подключите библиотеку в WordPress-плагин или в собственный admin workflow.')); ?></p>
                                        </div>
                                        <div class="wfc-docs__grid wfc-docs__grid--wide">
                                            <article><h4><?php echo esc_html($this->t('Via Composer', 'Через Composer')); ?></h4><pre><code><?php echo esc_html(<<<'BASH'
composer require rwsite/wp-field
BASH
                                            ); ?></code></pre><p><?php echo esc_html($this->t('This is the primary installation path for new projects.', 'Это основной способ установки для новых проектов.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('Build frontend assets', 'Сборка frontend assets')); ?></h4><pre><code><?php echo esc_html(<<<'BASH'
npm install
npm run build
BASH
                                            ); ?></code></pre><p><?php echo esc_html($this->t('Run this when you change React, Vite, or demo assets.', 'Нужно, если вы меняете React, Vite или demo-assets библиотеки.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('Requirements', 'Требования')); ?></h4><ul><li>PHP 8.3+</li><li>WordPress 6.0+</li><li><?php echo esc_html($this->t('Composer for PHP dependencies', 'Composer для PHP-зависимостей')); ?></li><li><?php echo esc_html($this->t('Node.js and npm for JS/CSS builds', 'Node.js и npm для сборки JS/CSS')); ?></li></ul></article>
                                        </div>
                                    </section>

                                    <section class="wfc-docs__section" id="wfc-docs-quickstart">
                                        <div class="wfc-docs__section-head">
                                            <h3><?php echo esc_html($this->t('Quick start', 'Быстрый старт')); ?></h3>
                                            <p><?php echo esc_html($this->t('The minimum flow is: create a field, render it, then reuse it for edit screens.', 'Минимальный сценарий: создать поле, отрендерить его и использовать повторно при редактировании.')); ?></p>
                                        </div>
                                        <pre><code><?php echo esc_html(<<<'PHP'
use WpField\Field\Field;

$field = Field::text('sku')
    ->label('SKU')
    ->placeholder('SKU-001')
    ->required();

echo $field->render();
PHP
                                        ); ?></code></pre>
                                        <ol class="wfc-docs__steps"><li><?php echo esc_html($this->t('Create a field instance with Field::make() or a typed shortcut.', 'Создайте field instance через Field::make() или typed shortcut.')); ?></li><li><?php echo esc_html($this->t('Describe labels, options, defaults, and validation with fluent methods.', 'Опишите label, options, default, validation и другие параметры fluent-методами.')); ?></li><li><?php echo esc_html($this->t('Render the field inside a WordPress admin form.', 'Рендерьте поле в WordPress admin форме.')); ?></li><li><?php echo esc_html($this->t('Normalize and save the submitted value through your container or save handler.', 'Сохраняйте и нормализуйте submitted value через ваш container или save handler.')); ?></li><li><?php echo esc_html($this->t('Pass the stored value back into the same field configuration.', 'Передавайте сохранённое значение обратно в ту же конфигурацию поля.')); ?></li></ol>
                                    </section>

                                    <section class="wfc-docs__section" id="wfc-docs-usage">
                                        <div class="wfc-docs__section-head">
                                            <h3><?php echo esc_html($this->t('How to use the library', 'Как использовать библиотеку')); ?></h3>
                                            <p><?php echo esc_html($this->t('The Modern API covers the full field lifecycle: definition, rendering, normalization, and reuse.', 'Modern API покрывает полный цикл жизни поля: описание, рендер, нормализация и повторное использование.')); ?></p>
                                        </div>
                                        <div class="wfc-docs__grid">
                                            <article><h4><?php echo esc_html($this->t('1. Create', '1. Создание')); ?></h4><p><?php echo wp_kses_post($this->t('Choose a type with <code>Field::make(&#039;type&#039;, &#039;name&#039;)</code> or a shortcut such as <code>Field::text(&#039;name&#039;)</code>.', 'Выберите тип через <code>Field::make(&#039;type&#039;, &#039;name&#039;)</code> или shorthand вроде <code>Field::text(&#039;name&#039;)</code>.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('2. Configure', '2. Конфигурация')); ?></h4><p><?php echo wp_kses_post($this->t('Configure <code>label()</code>, <code>placeholder()</code>, <code>value()</code>, <code>options()</code>, <code>required()</code>, <code>min()</code>, <code>max()</code>, and field-specific methods.', 'Настройте <code>label()</code>, <code>placeholder()</code>, <code>value()</code>, <code>options()</code>, <code>required()</code>, <code>min()</code>, <code>max()</code> и field-specific методы.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('3. Render', '3. Рендер')); ?></h4><p><?php echo wp_kses_post($this->t('The field renders on the server and returns HTML through <code>render()</code>.', 'Поле рендерится на сервере и возвращает HTML через <code>render()</code>.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('4. Save', '4. Сохранение')); ?></h4><p><?php echo esc_html($this->t('On save, the library expects a standard WordPress form lifecycle and normalizes the value before persistence.', 'При сохранении библиотека ожидает WordPress form lifecycle и нормализует значение перед записью.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('5. Reuse', '5. Повторный вывод')); ?></h4><p><?php echo esc_html($this->t('Reuse the same field definition for edit flows so selected values restore correctly.', 'Тот же field definition повторно используется для edit flow и восстанавливает выбранные значения.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('6. Nest data', '6. Вложенные структуры')); ?></h4><p><?php echo wp_kses_post($this->t('<code>repeater</code> and <code>flexible_content</code> fit nested and variadic data structures.', '<code>repeater</code> и <code>flexible_content</code> подходят для вариативных и nested data structures.')); ?></p></article>
                                        </div>
                                    </section>

                                    <section class="wfc-docs__section" id="wfc-docs-field-types">
                                        <div class="wfc-docs__section-head">
                                            <h3><?php echo esc_html($this->t('Field types', 'Типы полей')); ?></h3>
                                            <p><?php echo wp_kses_post($this->t('The library supports 52 unique field types and 4 compatibility aliases. The <strong>Examples</strong> tab above shows live demos.', 'Библиотека поддерживает 52 уникальных типа полей и 4 alias-совместимости. Вкладка <strong>Examples</strong> выше показывает живые примеры.')); ?></p>
                                        </div>
                                        <div class="wfc-docs__grid wfc-docs__grid--wide">
                                            <article><h4>Input fields</h4><p><code>text</code>, <code>password</code>, <code>email</code>, <code>url</code>, <code>tel</code>, <code>number</code>, <code>range</code>, <code>hidden</code>, <code>date</code>, <code>time</code>, <code>datetime-local</code>, <code>textarea</code>.</p></article>
                                            <article><h4>Choice fields</h4><p><code>select</code>, <code>multiselect</code>, <code>radio</code>, <code>checkbox</code>, <code>checkbox_group</code>, <code>switcher</code>, <code>button_set</code>, <code>slider</code>, <code>image_picker</code>, <code>image_select</code>, <code>palette</code>.</p></article>
                                            <article><h4><?php echo esc_html($this->t('Media and rich fields', 'Media и rich fields')); ?></h4><p><code>media</code>, <code>image</code>, <code>file</code>, <code>gallery</code>, <code>editor</code>, <code>code_editor</code>, <code>icon</code>, <code>map</code>, <code>color</code>.</p></article>
                                            <article><h4><?php echo esc_html($this->t('Composite and layout fields', 'Composite и layout fields')); ?></h4><p><code>group</code>, <code>fieldset</code>, <code>accordion</code>, <code>tabbed</code>, <code>typography</code>, <code>spacing</code>, <code>dimensions</code>, <code>border</code>, <code>background</code>, <code>link_color</code>, <code>color_group</code>.</p></article>
                                            <article><h4><?php echo esc_html($this->t('Dynamic collections', 'Dynamic collections')); ?></h4><p><code>repeater</code>, <code>flexible_content</code>, <code>sortable</code>, <code>sorter</code>, <code>spinner</code>, <code>link</code>, <code>backup</code>.</p></article>
                                            <article><h4>Aliases</h4><p><?php echo wp_kses_post($this->t('<code>date_time</code> and <code>datetime</code> normalize to <code>datetime-local</code>; <code>imagepicker</code> normalizes to <code>image_picker</code>.', '<code>date_time</code> и <code>datetime</code> нормализуются в <code>datetime-local</code>; <code>imagepicker</code> — в <code>image_picker</code>.')); ?></p></article>
                                        </div>
                                    </section>

                                    <section class="wfc-docs__section" id="wfc-docs-wp-deps">
                                        <div class="wfc-docs__section-head">
                                            <h3><?php echo esc_html($this->t('WordPress dependencies', 'WordPress зависимости')); ?></h3>
                                            <p><?php echo esc_html($this->t('Some fields use native WordPress UI assets. That is part of the intended production integration.', 'Часть полей использует штатные UI-ресурсы WordPress. Это нормальная часть production integration.')); ?></p>
                                        </div>
                                        <div class="wfc-docs__callout"><?php echo esc_html($this->t('Do not unload required WP scripts and styles on screens that render interactive fields.', 'Не отключайте нужные WP scripts и styles на страницах, где рендерятся интерактивные поля.')); ?></div>
                                        <div class="wfc-docs__grid">
                                            <article><h4><code>wp.media</code></h4><p><?php echo wp_kses_post($this->t('Required for <code>media</code>, <code>image</code>, <code>file</code>, <code>gallery</code>, and <code>background</code>.', 'Нужно для <code>media</code>, <code>image</code>, <code>file</code>, <code>gallery</code>, <code>background</code>.')); ?></p></article>
                                            <article><h4><code>wp-color-picker</code></h4><p><?php echo wp_kses_post($this->t('Required for <code>color</code>, <code>palette</code>, and color-based composite controls.', 'Нужно для <code>color</code>, <code>palette</code> и color-based composite controls.')); ?></p></article>
                                            <article><h4><code>wp.editor</code> / <code>wp.codeEditor</code></h4><p><?php echo wp_kses_post($this->t('Required for <code>editor</code> and <code>code_editor</code>.', 'Нужно для <code>editor</code> и <code>code_editor</code>.')); ?></p></article>
                                            <article><h4><code>jquery-ui-sortable</code></h4><p><?php echo wp_kses_post($this->t('Required for <code>sortable</code>, <code>sorter</code>, <code>repeater</code>, and <code>flexible_content</code>.', 'Нужно для <code>sortable</code>, <code>sorter</code>, <code>repeater</code>, <code>flexible_content</code>.')); ?></p></article>
                                            <article><h4><code>jquery-ui-datepicker</code></h4><p><?php echo esc_html($this->t('Used for date-related UI where WordPress-native picker behavior is expected.', 'Используется для date-related UI там, где ожидается WP-native behavior.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('Icons and maps', 'Иконки и карты')); ?></h4><p><?php echo wp_kses_post($this->t('<code>dashicons</code> or another icon library is needed for <code>icon</code>; map fields may require provider assets.', '<code>dashicons</code> или icon library нужны для <code>icon</code>; map field может требовать provider assets.')); ?></p></article>
                                        </div>
                                    </section>

                                    <section class="wfc-docs__section" id="wfc-docs-containers">
                                        <div class="wfc-docs__section-head">
                                            <h3><?php echo esc_html($this->t('Containers and storage', 'Контейнеры и storage')); ?></h3>
                                            <p><?php echo esc_html($this->t('The field defines the value contract, while containers and storage integrate with WordPress.', 'Поле описывает контракт значения, а контейнеры и storage отвечают за интеграцию с WordPress.')); ?></p>
                                        </div>
                                        <div class="wfc-docs__grid wfc-docs__grid--wide">
                                            <article><h4><?php echo esc_html($this->t('Containers', 'Контейнеры')); ?></h4><ul><li><code>MetaboxContainer</code> — post meta and metabox screens</li><li><code>SettingsContainer</code> — settings pages and options</li><li><code>TaxonomyContainer</code> — term meta forms</li><li><code>UserContainer</code> — user meta forms</li></ul></article>
                                            <article><h4><?php echo esc_html($this->t('Storage strategies', 'Storage strategies')); ?></h4><ul><li><code>PostMetaStorage</code></li><li><code>TermMetaStorage</code></li><li><code>UserMetaStorage</code></li><li><code>OptionStorage</code></li><li><code>CustomTableStorage</code></li></ul></article>
                                        </div>
                                    </section>

                                    <section class="wfc-docs__section" id="wfc-docs-examples">
                                        <div class="wfc-docs__section-head">
                                            <h3><?php echo esc_html($this->t('Practical examples', 'Практические примеры')); ?></h3>
                                            <p><?php echo esc_html($this->t('Use the same API for simple and composite scenarios.', 'Используйте один и тот же API для простых и составных сценариев.')); ?></p>
                                        </div>
                                        <div class="wfc-docs__examples">
                                            <article><h4>Metabox</h4><pre><code><?php echo esc_html(<<<'PHP'
use WpField\Container\MetaboxContainer;
use WpField\Field\Field;

$box = new MetaboxContainer('product_details', [
    'title' => 'Product Details',
    'post_types' => ['product'],
]);

$box->addField(
    Field::text('sku')->label('SKU')->required()
);

$box->register();
PHP
                                            ); ?></code></pre></article>
                                            <article><h4>Repeater</h4><pre><code><?php echo esc_html(<<<'PHP'
Field::repeater('team_members')
    ->label('Team Members')
    ->fields([
        Field::text('name')->label('Name')->required(),
        Field::text('position')->label('Position'),
        Field::make('email', 'email')->label('Email')->email(),
    ])
    ->min(1)
    ->max(10)
    ->buttonLabel('Add Member');
PHP
                                            ); ?></code></pre></article>
                                            <article><h4>Flexible content</h4><pre><code><?php echo esc_html(<<<'PHP'
Field::flexibleContent('page_sections')
    ->label('Page Sections')
    ->addLayout('text_block', 'Text Block', [
        Field::text('heading')->label('Heading'),
        Field::textarea('content')->label('Content'),
    ])
    ->addLayout('image_block', 'Image Block', [
        Field::image('image')->label('Image'),
        Field::text('caption')->label('Caption'),
    ]);
PHP
                                            ); ?></code></pre></article>
                                        </div>
                                    </section>

                                    <section class="wfc-docs__section" id="wfc-docs-developers">
                                        <div class="wfc-docs__section-head">
                                            <h3><?php echo esc_html($this->t('Developer guide', 'Для разработчиков библиотеки')); ?></h3>
                                            <p><?php echo esc_html($this->t('Use this section when you extend WP_Field, maintain the runtime, or add new field types.', 'Этот раздел нужен тем, кто расширяет WP_Field, поддерживает runtime и добавляет новые field types.')); ?></p>
                                        </div>
                                        <div class="wfc-docs__grid wfc-docs__grid--wide">
                                            <article><h4><?php echo esc_html($this->t('Architecture', 'Архитектура')); ?></h4><ul><li><code>src/Field/Field.php</code> — factory and type normalization</li><li><code>src/Field/Types/</code> — field type implementations</li><li><code>src/Container/</code> — WordPress integration points</li><li><code>src/Storage/</code> — persistence strategies</li><li><code>src/UI/</code> — admin shell, wizard, UI manager</li><li><code>examples/shared-catalog.php</code> — source of truth for demos</li></ul></article>
                                            <article><h4><?php echo esc_html($this->t('How to add a new type', 'Как добавлять новый тип')); ?></h4><ol><li><?php echo esc_html($this->t('Create a class in src/Field/Types/.', 'Создайте класс в src/Field/Types/.')); ?></li><li><?php echo esc_html($this->t('Register the type in Field::make().', 'Зарегистрируйте тип в Field::make().')); ?></li><li><?php echo esc_html($this->t('Add a typed shortcut when it belongs in the public API.', 'Добавьте typed shortcut, если он нужен публичному API.')); ?></li><li><?php echo esc_html($this->t('Verify render, normalize/save flow, and asset dependencies.', 'Проверьте рендер, normalize/save flow и asset dependencies.')); ?></li><li><?php echo esc_html($this->t('Add a demo entry to examples/shared-catalog.php.', 'Добавьте demo в examples/shared-catalog.php.')); ?></li><li><?php echo esc_html($this->t('Update tests and documentation.', 'Обновите тесты и документацию.')); ?></li></ol></article>
                                            <article><h4>Quality gate</h4><pre><code><?php echo esc_html(<<<'BASH'
npm run lint
npm run build
composer test
./.agents/skills/qa-gate/scripts/verify.sh
BASH
                                            ); ?></code></pre><p><?php echo wp_kses_post($this->t('For JS/JSX/SCSS changes, <code>npm run lint</code> is required.', 'Для JS/JSX/SCSS изменений обязательна проверка через <code>npm run lint</code>.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('Compatibility boundary', 'Границы совместимости')); ?></h4><p><?php echo wp_kses_post($this->t('Do not break the legacy API without an explicit decision. For unsupported or custom legacy controls, use <code>Field::legacy()</code>.', 'Не ломайте legacy API без отдельного решения. Для unsupported/custom legacy types используйте <code>Field::legacy()</code>.')); ?></p></article>
                                        </div>
                                    </section>

                                    <section class="wfc-docs__section" id="wfc-docs-troubleshooting">
                                        <div class="wfc-docs__section-head">
                                            <h3><?php echo esc_html($this->t('Troubleshooting', 'Troubleshooting')); ?></h3>
                                            <p><?php echo esc_html($this->t('Most integration issues come from missing assets or broken markup contracts, not from PHP rendering.', 'Типовые проблемы при интеграции связаны не с PHP-рендером, а с отсутствующими asset dependencies или некорректным markup contract.')); ?></p>
                                        </div>
                                        <div class="wfc-docs__grid">
                                            <article><h4><?php echo esc_html($this->t('Media, image, or gallery does not work', 'Не работает media/image/gallery')); ?></h4><p><?php echo wp_kses_post($this->t('Check that <code>wp.media</code> is loaded and the page runs inside WordPress admin.', 'Проверьте, что подключён <code>wp.media</code> и страница работает в WordPress admin context.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('Color picker or editor does not initialize', 'Не инициализируется color/editor/code editor')); ?></h4><p><?php echo wp_kses_post($this->t('Check the enqueue flow for <code>wp-color-picker</code>, <code>wp.editor</code>, and <code>wp.codeEditor</code>.', 'Проверьте enqueue для <code>wp-color-picker</code>, <code>wp.editor</code> и <code>wp.codeEditor</code>.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('Repeater or flexible content breaks', 'Ломается repeater или flexible content')); ?></h4><p><?php echo wp_kses_post($this->t('Check <code>jquery-ui-sortable</code>, wrapper classes, and required <code>data-*</code> attributes.', 'Проверьте <code>jquery-ui-sortable</code>, wrapper classes и ожидаемые <code>data-*</code> атрибуты.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('A field exists in legacy but not as a modern shortcut', 'Поле есть в legacy, но нет в modern shortcut')); ?></h4><p><?php echo wp_kses_post($this->t('Use <code>Field::make()</code> for a supported type key or <code>Field::legacy()</code> as the unsupported fallback.', 'Используйте <code>Field::make()</code> для поддерживаемого type key или <code>Field::legacy()</code> для unsupported fallback.')); ?></p></article>
                                        </div>
                                    </section>

                                    <section class="wfc-docs__section" id="wfc-docs-faq">
                                        <div class="wfc-docs__section-head">
                                            <h3>FAQ</h3>
                                            <p><?php echo esc_html($this->t('Short answers to common questions about using the library.', 'Короткие ответы на основные вопросы по использованию библиотеки.')); ?></p>
                                        </div>
                                        <div class="wfc-docs__faq">
                                            <article><h4><?php echo esc_html($this->t('Is this a replacement for the legacy API?', 'Это замена legacy API?')); ?></h4><p><?php echo esc_html($this->t('No. The Modern API is the recommended layer for new integrations. Legacy compatibility remains for older or custom behavior.', 'Нет. Modern API — рекомендуемый слой для новых интеграций. Legacy compatibility остаётся для старого и custom behavior.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('Can I use the library outside WordPress admin?', 'Можно ли использовать библиотеку вне WordPress admin?')); ?></h4><p><?php echo esc_html($this->t('Partly for simple server-rendered fields. Rich and interactive controls still need the native WordPress runtime.', 'Для простых server-rendered полей — частично. Для rich и interactive controls нужен штатный WordPress runtime.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('Can I plug in my own storage?', 'Можно ли подключить своё хранилище?')); ?></h4><p><?php echo esc_html($this->t('Yes. The field defines the shape of the value. The storage strategy can be native or custom as long as it accepts normalized data.', 'Да. Поле описывает форму значения. Storage strategy может быть стандартной или кастомной, если она принимает нормализованные данные.')); ?></p></article>
                                            <article><h4><?php echo esc_html($this->t('Where can I see live examples?', 'Где смотреть живые примеры?')); ?></h4><p><?php echo wp_kses_post($this->t('In the <strong>Examples</strong> tab on this page. It uses the same demo catalog as the library showcase pages.', 'Во вкладке <strong>Examples</strong> на этой странице. Она использует тот же каталог demo-полей, что и библиотечные showcase-страницы.')); ?></p></article>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_card(array $fieldDef): string
    {
        $type = esc_html($fieldDef['type']);
        $title = esc_html($fieldDef['title']);
        $desc = esc_html($fieldDef['description']);
        $code = esc_html($fieldDef['code'] ?? '');
        $props = $fieldDef['props'] ?? [];

        $renderedField = $fieldDef['field']->render();

        $propsHtml = '';
        if ($props) {
            $propsHtml = '<div class="wfc-card__props">';
            foreach ($props as $prop) {
                $propsHtml .= '<span class="wfc-card__prop">'.esc_html($prop).'</span>';
            }
            $propsHtml .= '</div>';
        }

        return <<<HTML
        <article class="wfc-card" data-type="$type">
            <header class="wfc-card__header">
                <div>
                    <h3>$title</h3>
                    <p>$desc</p>
                </div>
                <code class="wfc-card__type">$type</code>
            </header>
            <div class="wfc-card__preview">$renderedField</div>
            $propsHtml
            <details class="wfc-card__code">
                <summary>{$this->t('Code example', 'Пример кода')}</summary>
                <pre><code>$code</code></pre>
            </details>
        </article>
        HTML;
    }
}

if (is_admin()) {
    new WP_Field_Components;
}
