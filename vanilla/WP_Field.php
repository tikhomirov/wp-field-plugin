<?php

declare(strict_types=1);

/**
 * Legacy API for WP_Field.
 *
 * @deprecated 3.0.0 Use modern \WpField\Field\Field instead.
 */
#[AllowDynamicProperties]
class WP_Field
{
    public const file = __FILE__;

    /**
     * Типы хранилищ значений
     *
     * Поддерживаемые типы:
     * - post: post meta (get_post_meta)
     * - options: wp_options (get_option)
     * - term: term meta (get_term_meta)
     * - user: user meta (get_user_meta)
     * - comment: comment meta (get_comment_meta)
     * - nav_menu_item: nav menu item meta (get_post_meta)
     * - site_option: network/multisite options (get_site_option)
     * - attachment: attachment meta (get_post_meta)
     * - custom_table: пользовательские таблицы БД
     *
     * Можно расширить через фильтр wp_field_storage_types
     */
    public static array $allowed_storage_types = [
        'post',          // post meta
        'options',       // wp_options
        'term',          // term meta
        'user',          // user meta
        'comment',       // comment meta
        'nav_menu_item', // nav menu item meta
        'site_option',   // network/multisite options
        'attachment',    // attachment meta
        'custom_table',  // пользовательские таблицы
    ];

    /** Реестр типов полей: type => [renderer_method, defaults] */
    private static array $field_types = [];

    /** Флаг однократной инициализации assets */
    private static bool $assets_enqueued = false;

    /**
     * @var array{ id: string, type: string, label: string, class: string, custom_attributes: array }
     */
    public $field;

    /** @var string|null - тип хранилища (post|options|term|user|comment) */
    public $storage_type;

    /** @var int|string|null - ID объекта (post|term|user|comment) для получения значения */
    public $storage_id;

    public string $file;

    public string $url;

    public string $ver = '3.0.0';

    public function __construct($field, string $storage_type = 'post', $storage_id = null)
    {
        self::init_field_types();

        $this->storage_type = $storage_type;
        $this->storage_id = $storage_id;

        if (! isset($this->storage_id) && $storage_type === 'post') {
            $this->storage_id = function_exists('get_the_ID') ? get_the_ID() : 0;
        }

        // Нормализация алиасов полей
        $field['value'] = $field['value'] ?? $field['val'] ?? null;
        $field['label'] = $field['title'] ?? $field['label'] ?? null;
        $field['custom_attributes'] = $field['custom_attributes'] ?? $field['attributes'] ?? $field['attr'] ?? $field['atts'] ?? null;

        $this->field = $this->validate_field_data($field);

        $this->file = defined('WP_FIELD_PLUGIN_FILE') ? WP_FIELD_PLUGIN_FILE : self::file;
        $this->url = defined('WP_FIELD_PLUGIN_URL') ? WP_FIELD_PLUGIN_URL : (function_exists('plugin_dir_url') ? plugin_dir_url($this->file) : '/wp-content/plugins/wp-field-plugin/');
        $this->ver = defined('WP_DEBUG') && WP_DEBUG ? (string) time() : $this->ver;

        // Однократная инициализация assets
        $this->maybe_enqueue_assets();
    }

    /**
     * Инициализация реестра типов полей (однократно)
     */
    public static function init_field_types(): void
    {
        if (! empty(self::$field_types)) {
            return;
        }

        // Базовые поля
        self::$field_types['text'] = ['render_text', ['type' => 'text']];
        self::$field_types['password'] = ['render_text', ['type' => 'password']];
        self::$field_types['email'] = ['render_text', ['type' => 'email']];
        self::$field_types['url'] = ['render_text', ['type' => 'url']];
        self::$field_types['tel'] = ['render_text', ['type' => 'tel']];
        self::$field_types['number'] = ['render_number', ['type' => 'number']];
        self::$field_types['range'] = ['render_text', ['type' => 'range']];
        self::$field_types['hidden'] = ['render_text', ['type' => 'hidden']];
        self::$field_types['textarea'] = ['render_textarea', []];

        // Выбор
        self::$field_types['select'] = ['render_select', []];
        self::$field_types['multiselect'] = ['render_select', ['multiple' => true]];
        self::$field_types['radio'] = ['render_radio', []];
        self::$field_types['checkbox'] = ['render_checkbox', []];
        self::$field_types['checkbox_group'] = ['render_checkbox_group', []];

        // Продвинутые
        self::$field_types['editor'] = ['render_editor', []];
        self::$field_types['media'] = ['render_media', []];
        self::$field_types['image'] = ['render_image', []];
        self::$field_types['file'] = ['render_file', []];
        self::$field_types['gallery'] = ['render_gallery', []];
        self::$field_types['color'] = ['render_color', []];
        self::$field_types['date'] = ['render_date', ['type' => 'date']];
        self::$field_types['time'] = ['render_date', ['type' => 'time']];
        self::$field_types['datetime'] = ['render_date', ['type' => 'datetime-local']];

        // Композитные
        self::$field_types['group'] = ['render_group', []];
        self::$field_types['repeater'] = ['render_repeater', []];

        // Простые типы (v2.1)
        self::$field_types['switcher'] = ['render_switcher', ['text_on' => 'On', 'text_off' => 'Off']];
        self::$field_types['spinner'] = ['render_spinner', []];
        self::$field_types['button_set'] = ['render_button_set', []];
        self::$field_types['slider'] = ['render_slider', []];
        self::$field_types['heading'] = ['render_heading', []];
        self::$field_types['subheading'] = ['render_subheading', []];
        self::$field_types['notice'] = ['render_notice', []];
        self::$field_types['content'] = ['render_content', []];
        self::$field_types['fieldset'] = ['render_fieldset', []];

        // Средней сложности (v2.2)
        self::$field_types['accordion'] = ['render_accordion', []];
        self::$field_types['tabbed'] = ['render_tabbed', []];
        self::$field_types['typography'] = ['render_typography', []];
        self::$field_types['spacing'] = ['render_spacing', []];
        self::$field_types['dimensions'] = ['render_dimensions', []];
        self::$field_types['border'] = ['render_border', []];
        self::$field_types['background'] = ['render_background', []];
        self::$field_types['link_color'] = ['render_link_color', []];
        self::$field_types['color_group'] = ['render_color_group', []];
        self::$field_types['image_select'] = ['render_image_select', []];

        // Высокой сложности (v2.3)
        self::$field_types['code_editor'] = ['render_code_editor', ['mode' => 'css']];
        self::$field_types['icon'] = ['render_icon', ['library' => 'dashicons']];
        self::$field_types['map'] = ['render_map', ['zoom' => 12]];
        self::$field_types['sortable'] = ['render_sortable', []];
        self::$field_types['sorter'] = ['render_sorter', []];
        self::$field_types['palette'] = ['render_palette', []];
        self::$field_types['link'] = ['render_link', []];
        self::$field_types['backup'] = ['render_backup', []];

        // Алиасы для обратной совместимости
        self::$field_types['date_time'] = self::$field_types['datetime'];
        self::$field_types['datetime-local'] = self::$field_types['datetime'];
        self::$field_types['image_picker'] = ['render_image_picker', []];
        self::$field_types['imagepicker'] = self::$field_types['image_picker'];
    }

    /**
     * Validate field data before rendering
     */
    private function validate_field_data($field)
    {
        if (is_object($field)) {
            $field = get_object_vars($field);
        }

        if (is_string($field)) {
            $str = $field;
            $field = [];
            parse_str($str, $field);
        }

        if (empty($field) || ! isset($field['id'], $field['type']) || ! is_array($field)) {
            trigger_error('!!! Incorrect field data '.print_r($field, true));

            return 'Incorrect field data';
        }

        // label required for most fields but not all (e.g. fieldset has legend)
        if (! isset($field['label']) && ! in_array($field['type'], ['fieldset', 'content', 'notice', 'heading', 'subheading'], true)) {
            trigger_error('!!! Incorrect field data (missing label) '.print_r($field, true));

            return 'Incorrect field data';
        }

        return $field;
    }

