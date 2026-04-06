<?php
/**
 * WP_Field v2.3 — Примеры использования всех типов полей
 *
 * Подключение: require_once 'path/to/example.php';
 *
 * Добавляет страницу в меню "Инструменты" с демонстрацией всех 48 типов полей
 */
if (! defined('ABSPATH')) {
    exit;
}

// Подключаем WP_Field если еще не подключен
if (! class_exists('WP_Field')) {
    require_once __DIR__.'/WP_Field.php';
}

class WP_Field_Examples
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'save_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // Подключаем CodeMirror для code_editor
        add_action('admin_enqueue_scripts', function ($hook): void {
            if ($hook === 'tools_page_wp-field-examples') {
                wp_enqueue_code_editor(['type' => 'text/css']);
            }
        });
    }

    /**
     * Подключение необходимых скриптов и стилей
     */
    public function enqueue_assets($hook): void
    {
        // Загружаем только на нашей странице
        if ($hook !== 'tools_page_wp-field-examples') {
            return;
        }

        // WP встроенные скрипты
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-sortable');

        // wp-color-picker для color полей (с зависимостью iris)
        wp_enqueue_script('iris');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');

        // wp-media для media полей
        wp_enqueue_media();

        // Наш JS для зависимостей и инициализации
        $wp_field_url = plugin_dir_url(__FILE__);
        $wp_field_ver = defined('WP_DEBUG') && WP_DEBUG ? time() : '2.3.0';

        wp_enqueue_script(
            'wp-field-main',
            $wp_field_url.'assets/js/wp-field.js',
            ['jquery', 'wp-color-picker', 'jquery-ui-sortable'],
            $wp_field_ver,
            true,
        );

        // Добавляем inline скрипт для гарантированной инициализации Color Picker
        wp_add_inline_script('wp-field-main', '
            jQuery(document).ready(function($) {
                // Дополнительная инициализация Color Picker после загрузки страницы
                setTimeout(function() {
                    if (typeof $.fn.wpColorPicker !== "undefined") {
                        $(".wp-color-picker-field").each(function() {
                            if (!$(this).hasClass("wp-color-picker")) {
                                $(this).wpColorPicker();
                            }
                        });
                    }
                }, 500);
            });
        ');

        // Наш CSS
        wp_enqueue_style(
            'wp-field-main',
            $wp_field_url.'assets/css/wp-field.css',
            ['wp-color-picker'],
            $wp_field_ver,
        );

        // Prism.js для подсветки синтаксиса
        wp_enqueue_style(
            'prism-css',
            'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css',
            [],
            '1.29.0',
        );

        // Используем полную версию Prism с поддержкой PHP
        wp_enqueue_script(
            'prism-js',
            'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js',
            [],
            '1.29.0',
            true,
        );

        // Добавляем поддержку PHP через data-атрибут
        wp_add_inline_script('prism-js', '
            if (typeof Prism !== "undefined") {
                Prism.languages.php = Prism.languages.extend("clike", {
                    keyword: /\\b(?:and|or|xor|array|as|break|case|cfunction|class|const|continue|declare|default|die|do|else|elseif|enddeclare|endfor|endforeach|endif|endswitch|endwhile|extends|for|foreach|function|include|include_once|global|if|new|return|static|switch|use|require|require_once|var|while|abstract|interface|public|implements|private|protected|parent|throw|null|echo|print|trait|namespace|final|yield|goto|instanceof|finally|try|catch)\\b/i,
                    constant: /\\b[A-Z0-9_]{2,}\\b/,
                    comment: {
                        pattern: /(^|[^\\\\])(?:\\/\\*[\\s\\S]*?\\*\\/|\\/\\/.*)/,
                        lookbehind: true
                    }
                });
            }
        ', 'after');
    }

    /**
     * Добавить страницу в меню Инструменты
     */
    public function add_menu_page(): void
    {
        add_management_page(
            'WP_Field Examples',
            'WP_Field Examples',
            'manage_options',
            'wp-field-examples',
            [$this, 'render_page'],
        );
    }

    /**
     * Сохранение настроек
     */
    public function save_settings(): void
    {
        if (! isset($_POST['wp_field_examples_nonce'])) {
            return;
        }

        if (! wp_verify_nonce($_POST['wp_field_examples_nonce'], 'wp_field_examples_save')) {
            return;
        }

        if (! current_user_can('manage_options')) {
            return;
        }

        // Сохраняем все поля
        $fields = $this->get_all_fields();
        foreach ($fields as $section) {
            foreach ($section['fields'] as $field) {
                if (isset($_POST[$field['id']])) {
                    update_option('wpf_example_'.$field['id'], $_POST[$field['id']]);
                }
            }
        }

        add_settings_error('wp_field_examples', 'settings_updated', 'Настройки сохранены!', 'updated');
    }

    /**
     * Рендер страницы
     */
    public function render_page(): void
    {
        ?>
        <div class="wrap">
            <h1>WP_Field v3.0 — Примеры всех типов полей</h1>
            
            <div class="notice notice-info">
                <p><strong>48 типов полей</strong> с системой зависимостей, поддержкой всех типов хранилищ и встроенными WP компонентами.</p>
                <p>📌 <strong>Новое:</strong> <a href="<?php echo admin_url('tools.php?page=wp-field-v3-demo'); ?>">Смотрите v3.0 Demo</a> с Fluent API, Repeater и Flexible Content!</p>
            </div>
            
            <?php settings_errors('wp_field_examples'); ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('wp_field_examples_save', 'wp_field_examples_nonce'); ?>
                
                <div class="wp-field-examples-container">
                    <?php $this->render_all_fields(); ?>
                </div>
                
                <p class="submit">
                    <button type="submit" class="button button-primary button-large">Сохранить все настройки</button>
                    <button type="button" class="button button-secondary" onclick="location.reload()">Сбросить</button>
                </p>
            </form>
        </div>
        
        <style>
            .wp-field-examples-container {
                background: #fff;
                padding: 20px;
                margin: 20px 0;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .wp-field-section {
                margin-bottom: 40px;
                padding-bottom: 30px;
                border-bottom: 2px solid #f0f0f0;
            }
            .wp-field-section:last-child {
                border-bottom: none;
            }
            .wp-field-section h2 {
                margin-top: 0;
                padding: 10px 15px;
                background: #f9f9f9;
                border-left: 4px solid #0073aa;
            }
            .wp-field-section .description {
                padding: 0 15px;
                color: #666;
                font-style: italic;
            }
            .wp-field-example {
                margin: 20px 0;
                padding: 15px;
                background: #fafafa;
                border-left: 3px solid #0073aa;
            }
            .wp-field-example h3 {
                margin-top: 0;
                color: #0073aa;
            }
            .wp-field-example h3 code {
                background: #e8f4f8;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 12px;
                color: #0073aa;
                font-weight: normal;
            }
            .wp-field-example details {
                margin-top: 15px;
            }
            .wp-field-example details summary {
                cursor: pointer;
                color: #0073aa;
                font-weight: 600;
                padding: 8px 12px;
                background: #f0f8ff;
                border-radius: 4px;
                user-select: none;
                transition: background 0.2s;
            }
            .wp-field-example details summary:hover {
                background: #e0f0ff;
            }
            .wp-field-example details[open] summary {
                margin-bottom: 10px;
            }
            .wp-field-example pre {
                margin: 0;
                border-radius: 4px;
                overflow: hidden;
            }
            .wp-field-example pre code {
                display: block;
                padding: 15px !important;
                margin: 0;
                font-size: 13px;
                line-height: 1.6;
                overflow-x: auto;
                background: #2d2d2d !important;
            }
            .wp-field-description {
                background: #f0f8ff;
                padding: 12px 15px;
                border-left: 3px solid #0073aa;
                margin: 10px 0;
                border-radius: 3px;
            }
            .wp-field-description p {
                margin: 0;
                color: #333;
            }
            .wp-field-preview {
                background: #fff;
                padding: 20px;
                border: 1px solid #e0e0e0;
                border-radius: 4px;
                margin: 15px 0;
            }
            .wp-field-arguments,
            .wp-field-code,
            .wp-field-advanced {
                margin: 15px 0;
            }
            .wp-field-arguments summary,
            .wp-field-code summary,
            .wp-field-advanced summary {
                cursor: pointer;
                color: #0073aa;
                font-weight: 600;
                padding: 10px 15px;
                background: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 4px;
                user-select: none;
                transition: background 0.2s;
            }
            .wp-field-arguments summary:hover,
            .wp-field-code summary:hover,
            .wp-field-advanced summary:hover {
                background: #f0f0f0;
            }
            .wp-field-arguments[open] summary,
            .wp-field-code[open] summary,
            .wp-field-advanced[open] summary {
                margin-bottom: 15px;
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
            }
            .wp-field-args-table {
                width: 100%;
                border-collapse: collapse;
                background: #fff;
                border: 1px solid #ddd;
                border-top: none;
            }
            .wp-field-args-table th,
            .wp-field-args-table td {
                padding: 10px 12px;
                text-align: left;
                border-bottom: 1px solid #e0e0e0;
            }
            .wp-field-args-table th {
                background: #f5f5f5;
                font-weight: 600;
                color: #333;
            }
            .wp-field-args-table tr:last-child td {
                border-bottom: none;
            }
            .wp-field-args-table code {
                background: #f0f0f0;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 12px;
                color: #d63384;
            }
            .wp-field-advanced-item {
                background: #fff;
                padding: 15px;
                border: 1px solid #ddd;
                border-top: none;
                margin-bottom: 15px;
            }
            .wp-field-advanced-item:first-child {
                border-top: 1px solid #ddd;
            }
            .wp-field-advanced-item:last-child {
                margin-bottom: 0;
            }
            .wp-field-advanced-item h4 {
                margin: 0 0 8px 0;
                color: #0073aa;
                font-size: 14px;
            }
            .wp-field-advanced-item p {
                margin: 0 0 10px 0;
                color: #666;
                font-size: 13px;
            }
            .wp-field-advanced-item pre {
                margin: 0;
            }
        </style>
        <?php
    }

    /**
     * Рендер всех полей по категориям
     */
    private function render_all_fields(): void
    {
        $sections = $this->get_all_fields();

        foreach ($sections as $section) {
            echo '<div class="wp-field-section">';
            echo '<h2>'.esc_html($section['title']).'</h2>';
            if (! empty($section['description'])) {
                echo '<p class="description">'.esc_html($section['description']).'</p>';
            }

            foreach ($section['fields'] as $field) {
                $this->render_field_example($field);
            }

            echo '</div>';
        }
    }

    /**
     * Рендер примера одного поля
     */
    private function render_field_example($field): void
    {
        echo '<div class="wp-field-example">';
        echo '<h3>'.esc_html($field['label']).' <code>'.esc_html($field['type']).'</code></h3>';

        // Описание поля
        if (! empty($field['example_desc'])) {
            echo '<div class="wp-field-description">';
            echo '<p>'.wp_kses_post($field['example_desc']).'</p>';
            echo '</div>';
        }

        // Рендерим поле
        echo '<div class="wp-field-preview">';
        WP_Field::make($field, true, 'options');
        echo '</div>';

        // Список аргументов
        if (! empty($field['arguments'])) {
            echo '<details class="wp-field-arguments"><summary>📋 Список аргументов</summary>';
            echo '<table class="wp-field-args-table">';
            echo '<thead><tr><th>Аргумент</th><th>Тип</th><th>По умолчанию</th><th>Описание</th></tr></thead>';
            echo '<tbody>';
            foreach ($field['arguments'] as $arg) {
                printf(
                    '<tr><td><code>%s</code></td><td><code>%s</code></td><td><code>%s</code></td><td>%s</td></tr>',
                    esc_html($arg['name']),
                    esc_html($arg['type']),
                    esc_html($arg['default'] ?? '—'),
                    esc_html($arg['desc']),
                );
            }
            echo '</tbody></table>';
            echo '</details>';
        }

        // Базовый пример кода
        if (! empty($field['example_code'])) {
            echo '<details class="wp-field-code"><summary>💻 Базовый пример</summary>';
            echo '<pre><code class="language-php">'.esc_html($field['example_code']).'</code></pre>';
            echo '</details>';
        }

        // Расширенные примеры
        if (! empty($field['advanced_examples'])) {
            echo '<details class="wp-field-advanced"><summary>🚀 Расширенные примеры</summary>';
            foreach ($field['advanced_examples'] as $example) {
                echo '<div class="wp-field-advanced-item">';
                if (! empty($example['title'])) {
                    echo '<h4>'.esc_html($example['title']).'</h4>';
                }
                if (! empty($example['desc'])) {
                    echo '<p>'.esc_html($example['desc']).'</p>';
                }
                echo '<pre><code class="language-php">'.esc_html($example['code']).'</code></pre>';
                echo '</div>';
            }
            echo '</details>';
        }

        echo '</div>';
    }

    /**
     * Общие аргументы для всех полей
     */
    private function get_common_arguments()
    {
        return [
            ['name' => 'id', 'type' => 'string', 'default' => '—', 'desc' => 'Уникальный идентификатор (обязательно)'],
            ['name' => 'type', 'type' => 'string', 'default' => 'text', 'desc' => 'Тип поля'],
            ['name' => 'label', 'type' => 'string', 'default' => '', 'desc' => 'Заголовок поля'],
            ['name' => 'desc', 'type' => 'string', 'default' => '', 'desc' => 'Описание под полем'],
            ['name' => 'default', 'type' => 'mixed', 'default' => '', 'desc' => 'Значение по умолчанию'],
            ['name' => 'class', 'type' => 'string', 'default' => '', 'desc' => 'CSS класс'],
            ['name' => 'dependency', 'type' => 'array', 'default' => '[]', 'desc' => 'Условия зависимости'],
            ['name' => 'attributes', 'type' => 'array', 'default' => '[]', 'desc' => 'HTML атрибуты'],
        ];
    }

    /**
     * Получить расширенные данные для типа поля
     * Примечание: все данные теперь встроены в example.php
     */
    private function get_field_data($type)
    {
        // field-data.php удалён, все примеры встроены в example.php
        return ['arguments' => [], 'advanced_examples' => []];
    }

    /**
     * Объединить общие и специфичные аргументы
     */
    private function merge_arguments($type, $specific_args = [])
    {
        $common = $this->get_common_arguments();

        return array_merge($common, $specific_args);
    }

    /**
     * Получить все поля для демонстрации
     */
    private function get_all_fields()
    {
        return [
            // Базовые поля
            [
                'title' => '1. Базовые поля (9)',
                'description' => 'Стандартные HTML5 input типы',
                'fields' => [
                    [
                        'id' => 'text_field',
                        'type' => 'text',
                        'label' => 'Text — Текстовое поле',
                        'placeholder' => 'Введите текст...',
                        'desc' => 'Стандартное текстовое поле',
                        'example_desc' => 'Базовое текстовое поле для ввода любого текста. Поддерживает placeholder, валидацию, зависимости и все стандартные HTML5 атрибуты.',
                        'arguments' => [
                            ['name' => 'id', 'type' => 'string', 'default' => '—', 'desc' => 'Уникальный идентификатор поля (обязательно)'],
                            ['name' => 'type', 'type' => 'string', 'default' => 'text', 'desc' => 'Тип поля'],
                            ['name' => 'label', 'type' => 'string', 'default' => '', 'desc' => 'Заголовок поля'],
                            ['name' => 'placeholder', 'type' => 'string', 'default' => '', 'desc' => 'Текст-подсказка'],
                            ['name' => 'desc', 'type' => 'string', 'default' => '', 'desc' => 'Описание под полем'],
                            ['name' => 'default', 'type' => 'string', 'default' => '', 'desc' => 'Значение по умолчанию'],
                            ['name' => 'class', 'type' => 'string', 'default' => '', 'desc' => 'CSS класс для поля'],
                            ['name' => 'dependency', 'type' => 'array', 'default' => '[]', 'desc' => 'Условия зависимости'],
                            ['name' => 'attributes', 'type' => 'array', 'default' => '[]', 'desc' => 'HTML атрибуты'],
                        ],
                        'example_code' => "WP_Field::make([\n    'id' => 'text_field',\n    'type' => 'text',\n    'label' => 'Текст',\n    'placeholder' => 'Введите текст...'\n]);",
                        'advanced_examples' => [
                            [
                                'title' => 'С валидацией и классом',
                                'desc' => 'Добавление CSS класса и HTML атрибутов для валидации',
                                'code' => "WP_Field::make([\n    'id' => 'username',\n    'type' => 'text',\n    'label' => 'Имя пользователя',\n    'placeholder' => 'Только латиница и цифры',\n    'class' => 'regular-text',\n    'attributes' => [\n        'pattern' => '[a-zA-Z0-9]+',\n        'required' => true,\n        'minlength' => 3,\n        'maxlength' => 20\n    ],\n    'desc' => 'От 3 до 20 символов'\n]);",
                            ],
                            [
                                'title' => 'С зависимостью от другого поля',
                                'desc' => 'Поле отображается только если включен чекбокс',
                                'code' => "WP_Field::make([\n    'id' => 'custom_text',\n    'type' => 'text',\n    'label' => 'Пользовательский текст',\n    'dependency' => [\n        ['enable_custom', '==', '1']\n    ]\n]);",
                            ],
                            [
                                'title' => 'Для post meta',
                                'desc' => 'Сохранение в метаполе записи',
                                'code' => "// В metabox callback:\n\$post_id = get_the_ID();\n\nWP_Field::make([\n    'id' => 'custom_title',\n    'type' => 'text',\n    'label' => 'Дополнительный заголовок'\n], true, 'post', \$post_id);\n\n// Получение значения:\n\$value = get_post_meta(\$post_id, 'custom_title', true);",
                            ],
                        ],
                    ],
                    [
                        'id' => 'password_field',
                        'type' => 'password',
                        'label' => 'Password — Пароль',
                        'placeholder' => '••••••••',
                        'desc' => 'Поле для ввода пароля (скрытый текст)',
                        'example_code' => "WP_Field::make(['type' => 'password']);",
                    ],
                    [
                        'id' => 'email_field',
                        'type' => 'email',
                        'label' => 'Email — Email адрес',
                        'placeholder' => 'user@example.com',
                        'desc' => 'Поле с валидацией email',
                        'example_code' => "WP_Field::make(['type' => 'email']);",
                    ],
                    [
                        'id' => 'url_field',
                        'type' => 'url',
                        'label' => 'URL — Ссылка',
                        'placeholder' => 'https://example.com',
                        'desc' => 'Поле с валидацией URL',
                        'example_code' => "WP_Field::make(['type' => 'url']);",
                    ],
                    [
                        'id' => 'tel_field',
                        'type' => 'tel',
                        'label' => 'Tel — Телефон',
                        'placeholder' => '+7 (999) 123-45-67',
                        'desc' => 'Поле для ввода телефона',
                        'example_code' => "WP_Field::make(['type' => 'tel']);",
                    ],
                    [
                        'id' => 'number_field',
                        'type' => 'number',
                        'label' => 'Number — Число',
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                        'desc' => 'Числовое поле с min/max/step',
                        'example_code' => "WP_Field::make([\n    'type' => 'number',\n    'min' => 0,\n    'max' => 100,\n    'step' => 1\n]);",
                    ],
                    [
                        'id' => 'range_field',
                        'type' => 'range',
                        'label' => 'Range — Диапазон',
                        'min' => 0,
                        'max' => 100,
                        'step' => 5,
                        'desc' => 'Ползунок для выбора значения',
                        'example_code' => "WP_Field::make(['type' => 'range']);",
                    ],
                    array_merge([
                        'id' => 'textarea_field',
                        'type' => 'textarea',
                        'label' => 'Textarea — Многострочный текст',
                        'rows' => 5,
                        'placeholder' => 'Введите многострочный текст...',
                        'desc' => 'Текстовая область для длинного текста',
                        'example_desc' => 'Многострочное текстовое поле для ввода длинного текста. Поддерживает настройку количества строк и placeholder.',
                        'example_code' => "WP_Field::make([\n    'type' => 'textarea',\n    'rows' => 5\n]);",
                    ], $this->get_field_data('textarea')),
                ],
            ],

            // Выборные поля
            [
                'title' => '2. Выборные поля (5)',
                'description' => 'Поля для выбора из списка опций',
                'fields' => [
                    array_merge([
                        'id' => 'select_field',
                        'type' => 'select',
                        'label' => 'Select — Выпадающий список',
                        'options' => [
                            'option1' => 'Опция 1',
                            'option2' => 'Опция 2',
                            'option3' => 'Опция 3',
                        ],
                        'desc' => 'Выбор одного значения из списка',
                        'example_desc' => 'Выпадающий список для выбора одного значения. Поддерживает группировку опций, динамическую загрузку и placeholder.',
                        'example_code' => "WP_Field::make([\n    'type' => 'select',\n    'options' => [\n        'key1' => 'Label 1',\n        'key2' => 'Label 2'\n    ]\n]);",
                    ], $this->get_field_data('select')),
                    [
                        'id' => 'multiselect_field',
                        'type' => 'multiselect',
                        'label' => 'Multiselect — Множественный выбор',
                        'options' => [
                            'red' => 'Красный',
                            'green' => 'Зелёный',
                            'blue' => 'Синий',
                        ],
                        'desc' => 'Выбор нескольких значений (Ctrl+Click)',
                        'example_code' => "WP_Field::make([\n    'type' => 'multiselect',\n    'options' => [...]\n]);",
                    ],
                    [
                        'id' => 'radio_field',
                        'type' => 'radio',
                        'label' => 'Radio — Радиокнопки',
                        'options' => [
                            'yes' => 'Да',
                            'no' => 'Нет',
                            'maybe' => 'Возможно',
                        ],
                        'desc' => 'Выбор одного значения из группы',
                        'example_code' => "WP_Field::make(['type' => 'radio']);",
                    ],
                    [
                        'id' => 'checkbox_field',
                        'type' => 'checkbox',
                        'label' => 'Checkbox — Одиночный чекбокс',
                        'desc' => 'Включить/выключить опцию',
                        'example_code' => "WP_Field::make(['type' => 'checkbox']);",
                    ],
                    [
                        'id' => 'checkbox_group_field',
                        'type' => 'checkbox_group',
                        'label' => 'Checkbox Group — Группа чекбоксов',
                        'options' => [
                            'feature1' => 'Функция 1',
                            'feature2' => 'Функция 2',
                            'feature3' => 'Функция 3',
                        ],
                        'desc' => 'Выбор нескольких значений',
                        'example_code' => "WP_Field::make([\n    'type' => 'checkbox_group',\n    'options' => [...]\n]);",
                    ],
                ],
            ],

            // Продвинутые поля
            [
                'title' => '3. Продвинутые поля (9)',
                'description' => 'Поля с использованием встроенных WP компонентов',
                'fields' => [
                    [
                        'id' => 'editor_field',
                        'type' => 'editor',
                        'label' => 'Editor — WordPress редактор',
                        'desc' => 'Встроенный WordPress TinyMCE редактор',
                        'example_desc' => 'Полнофункциональный WYSIWYG редактор',
                        'example_code' => "WP_Field::make(['type' => 'editor']);",
                    ],
                    array_merge([
                        'id' => 'media_field',
                        'type' => 'media',
                        'label' => 'Media — Медиафайл',
                        'desc' => 'Выбор файла из медиабиблиотеки с URL и превью',
                        'example_desc' => 'Выбор любого файла из медиабиблиотеки WordPress. Поддерживает preview, url поле, placeholder и фильтр по типу файлов (image, video, audio).',
                        'example_code' => "WP_Field::make([
    'type' => 'media',
    'preview' => true,  // показать превью
    'url' => true,      // показать URL поле
    'placeholder' => 'Не выбрано',
    'library' => 'image' // фильтр: image, video, audio
]);",
                    ], $this->get_field_data('media')),
                    [
                        'id' => 'media_no_preview',
                        'type' => 'media',
                        'label' => 'Media без превью',
                        'preview' => false,
                        'desc' => 'Только URL без превью',
                        'example_code' => "WP_Field::make(['type' => 'media', 'preview' => false]);",
                    ],
                    [
                        'id' => 'media_no_url',
                        'type' => 'media',
                        'label' => 'Media без URL',
                        'url' => false,
                        'desc' => 'Только кнопка загрузки',
                        'example_code' => "WP_Field::make(['type' => 'media', 'url' => false]);",
                    ],
                    [
                        'id' => 'media_image_only',
                        'type' => 'media',
                        'label' => 'Media только изображения',
                        'library' => 'image',
                        'desc' => 'Фильтр по типу: только изображения',
                        'example_code' => "WP_Field::make(['type' => 'media', 'library' => 'image']);",
                    ],
                    [
                        'id' => 'media_video_only',
                        'type' => 'media',
                        'label' => 'Media только видео',
                        'library' => 'video',
                        'desc' => 'Фильтр по типу: только видео',
                        'example_code' => "WP_Field::make(['type' => 'media', 'library' => 'video']);",
                    ],
                    [
                        'id' => 'media_audio_only',
                        'type' => 'media',
                        'label' => 'Media только аудио',
                        'library' => 'audio',
                        'desc' => 'Фильтр по типу: только аудио',
                        'example_code' => "WP_Field::make(['type' => 'media', 'library' => 'audio']);",
                    ],
                    [
                        'id' => 'image_field',
                        'type' => 'image',
                        'label' => 'Image — Изображение',
                        'desc' => 'Выбор изображения с превью',
                        'example_desc' => 'Показывает превью выбранного изображения с кнопкой удаления',
                        'example_code' => "WP_Field::make(['type' => 'image']);",
                    ],
                    [
                        'id' => 'image_no_preview',
                        'type' => 'image',
                        'label' => 'Image без превью',
                        'preview' => false,
                        'desc' => 'Только URL без превью',
                        'example_code' => "WP_Field::make(['type' => 'image', 'preview' => false]);",
                    ],
                    [
                        'id' => 'image_placeholder',
                        'type' => 'image',
                        'label' => 'Image с placeholder',
                        'placeholder' => 'http://',
                        'desc' => 'Кастомный placeholder для URL поля',
                        'example_code' => "WP_Field::make(['type' => 'image', 'placeholder' => 'http://']);",
                    ],
                    [
                        'id' => 'file_field',
                        'type' => 'file',
                        'label' => 'File — Файл',
                        'desc' => 'Выбор любого файла',
                        'example_code' => "WP_Field::make(['type' => 'file']);",
                    ],
                    [
                        'id' => 'file_image_only',
                        'type' => 'file',
                        'label' => 'File только изображения',
                        'library' => 'image',
                        'button_text' => 'Upload Image',
                        'desc' => 'Фильтр по типу: только изображения',
                        'example_code' => "WP_Field::make(['type' => 'file', 'library' => 'image']);",
                    ],
                    [
                        'id' => 'file_video_only',
                        'type' => 'file',
                        'label' => 'File только видео',
                        'library' => 'video',
                        'button_text' => 'Upload Video',
                        'desc' => 'Фильтр по типу: только видео',
                        'example_code' => "WP_Field::make(['type' => 'file', 'library' => 'video']);",
                    ],
                    [
                        'id' => 'file_audio_only',
                        'type' => 'file',
                        'label' => 'File только аудио',
                        'library' => 'audio',
                        'button_text' => 'Upload Audio',
                        'desc' => 'Фильтр по типу: только аудио',
                        'example_code' => "WP_Field::make(['type' => 'file', 'library' => 'audio']);",
                    ],
                    array_merge([
                        'id' => 'gallery_field',
                        'type' => 'gallery',
                        'label' => 'Gallery — Галерея',
                        'desc' => 'Выбор нескольких изображений с превью в виде плиток',
                        'example_desc' => 'Множественный выбор изображений с возможностью сортировки перетаскиванием. Отображает превью всех выбранных изображений с возможностью редактирования и удаления.',
                        'example_code' => "WP_Field::make(['type' => 'gallery']);",
                    ], $this->get_field_data('gallery')),
                    [
                        'id' => 'gallery_custom_buttons',
                        'type' => 'gallery',
                        'label' => 'Gallery с кастомными кнопками',
                        'add_button' => 'Add Image(s)',
                        'edit_button' => 'Edit Images',
                        'clear_button' => 'Remove Images',
                        'desc' => 'Кастомные тексты для кнопок управления',
                        'example_code' => "WP_Field::make([
    'type' => 'gallery',
    'add_button' => 'Add Image(s)',
    'edit_button' => 'Edit Images',
    'clear_button' => 'Remove Images'
]);",
                    ],
                    array_merge([
                        'id' => 'color_field',
                        'type' => 'color',
                        'label' => 'Color — Выбор цвета с прозрачностью',
                        'default' => '#0073aa',
                        'alpha' => true,
                        'desc' => 'WordPress Color Picker с поддержкой альфа-канала (прозрачность)',
                        'example_desc' => 'Встроенный WordPress color picker с поддержкой прозрачности (RGBA). Позволяет выбирать цвет визуально или вводить HEX/RGBA значение.',
                        'example_code' => "WP_Field::make([\n    'type' => 'color',\n    'alpha' => true, // включить прозрачность\n    'default' => 'rgba(0, 115, 170, 0.5)'\n]);\n\n// Без прозрачности:\nWP_Field::make([\n    'type' => 'color',\n    'alpha' => false\n]);",
                    ], $this->get_field_data('color')),
                    [
                        'id' => 'date_field',
                        'type' => 'date',
                        'label' => 'Date — Дата',
                        'desc' => 'Выбор даты (HTML5)',
                        'example_code' => "WP_Field::make(['type' => 'date']);",
                    ],
                    [
                        'id' => 'time_field',
                        'type' => 'time',
                        'label' => 'Time — Время',
                        'desc' => 'Выбор времени (HTML5)',
                        'example_code' => "WP_Field::make(['type' => 'time']);",
                    ],
                    [
                        'id' => 'datetime_field',
                        'type' => 'datetime-local',
                        'label' => 'DateTime — Дата и время',
                        'desc' => 'Выбор даты и времени (HTML5)',
                        'example_desc' => 'Использует нативный HTML5 datetime-local picker',
                        'example_code' => "WP_Field::make(['type' => 'datetime-local']);",
                    ],
                ],
            ],

            // Простые поля v2.1
            [
                'title' => '4. Простые поля v2.1 (9)',
                'description' => 'UI компоненты и информационные элементы',
                'fields' => [
                    array_merge([
                        'id' => 'switcher_field',
                        'type' => 'switcher',
                        'label' => 'Switcher — Переключатель',
                        'text_on' => 'Вкл',
                        'text_off' => 'Выкл',
                        'desc' => 'Красивый переключатель вкл/выкл',
                        'example_desc' => 'Современный UI переключатель с анимацией. Альтернатива обычному checkbox с более наглядным интерфейсом.',
                        'example_code' => "WP_Field::make([\n    'type' => 'switcher',\n    'text_on' => 'On',\n    'text_off' => 'Off'\n]);",
                    ], $this->get_field_data('switcher')),
                    [
                        'id' => 'spinner_field_1',
                        'type' => 'spinner',
                        'label' => 'Spinner — Счётчик',
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                        'desc' => 'max:100 | min:0 | step:1',
                        'example_code' => "WP_Field::make([\n    'type' => 'spinner',\n    'min' => 0,\n    'max' => 100,\n    'step' => 1\n]);",
                    ],
                    [
                        'id' => 'spinner_field_2',
                        'type' => 'spinner',
                        'label' => 'Spinner',
                        'min' => 100,
                        'max' => 200,
                        'step' => 10,
                        'desc' => 'max:200 | min:100 | step:10',
                        'example_code' => "WP_Field::make([\n    'type' => 'spinner',\n    'min' => 100,\n    'max' => 200,\n    'step' => 10\n]);",
                    ],
                    [
                        'id' => 'spinner_field_3',
                        'type' => 'spinner',
                        'label' => 'Spinner',
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.1,
                        'unit' => 'px',
                        'desc' => 'max:1 | min:0 | step:0.1 | unit:px',
                        'example_code' => "WP_Field::make([\n    'type' => 'spinner',\n    'min' => 0,\n    'max' => 1,\n    'step' => 0.1,\n    'unit' => 'px'\n]);",
                    ],
                    [
                        'id' => 'button_set_field',
                        'type' => 'button_set',
                        'label' => 'Button Set — Группа кнопок',
                        'options' => [
                            'left' => 'Слева',
                            'center' => 'По центру',
                            'right' => 'Справа',
                        ],
                        'desc' => 'Выбор через кнопки',
                        'example_code' => "WP_Field::make([\n    'type' => 'button_set',\n    'options' => [...]\n]);",
                    ],
                    [
                        'id' => 'slider_field',
                        'type' => 'slider',
                        'label' => 'Slider — Ползунок',
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                        'show_value' => true,
                        'desc' => 'Ползунок с отображением значения',
                        'example_code' => "WP_Field::make([\n    'type' => 'slider',\n    'show_value' => true\n]);",
                    ],
                    [
                        'id' => 'heading_field',
                        'type' => 'heading',
                        'label' => 'Heading — Заголовок',
                        'desc' => 'Информационный заголовок (не сохраняется)',
                        'example_code' => "WP_Field::make(['type' => 'heading']);",
                    ],
                    [
                        'id' => 'notice_field',
                        'type' => 'notice',
                        'label' => 'Это информационное уведомление',
                        'notice_type' => 'info',
                        'desc' => 'Типы: info, success, warning, error',
                        'example_code' => "WP_Field::make([\n    'type' => 'notice',\n    'notice_type' => 'info'\n]);",
                    ],
                    [
                        'id' => 'subheading_field',
                        'type' => 'subheading',
                        'label' => 'Subheading — Подзаголовок',
                        'desc' => 'Подзаголовок для группировки полей',
                        'example_code' => "WP_Field::make(['type' => 'subheading']);",
                    ],
                    [
                        'id' => 'content_field',
                        'type' => 'content',
                        'label' => 'Content — Произвольный контент',
                        'content' => '<p>Это произвольный HTML контент. Можно использовать для инструкций, описаний и т.д.</p>',
                        'desc' => 'Вывод произвольного HTML',
                        'example_code' => "WP_Field::make([\n    'type' => 'content',\n    'content' => '<p>HTML...</p>'\n]);",
                    ],
                    [
                        'id' => 'fieldset_field',
                        'type' => 'fieldset',
                        'label' => 'Fieldset — Группа полей',
                        'fields' => [
                            ['id' => 'fs_text', 'type' => 'text', 'label' => 'Текст внутри fieldset'],
                            ['id' => 'fs_checkbox', 'type' => 'checkbox', 'label' => 'Чекбокс внутри fieldset'],
                        ],
                        'desc' => 'Группировка полей в fieldset',
                        'example_code' => "WP_Field::make([\n    'type' => 'fieldset',\n    'fields' => [...]\n]);",
                    ],
                ],
            ],

            // Композитные поля
            [
                'title' => '5. Композитные поля (2)',
                'description' => 'Группировка и повторение полей',
                'fields' => [
                    [
                        'id' => 'group_field',
                        'type' => 'group',
                        'label' => 'Group — Группа полей',
                        'fields' => [
                            ['id' => 'group_name', 'type' => 'text', 'label' => 'Имя'],
                            ['id' => 'group_email', 'type' => 'email', 'label' => 'Email'],
                            ['id' => 'group_phone', 'type' => 'tel', 'label' => 'Телефон'],
                        ],
                        'desc' => 'Группировка связанных полей',
                        'example_code' => "WP_Field::make([\n    'type' => 'group',\n    'fields' => [...]\n]);",
                    ],
                    array_merge([
                        'id' => 'repeater_field',
                        'type' => 'repeater',
                        'label' => 'Repeater — Повторяемые поля',
                        'fields' => [
                            ['id' => 'rep_title', 'type' => 'text', 'label' => 'Заголовок'],
                            ['id' => 'rep_desc', 'type' => 'textarea', 'label' => 'Описание', 'rows' => 3],
                        ],
                        'desc' => 'Добавление/удаление групп полей',
                        'example_desc' => 'Позволяет создавать неограниченное количество наборов полей. Поддерживает сортировку перетаскиванием, удаление и добавление новых элементов.',
                        'example_code' => "WP_Field::make([\n    'type' => 'repeater',\n    'fields' => [...],\n    'button_text' => 'Добавить элемент'\n]);",
                    ], $this->get_field_data('repeater')),
                ],
            ],

            // Средней сложности v2.2
            [
                'title' => '6. Средней сложности v2.2 (10)',
                'description' => 'Составные поля для дизайна и типографики',
                'fields' => [
                    [
                        'id' => 'accordion_field',
                        'type' => 'accordion',
                        'label' => 'Accordion — Аккордеон',
                        'items' => [
                            [
                                'title' => 'Как оформить заказ?',
                                'content' => '<p>Выберите товары, добавьте их в корзину и перейдите к оформлению заказа.</p>',
                            ],
                            [
                                'title' => 'Какие способы доставки?',
                                'content' => '<p>Мы предлагаем доставку курьером, почтой и самовывоз.</p>',
                            ],
                            [
                                'title' => 'Как вернуть товар?',
                                'content' => '<p>Товар можно вернуть в течение 14 дней с момента покупки.</p>',
                            ],
                        ],
                        'desc' => 'Свёртываемые секции с контентом - нажмите для открытия/закрытия',
                        'example_code' => "WP_Field::make([\n    'type' => 'accordion',\n    'label' => 'FAQ',\n    'items' => [\n        ['title' => 'Вопрос 1', 'content' => 'Ответ 1'],\n        ['title' => 'Вопрос 2', 'content' => 'Ответ 2']\n    ]\n]);",
                        'advanced_examples' => [
                            [
                                'title' => 'Аккордеон с полями',
                                'desc' => 'Аккордеон с редактируемыми полями внутри разделов',
                                'code' => "WP_Field::make([\n    'id' => 'settings_accordion',\n    'type' => 'accordion',\n    'label' => 'Настройки',\n    'items' => [\n        [\n            'title' => 'Основные',\n            'open' => true,\n            'fields' => [\n                ['id' => 'site_name', 'type' => 'text', 'label' => 'Название сайта'],\n                ['id' => 'site_desc', 'type' => 'textarea', 'label' => 'Описание']\n            ]\n        ],\n        [\n            'title' => 'Дизайн',\n            'fields' => [\n                ['id' => 'primary_color', 'type' => 'color', 'label' => 'Основной цвет'],\n                ['id' => 'secondary_color', 'type' => 'color', 'label' => 'Дополнительный цвет']\n            ]\n        ]\n    ]\n]);",
                            ],
                            [
                                'title' => 'С дефолтным открытым разделом',
                                'desc' => 'Указание, какой раздел открыт по умолчанию',
                                'code' => "WP_Field::make([\n    'id' => 'default_accordion',\n    'type' => 'accordion',\n    'label' => 'Аккордеон с дефолтом',\n    'items' => [\n        ['title' => 'Раздел 1', 'content' => 'Содержимое...'],\n        ['title' => 'Раздел 2', 'content' => 'Содержимое...', 'open' => true],\n        ['title' => 'Раздел 3', 'content' => 'Содержимое...']\n    ]\n]);",
                            ],
                            [
                                'title' => 'Кастомные иконки',
                                'desc' => 'Использование собственных иконок для аккордеона',
                                'code' => "WP_Field::make([\n    'id' => 'custom_accordion',\n    'type' => 'accordion',\n    'label' => 'Кастомный аккордеон',\n    'open_icon' => '−',\n    'close_icon' => '+',\n    'items' => [\n        ['title' => 'Раздел 1', 'content' => 'Содержимое...'],\n        ['title' => 'Раздел 2', 'content' => 'Содержимое...']\n    ]\n]);",
                            ],
                        ],
                    ],
                    [
                        'id' => 'tabbed_field',
                        'type' => 'tabbed',
                        'label' => 'Tabbed — Вкладки',
                        'tabs' => [
                            [
                                'title' => 'Вкладка 1',
                                'icon' => '⚙️',
                                'content' => '<p>Содержимое первой вкладки</p>',
                                'fields' => [
                                    ['id' => 'tab_text1', 'type' => 'text', 'label' => 'Текст 1'],
                                ],
                            ],
                            [
                                'title' => 'Вкладка 2',
                                'icon' => '🎨',
                                'content' => '<p>Содержимое второй вкладки</p>',
                                'fields' => [
                                    ['id' => 'tab_text2', 'type' => 'text', 'label' => 'Текст 2'],
                                ],
                            ],
                        ],
                        'desc' => 'Организация полей во вкладках',
                        'example_code' => "WP_Field::make([\n    'type' => 'tabbed',\n    'tabs' => [...]\n]);",
                    ],
                    [
                        'id' => 'typography_field',
                        'type' => 'typography',
                        'label' => 'Typography — Типографика',
                        'desc' => 'Настройка шрифта: семейство, размер, вес, высота строки, выравнивание, трансформация, цвет',
                        'example_code' => "WP_Field::make(['type' => 'typography']);",
                    ],
                    array_merge([
                        'id' => 'spacing_field',
                        'type' => 'spacing',
                        'label' => 'Spacing — Отступы',
                        'desc' => 'Настройка padding и margin (top, right, bottom, left)',
                        'example_desc' => 'Визуальный редактор отступов с полями для всех 4 сторон и выбором единиц измерения (px, em, rem, %).',
                        'example_code' => "WP_Field::make(['type' => 'spacing']);",
                    ], $this->get_field_data('spacing')),
                    [
                        'id' => 'dimensions_field',
                        'type' => 'dimensions',
                        'label' => 'Dimensions — Размеры',
                        'desc' => 'Настройка width и height с единицами измерения',
                        'example_code' => "WP_Field::make(['type' => 'dimensions']);",
                    ],
                    [
                        'id' => 'border_field',
                        'type' => 'border',
                        'label' => 'Border — Рамка',
                        'desc' => 'Настройка границ: стиль, ширина, цвет, радиус',
                        'example_code' => "WP_Field::make(['type' => 'border']);",
                    ],
                    [
                        'id' => 'background_field',
                        'type' => 'background',
                        'label' => 'Background — Фон',
                        'desc' => 'Настройка фона: цвет, изображение, позиция, размер, повтор',
                        'example_code' => "WP_Field::make(['type' => 'background']);",
                    ],
                    [
                        'id' => 'link_color_field',
                        'type' => 'link_color',
                        'label' => 'Link Color — Цвета ссылок',
                        'desc' => 'Настройка цветов для состояний ссылки: normal, hover, active, visited',
                        'example_code' => "WP_Field::make(['type' => 'link_color']);",
                    ],
                    [
                        'id' => 'color_group_field',
                        'type' => 'color_group',
                        'label' => 'Color Group — Группа цветов',
                        'colors' => [
                            'primary' => 'Основной',
                            'secondary' => 'Вторичный',
                            'accent' => 'Акцент',
                        ],
                        'desc' => 'Группа связанных цветов',
                        'example_code' => "WP_Field::make([\n    'type' => 'color_group',\n    'colors' => [...]\n]);",
                    ],
                    array_merge([
                        'id' => 'image_select_field',
                        'type' => 'image_select',
                        'label' => 'Image Select — Выбор с изображениями',
                        'options' => [
                            'layout1' => [
                                'src' => plugins_url('placeholder.svg', __FILE__),
                                'label' => 'Макет 1',
                            ],
                            'layout2' => [
                                'src' => plugins_url('placeholder.svg', __FILE__),
                                'label' => 'Макет 2',
                            ],
                            'layout3' => [
                                'src' => plugins_url('placeholder.svg', __FILE__),
                                'label' => 'Макет 3',
                            ],
                        ],
                        'desc' => 'Визуальный выбор через изображения (например, макеты)',
                        'example_desc' => 'Визуальный выбор через изображения с превью. Поддерживает обычные URL изображений и inline SVG через data URI.',
                        'example_code' => "WP_Field::make([\n    'type' => 'image_select',\n    'options' => [\n        'layout1' => [\n            'src' => 'url/to/image.jpg',\n            'label' => 'Макет 1'\n        ],\n        // или просто URL:\n        'layout2' => 'url/to/image2.jpg'\n    ]\n]);",
                    ], $this->get_field_data('image_select')),
                ],
            ],

            // Высокой сложности v2.3
            [
                'title' => '7. Высокой сложности v2.3 (8)',
                'description' => 'Специализированные поля с продвинутым функционалом',
                'fields' => [
                    [
                        'id' => 'code_editor_field',
                        'type' => 'code_editor',
                        'label' => 'Code Editor — Редактор кода',
                        'mode' => 'css',
                        'height' => '200px',
                        'desc' => 'Редактор с подсветкой синтаксиса (CSS/JS/PHP/HTML)',
                        'example_desc' => 'Использует встроенный WordPress CodeMirror',
                        'example_code' => "WP_Field::make([\n    'type' => 'code_editor',\n    'mode' => 'css',\n    'height' => '300px'\n]);",
                    ],
                    [
                        'id' => 'icon_field',
                        'type' => 'icon',
                        'label' => 'Icon — Выбор иконки',
                        'library' => 'dashicons',
                        'desc' => 'Выбор иконки из библиотеки Dashicons',
                        'example_desc' => 'Modal с поиском по 50+ иконкам',
                        'example_code' => "WP_Field::make([\n    'type' => 'icon',\n    'library' => 'dashicons'\n]);",
                        'advanced_examples' => [
                            [
                                'title' => 'Кастомный набор иконок',
                                'desc' => 'Использование собственного набора иконок (например, Font Awesome)',
                                'code' => "WP_Field::make([\n    'id' => 'custom_icon',\n    'type' => 'icon',\n    'label' => 'Выберите иконку',\n    'library' => 'fa',\n    'icons' => [\n        'fa-home',\n        'fa-user',\n        'fa-cog',\n        'fa-heart',\n        'fa-star',\n        'fa-check',\n        'fa-times',\n        'fa-search'\n    ]\n]);",
                            ],
                            [
                                'title' => 'Регистрация библиотеки через фильтр',
                                'desc' => 'Добавление собственной библиотеки иконок через wp_field_icon_library фильтр',
                                'code' => "// В functions.php:\nadd_filter('wp_field_icon_library', function(\$icons, \$library) {\n    if (\$library === 'custom') {\n        return [\n            'custom-icon-1',\n            'custom-icon-2',\n            'custom-icon-3',\n            'custom-icon-home',\n            'custom-icon-user',\n            'custom-icon-settings'\n        ];\n    }\n    return \$icons;\n}, 10, 2);\n\n// Использование:\nWP_Field::make([\n    'id' => 'my_icon',\n    'type' => 'icon',\n    'label' => 'Выберите иконку',\n    'library' => 'custom'\n]);",
                            ],
                            [
                                'title' => 'Получение выбранной иконки',
                                'desc' => 'Использование выбранной иконки на фронтенде',
                                'code' => "\$icon = get_option('icon_field');\nif (\$icon) {\n    echo '<i class=\"dashicons ' . esc_attr(\$icon) . '\"></i>';\n    echo ' ' . esc_html(\$icon);\n}",
                            ],
                        ],
                    ],
                    [
                        'id' => 'sortable_field',
                        'type' => 'sortable',
                        'label' => 'Sortable — Сортируемый список',
                        'options' => [
                            'item1' => 'Элемент 1',
                            'item2' => 'Элемент 2',
                            'item3' => 'Элемент 3',
                            'item4' => 'Элемент 4',
                        ],
                        'desc' => 'Drag & Drop сортировка элементов',
                        'example_desc' => 'Перетащите элементы для изменения порядка',
                        'example_code' => "WP_Field::make([\n    'type' => 'sortable',\n    'options' => [...]\n]);",
                    ],
                    [
                        'id' => 'palette_field',
                        'type' => 'palette',
                        'label' => 'Palette — Палитра цветов',
                        'palettes' => [
                            'blue' => ['#0073aa', '#005a87', '#003d82'],
                            'green' => ['#28a745', '#218838', '#1e7e34'],
                            'red' => ['#dc3545', '#c82333', '#bd2130'],
                        ],
                        'desc' => 'Визуальный выбор цветовой схемы',
                        'example_code' => "WP_Field::make([\n    'type' => 'palette',\n    'palettes' => [\n        'blue' => ['#0073aa', '#005a87']\n    ]\n]);",
                    ],
                    [
                        'id' => 'link_field',
                        'type' => 'link',
                        'label' => 'Link — Поле ссылки',
                        'desc' => 'URL + текст + target (_self/_blank)',
                        'example_desc' => 'Комплексное поле для настройки ссылки',
                        'example_code' => "WP_Field::make(['type' => 'link']);\n// Возвращает:\n// ['url' => '...', 'text' => '...', 'target' => '_blank']",
                    ],
                    [
                        'id' => 'map_field',
                        'type' => 'map',
                        'label' => 'Map — Карта',
                        'zoom' => 12,
                        'desc' => 'Выбор координат на карте (требует Google Maps API)',
                        'example_desc' => 'Интерактивная карта для выбора местоположения',
                        'example_code' => "WP_Field::make([\n    'type' => 'map',\n    'zoom' => 12,\n    'center' => [55.7558, 37.6173]\n]);",
                    ],
                    [
                        'id' => 'sorter_field',
                        'type' => 'sorter',
                        'label' => 'Sorter — Сортировщик',
                        'enabled' => [
                            'item1' => 'Включенный элемент 1',
                            'item2' => 'Включенный элемент 2',
                        ],
                        'disabled' => [
                            'item3' => 'Отключенный элемент 3',
                            'item4' => 'Отключенный элемент 4',
                        ],
                        'desc' => 'Две колонки: включено/отключено с сортировкой',
                        'example_code' => "WP_Field::make([\n    'type' => 'sorter',\n    'enabled' => [...],\n    'disabled' => [...]\n]);",
                    ],
                    [
                        'id' => 'backup_field',
                        'type' => 'backup',
                        'label' => 'Backup — Резервное копирование',
                        'desc' => 'Экспорт/импорт настроек в JSON',
                        'example_desc' => 'Кнопки для экспорта и импорта всех настроек',
                        'example_code' => "WP_Field::make(['type' => 'backup']);",
                    ],
                ],
            ],

            // Система зависимостей
            [
                'title' => '8. Система зависимостей',
                'description' => '12 операторов сравнения, AND/OR логика',
                'fields' => [
                    [
                        'id' => 'enable_feature',
                        'type' => 'switcher',
                        'label' => 'Включить функцию',
                        'desc' => 'Включите для отображения зависимых полей',
                        'example_desc' => 'Управляющее поле для демонстрации зависимостей',
                    ],
                    [
                        'id' => 'feature_text',
                        'type' => 'text',
                        'label' => 'Текст функции (зависимое поле)',
                        'placeholder' => 'Это поле видно только когда функция включена',
                        'dependency' => [
                            ['enable_feature', '==', '1'],
                        ],
                        'desc' => 'Показывается только если switcher включен',
                        'example_code' => "WP_Field::make([\n    'id' => 'dependent_field',\n    'type' => 'text',\n    'dependency' => [\n        ['enable_feature', '==', '1']\n    ]\n]);",
                    ],
                    [
                        'id' => 'delivery_type',
                        'type' => 'select',
                        'label' => 'Тип доставки',
                        'options' => [
                            'courier' => 'Курьер',
                            'pickup' => 'Самовывоз',
                        ],
                        'desc' => 'Выберите "Курьер" для отображения адреса',
                    ],
                    [
                        'id' => 'delivery_address',
                        'type' => 'text',
                        'label' => 'Адрес доставки (зависимое)',
                        'placeholder' => 'Введите адрес...',
                        'dependency' => [
                            ['delivery_type', '==', 'courier'],
                        ],
                        'desc' => 'Показывается только для курьерской доставки',
                        'example_code' => "// Операторы: ==, !=, >, >=, <, <=, in, not_in,\n// contains, not_contains, empty, not_empty\n\n// AND логика:\n'dependency' => [\n    ['field1', '==', 'value1'],\n    ['field2', '!=', 'value2'],\n    'relation' => 'AND'\n]\n\n// OR логика:\n'dependency' => [\n    ['field1', '==', 'value1'],\n    ['field2', '==', 'value2'],\n    'relation' => 'OR'\n]",
                    ],
                ],
            ],
        ];
    }
}

// Инициализация
new WP_Field_Examples;
