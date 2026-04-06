<?php

/**
 * WP_Field Demo Page
 *
 * Deliberately demonstrates only fields that render in the modern runtime
 * without legacy bootstrap/assets.
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

use WpField\Field\Field;
use WpField\Field\FieldInterface;

class WP_Field_V3_Demo
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
            'WP_Field Demo',
            'WP_Field Demo',
            'manage_options',
            'wp-field-v3-demo',
            [$this, 'render_page']
        );
    }

    public function enqueue_assets(string $hook): void
    {
        if ($hook !== 'tools_page_wp-field-v3-demo') {
            return;
        }

        $stylePath = WP_FIELD_PLUGIN_DIR.'assets/css/wp-field-demo.css';
        if (! file_exists($stylePath)) {
            return;
        }

        wp_enqueue_style(
            'wp-field-demo',
            WP_FIELD_PLUGIN_URL.'assets/css/wp-field-demo.css',
            [],
            (string) filemtime($stylePath),
        );
    }

    public function render_page(): void
    {
        $sections = $this->get_sections();
        $fieldCount = array_sum(array_map(static fn (array $section): int => count($section['fields']), $sections));

        echo '<div class="wrap wp-field-demo-page">';
        echo '<h1>WP_Field Demo</h1>';
        echo '<p class="wp-field-demo-page__lead">';
        echo 'Страница работает как baseline для <strong>legacy disabled</strong>: без legacy bootstrap, без legacy assets и только с теми полями, которые рендерятся современным runtime самостоятельно.';
        echo '</p>';

        echo '<div class="wp-field-demo-summary">';
        echo '<div class="wp-field-demo-summary__item"><span class="wp-field-demo-summary__label">Режим</span><strong>modern only</strong></div>';
        echo '<div class="wp-field-demo-summary__item"><span class="wp-field-demo-summary__label">Legacy runtime</span><strong>disabled baseline</strong></div>';
        echo '<div class="wp-field-demo-summary__item"><span class="wp-field-demo-summary__label">Показано полей</span><strong>'.esc_html((string) $fieldCount).'</strong></div>';
        echo '</div>';

        echo '<div class="notice notice-info"><p>';
        echo 'В официальном registry больше нет bridge-типов: все стандартные типы и алиасы работают в native runtime.';
        echo '</p></div>';

        echo '<section class="wp-field-demo-section">';
        echo '<div class="wp-field-demo-section__header">';
        echo '<h2>Не входят в modern-only demo</h2>';
        echo '<p>Ограничение осталось только для unknown/custom типов, которые идут через compat fallback.</p>';
        echo '</div>';
        echo '<div class="wp-field-demo-excluded">';

        foreach ($this->get_excluded_types() as $group => $types) {
            echo '<div class="wp-field-demo-excluded__group">';
            echo '<h3>'.esc_html($group).'</h3>';
            echo '<ul>';

            foreach ($types as $type) {
                echo '<li><code>'.esc_html($type).'</code></li>';
            }

            echo '</ul>';
            echo '</div>';
        }

        echo '</div>';
        echo '</section>';

        foreach ($sections as $section) {
            echo '<section class="wp-field-demo-section">';
            echo '<div class="wp-field-demo-section__header">';
            echo '<h2>'.esc_html($section['title']).'</h2>';
            echo '<p>'.esc_html($section['description']).'</p>';
            echo '</div>';
            echo '<div class="wp-field-demo-grid">';

            foreach ($section['fields'] as $fieldDefinition) {
                echo $this->render_demo_card(
                    $fieldDefinition['type'],
                    $fieldDefinition['title'],
                    $fieldDefinition['description'],
                    $fieldDefinition['field']
                );
            }

            echo '</div>';
            echo '</section>';
        }

        echo '</div>';
    }

    /**
     * @return array<int, array{title: string, description: string, fields: array<int, array{type: string, title: string, description: string, field: FieldInterface}>}>
     */
    private function get_sections(): array
    {
        $placeholderUrl = WP_FIELD_PLUGIN_URL.'placeholder.svg';

        return [
            [
                'title' => 'Input fields',
                'description' => 'Нативные input-based поля, которые рендерятся без legacy bridge.',
                'fields' => [
                    $this->demoField('text', 'Text', 'Базовое текстовое поле.', Field::text('demo_text')->label('Site title')->placeholder('Bagel Shop')->value('Woo Store')),
                    $this->demoField('password', 'Password', 'Пароль через InputField.', Field::make('password', 'demo_password')->label('API password')->placeholder('••••••••')->value('secret-123')),
                    $this->demoField('email', 'Email', 'Email c fluent validation rule.', Field::make('email', 'demo_email')->label('Support email')->placeholder('support@example.com')->value('hello@example.com')->email()),
                    $this->demoField('url', 'URL', 'URL input.', Field::make('url', 'demo_url')->label('Website')->placeholder('https://example.com')->value('https://woocommerce.local')->url()),
                    $this->demoField('tel', 'Tel', 'Телефонный input.', Field::make('tel', 'demo_tel')->label('Phone')->placeholder('+7 999 123-45-67')->value('+7 999 123-45-67')),
                    $this->demoField('number', 'Number', 'Числовое поле c min/max.', Field::make('number', 'demo_number')->label('Delivery time')->min(15)->max(180)->value(45)),
                    $this->demoField('range', 'Range', 'HTML range input.', Field::make('range', 'demo_range')->label('Priority')->attribute('min', 1)->attribute('max', 10)->value(6)),
                    $this->demoField('hidden', 'Hidden', 'Скрытое поле.', Field::make('hidden', 'demo_hidden')->value('hidden-value-123')),
                    $this->demoField('date', 'Date', 'Выбор даты.', Field::make('date', 'demo_date')->label('Start date')->value('2026-04-06')),
                    $this->demoField('time', 'Time', 'Выбор времени.', Field::make('time', 'demo_time')->label('Open at')->value('09:30')),
                    $this->demoField('datetime-local', 'Datetime local', 'Дата и время.', Field::make('datetime-local', 'demo_datetime')->label('Scheduled at')->value('2026-04-06T12:45')),
                    $this->demoField('textarea', 'Textarea', 'Многострочный ввод.', Field::make('textarea', 'demo_textarea')->label('Comment')->placeholder('Leave a comment')->value("Line 1\nLine 2")),
                ],
            ],
            [
                'title' => 'Choice fields',
                'description' => 'Нативные варианты выбора без обращения к legacy runtime.',
                'fields' => [
                    $this->demoField('select', 'Select', 'Обычный select.', Field::make('select', 'demo_select')->label('Delivery type')->options([
                        'pickup' => 'Pickup',
                        'courier' => 'Courier',
                        'dinein' => 'Dine in',
                    ])->value('courier')),
                    $this->demoField('multiselect', 'Multiselect', 'Select с multiple.', Field::make('multiselect', 'demo_multiselect')->label('Working days')->options([
                        'mon' => 'Monday',
                        'tue' => 'Tuesday',
                        'wed' => 'Wednesday',
                        'thu' => 'Thursday',
                    ])->value(['mon', 'wed'])),
                    $this->demoField('checkbox', 'Checkbox', 'Одиночный чекбокс.', Field::make('checkbox', 'demo_checkbox')->label('Enable sync')->checkedValue('yes')->value('yes')),
                    $this->demoField('checkbox_group', 'Checkbox group', 'Группа чекбоксов.', Field::make('checkbox_group', 'demo_checkbox_group')->label('Channels')->options([
                        'site' => 'Website',
                        'app' => 'Mobile app',
                        'phone' => 'Phone orders',
                    ])->value(['site', 'phone'])),
                    $this->demoField('radio', 'Radio', 'Нативный radio-group (ранее bridge).', Field::make('radio', 'demo_radio')->label('Payment type')->attribute('options', [
                        'card' => 'Card',
                        'cash' => 'Cash',
                        'sbp' => 'SBP',
                    ])->value('sbp')),
                    $this->demoField('switcher', 'Switcher', 'Нативный on/off switcher.', Field::make('switcher', 'demo_switcher')->textOn('Enabled')->textOff('Disabled')->checkedValue('on')->value('on')->description('Switcher still renders without legacy CSS.')),
                    $this->demoField('image_picker', 'Image picker', 'Select с preview-src (ранее bridge).', Field::make('image_picker', 'demo_image_picker')->label('Card style')->options([
                        'classic' => ['src' => $placeholderUrl, 'label' => 'Classic'],
                        'minimal' => ['src' => $placeholderUrl, 'label' => 'Minimal'],
                    ])->value('minimal')),
                    $this->demoField('palette', 'Palette', 'Выбор палитры (ранее bridge).', Field::make('palette', 'demo_palette')->label('Color palette')->palettes([
                        'warm' => ['#ff7f50', '#ffb347', '#ffd166'],
                        'cold' => ['#4facfe', '#00f2fe', '#90cdf4'],
                    ])->value('cold')),
                    $this->demoField('button_set', 'Button set', 'Single choice button set.', Field::make('button_set', 'demo_button_set')->options([
                        'small' => 'Small',
                        'medium' => 'Medium',
                        'large' => 'Large',
                    ])->value('medium')),
                    $this->demoField('slider', 'Slider', 'Range slider with value.', Field::make('slider', 'demo_slider')->min(0)->max(100)->step(5)->showValue()->value(65)->description('Uses native range markup.')),
                    $this->demoField('image_select', 'Image select', 'Карточки выбора c изображениями.', Field::make('image_select', 'demo_image_select')->label('Layout')->options([
                        'grid' => ['src' => $placeholderUrl, 'label' => 'Grid'],
                        'list' => ['src' => $placeholderUrl, 'label' => 'List'],
                    ])->value('grid')),
                ],
            ],
            [
                'title' => 'Content fields',
                'description' => 'Статические элементы интерфейса, доступные без legacy bootstrap.',
                'fields' => [
                    $this->demoField('heading', 'Heading', 'Статический заголовок.', Field::make('heading', 'demo_heading')->label('Section heading')->tag('h3')),
                    $this->demoField('subheading', 'Subheading', 'Статический подзаголовок.', Field::make('subheading', 'demo_subheading')->label('Subsection title')->tag('h4')),
                    $this->demoField('notice', 'Notice', 'Информационный блок.', Field::make('notice', 'demo_notice')->label('<strong>Modern-only mode:</strong> this notice is rendered without legacy bridge.')->noticeType('info')),
                    $this->demoField('content', 'Content', 'HTML блок.', Field::make('content', 'demo_content')->content('<p><strong>HTML content</strong> with <a href="https://woocommerce.local" target="_blank" rel="noopener noreferrer">link</a>.</p>')),
                ],
            ],
            [
                'title' => 'Composite fields',
                'description' => 'Составные native-поля, которые собирают вложенные структуры без `WP_Field`.',
                'fields' => [
                    $this->demoField('group', 'Group', 'Вложенные поля с именами вида parent[child].', Field::make('group', 'demo_group')
                        ->label('Contact data')
                        ->fields([
                            Field::text('name')->label('Name')->value('Alex'),
                            Field::make('email', 'email')->label('Email')->value('alex@example.com'),
                            Field::make('tel', 'phone')->label('Phone')->value('+7 900 000-00-00'),
                        ])),
                    $this->demoField('fieldset', 'Fieldset', 'Группировка вложенных полей (ранее bridge).', Field::make('fieldset', 'demo_fieldset')
                        ->attribute('legend', 'Delivery settings')
                        ->fields([
                            Field::text('zone')->label('Zone')->value('Center'),
                            [
                                'id' => 'courier_enabled',
                                'type' => 'checkbox',
                                'label' => 'Courier enabled',
                                'value' => '1',
                            ],
                        ])),
                    $this->demoField('spinner', 'Spinner', 'Числовой счётчик.', Field::make('spinner', 'demo_spinner')->label('Guests')->min(1)->max(12)->step(1)->value(3)->description('Native numeric control.')),
                    $this->demoField('repeater', 'Repeater', 'Повторяющиеся строки с native render.', Field::make('repeater', 'demo_repeater')
                        ->label('Team members')
                        ->fields([
                            Field::text('name')->label('Name'),
                            Field::make('email', 'email')->label('Email'),
                        ])
                        ->min(1)
                        ->max(3)
                        ->buttonLabel('Add member')
                        ->layout('table')
                        ->value([
                            ['name' => 'Alex', 'email' => 'alex@example.com'],
                            ['name' => 'Kate', 'email' => 'kate@example.com'],
                        ])),
                    $this->demoField('flexible_content', 'Flexible content', 'Layout builder без legacy зависимости.', Field::make('flexible_content', 'demo_flexible')
                        ->label('Page blocks')
                        ->addLayout('hero', 'Hero', [
                            Field::text('title')->label('Title'),
                            Field::make('textarea', 'description')->label('Description'),
                        ])
                        ->addLayout('cta', 'CTA', [
                            Field::text('button_text')->label('Button text'),
                            Field::make('url', 'button_url')->label('Button URL'),
                        ])
                        ->buttonLabel('Add block')
                        ->min(1)
                        ->value([
                            [
                                'acf_fc_layout' => 'hero',
                                'title' => 'Welcome to WP_Field',
                                'description' => 'Modern runtime demo without legacy assets.',
                            ],
                            [
                                'acf_fc_layout' => 'cta',
                                'button_text' => 'Read docs',
                                'button_url' => 'https://woocommerce.local',
                            ],
                        ])),
                    $this->demoField('link', 'Link', 'Составное поле ссылки (ранее bridge).', Field::make('link', 'demo_link')->label('CTA link')->value([
                        'url' => 'https://woocommerce.local/docs',
                        'text' => 'Read documentation',
                        'target' => '_blank',
                    ])),
                    $this->demoField('backup', 'Backup', 'UI экспорта/импорта JSON (ранее bridge).', Field::make('backup', 'demo_backup')->label('Settings backup')->attribute('export_data', [
                        'enabled' => true,
                        'endpoint' => 'https://api.example.com',
                    ])),
                    $this->demoField('accordion', 'Accordion', 'Секции с nested-полями и accessible baseline без обязательного JS.', Field::make('accordion', 'demo_accordion')->label('FAQ')->sections([
                        [
                            'title' => 'Delivery',
                            'open' => true,
                            'fields' => [
                                ['id' => 'delivery_note', 'type' => 'text', 'label' => 'Delivery note', 'value' => '30-40 min'],
                            ],
                        ],
                        [
                            'title' => 'Payment',
                            'fields' => [
                                ['id' => 'payment_note', 'type' => 'text', 'label' => 'Payment note', 'value' => 'Card / Cash'],
                            ],
                        ],
                    ])),
                    $this->demoField('tabbed', 'Tabbed', 'Вкладки с role=tablist/tabpanel и nested-полями.', Field::make('tabbed', 'demo_tabbed')->label('Tabs')->tabs([
                        [
                            'title' => 'General',
                            'active' => true,
                            'fields' => [
                                ['id' => 'tab_title_general', 'type' => 'text', 'label' => 'Title', 'value' => 'General settings'],
                            ],
                        ],
                        [
                            'title' => 'Advanced',
                            'fields' => [
                                ['id' => 'tab_title_advanced', 'type' => 'text', 'label' => 'Title', 'value' => 'Advanced settings'],
                            ],
                        ],
                    ])),
                    $this->demoField('sortable', 'Sortable', 'Server-render сортировка через hidden-input contract.', Field::make('sortable', 'demo_sortable')->label('Block order')->options([
                        'hero' => 'Hero',
                        'menu' => 'Menu',
                        'reviews' => 'Reviews',
                    ])->value(['menu', 'hero'])),
                    $this->demoField('sorter', 'Sorter', 'Две колонки enabled/disabled без обязательного JS drag-and-drop.', Field::make('sorter', 'demo_sorter')->label('Visible sections')->options([
                        'hero' => 'Hero',
                        'menu' => 'Menu',
                        'reviews' => 'Reviews',
                        'contacts' => 'Contacts',
                    ])->groups([
                        'enabled' => 'Enabled',
                        'disabled' => 'Disabled',
                    ])->value([
                        'enabled' => ['hero', 'menu'],
                        'disabled' => ['reviews'],
                    ])),
                    $this->demoField('color_group', 'Color group', 'Группа цветовых значений с shape `name[key]`.', Field::make('color_group', 'demo_color_group')->label('Brand colors')->options([
                        'primary' => 'Primary',
                        'secondary' => 'Secondary',
                        'accent' => 'Accent',
                    ])->value([
                        'primary' => '#111827',
                        'secondary' => '#1f2937',
                        'accent' => '#f59e0b',
                    ])),
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function get_excluded_types(): array
    {
        return [
            'Legacy-only fallback route' => [
                'любые custom/unknown типы вне официального registry',
            ],
        ];
    }

    /**
     * @return array{type: string, title: string, description: string, field: FieldInterface}
     */
    private function demoField(string $type, string $title, string $description, FieldInterface $field): array
    {
        return [
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'field' => $field,
        ];
    }

    private function render_demo_card(string $type, string $title, string $description, FieldInterface $field): string
    {
        return sprintf(
            '<article class="wp-field-demo-card"><header class="wp-field-demo-card__header"><div><h3>%s</h3><p>%s</p></div><code>%s</code></header><div class="wp-field-demo-card__preview">%s</div></article>',
            esc_html($title),
            esc_html($description),
            esc_html($type),
            $field->render(),
        );
    }
}

if (is_admin()) {
    new WP_Field_V3_Demo;
}