    /**
     * Однократная инициализация assets
     */
    private function maybe_enqueue_assets(): void
    {
        if (self::$assets_enqueued || (function_exists('is_admin') && ! is_admin())) {
            return;
        }

        self::$assets_enqueued = true;

        if (function_exists('did_action') && did_action('admin_enqueue_scripts') > 0) {
            // Если конструктор вызван во время render метабокса, action уже прошел.
            // В этом случае подключаем assets сразу.
            $this->enqueue_assets();

            return;
        }

        if (function_exists('add_action')) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        }
    }

    /**
     * Подключение assets (JS/CSS)
     */
    public function enqueue_assets(): void
    {
        // WP встроенные скрипты
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('jquery-ui');

        // wp-color-picker для color полей
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');

        // wp-media для media полей
        wp_enqueue_media();

        // Наш JS для зависимостей и инициализации
        wp_enqueue_script(
            'wp-field-main',
            $this->url.'vanilla/assets/js/wp-field.js',
            ['jquery'],
            $this->ver,
            true,
        );

        // Наш CSS
        wp_enqueue_style(
            'wp-field-main',
            $this->url.'vanilla/assets/css/wp-field.css',
            [],
            $this->ver,
        );

        // Локализация
        wp_localize_script('wp-field-main', 'wpFieldConfig', [
            'nonce' => wp_create_nonce('wp_field_nonce'),
        ]);
    }

    /**
     * Рендер базового текстового поля (text, password, email, url, tel, range, hidden)
     */
    private function render_text(array $field): void
    {
        $this->render_label($field);

        printf(
            '<input class="%s" id="%s" name="%s" type="%s" value="%s" placeholder="%s" %s %s>',
            esc_attr($field['class'] ?? 'regular-text'),
            esc_attr($field['id']),
            esc_attr($field['name'] ?? $field['id']),
            esc_attr($field['type'] ?? 'text'),
            esc_attr($this->get_field_value($field)),
            esc_attr($field['placeholder'] ?? ''),
            $this->get_field_attributes($field),
            $this->get_readonly_disabled($field),
        );

        $this->render_description($field);
    }

    /**
     * Рендер label
     */
    private function render_label(array $field): void
    {
        if (! empty($field['label'])) {
            printf(
                '<label for="%s" class="%s">%s</label>',
                esc_attr($field['id']),
                esc_attr($field['label-class'] ?? 'input-label'),
                esc_html($field['label']),
            );
        }
    }

    /**
     * Получить значение поля
     */
    private function get_field_value(array $field)
    {
        $value = $field['value'] ?? null;

        // Если значение не задано, пытаемся получить из БД
        if ($value === null) {
            $value = $this->get_value($field['id'], $this->storage_id);
        }

        // Если всё ещё null, используем default
        if ($value === null) {
            $value = $field['default'] ?? '';
        }

        return $value;
    }

    /**
     * Получить значение поля из БД по типу хранилища
     *
     * @param  string  $key  - option|meta name
     * @param  int  $id  - object ID
     */
    public function get_value(string $key, $id = null)
    {
        $id = function_exists('absint') ? absint($id) : (int) $id;

        // Позволяем расширить типы хранилищ через фильтр
        if (function_exists('apply_filters')) {
            $value = apply_filters('wp_field_get_value', null, $this->storage_type, $key, $id, $this->field);
            if ($value !== null) {
                return $value;
            }
        }

        switch ($this->storage_type) {
            case 'options':
                return function_exists('get_option') ? get_option($key, null) : null;

            case 'term':
                return function_exists('get_term_meta') ? get_term_meta($id, $key, true) : null;

            case 'user':
                return function_exists('get_user_meta') ? get_user_meta($id, $key, true) : null;

            case 'comment':
                return function_exists('get_comment_meta') ? get_comment_meta($id, $key, true) : null;

            case 'nav_menu_item':
                return function_exists('get_post_meta') ? get_post_meta($id, $key, true) : null; // nav_menu_item - это посты

            case 'site_option':
                return function_exists('get_site_option') ? get_site_option($key, null) : null;

            case 'attachment':
                return function_exists('get_post_meta') ? get_post_meta($id, $key, true) : null; // attachment - это посты

            case 'custom_table':
                return $this->get_custom_table_value($key, $id);

            case 'post':
            default:
                $post_id = $id;
                if (! $post_id && function_exists('get_the_ID')) {
                    $post_id = get_the_ID();
                }

                return function_exists('get_post_meta') ? get_post_meta($post_id, $key, true) : null;
        }
    }

    /**
     * Получить значение из пользовательской таблицы
     *
     * @return mixed
     */
    private function get_custom_table_value(string $key, int $id)
    {
        global $wpdb;

        $table_name = $this->field['table'] ?? $wpdb->prefix.'custom_meta';
        $object_id_column = $this->field['object_id_column'] ?? 'object_id';
        $meta_key_column = $this->field['meta_key_column'] ?? 'meta_key';
        $meta_value_column = $this->field['meta_value_column'] ?? 'meta_value';

        return $wpdb->get_var($wpdb->prepare(
            "SELECT {$meta_value_column} FROM {$table_name} WHERE {$object_id_column} = %d AND {$meta_key_column} = %s",
            $id,
            $key,
        ));
    }

    /**
     * Получить HTML атрибутов поля
     */
    private function get_field_attributes(array $field): string
    {
        $attributes = [];

        if (! empty($field['custom_attributes']) && is_array($field['custom_attributes'])) {
            foreach ($field['custom_attributes'] as $attr => $val) {
                if (is_array($val) || is_object($val)) {
                    $val = function_exists('wp_json_encode') ? wp_json_encode($val) : json_encode($val);
                }

                if ($val === null || $val === false) {
                    continue;
                }

                $attributes[] = esc_attr($attr).'="'.esc_attr((string) $val).'"';
            }
        }

        return implode(' ', $attributes);
    }

    /**
     * Получить readonly/disabled атрибуты
     */
    private function get_readonly_disabled(array $field): string
    {
        $attrs = [];

        if (! empty($field['readonly'])) {
            $attrs[] = 'readonly';
        }

        if (! empty($field['disabled'])) {
            $attrs[] = 'disabled';
        }

        return implode(' ', $attrs);
    }

    /**
     * Рендер description
     */
    private function render_description(array $field): void
    {
        if (! empty($field['desc'])) {
            printf(
                '<p class="description">%s</p>',
                function_exists('wp_kses_post') ? wp_kses_post($field['desc']) : $field['desc'],
            );
        }
    }

    /**
     * Рендер числового поля
     */
    private function render_number(array $field): void
    {
        $this->render_label($field);

        printf(
            '<input class="%s" id="%s" name="%s" type="number" value="%s" %s %s %s %s>',
            esc_attr($field['class'] ?? 'regular-text'),
            esc_attr($field['id']),
            esc_attr($field['name'] ?? $field['id']),
            esc_attr((string) $this->get_field_value($field)),
            isset($field['min']) ? 'min="'.esc_attr((string) $field['min']).'"' : '',
            isset($field['max']) ? 'max="'.esc_attr((string) $field['max']).'"' : '',
            isset($field['step']) ? 'step="'.esc_attr((string) $field['step']).'"' : '',
            $this->get_field_attributes($field),
            $this->get_readonly_disabled($field),
        );

        $this->render_description($field);
    }

    /**
     * Рендер textarea
     */
    private function render_textarea(array $field): void
    {
        $this->render_label($field);

        printf(
            '<textarea class="%s" id="%s" name="%s" rows="%d" %s %s>%s</textarea>',
            esc_attr($field['class'] ?? 'regular-text'),
            esc_attr($field['id']),
            esc_attr($field['name'] ?? $field['id']),
            function_exists('absint') ? absint($field['rows'] ?? 5) : (int) ($field['rows'] ?? 5),
            $this->get_field_attributes($field),
            $this->get_readonly_disabled($field),
            function_exists('esc_textarea') ? esc_textarea($this->get_field_value($field)) : htmlspecialchars($this->get_field_value($field), ENT_QUOTES),
        );

        $this->render_description($field);
    }

    /**
     * Рендер select
     */
    private function render_select(array $field): void
    {
        $this->render_label($field);

        $multiple = ! empty($field['multiple']) ? ' multiple="multiple"' : '';

        // Для multiselect добавляем [] к имени, чтобы PHP получил массив значений
        $name = $field['name'] ?? $field['id'];
        if (! empty($field['multiple']) && ! str_ends_with($name, '[]')) {
            $name .= '[]';
        }

        printf(
            '<select class="%s" id="%s" name="%s"%s %s>%s</select>',
            esc_attr($field['class'] ?? ''),
            esc_attr($field['id']),
            esc_attr($name),
            $multiple,
            $this->get_field_attributes($field),
            $this->render_select_options($field),
        );

        $this->render_description($field);
    }

    /**
     * Рендер опций select
     */
    private function render_select_options(array $field): string
    {
        $options = $field['options'] ?? [];
        $value = $this->get_field_value($field);

        if (is_string($options)) {
            $options = array_filter(array_map('trim', explode("\n", $options)));
        }

        $output = [];

        foreach ($options as $opt_value => $opt_label) {
            // Поддержка parse_options: "label:value"
            if (! empty($field['parse_options']) && is_string($opt_label) && str_contains($opt_label, ':')) {
                [$opt_label, $opt_value] = array_map('trim', explode(':', $opt_label, 2));
            }

            $selected = $this->is_value_selected($value, $opt_value) ? ' selected' : '';

            $output[] = sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($opt_value),
                $selected,
                esc_html($opt_label),
            );
        }

        return implode('', $output);
    }

    /**
     * Проверить, выбрано ли значение
     */
    private function is_value_selected($current_value, $compare_value): bool
    {
        if (is_array($current_value)) {
            // Для массива проверяем и строгое, и нестрогое сравнение (для совместимости с числовыми ключами)
            return in_array($compare_value, $current_value, true) || in_array((string) $compare_value, $current_value, true);
        }

        // Приводим к строке для корректного сравнения (избегаем 0 == '' = true)
        return (string) $current_value === (string) $compare_value;
    }

    /**
     * Рендер radio
     */
    private function render_radio(array $field): void
    {
        $this->render_label($field);

        $options = $field['options'] ?? [];
        $value = $this->get_field_value($field);

        if (is_string($options)) {
            $options = array_filter(array_map('trim', explode("\n", $options)));
        }

        $output = [];
        $i = 0;

        foreach ($options as $opt_value => $opt_label) {
            $checked = $this->is_value_selected($value, $opt_value) ? ' checked' : '';

            $output[] = sprintf(
                '<label><input type="radio" name="%s" value="%s" id="%s-%d"%s> %s</label>',
                esc_attr($field['name'] ?? $field['id']),
                esc_attr($opt_value),
                esc_attr($field['id']),
                $i,
                $checked,
                esc_html($opt_label),
            );

            $i++;
        }

        echo '<div class="wp-field-radio-group">'.implode('', $output).'</div>';

        $this->render_description($field);
    }

    /**
     * Рендер одиночного checkbox
     */
    private function render_checkbox(array $field): void
    {
        $value = $this->get_field_value($field);
        $checked = $this->is_checked($value) ? ' checked' : '';

        printf(
            '<input type="checkbox" id="%s" name="%s" value="1"%s %s>',
            esc_attr($field['id']),
            esc_attr($field['name'] ?? $field['id']),
            $checked,
            $this->get_field_attributes($field),
        );

        $this->render_label($field);
        $this->render_description($field);
    }

    /**
     * Проверить, отмечен ли checkbox
     */
    private function is_checked($value): bool
    {
        return $value === '1' || $value === 'on' || $value === 'yes' || $value === true;
    }

    /**
     * Рендер группы checkbox'ов
     */
    private function render_checkbox_group(array $field): void
    {
        $this->render_label($field);

        $options = $field['options'] ?? [];
        $values = (array) $this->get_field_value($field);

        if (is_string($options)) {
            $options = array_filter(array_map('trim', explode("\n", $options)));
        }

        $output = [];
        $i = 0;

        foreach ($options as $opt_value => $opt_label) {
            $checked = in_array($opt_value, $values, true) ? ' checked' : '';

            $output[] = sprintf(
                '<label><input type="checkbox" name="%s[]" value="%s" id="%s-%d"%s> %s</label>',
                esc_attr($field['name'] ?? $field['id']),
                esc_attr($opt_value),
                esc_attr($field['id']),
                $i,
                $checked,
                esc_html($opt_label),
            );

            $i++;
        }

        echo '<div class="wp-field-checkbox-group">'.implode('', $output).'</div>';

        $this->render_description($field);
    }

    /**
     * Рендер wp_editor
     */
    private function render_editor(array $field): void
    {
        $this->render_label($field);

        if (function_exists('wp_editor')) {
            wp_editor($this->get_field_value($field), $field['id'], [
                'wpautop' => ! empty($field['wpautop']),
                'media_buttons' => ! empty($field['media_buttons']),
                'textarea_name' => $field['name'] ?? $field['id'],
                'textarea_rows' => function_exists('absint') ? absint($field['rows'] ?? 10) : (int) ($field['rows'] ?? 10),
                'teeny' => ! empty($field['teeny']),
            ]);
        } else {
            // Fallback for tests or outside WP
            printf(
                '<textarea id="%s" name="%s" rows="%d" class="wp-editor-area">%s</textarea>',
                esc_attr($field['id']),
                esc_attr($field['name'] ?? $field['id']),
                function_exists('absint') ? absint($field['rows'] ?? 10) : (int) ($field['rows'] ?? 10),
                function_exists('esc_textarea') ? esc_textarea($this->get_field_value($field)) : htmlspecialchars($this->get_field_value($field), ENT_QUOTES),
            );
        }

        $this->render_description($field);
    }

    /**
     * Рендер media (ID или URL)
     */
    private function render_media(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $preview = ! empty($field['preview']) ? $field['preview'] : true;
        $url = ! empty($field['url']) ? $field['url'] : true;
        $placeholder = $field['placeholder'] ?? (function_exists('__') ? __('Не выбрано', 'wp-field') : 'Не выбрано');
        $button_text = $field['button_text'] ?? (function_exists('__') ? __('Загрузить', 'wp-field') : 'Загрузить');
        $library = $field['library'] ?? ''; // image, video, audio

        // Получаем URL файла если есть ID
        $file_url = '';
        $file_name = '';
        if ($value && is_numeric($value)) {
            $file_url = wp_get_attachment_url($value);
            $file_name = basename($file_url);
        } elseif ($value) {
            $file_url = $value;
            $file_name = basename($value);
        }

        echo '<div class="wp-field-media-wrapper">';

        // Поле ввода с placeholder
        if ($url) {
            printf(
                '<input type="text" class="regular-text wp-field-media-url" value="%s" placeholder="%s" readonly>',
                esc_attr($file_url),
                esc_attr($placeholder),
            );
        }

        // Hidden поле для ID
        printf(
            '<input type="hidden" id="%s" name="%s" value="%s" class="wp-field-media-id">',
            esc_attr($field['id']),
            esc_attr($field['name'] ?? $field['id']),
            esc_attr($value),
        );

        // Кнопка загрузки
        printf(
            '<button type="button" class="button wp-field-media-button" data-field-id="%s" data-library="%s">%s</button>',
            esc_attr($field['id']),
            esc_attr($library),
            esc_html($button_text),
        );

        // Превью если включено
        if ($preview && $file_url) {
            $mime_type = '';
            if (is_numeric($value)) {
                $mime_type = get_post_mime_type($value);
            }

            echo '<div class="wp-field-media-preview">';
            if (strpos($mime_type, 'image') !== false || (! $mime_type && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $file_url))) {
                printf('<img src="%s" alt="">', esc_url($file_url));
            } else {
                printf('<span class="wp-field-media-filename">%s</span>', esc_html($file_name));
            }
            echo '</div>';
        }

        echo '</div>';

        $this->render_description($field);
    }

    /**
     * Рендер image
     */
    private function render_image(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $src = is_numeric($value) ? wp_get_attachment_url($value) : $value;
        $preview = ! empty($field['preview']) ? $field['preview'] : true;
        $url = ! empty($field['url']) ? $field['url'] : true;
        $placeholder = $field['placeholder'] ?? (function_exists('__') ? __('Не выбрано', 'wp-field') : 'Не выбрано');
        $button_text = $field['button_text'] ?? (function_exists('__') ? __('Загрузить', 'wp-field') : 'Загрузить');
        $remove_text = $field['remove_text'] ?? (function_exists('__') ? __('Удалить', 'wp-field') : 'Удалить');

        echo '<div class="wp-field-image-wrapper">';

        // Поле ввода с URL если включено
        if ($url) {
            printf(
                '<input type="text" class="regular-text wp-field-image-url" value="%s" placeholder="%s" readonly>',
                esc_attr($src),
                esc_attr($placeholder),
            );
        }

        // Hidden поле для ID
        printf(
            '<input type="hidden" id="%s" name="%s" value="%s" class="wp-field-image-id">',
            esc_attr($field['id']),
            esc_attr($field['name'] ?? $field['id']),
            esc_attr($value),
        );

        // Кнопки
        printf(
            '<button type="button" class="button wp-field-image-button" data-field-id="%s">%s</button>',
            esc_attr($field['id']),
            esc_html($button_text),
        );

        if ($value) {
            printf(
                '<button type="button" class="button wp-field-image-remove" data-field-id="%s">%s</button>',
                esc_attr($field['id']),
                esc_html($remove_text),
            );
        }

        // Превью если включено
        if ($preview && $src) {
            echo '<div class="wp-field-image-preview-wrapper">';
            printf('<img src="%s" alt="" class="wp-field-image-preview">', esc_url($src));
            echo '</div>';
        }

        echo '</div>';

        $this->render_description($field);
    }

    /**
     * Рендер file
     */
    private function render_file(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $url = ! empty($field['url']) ? $field['url'] : true;
        $placeholder = $field['placeholder'] ?? (function_exists('__') ? __('Не выбрано', 'wp-field') : 'Не выбрано');
        $button_text = $field['button_text'] ?? (function_exists('__') ? __('Загрузить', 'wp-field') : 'Загрузить');
        $library = $field['library'] ?? ''; // image, video, audio

        // Получаем URL файла если есть ID
        $file_url = '';
        $file_name = '';
        if ($value && is_numeric($value)) {
            $file_url = wp_get_attachment_url($value);
            $file_name = basename($file_url);
        } elseif ($value) {
            $file_url = $value;
            $file_name = basename($value);
        }

        echo '<div class="wp-field-file-wrapper">';

        // Поле ввода с URL если включено
        if ($url) {
            printf(
                '<input type="text" class="regular-text wp-field-file-url" value="%s" placeholder="%s" readonly>',
                esc_attr($file_url),
                esc_attr($placeholder),
            );
        }

        // Hidden поле для ID
        printf(
            '<input type="hidden" id="%s" name="%s" value="%s" class="wp-field-file-id">',
            esc_attr($field['id']),
            esc_attr($field['name'] ?? $field['id']),
            esc_attr($value),
        );

        // Кнопка загрузки
        printf(
            '<button type="button" class="button wp-field-file-button" data-field-id="%s" data-library="%s">%s</button>',
            esc_attr($field['id']),
            esc_attr($library),
            esc_html($button_text),
        );

        // Показываем имя файла если выбран
        if ($file_name) {
            printf('<span class="wp-field-file-name">%s</span>', esc_html($file_name));
        }

        echo '</div>';

        $this->render_description($field);
    }

    /**
     * Рендер gallery
     */
    private function render_gallery(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $ids = is_array($value) ? $value : array_filter(explode(',', (string) $value));

        $add_text = $field['add_button'] ?? (function_exists('__') ? __('Добавить галерею', 'wp-field') : 'Добавить галерею');
        $edit_text = $field['edit_button'] ?? (function_exists('__') ? __('Редактировать галерею', 'wp-field') : 'Редактировать галерею');
        $remove_text = $field['clear_button'] ?? (function_exists('__') ? __('Сброс', 'wp-field') : 'Сброс');

        echo '<div class="wp-field-gallery-wrapper">';

        // Hidden поле для хранения ID изображений
        printf(
            '<input type="hidden" id="%s" name="%s" value="%s" class="wp-field-gallery-ids">',
            esc_attr($field['id']),
            esc_attr($field['name'] ?? $field['id']),
            esc_attr(implode(',', $ids)),
        );

        // Контейнер для превью изображений
        echo '<div class="wp-field-gallery-preview">';
        if (! empty($ids)) {
            foreach ($ids as $id) {
                if (is_numeric($id)) {
                    $img_url = wp_get_attachment_image_url($id, 'thumbnail');
                    if ($img_url) {
                        printf(
                            '<div class="wp-field-gallery-item" data-id="%s"><img src="%s" alt=""><span class="wp-field-gallery-remove" data-id="%s">×</span></div>',
                            esc_attr($id),
                            esc_url($img_url),
                            esc_attr($id),
                        );
                    }
                }
            }
        }
        echo '</div>';

        // Кнопки управления
        echo '<div class="wp-field-gallery-buttons">';
        printf(
            '<button type="button" class="button wp-field-gallery-add" data-field-id="%s">%s</button>',
            esc_attr($field['id']),
            esc_html($add_text),
        );
        printf(
            '<button type="button" class="button wp-field-gallery-edit" data-field-id="%s">%s</button>',
            esc_attr($field['id']),
            esc_html($edit_text),
        );
        printf(
            '<button type="button" class="button wp-field-gallery-clear" data-field-id="%s">%s</button>',
            esc_attr($field['id']),
            esc_html($remove_text),
        );
        echo '</div>';

        echo '</div>';

        $this->render_description($field);
    }

    /**
     * Рендер color picker
     */
    private function render_color(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $default = $field['default'] ?? '#000000';
        $alpha = isset($field['alpha']) && $field['alpha'] === false ? 'false' : 'true';

        printf(
            '<input type="text" id="%s" name="%s" value="%s" class="wp-color-picker-field" data-default-color="%s" data-alpha="%s" %s>',
            esc_attr($field['id']),
            esc_attr($field['name'] ?? $field['id']),
            esc_attr($value),
            esc_attr($default),
            esc_attr($alpha),
            $this->get_field_attributes($field),
        );

        $this->render_description($field);
    }

    /**
     * Рендер date/time/datetime
     */
    private function render_date(array $field): void
    {
        $this->render_label($field);

        printf(
            '<input class="%s" id="%s" name="%s" type="%s" value="%s" %s>',
            esc_attr($field['class'] ?? 'regular-text'),
            esc_attr($field['id']),
            esc_attr($field['name'] ?? $field['id']),
            esc_attr($field['type'] ?? 'date'),
            esc_attr($this->get_field_value($field)),
            $this->get_field_attributes($field),
        );

        $this->render_description($field);
    }

    /**
     * Рендер group (вложенные поля)
     */
    private function render_group(array $field): void
    {
        $this->render_label($field);

        if (empty($field['fields'])) {
            return;
        }

        echo '<div class="wp-field-group">';

        foreach ($field['fields'] as $sub_field) {
            $sub_field['name'] = $field['name'] ?? $field['id'];
            $obj = new self($sub_field, $this->storage_type, $this->storage_id);
            $obj->render(true);
        }

        echo '</div>';

        $this->render_description($field);
    }

    /**
     * Рендер поля с wrapper'ом
     *
     * @param  bool  $output  выводить HTML или вернуть строку
     * @return false|string|void
     */
    public function render($output = true)
    {
        if (! $output) {
            ob_start();
        }

        if (! is_array($this->field)) {
            echo esc_html((string) $this->field);
            if (! $output) {
                return ob_get_clean();
            }

            return;
        }

        // Определяем видимость по зависимостям
        $is_hidden = $this->is_field_hidden();
        $hidden_class = $is_hidden ? ' is-hidden' : '';
        $dependency_data = $this->get_dependency_data();

        // Открываем wrapper
        printf(
            '<div class="wp-field wp-field-%s%s" data-field-id="%s" data-field-type="%s"%s>',
            esc_attr($this->field['type']),
            $hidden_class,
            esc_attr($this->field['id']),
            esc_attr($this->field['type']),
            $dependency_data ? ' data-dependency=\''.esc_attr($dependency_data).'\'' : '',
        );

        // Рендерим поле через реестр
        $this->render_field();

        // Закрываем wrapper
        echo '</div>';

        if (! $output) {
            return ob_get_clean();
        }
    }

    /**
     * Проверка, скрыто ли поле по зависимостям
     */
    private function is_field_hidden(): bool
    {
        if (empty($this->field['dependency'])) {
            return false;
        }

        return ! $this->evaluate_dependency($this->field['dependency']);
    }

    /**
     * Оценка условия зависимости
     */
    private function evaluate_dependency(array $dependency): bool
    {
        $relation = $dependency['relation'] ?? 'AND';
        $conditions = array_filter($dependency, fn ($k) => $k !== 'relation', ARRAY_FILTER_USE_KEY);

        if (empty($conditions)) {
            return true;
        }

        $results = [];
        foreach ($conditions as $condition) {
            if (! is_array($condition) || count($condition) < 3) {
                continue;
            }

            [$field_id, $operator, $value] = $condition;
            $field_value = $this->get_value_for_dependency($field_id);
            $results[] = $this->evaluate_condition($field_value, $operator, $value);
        }

        if (empty($results)) {
            return true;
        }

        return $relation === 'AND' ? ! in_array(false, $results, true) : in_array(true, $results, true);
    }

    /**
     * Получить значение поля для проверки зависимости
     */
    private function get_value_for_dependency(string $field_id)
    {
        return $this->get_value($field_id, $this->storage_id);
    }

    /**
     * Оценить одно условие
     */
    private function evaluate_condition($field_value, string $operator, $compare_value): bool
    {
        return match ($operator) {
            '==' => $field_value == $compare_value,
            '!=' => $field_value != $compare_value,
            '>' => $field_value > $compare_value,
            '>=' => $field_value >= $compare_value,
            '<' => $field_value < $compare_value,
            '<=' => $field_value <= $compare_value,
            'in' => is_array($compare_value) && in_array($field_value, $compare_value, true),
            'not_in' => is_array($compare_value) && ! in_array($field_value, $compare_value, true),
            'contains' => is_string($field_value) && str_contains($field_value, (string) $compare_value),
            'not_contains' => is_string($field_value) && ! str_contains($field_value, (string) $compare_value),
            'empty' => empty($field_value),
            'not_empty' => ! empty($field_value),
            default => false,
        };
    }

    /**
     * Получить JSON данные зависимостей для JS
     */
    private function get_dependency_data(): string
    {
        if (empty($this->field['dependency'])) {
            return '';
        }

        return function_exists('wp_json_encode') ? wp_json_encode($this->field['dependency']) : json_encode($this->field['dependency']);
    }

    /**
     * Рендер поля по типу из реестра
     */
    private function render_field(): void
    {
        if (! is_array($this->field)) {
            echo esc_html((string) $this->field);

            return;
        }

        $type = $this->field['type'] ?? 'text';

        if (! isset(self::$field_types[$type])) {
            trigger_error("Неизвестный тип поля: {$type}");

            return;
        }

        [$method, $defaults] = self::$field_types[$type];

        // Мержим дефолты с полем
        $field = array_merge($defaults, $this->field);

        // Вызываем метод рендера
        if (method_exists($this, $method)) {
            $this->$method($field);
        } else {
            trigger_error("Метод рендера не найден: {$method}");
        }
    }

    /**
     * Рендер repeater (массив элементов)
     */
    private function render_repeater(array $field): void
    {
        $this->render_label($field);

        $values = (array) $this->get_field_value($field);
        $min = function_exists('absint') ? absint($field['min'] ?? 0) : (int) ($field['min'] ?? 0);
        $max = function_exists('absint') ? absint($field['max'] ?? 0) : (int) ($field['max'] ?? 0);

        echo '<div class="wp-field-repeater" data-field-id="'.esc_attr($field['id']).'" data-min="'.$min.'" data-max="'.$max.'">';

        // Рендерим существующие элементы
        if (! empty($values)) {
            foreach ($values as $index => $item) {
                $this->render_repeater_item($field, $index, $item);
            }
        }

        // Всегда добавляем один шаблон для клонирования (скрыт через CSS)
        $this->render_repeater_item($field, 0, [], true);

        echo '</div>';

        printf(
            '<button type="button" class="button wp-field-repeater-add" data-field-id="%s">%s</button>',
            esc_attr($field['id']),
            esc_html($field['add_text'] ?? (function_exists('__') ? __('Add Item', 'wp-field') : 'Add Item')),
        );

        $this->render_description($field);
    }

    /**
     * Рендер одного элемента repeater
     */
    private function render_repeater_item(array $field, int $index, $item = [], bool $template = false): void
    {
        $class = $template ? ' wp-field-repeater-template' : '';

        printf(
            '<div class="wp-field-repeater-item%s" data-index="%d">',
            $class,
            $index,
        );

        if (! empty($field['fields'])) {
            foreach ($field['fields'] as $sub_field) {
                // Формируем правильный name: parent_name[index][subfield_id]
                $parent_name = $field['name'] ?? $field['id'];
                $sub_field['name'] = sprintf('%s[%d][%s]', $parent_name, $index, $sub_field['id']);
                $sub_field['value'] = is_array($item) && isset($item[$sub_field['id']]) ? $item[$sub_field['id']] : null;

                $obj = new self($sub_field, $this->storage_type, $this->storage_id);
                $obj->render(true);
            }
        }

        printf(
            '<button type="button" class="button wp-field-repeater-remove">%s</button>',
            esc_html(function_exists('__') ? __('Remove', 'wp-field') : 'Remove'),
        );

        echo '</div>';
    }

    /**
     * Рендер image picker (vanilla)
     */
    private function render_image_picker(array $field): void
    {
        // Используем select с data-img-src для каждой опции
        $this->render_label($field);

        $options = $field['options'] ?? [];
        $value = $this->get_field_value($field);

        printf(
            '<select class="wp-field-image-picker" id="%s" name="%s">',
            esc_attr($field['id']),
            esc_attr($field['name'] ?? $field['id']),
        );

        foreach ($options as $opt_value => $opt_data) {
            $selected = $value === $opt_value ? ' selected' : '';
            $img_src = is_array($opt_data) ? ($opt_data['src'] ?? '') : $opt_data;

            printf(
                '<option value="%s" data-img-src="%s"%s>%s</option>',
                esc_attr($opt_value),
                esc_url($img_src),
                $selected,
                esc_html(is_array($opt_data) ? ($opt_data['label'] ?? $opt_value) : $opt_value),
            );
        }

        echo '</select>';

        $this->render_description($field);
    }

    /**
     * Рендер switcher (переключатель on/off)
     */
    private function render_switcher(array $field): void
    {
        $value = $this->get_field_value($field);
        $checked = ! empty($value) ? 'checked' : '';
        $text_on = $field['text_on'] ?? 'On';
        $text_off = $field['text_off'] ?? 'Off';

        printf(
            '<label class="wp-field-switcher">
                <input type="checkbox" name="%s" value="1" %s %s %s />
                <span class="wp-field-switcher-slider">
                    <span class="wp-field-switcher-on">%s</span>
                    <span class="wp-field-switcher-off">%s</span>
                </span>
            </label>',
            esc_attr($field['name'] ?? $field['id']),
            $checked,
            $this->get_field_attributes($field),
            $this->get_readonly_disabled($field),
            esc_html($text_on),
            esc_html($text_off),
        );

        $this->render_description($field);
    }

    /**
     * Рендер spinner (счётчик с кнопками)
     */
    private function render_spinner(array $field): void
    {
        $value = $this->get_field_value($field);
        $min = $field['min'] ?? 0;
        $max = $field['max'] ?? 100;
        $step = $field['step'] ?? 1;
        $unit = $field['unit'] ?? '';

        echo '<div class="wp-field-spinner">';

        // Кнопка уменьшения
        printf(
            '<button type="button" class="wp-field-spinner-btn wp-field-spinner-down" data-step="%s">◄</button>',
            esc_attr((string) $step),
        );

        // Обёртка для input + unit
        echo '<div class="wp-field-spinner-input-wrap">';

        printf(
            '<input type="number" name="%s" value="%s" min="%s" max="%s" step="%s" %s %s />',
            esc_attr($field['name'] ?? $field['id']),
            esc_attr((string) $value),
            esc_attr((string) $min),
            esc_attr((string) $max),
            esc_attr((string) $step),
            $this->get_field_attributes($field),
            $this->get_readonly_disabled($field),
        );

        // Unit внутри обёртки справа
        if ($unit) {
            printf('<span class="wp-field-spinner-unit">%s</span>', esc_html($unit));
        }

        echo '</div>';

        // Кнопка увеличения
        printf(
            '<button type="button" class="wp-field-spinner-btn wp-field-spinner-up" data-step="%s">►</button>',
            esc_attr((string) $step),
        );

        echo '</div>';

        $this->render_description($field);
    }

    /**
     * Рендер button_set (группа кнопок для выбора)
     */
    private function render_button_set(array $field): void
    {
        $value = $this->get_field_value($field);
        $options = $field['options'] ?? [];
        $multiple = ! empty($field['multiple']);

        if (empty($options)) {
            echo '<p class="description">No options provided</p>';

            return;
        }

        echo '<div class="wp-field-button-set">';

        if ($multiple) {
            $values = is_array($value) ? $value : ($value ? [$value] : []);
            foreach ($options as $key => $label) {
                $checked = in_array($key, $values) ? 'checked' : '';
                printf(
                    '<label class="wp-field-button-set-item %s">
                        <input type="checkbox" name="%s[]" value="%s" %s %s />
                        <span>%s</span>
                    </label>',
                    $checked ? 'active' : '',
                    esc_attr($field['name'] ?? $field['id']),
                    esc_attr($key),
                    $checked,
                    $this->get_field_attributes($field),
                    esc_html($label),
                );
            }
        } else {
            foreach ($options as $key => $label) {
                $checked = $value === $key ? 'checked' : '';
                printf(
                    '<label class="wp-field-button-set-item %s">
                        <input type="radio" name="%s" value="%s" %s %s />
                        <span>%s</span>
                    </label>',
                    $checked ? 'active' : '',
                    esc_attr($field['name'] ?? $field['id']),
                    esc_attr($key),
                    $checked,
                    $this->get_field_attributes($field),
                    esc_html($label),
                );
            }
        }

        echo '</div>';
        $this->render_description($field);
    }

    /**
     * Рендер slider (ползунок)
     */
    private function render_slider(array $field): void
    {
        $value = $this->get_field_value($field);
        $min = $field['min'] ?? 0;
        $max = $field['max'] ?? 100;
        $step = $field['step'] ?? 1;
        $show_value = ! empty($field['show_value']);

        printf(
            '<div class="wp-field-slider-wrapper">
                <input type="range" class="wp-field-slider" name="%s" value="%s" min="%s" max="%s" step="%s" %s %s />
                %s
            </div>',
            esc_attr($field['name'] ?? $field['id']),
            esc_attr((string) $value),
            esc_attr((string) $min),
            esc_attr((string) $max),
            esc_attr((string) $step),
            $this->get_field_attributes($field),
            $this->get_readonly_disabled($field),
            $show_value ? '<span class="wp-field-slider-value">'.esc_html((string) $value).'</span>' : '',
        );

        $this->render_description($field);
    }

    /**
     * Рендер heading (заголовок)
     */
    private function render_heading(array $field): void
    {
        $tag = $field['tag'] ?? 'h3';
        $class = $field['class'] ?? 'wp-field-heading';
        $escaped_tag = function_exists('tag_escape') ? tag_escape($tag) : htmlspecialchars($tag, ENT_QUOTES);

        printf(
            '<%s class="%s">%s</%s>',
            $escaped_tag,
            esc_attr($class),
            esc_html($field['label'] ?? ''),
            $escaped_tag,
        );
    }

    /**
     * Рендер subheading (подзаголовок)
     */
    private function render_subheading(array $field): void
    {
        $tag = $field['tag'] ?? 'h4';
        $class = $field['class'] ?? 'wp-field-subheading';
        $escaped_tag = function_exists('tag_escape') ? tag_escape($tag) : htmlspecialchars($tag, ENT_QUOTES);

        printf(
            '<%s class="%s">%s</%s>',
            $escaped_tag,
            esc_attr($class),
            esc_html($field['label'] ?? ''),
            $escaped_tag,
        );
    }

    /**
     * Рендер notice (уведомление)
     */
    private function render_notice(array $field): void
    {
        $type = $field['type_notice'] ?? $field['notice_type'] ?? 'info';
        $class = 'wp-field-notice wp-field-notice-'.esc_attr($type);

        printf(
            '<div class="%s">%s</div>',
            $class,
            function_exists('wp_kses_post') ? wp_kses_post($field['label'] ?? '') : ($field['label'] ?? ''),
        );
    }

    /**
     * Рендер content (контент/HTML)
     */
    private function render_content(array $field): void
    {
        echo function_exists('wp_kses_post') ? wp_kses_post($field['label'] ?? '') : ($field['label'] ?? '');
    }

    /**
     * Рендер fieldset (группировка полей)
     */
    private function render_fieldset(array $field): void
    {
        $legend = $field['legend'] ?? $field['label'] ?? '';
        $class = $field['class'] ?? 'wp-field-fieldset';

        printf(
            '<fieldset class="%s">',
            esc_attr($class),
        );

        if ($legend) {
            printf(
                '<legend>%s</legend>',
                esc_html($legend),
            );
        }

        // Рендер вложенных полей если они есть
        if (! empty($field['fields']) && is_array($field['fields'])) {
            foreach ($field['fields'] as $nested_field) {
                if (is_array($nested_field)) {
                    WP_Field::make($nested_field, true);
                }
            }
        }

        echo '</fieldset>';
    }

    /**
     * Make new filed
     *
     * @param  array  $params  data, type, id,
     * @param  bool  $output  html|string
     * @return false|string|null
     */
    public static function make(array $params, bool $output = true)
    {
        if (isset($params[0]) && is_array($params[0])) {
            $obj = new self($params[0], $params[1] ?? 'post', $params[2] ?? null);
        } else {
            $obj = new self($params);
        }

        return $obj->render($output);
    }

    /**
     * Рендер accordion (свёртываемые секции)
     */
    private function render_accordion(array $field): void
    {
        $this->render_label($field);

        // Поддерживаем оба варианта: items и sections
        $items = $field['items'] ?? $field['sections'] ?? [];

        if (empty($items) || ! is_array($items)) {
            echo '<p class="description">No items provided</p>';

            return;
        }

        echo '<div class="wp-field-accordion" data-field-id="'.esc_attr($field['id']).'">';

        foreach ($items as $index => $item) {
            $title = $item['title'] ?? 'Item '.($index + 1);
            $content = $item['content'] ?? '';
            $fields = $item['fields'] ?? [];
            $open = ! empty($item['open']);

            printf(
                '<div class="wp-field-accordion-item %s" data-index="%d">
                    <div class="wp-field-accordion-header">
                        <span class="wp-field-accordion-icon">%s</span>
                        <span class="wp-field-accordion-title">%s</span>
                    </div>
                    <div class="wp-field-accordion-content">',
                $open ? 'is-open' : '',
                $index,
                $open ? '▼' : '▶',
                esc_html($title),
            );

            if ($content) {
                echo function_exists('wp_kses_post') ? wp_kses_post($content) : $content;
            }

            if (! empty($fields)) {
                foreach ($fields as $sub_field) {
                    $obj = new self($sub_field, $this->storage_type, $this->storage_id);
                    $obj->render(true);
                }
            }

            echo '</div></div>';
        }

        echo '</div>';
        $this->render_description($field);
    }

    /**
     * Рендер tabbed (вкладки)
     */
    private function render_tabbed(array $field): void
    {
        $this->render_label($field);

        if (empty($field['tabs']) || ! is_array($field['tabs'])) {
            echo '<p class="description">No tabs provided</p>';

            return;
        }

        $field_id = esc_attr($field['id']);

        // Определяем, какая вкладка должна быть активной по умолчанию
        $default_active_index = 0;
        foreach ($field['tabs'] as $index => $tab) {
            if (! empty($tab['active'])) {
                $default_active_index = $index;
                break;
            }
        }

        echo '<div class="wp-field-tabbed" data-field-id="'.$field_id.'" data-default-tab="'.$default_active_index.'">';
        echo '<div class="wp-field-tabbed-nav">';

        foreach ($field['tabs'] as $index => $tab) {
            $title = $tab['title'] ?? 'Tab '.($index + 1);
            $icon = $tab['icon'] ?? '';
            $active = $index === $default_active_index ? 'active' : '';

            printf(
                '<button type="button" class="wp-field-tabbed-nav-item %s" data-tab="%s-%d">
                    %s<span>%s</span>
                </button>',
                $active,
                $field_id,
                $index,
                $icon ? '<span class="wp-field-tabbed-icon">'.esc_html($icon).'</span>' : '',
                esc_html($title),
            );
        }

        echo '</div><div class="wp-field-tabbed-content">';

        foreach ($field['tabs'] as $index => $tab) {
            $content = $tab['content'] ?? '';
            $fields = $tab['fields'] ?? [];
            $active = $index === $default_active_index ? 'active' : '';

            printf(
                '<div class="wp-field-tabbed-pane %s" data-tab="%s-%d">',
                $active,
                $field_id,
                $index,
            );

            if ($content) {
                echo function_exists('wp_kses_post') ? wp_kses_post($content) : $content;
            }

            if (! empty($fields)) {
                foreach ($fields as $sub_field) {
                    $obj = new self($sub_field, $this->storage_type, $this->storage_id);
                    $obj->render(true);
                }
            }

            echo '</div>';
        }

        echo '</div></div>';
        $this->render_description($field);
    }

    /**
     * Рендер typography (типография)
     */
    private function render_typography(array $field): void
    {
        $this->render_label($field);

        $value = (array) $this->get_field_value($field);
        $name = $field['name'] ?? $field['id'];

        $defaults = [
            'font_family' => $value['font_family'] ?? '',
            'font_size' => $value['font_size'] ?? '',
            'font_weight' => $value['font_weight'] ?? '',
            'line_height' => $value['line_height'] ?? '',
            'text_align' => $value['text_align'] ?? '',
            'text_transform' => $value['text_transform'] ?? '',
            'color' => $value['color'] ?? '',
        ];

        echo '<div class="wp-field-typography">';

        // Font Family
        printf(
            '<div class="wp-field-typography-item">
                <label>Font Family</label>
                <select name="%s[font_family]">
                    <option value="">Default</option>
                    <option value="Arial" %s>Arial</option>
                    <option value="Helvetica" %s>Helvetica</option>
                    <option value="Times New Roman" %s>Times New Roman</option>
                    <option value="Georgia" %s>Georgia</option>
                    <option value="Verdana" %s>Verdana</option>
                </select>
            </div>',
            esc_attr($name),
            selected($defaults['font_family'], 'Arial', false),
            selected($defaults['font_family'], 'Helvetica', false),
            selected($defaults['font_family'], 'Times New Roman', false),
            selected($defaults['font_family'], 'Georgia', false),
            selected($defaults['font_family'], 'Verdana', false),
        );

        // Font Size
        printf(
            '<div class="wp-field-typography-item">
                <label>Font Size</label>
                <input type="number" name="%s[font_size]" value="%s" min="8" max="72" placeholder="16">
            </div>',
            esc_attr($name),
            esc_attr($defaults['font_size']),
        );

        // Font Weight
        printf(
            '<div class="wp-field-typography-item">
                <label>Font Weight</label>
                <select name="%s[font_weight]">
                    <option value="">Default</option>
                    <option value="300" %s>Light (300)</option>
                    <option value="400" %s>Normal (400)</option>
                    <option value="600" %s>Semi Bold (600)</option>
                    <option value="700" %s>Bold (700)</option>
                </select>
            </div>',
            esc_attr($name),
            selected($defaults['font_weight'], '300', false),
            selected($defaults['font_weight'], '400', false),
            selected($defaults['font_weight'], '600', false),
            selected($defaults['font_weight'], '700', false),
        );

        // Line Height
        printf(
            '<div class="wp-field-typography-item">
                <label>Line Height</label>
                <input type="number" name="%s[line_height]" value="%s" min="1" max="3" step="0.1" placeholder="1.5">
            </div>',
            esc_attr($name),
            esc_attr($defaults['line_height']),
        );

        // Text Align
        printf(
            '<div class="wp-field-typography-item">
                <label>Text Align</label>
                <select name="%s[text_align]">
                    <option value="">Default</option>
                    <option value="left" %s>Left</option>
                    <option value="center" %s>Center</option>
                    <option value="right" %s>Right</option>
                    <option value="justify" %s>Justify</option>
                </select>
            </div>',
            esc_attr($name),
            selected($defaults['text_align'], 'left', false),
            selected($defaults['text_align'], 'center', false),
            selected($defaults['text_align'], 'right', false),
            selected($defaults['text_align'], 'justify', false),
        );

        // Text Transform
        printf(
            '<div class="wp-field-typography-item">
                <label>Text Transform</label>
                <select name="%s[text_transform]">
                    <option value="">Default</option>
                    <option value="none" %s>None</option>
                    <option value="uppercase" %s>Uppercase</option>
                    <option value="lowercase" %s>Lowercase</option>
                    <option value="capitalize" %s>Capitalize</option>
                </select>
            </div>',
            esc_attr($name),
            selected($defaults['text_transform'], 'none', false),
            selected($defaults['text_transform'], 'uppercase', false),
            selected($defaults['text_transform'], 'lowercase', false),
            selected($defaults['text_transform'], 'capitalize', false),
        );

        // Color
        printf(
            '<div class="wp-field-typography-item">
                <label>Color</label>
                <input type="text" name="%s[color]" value="%s" class="wp-color-picker-field">
            </div>',
            esc_attr($name),
            esc_attr($defaults['color']),
        );

        echo '</div>';
        $this->render_description($field);
    }

    /**
     * Рендер spacing (отступы)
     */
    private function render_spacing(array $field): void
    {
        $this->render_label($field);

        $value = (array) $this->get_field_value($field);
        $name = $field['name'] ?? $field['id'];
        $type = $field['spacing_type'] ?? 'margin'; // margin или padding

        $defaults = [
            'top' => $value['top'] ?? '',
            'right' => $value['right'] ?? '',
            'bottom' => $value['bottom'] ?? '',
            'left' => $value['left'] ?? '',
            'unit' => $value['unit'] ?? 'px',
        ];

        echo '<div class="wp-field-spacing">';
        echo '<div class="wp-field-spacing-wrapper">';
        echo '<div class="wp-field-spacing-visual">';

        // Top
        printf(
            '<div class="wp-field-spacing-side wp-field-spacing-top">
                <label>Top</label>
                <input type="number" name="%s[top]" value="%s" placeholder="0" step="1">
            </div>',
            esc_attr($name),
            esc_attr($defaults['top']),
        );

        // Right
        printf(
            '<div class="wp-field-spacing-side wp-field-spacing-right">
                <label>Right</label>
                <input type="number" name="%s[right]" value="%s" placeholder="0" step="1">
            </div>',
            esc_attr($name),
            esc_attr($defaults['right']),
        );

        // Bottom
        printf(
            '<div class="wp-field-spacing-side wp-field-spacing-bottom">
                <label>Bottom</label>
                <input type="number" name="%s[bottom]" value="%s" placeholder="0" step="1">
            </div>',
            esc_attr($name),
            esc_attr($defaults['bottom']),
        );

        // Left
        printf(
            '<div class="wp-field-spacing-side wp-field-spacing-left">
                <label>Left</label>
                <input type="number" name="%s[left]" value="%s" placeholder="0" step="1">
            </div>',
            esc_attr($name),
            esc_attr($defaults['left']),
        );

        // Center label
        echo '<div class="wp-field-spacing-center">'.esc_html($type).'</div>';

        echo '</div>'; // .wp-field-spacing-visual

        // Unit selector
        printf(
            '<div class="wp-field-spacing-unit">
                <select name="%s[unit]">
                    <option value="px" %s>px</option>
                    <option value="em" %s>em</option>
                    <option value="rem" %s>rem</option>
                    <option value="%%" %s>%%</option>
                </select>
            </div>',
            esc_attr($name),
            selected($defaults['unit'], 'px', false),
            selected($defaults['unit'], 'em', false),
            selected($defaults['unit'], 'rem', false),
            selected($defaults['unit'], '%', false),
        );

        echo '</div>'; // .wp-field-spacing-wrapper
        echo '</div>'; // .wp-field-spacing
        $this->render_description($field);
    }

    /**
     * Рендер dimensions (размеры)
     */
    private function render_dimensions(array $field): void
    {
        $this->render_label($field);

        $value = (array) $this->get_field_value($field);
        $name = $field['name'] ?? $field['id'];

        $defaults = [
            'width' => $value['width'] ?? '',
            'height' => $value['height'] ?? '',
            'unit' => $value['unit'] ?? 'px',
        ];

        echo '<div class="wp-field-dimensions">';

        // Width
        printf(
            '<div class="wp-field-dimensions-item">
                <label>Width</label>
                <input type="number" name="%s[width]" value="%s" placeholder="0">
            </div>',
            esc_attr($name),
            esc_attr($defaults['width']),
        );

        // Height
        printf(
            '<div class="wp-field-dimensions-item">
                <label>Height</label>
                <input type="number" name="%s[height]" value="%s" placeholder="0">
            </div>',
            esc_attr($name),
            esc_attr($defaults['height']),
        );

        // Unit
        printf(
            '<div class="wp-field-dimensions-item">
                <label>Unit</label>
                <select name="%s[unit]">
                    <option value="px" %s>px</option>
                    <option value="em" %s>em</option>
                    <option value="rem" %s>rem</option>
                    <option value="%%" %s>%%</option>
                    <option value="vh" %s>vh</option>
                    <option value="vw" %s>vw</option>
                </select>
            </div>',
            esc_attr($name),
            selected($defaults['unit'], 'px', false),
            selected($defaults['unit'], 'em', false),
            selected($defaults['unit'], 'rem', false),
            selected($defaults['unit'], '%', false),
            selected($defaults['unit'], 'vh', false),
            selected($defaults['unit'], 'vw', false),
        );

        echo '</div>';
        $this->render_description($field);
    }

    /**
     * Рендер border (граница)
     */
    private function render_border(array $field): void
    {
        $this->render_label($field);

        $value = (array) $this->get_field_value($field);
        $name = $field['name'] ?? $field['id'];

        $defaults = [
            'style' => $value['style'] ?? 'solid',
            'width' => $value['width'] ?? '',
            'color' => $value['color'] ?? '',
        ];

        echo '<div class="wp-field-border">';

        // Style
        printf(
            '<div class="wp-field-border-item">
                <label>Style</label>
                <select name="%s[style]">
                    <option value="none" %s>None</option>
                    <option value="solid" %s>Solid</option>
                    <option value="dashed" %s>Dashed</option>
                    <option value="dotted" %s>Dotted</option>
                    <option value="double" %s>Double</option>
                </select>
            </div>',
            esc_attr($name),
            selected($defaults['style'], 'none', false),
            selected($defaults['style'], 'solid', false),
            selected($defaults['style'], 'dashed', false),
            selected($defaults['style'], 'dotted', false),
            selected($defaults['style'], 'double', false),
        );

        // Width
        printf(
            '<div class="wp-field-border-item">
                <label>Width (px)</label>
                <input type="number" name="%s[width]" value="%s" min="0" max="20" placeholder="1">
            </div>',
            esc_attr($name),
            esc_attr($defaults['width']),
        );

        // Color
        printf(
            '<div class="wp-field-border-item">
                <label>Color</label>
                <input type="text" name="%s[color]" value="%s" class="wp-color-picker-field">
            </div>',
            esc_attr($name),
            esc_attr($defaults['color']),
        );

        echo '</div>';
        $this->render_description($field);
    }

    /**
     * Рендер background (фон)
     */
    private function render_background(array $field): void
    {
        $this->render_label($field);

        $value = (array) $this->get_field_value($field);
        $name = $field['name'] ?? $field['id'];

        $defaults = [
            'color' => $value['color'] ?? '',
            'image' => $value['image'] ?? '',
            'position' => $value['position'] ?? 'center center',
            'size' => $value['size'] ?? 'cover',
            'repeat' => $value['repeat'] ?? 'no-repeat',
            'attachment' => $value['attachment'] ?? 'scroll',
        ];

        echo '<div class="wp-field-background">';

        // Color
        printf(
            '<div class="wp-field-background-item">
                <label>Background Color</label>
                <input type="text" name="%s[color]" value="%s" class="wp-color-picker-field">
            </div>',
            esc_attr($name),
            esc_attr($defaults['color']),
        );

        // Image
        printf(
            '<div class="wp-field-background-item">
                <label>Background Image</label>
                <input type="hidden" name="%s[image]" value="%s" class="wp-field-background-image-id">
                <button type="button" class="button wp-field-background-image-button" data-field-name="%s">Choose Image</button>
            </div>',
            esc_attr($name),
            esc_attr($defaults['image']),
            esc_attr($name),
        );

        // Position
        printf(
            '<div class="wp-field-background-item">
                <label>Position</label>
                <select name="%s[position]">
                    <option value="left top" %s>Left Top</option>
                    <option value="center top" %s>Center Top</option>
                    <option value="right top" %s>Right Top</option>
                    <option value="left center" %s>Left Center</option>
                    <option value="center center" %s>Center Center</option>
                    <option value="right center" %s>Right Center</option>
                    <option value="left bottom" %s>Left Bottom</option>
                    <option value="center bottom" %s>Center Bottom</option>
                    <option value="right bottom" %s>Right Bottom</option>
                </select>
            </div>',
            esc_attr($name),
            selected($defaults['position'], 'left top', false),
            selected($defaults['position'], 'center top', false),
            selected($defaults['position'], 'right top', false),
            selected($defaults['position'], 'left center', false),
            selected($defaults['position'], 'center center', false),
            selected($defaults['position'], 'right center', false),
            selected($defaults['position'], 'left bottom', false),
            selected($defaults['position'], 'center bottom', false),
            selected($defaults['position'], 'right bottom', false),
        );

        // Size
        printf(
            '<div class="wp-field-background-item">
                <label>Size</label>
                <select name="%s[size]">
                    <option value="auto" %s>Auto</option>
                    <option value="cover" %s>Cover</option>
                    <option value="contain" %s>Contain</option>
                </select>
            </div>',
            esc_attr($name),
            selected($defaults['size'], 'auto', false),
            selected($defaults['size'], 'cover', false),
            selected($defaults['size'], 'contain', false),
        );

        // Repeat
        printf(
            '<div class="wp-field-background-item">
                <label>Repeat</label>
                <select name="%s[repeat]">
                    <option value="no-repeat" %s>No Repeat</option>
                    <option value="repeat" %s>Repeat</option>
                    <option value="repeat-x" %s>Repeat X</option>
                    <option value="repeat-y" %s>Repeat Y</option>
                </select>
            </div>',
            esc_attr($name),
            selected($defaults['repeat'], 'no-repeat', false),
            selected($defaults['repeat'], 'repeat', false),
            selected($defaults['repeat'], 'repeat-x', false),
            selected($defaults['repeat'], 'repeat-y', false),
        );

        // Attachment
        printf(
            '<div class="wp-field-background-item">
                <label>Attachment</label>
                <select name="%s[attachment]">
                    <option value="scroll" %s>Scroll</option>
                    <option value="fixed" %s>Fixed</option>
                </select>
            </div>',
            esc_attr($name),
            selected($defaults['attachment'], 'scroll', false),
            selected($defaults['attachment'], 'fixed', false),
        );

        echo '</div>';
        $this->render_description($field);
    }

    /**
     * Рендер link_color (цвета ссылок)
     */
    private function render_link_color(array $field): void
    {
        $this->render_label($field);

        $value = (array) $this->get_field_value($field);
        $name = $field['name'] ?? $field['id'];

        $defaults = [
            'normal' => $value['normal'] ?? '',
            'hover' => $value['hover'] ?? '',
            'active' => $value['active'] ?? '',
        ];

        echo '<div class="wp-field-link-color">';

        // Normal
        printf(
            '<div class="wp-field-link-color-item">
                <label>Normal</label>
                <input type="text" name="%s[normal]" value="%s" class="wp-color-picker-field">
            </div>',
            esc_attr($name),
            esc_attr($defaults['normal']),
        );

        // Hover
        printf(
            '<div class="wp-field-link-color-item">
                <label>Hover</label>
                <input type="text" name="%s[hover]" value="%s" class="wp-color-picker-field">
            </div>',
            esc_attr($name),
            esc_attr($defaults['hover']),
        );

        // Active
        printf(
            '<div class="wp-field-link-color-item">
                <label>Active</label>
                <input type="text" name="%s[active]" value="%s" class="wp-color-picker-field">
            </div>',
            esc_attr($name),
            esc_attr($defaults['active']),
        );

        echo '</div>';
        $this->render_description($field);
    }

    /**
     * Рендер color_group (группа цветов)
     */
    private function render_color_group(array $field): void
    {
        $this->render_label($field);

        $value = (array) $this->get_field_value($field);
        $name = $field['name'] ?? $field['id'];
        $colors = $field['colors'] ?? ['primary' => 'Primary', 'secondary' => 'Secondary', 'accent' => 'Accent'];

        echo '<div class="wp-field-color-group">';

        foreach ($colors as $key => $label) {
            printf(
                '<div class="wp-field-color-group-item">
                    <label>%s</label>
                    <input type="text" name="%s[%s]" value="%s" class="wp-color-picker-field">
                </div>',
                esc_html($label),
                esc_attr($name),
                esc_attr($key),
                esc_attr($value[$key] ?? ''),
            );
        }

        echo '</div>';
        $this->render_description($field);
    }

    /**
     * Рендер image_select (выбор из изображений)
     */
    private function render_image_select(array $field): void
    {
        $this->render_label($field);

        $options = $field['options'] ?? [];
        $value = $this->get_field_value($field);
        $name = $field['name'] ?? $field['id'];

        if (empty($options)) {
            echo '<p class="description">No options provided</p>';

            return;
        }

        echo '<div class="wp-field-image-select">';

        foreach ($options as $key => $option) {
            $img_src = is_array($option) ? ($option['src'] ?? '') : $option;
            $img_label = is_array($option) ? ($option['label'] ?? $key) : $key;
            $checked = $value === $key ? 'checked' : '';

            printf(
                '<label class="wp-field-image-select-item %s">
                    <input type="radio" name="%s" value="%s" %s>
                    <img src="%s" alt="%s">
                    <span>%s</span>
                </label>',
                $checked ? 'selected' : '',
                esc_attr($name),
                esc_attr($key),
                $checked,
                esc_url($img_src),
                esc_attr($img_label),
                esc_html($img_label),
            );
        }

        echo '</div>';
        $this->render_description($field);
    }

    /**
     * Рендер code_editor (редактор кода с подсветкой синтаксиса)
     */
    private function render_code_editor(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $mode = $field['mode'] ?? 'css';
        $height = $field['height'] ?? '300px';
        $name = $field['name'] ?? $field['id'];

        // Подключаем wp_enqueue_code_editor
        if (function_exists('wp_enqueue_code_editor')) {
            wp_enqueue_code_editor(['type' => $mode]);
        }

        printf(
            '<textarea id="%s" name="%s" class="wp-field-code-editor" data-mode="%s" style="height:%s;width:100%%;">%s</textarea>',
            esc_attr($field['id']),
            esc_attr($name),
            esc_attr($mode),
            esc_attr($height),
            function_exists('esc_textarea') ? esc_textarea($value) : htmlspecialchars($value, ENT_QUOTES),
        );

        $this->render_description($field);
    }

    /**
     * Рендер icon (выбор иконки)
     */
    private function render_icon(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $library = $field['library'] ?? 'dashicons';
        $name = $field['name'] ?? $field['id'];

        echo '<div class="wp-field-icon-picker">';

        printf(
            '<input type="hidden" name="%s" value="%s" class="wp-field-icon-value" />',
            esc_attr($name),
            esc_attr($value),
        );

        echo '<button type="button" class="button wp-field-icon-button">';
        if ($value) {
            printf('<span class="%s %s"></span> %s', esc_attr($library), esc_attr($value), esc_html($value));
        } else {
            echo function_exists('esc_html__') ? esc_html__('Select Icon', 'wp-field') : 'Select Icon';
        }
        echo '</button>';

        // Modal с иконками
        echo '<div class="wp-field-icon-modal" style="display:none;">';
        echo '<div class="wp-field-icon-modal-header">';
        echo '<input type="text" class="wp-field-icon-search" placeholder="'.(function_exists('esc_attr__') ? esc_attr__('Search icons...', 'wp-field') : 'Search icons...').'">';
        echo '<button type="button" class="button wp-field-icon-close">×</button>';
        echo '</div>';
        echo '<div class="wp-field-icon-grid">';

        $custom_icons = $field['icons'] ?? null;
        $icons = $this->get_icon_library($library, $custom_icons);
        foreach ($icons as $icon) {
            printf(
                '<span class="%s %s" data-icon="%s" title="%s"></span>',
                esc_attr($library),
                esc_attr($icon),
                esc_attr($icon),
                esc_attr($icon),
            );
        }

        echo '</div></div></div>';

        $this->render_description($field);
    }

    /**
     * Получить список иконок библиотеки
     */
    private function get_icon_library(string $library, ?array $custom_icons = null): array
    {
        // Если переданы кастомные иконки, используем их
        if (! empty($custom_icons)) {
            return $custom_icons;
        }

        if ($library === 'dashicons') {
            return [
                'dashicons-admin-site', 'dashicons-dashboard', 'dashicons-admin-post', 'dashicons-admin-media',
                'dashicons-admin-links', 'dashicons-admin-page', 'dashicons-admin-comments', 'dashicons-admin-appearance',
                'dashicons-admin-plugins', 'dashicons-admin-users', 'dashicons-admin-tools', 'dashicons-admin-settings',
                'dashicons-admin-network', 'dashicons-admin-home', 'dashicons-admin-generic', 'dashicons-admin-collapse',
                'dashicons-filter', 'dashicons-admin-customizer', 'dashicons-admin-multisite', 'dashicons-welcome-write-blog',
                'dashicons-welcome-add-page', 'dashicons-welcome-view-site', 'dashicons-welcome-widgets-menus', 'dashicons-welcome-comments',
                'dashicons-welcome-learn-more', 'dashicons-format-aside', 'dashicons-format-image', 'dashicons-format-gallery',
                'dashicons-format-video', 'dashicons-format-status', 'dashicons-format-quote', 'dashicons-format-chat',
                'dashicons-format-audio', 'dashicons-camera', 'dashicons-images-alt', 'dashicons-images-alt2',
                'dashicons-video-alt', 'dashicons-video-alt2', 'dashicons-video-alt3', 'dashicons-media-archive',
                'dashicons-media-audio', 'dashicons-media-code', 'dashicons-media-default', 'dashicons-media-document',
                'dashicons-media-interactive', 'dashicons-media-spreadsheet', 'dashicons-media-text', 'dashicons-media-video',
                'dashicons-playlist-audio', 'dashicons-playlist-video', 'dashicons-controls-play', 'dashicons-controls-pause',
            ];
        }

        return apply_filters('wp_field_icon_library', [], $library);
    }

    /**
     * Рендер map (карта Google Maps)
     */
    private function render_map(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $value = is_array($value) ? $value : ['lat' => '', 'lng' => ''];
        $api_key = $field['api_key'] ?? '';
        $provider = strtolower((string) ($field['provider'] ?? 'google'));
        $name = $field['name'] ?? $field['id'];

        echo '<div class="wp-field-map-wrapper" data-map-provider="'.esc_attr($provider).'" data-api-key="'.esc_attr($api_key).'">';

        if ($provider === 'google' && empty($api_key)) {
            echo '<p class="description">'.esc_html__('Google Maps API key required', 'wp-field').'</p>';
            $this->render_description($field);

            echo '</div>';

            return;
        }

        if ($provider === 'google') {
            // Подключаем Google Maps API
            if (function_exists('wp_enqueue_script')) {
                wp_enqueue_script(
                    'google-maps-api',
                    'https://maps.googleapis.com/maps/api/js?key='.urlencode($api_key),
                    [],
                    null,
                    true,
                );
            } else {
                echo '<script src="https://maps.googleapis.com/maps/api/js?key='.urlencode($api_key).'"></script>';
            }
        } else {
            if (function_exists('wp_enqueue_script')) {
                wp_enqueue_script(
                    'leaflet',
                    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
                    [],
                    '1.9.4',
                    true,
                );
                wp_enqueue_style(
                    'leaflet',
                    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
                    [],
                    '1.9.4',
                );
            } else {
                echo '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">';
                echo '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>';
            }
        }

        printf(
            '<input type="hidden" name="%s[lat]" value="%s" class="wp-field-map-lat" />',
            esc_attr($name),
            esc_attr($value['lat']),
        );

        printf(
            '<input type="hidden" name="%s[lng]" value="%s" class="wp-field-map-lng" />',
            esc_attr($name),
            esc_attr($value['lng']),
        );

        printf(
            '<div class="wp-field-map" data-zoom="%d" data-center-lat="%s" data-center-lng="%s" style="height:400px;width:100%%;"></div>',
            function_exists('absint') ? absint($field['zoom'] ?? 12) : (int) ($field['zoom'] ?? 12),
            esc_attr($field['center']['lat'] ?? '55.7558'),
            esc_attr($field['center']['lng'] ?? '37.6173'),
        );

        echo '</div>';

        $this->render_description($field);
    }

    /**
     * Рендер sortable (сортируемый список)
     */
    private function render_sortable(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $value = is_array($value) ? $value : [];
        $options = $field['options'] ?? [];
        $name = $field['name'] ?? $field['id'];

        if (empty($options)) {
            echo '<p class="description">'.esc_html__('No options provided', 'wp-field').'</p>';

            return;
        }

        // Сортируем опции по сохранённому порядку
        $sorted = [];
        foreach ($value as $key) {
            if (isset($options[$key])) {
                $sorted[$key] = $options[$key];
            }
        }

        // Добавляем оставшиеся
        foreach ($options as $key => $label) {
            if (! isset($sorted[$key])) {
                $sorted[$key] = $label;
            }
        }

        echo '<ul class="wp-field-sortable">';

        foreach ($sorted as $key => $label) {
            printf(
                '<li data-value="%s">
                    <span class="dashicons dashicons-menu"></span>
                    <span>%s</span>
                    <input type="hidden" name="%s[]" value="%s" />
                </li>',
                esc_attr($key),
                esc_html($label),
                esc_attr($name),
                esc_attr($key),
            );
        }

        echo '</ul>';

        $this->render_description($field);
    }

    /**
     * Рендер sorter (сортировщик с enabled/disabled)
     */
    private function render_sorter(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $value = is_array($value) ? $value : ['enabled' => [], 'disabled' => []];
        $options = $field['options'] ?? [];
        $name = $field['name'] ?? $field['id'];

        if (empty($options)) {
            echo '<p class="description">'.esc_html__('No options provided', 'wp-field').'</p>';

            return;
        }

        $enabled = $value['enabled'] ?? [];
        $disabled = $value['disabled'] ?? [];

        // Распределяем опции
        $enabled_items = [];
        $disabled_items = [];

        foreach ($enabled as $key) {
            if (isset($options[$key])) {
                $enabled_items[$key] = $options[$key];
            }
        }

        foreach ($options as $key => $label) {
            if (! isset($enabled_items[$key])) {
                $disabled_items[$key] = $label;
            }
        }

        echo '<div class="wp-field-sorter">';

        // Enabled
        echo '<div class="wp-field-sorter-column">';
        echo '<h4>'.esc_html__('Enabled', 'wp-field').'</h4>';
        echo '<ul class="wp-field-sorter-list" data-type="enabled">';

        foreach ($enabled_items as $key => $label) {
            printf(
                '<li data-value="%s">
                    <span class="dashicons dashicons-menu"></span>
                    <span>%s</span>
                    <input type="hidden" name="%s[enabled][]" value="%s" />
                </li>',
                esc_attr($key),
                esc_html($label),
                esc_attr($name),
                esc_attr($key),
            );
        }

        echo '</ul></div>';

        // Disabled
        echo '<div class="wp-field-sorter-column">';
        echo '<h4>'.esc_html__('Disabled', 'wp-field').'</h4>';
        echo '<ul class="wp-field-sorter-list" data-type="disabled">';

        foreach ($disabled_items as $key => $label) {
            printf(
                '<li data-value="%s">
                    <span class="dashicons dashicons-menu"></span>
                    <span>%s</span>
                    <input type="hidden" name="%s[disabled][]" value="%s" />
                </li>',
                esc_attr($key),
                esc_html($label),
                esc_attr($name),
                esc_attr($key),
            );
        }

        echo '</ul></div></div>';

        $this->render_description($field);
    }

    /**
     * Рендер palette (палитра цветов)
     */
    private function render_palette(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $palettes = $field['palettes'] ?? [];
        $name = $field['name'] ?? $field['id'];

        if (empty($palettes)) {
            echo '<p class="description">'.esc_html__('No palettes provided', 'wp-field').'</p>';

            return;
        }

        echo '<div class="wp-field-palette">';

        foreach ($palettes as $key => $palette) {
            $colors = is_array($palette) ? $palette : [$palette];
            $checked = $value === $key ? 'checked' : '';

            printf(
                '<label class="wp-field-palette-item %s">
                    <input type="radio" name="%s" value="%s" %s>
                    <div class="wp-field-palette-colors">',
                $checked ? 'selected' : '',
                esc_attr($name),
                esc_attr($key),
                $checked,
            );

            foreach ($colors as $color) {
                printf(
                    '<span class="wp-field-palette-color" style="background-color:%s;"></span>',
                    esc_attr($color),
                );
            }

            echo '</div></label>';
        }

        echo '</div>';

        $this->render_description($field);
    }

    /**
     * Рендер link (поле ссылки с URL и target)
     */
    private function render_link(array $field): void
    {
        $this->render_label($field);

        $value = $this->get_field_value($field);
        $value = is_array($value) ? $value : ['url' => '', 'text' => '', 'target' => '_self'];
        $name = $field['name'] ?? $field['id'];

        echo '<div class="wp-field-link">';

        // URL
        printf(
            '<div class="wp-field-link-item">
                <label>%s</label>
                <input type="url" name="%s[url]" value="%s" placeholder="https://" class="regular-text">
            </div>',
            esc_html__('URL', 'wp-field'),
            esc_attr($name),
            esc_attr($value['url']),
        );

        // Text
        printf(
            '<div class="wp-field-link-item">
                <label>%s</label>
                <input type="text" name="%s[text]" value="%s" placeholder="%s" class="regular-text">
            </div>',
            esc_html__('Link Text', 'wp-field'),
            esc_attr($name),
            esc_attr($value['text']),
            esc_attr__('Click here', 'wp-field'),
        );

        // Target
        printf(
            '<div class="wp-field-link-item">
                <label>%s</label>
                <select name="%s[target]">
                    <option value="_self" %s>%s</option>
                    <option value="_blank" %s>%s</option>
                </select>
            </div>',
            esc_html__('Target', 'wp-field'),
            esc_attr($name),
            selected($value['target'], '_self', false),
            esc_html__('Same window', 'wp-field'),
            selected($value['target'], '_blank', false),
            esc_html__('New window', 'wp-field'),
        );

        echo '</div>';

        $this->render_description($field);
    }

    /**
     * Рендер backup (экспорт/импорт настроек)
     */
    private function render_backup(array $field): void
    {
        $this->render_label($field);

        $name = $field['name'] ?? $field['id'];
        $export_data = $field['export_data'] ?? [];

        echo '<div class="wp-field-backup">';

        // Export
        echo '<div class="wp-field-backup-section">';
        echo '<h4>'.esc_html__('Export Settings', 'wp-field').'</h4>';

        if (! empty($export_data)) {
            $json_data = function_exists('wp_json_encode') ? wp_json_encode($export_data, JSON_PRETTY_PRINT) : json_encode($export_data, JSON_PRETTY_PRINT);
            printf(
                '<textarea readonly class="wp-field-backup-export" rows="10" style="width:100%%;">%s</textarea>',
                function_exists('esc_textarea') ? esc_textarea($json_data) : htmlspecialchars($json_data, ENT_QUOTES),
            );
            echo '<button type="button" class="button wp-field-backup-copy">'.esc_html__('Copy to Clipboard', 'wp-field').'</button>';
            echo '<button type="button" class="button wp-field-backup-download">'.esc_html__('Download JSON', 'wp-field').'</button>';
        } else {
            echo '<p class="description">'.esc_html__('No data to export', 'wp-field').'</p>';
        }

        echo '</div>';

        // Import
        echo '<div class="wp-field-backup-section">';
        echo '<h4>'.esc_html__('Import Settings', 'wp-field').'</h4>';

        printf(
            '<textarea name="%s" class="wp-field-backup-import" rows="10" placeholder="%s" style="width:100%%;"></textarea>',
            esc_attr($name),
            esc_attr__('Paste JSON data here...', 'wp-field'),
        );

        echo '<button type="button" class="button button-primary wp-field-backup-validate">'.esc_html__('Validate JSON', 'wp-field').'</button>';
        echo '<div class="wp-field-backup-status"></div>';

        echo '</div></div>';

        $this->render_description($field);
    }
}
