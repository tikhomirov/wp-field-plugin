<?php

/**
 * WP_Field — Shared Demo Catalog
 *
 * Single source of truth for field examples used by:
 *  - wp-field-components (React documentation page)
 *  - wp-field-ui-demo (Flux UI admin framework showcase)
 *
 * Each section contains typed field definitions that can be rendered
 * by both PHP server-side and React client-side.
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

use WpField\Field\Field;

/**
 * Returns the full catalog of demo sections and fields.
 *
 * @return array<int, array{id: string, title: string, description: string, fields: array}>
 */
function wp_field_get_demo_catalog(): array
{
    $placeholderUrl = defined('WP_FIELD_PLUGIN_URL')
        ? WP_FIELD_PLUGIN_URL . 'placeholder.svg'
        : '';

    return [
        // ── Input fields ──
        [
            'id' => 'input-fields',
            'title' => 'Input Fields',
            'description' => 'Standard HTML5 input types with built-in validation.',
            'fields' => [
                [
                    'type' => 'text',
                    'title' => 'Text',
                    'description' => 'Basic text input with placeholder and validation.',
                    'field' => Field::text('demo_text')->label('Site title')->placeholder('Bagel Shop')->value('Woo Store'),
                    'code' => "Field::text('site_title')\n    ->label('Site title')\n    ->placeholder('Enter title...')\n    ->required();",
                    'props' => ['label', 'placeholder', 'default', 'required', 'class', 'attributes', 'dependency'],
                ],
                [
                    'type' => 'password',
                    'title' => 'Password',
                    'description' => 'Masked password input.',
                    'field' => Field::make('password', 'demo_password')->label('API password')->placeholder('••••••••')->value('secret-123'),
                    'code' => "Field::make('password', 'api_key')\n    ->label('API password')\n    ->placeholder('••••••••');",
                    'props' => ['label', 'placeholder'],
                ],
                [
                    'type' => 'email',
                    'title' => 'Email',
                    'description' => 'Email with built-in validation rule.',
                    'field' => Field::make('email', 'demo_email')->label('Support email')->placeholder('support@example.com')->value('hello@example.com')->email(),
                    'code' => "Field::make('email', 'support_email')\n    ->label('Support email')\n    ->email();",
                    'props' => ['label', 'placeholder', 'email()'],
                ],
                [
                    'type' => 'url',
                    'title' => 'URL',
                    'description' => 'URL input with url() validation.',
                    'field' => Field::make('url', 'demo_url')->label('Website')->placeholder('https://example.com')->value('https://woocommerce.local')->url(),
                    'code' => "Field::make('url', 'website')\n    ->label('Website')\n    ->url();",
                    'props' => ['label', 'placeholder', 'url()'],
                ],
                [
                    'type' => 'tel',
                    'title' => 'Tel',
                    'description' => 'Phone number input.',
                    'field' => Field::make('tel', 'demo_tel')->label('Phone')->placeholder('+7 999 123-45-67')->value('+7 999 123-45-67'),
                    'code' => "Field::make('tel', 'phone')\n    ->label('Phone')\n    ->placeholder('+7 999 123-45-67');",
                    'props' => ['label', 'placeholder', 'pattern'],
                ],
                [
                    'type' => 'number',
                    'title' => 'Number',
                    'description' => 'Numeric input with min/max/step.',
                    'field' => Field::make('number', 'demo_number')->label('Delivery time (min)')->min(15)->max(180)->value(45),
                    'code' => "Field::make('number', 'delivery_time')\n    ->label('Delivery time')\n    ->min(15)->max(180);",
                    'props' => ['label', 'min', 'max', 'step'],
                ],
                [
                    'type' => 'range',
                    'title' => 'Range',
                    'description' => 'HTML range slider.',
                    'field' => Field::make('range', 'demo_range')->label('Priority')->attribute('min', 1)->attribute('max', 10)->value(6),
                    'code' => "Field::make('range', 'priority')\n    ->label('Priority')\n    ->attribute('min', 1)\n    ->attribute('max', 10);",
                    'props' => ['label', 'min', 'max', 'step'],
                ],
                [
                    'type' => 'hidden',
                    'title' => 'Hidden',
                    'description' => 'Hidden form field.',
                    'field' => Field::make('hidden', 'demo_hidden')->value('hidden-value-123'),
                    'code' => "Field::make('hidden', 'token')\n    ->value('abc123');",
                    'props' => ['value'],
                ],
                [
                    'type' => 'date',
                    'title' => 'Date',
                    'description' => 'Native HTML5 date picker.',
                    'field' => Field::make('date', 'demo_date')->label('Start date')->value('2026-04-06'),
                    'code' => "Field::make('date', 'start_date')\n    ->label('Start date');",
                    'props' => ['label', 'min', 'max'],
                ],
                [
                    'type' => 'time',
                    'title' => 'Time',
                    'description' => 'Native HTML5 time picker.',
                    'field' => Field::make('time', 'demo_time')->label('Open at')->value('09:30'),
                    'code' => "Field::make('time', 'open_at')\n    ->label('Open at');",
                    'props' => ['label'],
                ],
                [
                    'type' => 'datetime-local',
                    'title' => 'DateTime',
                    'description' => 'Combined date and time picker.',
                    'field' => Field::make('datetime-local', 'demo_datetime')->label('Scheduled at')->value('2026-04-06T12:45'),
                    'code' => "Field::make('datetime-local', 'scheduled_at')\n    ->label('Scheduled at');",
                    'props' => ['label'],
                ],
                [
                    'type' => 'textarea',
                    'title' => 'Textarea',
                    'description' => 'Multiline text area.',
                    'field' => Field::make('textarea', 'demo_textarea')->label('Comment')->placeholder('Leave a comment')->value("Line 1\nLine 2"),
                    'code' => "Field::make('textarea', 'comment')\n    ->label('Comment')\n    ->placeholder('Leave a comment');",
                    'props' => ['label', 'placeholder', 'rows'],
                ],
            ],
        ],

        // ── Choice fields ──
        [
            'id' => 'choice-fields',
            'title' => 'Choice Fields',
            'description' => 'Selection controls — dropdowns, radios, checkboxes.',
            'fields' => [
                [
                    'type' => 'select',
                    'title' => 'Select',
                    'description' => 'Single select dropdown.',
                    'field' => Field::make('select', 'demo_select')->label('Delivery type')->options([
                        'pickup' => 'Pickup',
                        'courier' => 'Courier',
                        'dinein' => 'Dine in',
                    ])->value('courier'),
                    'code' => "Field::make('select', 'delivery_type')\n    ->label('Delivery type')\n    ->options([\n        'pickup' => 'Pickup',\n        'courier' => 'Courier',\n    ])->value('courier');",
                    'props' => ['label', 'options', 'placeholder'],
                ],
                [
                    'type' => 'multiselect',
                    'title' => 'Multiselect',
                    'description' => 'Multiple selection dropdown.',
                    'field' => Field::make('multiselect', 'demo_multiselect')->label('Working days')->options([
                        'mon' => 'Monday',
                        'tue' => 'Tuesday',
                        'wed' => 'Wednesday',
                        'thu' => 'Thursday',
                        'fri' => 'Friday',
                    ])->value(['mon', 'wed']),
                    'code' => "Field::make('multiselect', 'working_days')\n    ->label('Working days')\n    ->options([...]);",
                    'props' => ['label', 'options'],
                ],
                [
                    'type' => 'radio',
                    'title' => 'Radio',
                    'description' => 'Single choice radio group.',
                    'field' => Field::make('radio', 'demo_radio')->label('Payment type')->attribute('options', [
                        'card' => 'Card',
                        'cash' => 'Cash',
                        'sbp' => 'SBP',
                    ])->value('sbp'),
                    'code' => "Field::make('radio', 'payment_type')\n    ->label('Payment')\n    ->attribute('options', [\n        'card' => 'Card',\n        'cash' => 'Cash',\n    ]);",
                    'props' => ['label', 'options'],
                ],
                [
                    'type' => 'checkbox',
                    'title' => 'Checkbox',
                    'description' => 'Single checkbox toggle.',
                    'field' => Field::make('checkbox', 'demo_checkbox')->label('Enable sync')->checkedValue('yes')->value('yes'),
                    'code' => "Field::make('checkbox', 'enable_sync')\n    ->label('Enable sync')\n    ->checkedValue('yes');",
                    'props' => ['label', 'checkedValue'],
                ],
                [
                    'type' => 'checkbox_group',
                    'title' => 'Checkbox Group',
                    'description' => 'Multiple checkbox selections.',
                    'field' => Field::make('checkbox_group', 'demo_checkbox_group')->label('Channels')->options([
                        'site' => 'Website',
                        'app' => 'Mobile app',
                        'phone' => 'Phone orders',
                    ])->value(['site', 'phone']),
                    'code' => "Field::make('checkbox_group', 'channels')\n    ->label('Channels')\n    ->options([...]);",
                    'props' => ['label', 'options'],
                ],
                [
                    'type' => 'switcher',
                    'title' => 'Switcher',
                    'description' => 'Toggle switch on/off.',
                    'field' => Field::make('switcher', 'demo_switcher')->textOn('Enabled')->textOff('Disabled')->checkedValue('on')->value('on')->description('Modern toggle switch.'),
                    'code' => "Field::make('switcher', 'feature_toggle')\n    ->textOn('Enabled')\n    ->textOff('Disabled')\n    ->checkedValue('on');",
                    'props' => ['label', 'textOn', 'textOff', 'checkedValue'],
                ],
                [
                    'type' => 'button_set',
                    'title' => 'Button Set',
                    'description' => 'Segmented button selector.',
                    'field' => Field::make('button_set', 'demo_button_set')->options([
                        'small' => 'Small',
                        'medium' => 'Medium',
                        'large' => 'Large',
                    ])->value('medium'),
                    'code' => "Field::make('button_set', 'size')\n    ->options([\n        'small' => 'Small',\n        'medium' => 'Medium',\n    ]);",
                    'props' => ['options'],
                ],
                [
                    'type' => 'slider',
                    'title' => 'Slider',
                    'description' => 'Range slider with value display.',
                    'field' => Field::make('slider', 'demo_slider')->min(0)->max(100)->step(5)->showValue()->value(65)->description('Drag to adjust.'),
                    'code' => "Field::make('slider', 'opacity')\n    ->min(0)->max(100)->step(5)\n    ->showValue();",
                    'props' => ['min', 'max', 'step', 'showValue'],
                ],
                [
                    'type' => 'image_picker',
                    'title' => 'Image Picker',
                    'description' => 'Select with image preview.',
                    'field' => Field::make('image_picker', 'demo_image_picker')->label('Card style')->options([
                        'classic' => ['src' => $placeholderUrl, 'label' => 'Classic'],
                        'minimal' => ['src' => $placeholderUrl, 'label' => 'Minimal'],
                    ])->value('minimal'),
                    'code' => "Field::make('image_picker', 'card_style')\n    ->label('Card style')\n    ->options([\n        'classic' => ['src' => '...', 'label' => 'Classic'],\n    ]);",
                    'props' => ['label', 'options'],
                ],
                [
                    'type' => 'image_select',
                    'title' => 'Image Select',
                    'description' => 'Card-based image selection.',
                    'field' => Field::make('image_select', 'demo_image_select')->label('Layout')->options([
                        'grid' => ['src' => $placeholderUrl, 'label' => 'Grid'],
                        'list' => ['src' => $placeholderUrl, 'label' => 'List'],
                    ])->value('grid'),
                    'code' => "Field::make('image_select', 'layout')\n    ->label('Layout')\n    ->options([\n        'grid' => ['src' => '...', 'label' => 'Grid'],\n    ]);",
                    'props' => ['label', 'options'],
                ],
                [
                    'type' => 'palette',
                    'title' => 'Palette',
                    'description' => 'Color palette selector.',
                    'field' => Field::make('palette', 'demo_palette')->label('Color palette')->palettes([
                        'warm' => ['#ff7f50', '#ffb347', '#ffd166'],
                        'cold' => ['#4facfe', '#00f2fe', '#90cdf4'],
                    ])->value('cold'),
                    'code' => "Field::make('palette', 'theme_palette')\n    ->label('Color palette')\n    ->palettes([\n        'warm' => ['#ff7f50', '#ffb347'],\n    ]);",
                    'props' => ['label', 'palettes'],
                ],
            ],
        ],

        // ── Content / Display fields ──
        [
            'id' => 'content-fields',
            'title' => 'Content & Display',
            'description' => 'Static UI elements — headings, notices, custom HTML.',
            'fields' => [
                [
                    'type' => 'heading',
                    'title' => 'Heading',
                    'description' => 'Static section heading (not saved).',
                    'field' => Field::make('heading', 'demo_heading')->label('Section heading')->tag('h3'),
                    'code' => "Field::make('heading', 'section_title')\n    ->label('Section heading')\n    ->tag('h3');",
                    'props' => ['label', 'tag'],
                ],
                [
                    'type' => 'subheading',
                    'title' => 'Subheading',
                    'description' => 'Smaller sub-section heading.',
                    'field' => Field::make('subheading', 'demo_subheading')->label('Subsection title')->tag('h4'),
                    'code' => "Field::make('subheading', 'sub_title')\n    ->label('Subsection')\n    ->tag('h4');",
                    'props' => ['label', 'tag'],
                ],
                [
                    'type' => 'notice',
                    'title' => 'Notice',
                    'description' => 'Information callout block.',
                    'field' => Field::make('notice', 'demo_notice')->label('This is an informational notice.')->noticeType('info'),
                    'code' => "Field::make('notice', 'info_box')\n    ->label('Important information.')\n    ->noticeType('info');",
                    'props' => ['label', 'noticeType'],
                ],
                [
                    'type' => 'content',
                    'title' => 'Content',
                    'description' => 'Custom HTML block.',
                    'field' => Field::make('content', 'demo_content')->content('<p><strong>Custom HTML</strong> with <a href="#">link</a> support.</p>'),
                    'code' => "Field::make('content', 'help_text')\n    ->content('<p>Any HTML here.</p>');",
                    'props' => ['content'],
                ],
            ],
        ],

        // ── WordPress Integration fields ──
        [
            'id' => 'wp-integration-fields',
            'title' => 'WordPress Integration',
            'description' => 'Fields using WordPress built-in components (media, editor, color picker).',
            'fields' => [
                [
                    'type' => 'color',
                    'title' => 'Color',
                    'description' => 'Color picker with optional alpha channel.',
                    'field' => Field::make('color', 'demo_color')->label('Brand color')->value('#0073aa'),
                    'code' => "Field::make('color', 'brand_color')\n    ->label('Brand color')\n    ->value('#0073aa');",
                    'props' => ['label', 'alpha', 'default'],
                ],
                [
                    'type' => 'media',
                    'title' => 'Media',
                    'description' => 'WordPress media library picker.',
                    'field' => Field::make('media', 'demo_media')->label('Logo')->description('Select from media library.'),
                    'code' => "Field::make('media', 'logo')\n    ->label('Logo')\n    ->attribute('library', 'image');",
                    'props' => ['label', 'button_text', 'library', 'preview'],
                ],
                [
                    'type' => 'image',
                    'title' => 'Image',
                    'description' => 'Image selector with preview.',
                    'field' => Field::make('image', 'demo_image')->label('Featured image')->description('Image with preview.'),
                    'code' => "Field::make('image', 'featured')\n    ->label('Featured image');",
                    'props' => ['label', 'button_text', 'remove_text', 'preview'],
                ],
                [
                    'type' => 'file',
                    'title' => 'File',
                    'description' => 'File upload / selection.',
                    'field' => Field::make('file', 'demo_file')->label('Document')->description('Any file type.'),
                    'code' => "Field::make('file', 'document')\n    ->label('Document');",
                    'props' => ['label', 'button_text', 'library'],
                ],
                [
                    'type' => 'gallery',
                    'title' => 'Gallery',
                    'description' => 'Multi-image gallery with sortable thumbnails.',
                    'field' => Field::make('gallery', 'demo_gallery')->label('Photo gallery'),
                    'code' => "Field::make('gallery', 'photos')\n    ->label('Photo gallery');",
                    'props' => ['label', 'add_button', 'edit_button', 'clear_button'],
                ],
                [
                    'type' => 'editor',
                    'title' => 'Editor',
                    'description' => 'WordPress TinyMCE/classic editor.',
                    'field' => Field::make('editor', 'demo_editor')->label('Page content'),
                    'code' => "Field::make('editor', 'page_content')\n    ->label('Page content');",
                    'props' => ['label', 'rows', 'media_buttons', 'teeny', 'wpautop'],
                ],
                [
                    'type' => 'code_editor',
                    'title' => 'Code Editor',
                    'description' => 'Syntax-highlighted code editor (CodeMirror).',
                    'field' => Field::make('code_editor', 'demo_code_editor')->label('Custom CSS'),
                    'code' => "Field::make('code_editor', 'custom_css')\n    ->label('Custom CSS')\n    ->attribute('mode', 'css');",
                    'props' => ['label', 'mode'],
                ],
                [
                    'type' => 'icon',
                    'title' => 'Icon',
                    'description' => 'Icon picker from registered libraries.',
                    'field' => Field::make('icon', 'demo_icon')->label('Menu icon'),
                    'code' => "Field::make('icon', 'menu_icon')\n    ->label('Menu icon')\n    ->attribute('library', 'dashicons');",
                    'props' => ['label', 'library'],
                ],
            ],
        ],

        // ── Composite fields ──
        [
            'id' => 'composite-fields',
            'title' => 'Composite Fields',
            'description' => 'Nested and repeatable field structures.',
            'fields' => [
                [
                    'type' => 'group',
                    'title' => 'Group',
                    'description' => 'Nested fields with parent[child] naming.',
                    'field' => Field::make('group', 'demo_group')
                        ->label('Contact data')
                        ->fields([
                            Field::text('name')->label('Name')->value('Alex'),
                            Field::make('email', 'email')->label('Email')->value('alex@example.com'),
                            Field::make('tel', 'phone')->label('Phone')->value('+7 900 000-00-00'),
                        ]),
                    'code' => "Field::make('group', 'contact')\n    ->label('Contact')\n    ->fields([\n        Field::text('name')->label('Name'),\n        Field::make('email', 'email')->label('Email'),\n    ]);",
                    'props' => ['label', 'fields'],
                ],
                [
                    'type' => 'fieldset',
                    'title' => 'Fieldset',
                    'description' => 'HTML fieldset with legend.',
                    'field' => Field::make('fieldset', 'demo_fieldset')
                        ->attribute('legend', 'Delivery settings')
                        ->fields([
                            Field::text('zone')->label('Zone')->value('Center'),
                            ['id' => 'courier_on', 'type' => 'checkbox', 'label' => 'Courier enabled', 'value' => '1'],
                        ]),
                    'code' => "Field::make('fieldset', 'delivery')\n    ->attribute('legend', 'Delivery')\n    ->fields([...]);",
                    'props' => ['legend', 'fields'],
                ],
                [
                    'type' => 'repeater',
                    'title' => 'Repeater',
                    'description' => 'Dynamic repeatable rows.',
                    'field' => Field::make('repeater', 'demo_repeater')
                        ->label('Team members')
                        ->fields([
                            Field::text('name')->label('Name'),
                            Field::make('email', 'email')->label('Email'),
                        ])
                        ->min(1)->max(3)
                        ->buttonLabel('Add member')
                        ->layout('table')
                        ->value([
                            ['name' => 'Alex', 'email' => 'alex@example.com'],
                            ['name' => 'Kate', 'email' => 'kate@example.com'],
                        ]),
                    'code' => "Field::make('repeater', 'team')\n    ->label('Team')\n    ->fields([...])\n    ->min(1)->max(5)\n    ->buttonLabel('Add member');",
                    'props' => ['label', 'fields', 'min', 'max', 'buttonLabel', 'layout'],
                ],
                [
                    'type' => 'flexible_content',
                    'title' => 'Flexible Content',
                    'description' => 'Layout builder with multiple block types.',
                    'field' => Field::make('flexible_content', 'demo_flexible')
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
                            ['acf_fc_layout' => 'hero', 'title' => 'Welcome', 'description' => 'Modern field demo.'],
                            ['acf_fc_layout' => 'cta', 'button_text' => 'Read docs', 'button_url' => 'https://github.com'],
                        ]),
                    'code' => "Field::make('flexible_content', 'page')\n    ->addLayout('hero', 'Hero', [...])\n    ->addLayout('cta', 'CTA', [...])\n    ->min(1);",
                    'props' => ['label', 'addLayout', 'min', 'max', 'buttonLabel'],
                ],
                [
                    'type' => 'spinner',
                    'title' => 'Spinner',
                    'description' => 'Numeric counter with +/- controls.',
                    'field' => Field::make('spinner', 'demo_spinner')->label('Guests')->min(1)->max(12)->step(1)->value(3),
                    'code' => "Field::make('spinner', 'guests')\n    ->label('Guests')\n    ->min(1)->max(12);",
                    'props' => ['label', 'min', 'max', 'step'],
                ],
                [
                    'type' => 'link',
                    'title' => 'Link',
                    'description' => 'URL + text + target composite field.',
                    'field' => Field::make('link', 'demo_link')->label('CTA link')->value([
                        'url' => 'https://github.com',
                        'text' => 'View on GitHub',
                        'target' => '_blank',
                    ]),
                    'code' => "Field::make('link', 'cta')\n    ->label('CTA link');",
                    'props' => ['label'],
                ],
                [
                    'type' => 'backup',
                    'title' => 'Backup',
                    'description' => 'Export/import JSON settings.',
                    'field' => Field::make('backup', 'demo_backup')->label('Settings backup')->attribute('export_data', [
                        'enabled' => true,
                        'endpoint' => 'https://api.example.com',
                    ]),
                    'code' => "Field::make('backup', 'settings_backup')\n    ->label('Backup');",
                    'props' => ['label', 'export_data'],
                ],
                [
                    'type' => 'map',
                    'title' => 'Map',
                    'description' => 'Coordinate selector (lat/lng).',
                    'field' => Field::make('map', 'demo_map')->label('Location')->value(['lat' => '55.7558', 'lng' => '37.6173']),
                    'code' => "Field::make('map', 'location')\n    ->label('Location');",
                    'props' => ['label', 'zoom', 'center', 'api_key'],
                ],
            ],
        ],

        // ── Layout fields ──
        [
            'id' => 'layout-fields',
            'title' => 'Layout & Organization',
            'description' => 'Accordion, tabs, sortable lists.',
            'fields' => [
                [
                    'type' => 'accordion',
                    'title' => 'Accordion',
                    'description' => 'Collapsible content sections.',
                    'field' => Field::make('accordion', 'demo_accordion')->label('FAQ')->sections([
                        ['title' => 'Delivery', 'open' => true, 'fields' => [
                            ['id' => 'delivery_note', 'type' => 'text', 'label' => 'Delivery note', 'value' => '30-40 min'],
                        ]],
                        ['title' => 'Payment', 'fields' => [
                            ['id' => 'payment_note', 'type' => 'text', 'label' => 'Payment note', 'value' => 'Card / Cash'],
                        ]],
                    ]),
                    'code' => "Field::make('accordion', 'faq')\n    ->sections([\n        ['title' => 'Q1', 'open' => true, 'fields' => [...]],\n        ['title' => 'Q2', 'fields' => [...]],\n    ]);",
                    'props' => ['label', 'sections'],
                ],
                [
                    'type' => 'tabbed',
                    'title' => 'Tabbed',
                    'description' => 'Tabbed content panels.',
                    'field' => Field::make('tabbed', 'demo_tabbed')->label('Settings')->tabs([
                        ['title' => 'General', 'active' => true, 'fields' => [
                            ['id' => 'tab_general', 'type' => 'text', 'label' => 'Title', 'value' => 'General settings'],
                        ]],
                        ['title' => 'Advanced', 'fields' => [
                            ['id' => 'tab_advanced', 'type' => 'text', 'label' => 'Title', 'value' => 'Advanced settings'],
                        ]],
                    ]),
                    'code' => "Field::make('tabbed', 'settings')\n    ->tabs([\n        ['title' => 'General', 'active' => true, 'fields' => [...]],\n    ]);",
                    'props' => ['label', 'tabs'],
                ],
                [
                    'type' => 'sortable',
                    'title' => 'Sortable',
                    'description' => 'Drag-and-drop reorderable list.',
                    'field' => Field::make('sortable', 'demo_sortable')->label('Block order')->options([
                        'hero' => 'Hero',
                        'menu' => 'Menu',
                        'reviews' => 'Reviews',
                    ])->value(['menu', 'hero']),
                    'code' => "Field::make('sortable', 'block_order')\n    ->label('Block order')\n    ->options([...]);",
                    'props' => ['label', 'options'],
                ],
                [
                    'type' => 'sorter',
                    'title' => 'Sorter',
                    'description' => 'Two-column enabled/disabled sorter.',
                    'field' => Field::make('sorter', 'demo_sorter')->label('Visible sections')->options([
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
                    ]),
                    'code' => "Field::make('sorter', 'sections')\n    ->options([...])\n    ->groups(['enabled' => 'Enabled', 'disabled' => 'Disabled']);",
                    'props' => ['label', 'options', 'groups'],
                ],
            ],
        ],

        // ── Design / Settings-object fields ──
        [
            'id' => 'design-fields',
            'title' => 'Design & Settings Objects',
            'description' => 'Composite design controls — typography, spacing, colors.',
            'fields' => [
                [
                    'type' => 'typography',
                    'title' => 'Typography',
                    'description' => 'Font family, size, weight, line-height, color.',
                    'field' => Field::make('typography', 'demo_typography')->label('Body font'),
                    'code' => "Field::make('typography', 'body_font')\n    ->label('Body font');",
                    'props' => ['label'],
                ],
                [
                    'type' => 'spacing',
                    'title' => 'Spacing',
                    'description' => 'Padding/margin with 4-side controls.',
                    'field' => Field::make('spacing', 'demo_spacing')->label('Content padding'),
                    'code' => "Field::make('spacing', 'content_padding')\n    ->label('Content padding');",
                    'props' => ['label', 'units', 'sides'],
                ],
                [
                    'type' => 'dimensions',
                    'title' => 'Dimensions',
                    'description' => 'Width and height controls.',
                    'field' => Field::make('dimensions', 'demo_dimensions')->label('Container size'),
                    'code' => "Field::make('dimensions', 'container')\n    ->label('Container size');",
                    'props' => ['label', 'units'],
                ],
                [
                    'type' => 'border',
                    'title' => 'Border',
                    'description' => 'Border style, width, color, radius.',
                    'field' => Field::make('border', 'demo_border')->label('Card border'),
                    'code' => "Field::make('border', 'card_border')\n    ->label('Card border');",
                    'props' => ['label', 'styles'],
                ],
                [
                    'type' => 'background',
                    'title' => 'Background',
                    'description' => 'Background color, image, position, size.',
                    'field' => Field::make('background', 'demo_background')->label('Section background'),
                    'code' => "Field::make('background', 'section_bg')\n    ->label('Section background');",
                    'props' => ['label', 'background_fields'],
                ],
                [
                    'type' => 'link_color',
                    'title' => 'Link Color',
                    'description' => 'Color states — normal, hover, active.',
                    'field' => Field::make('link_color', 'demo_link_color')->label('Link colors'),
                    'code' => "Field::make('link_color', 'link_colors')\n    ->label('Link colors');",
                    'props' => ['label', 'states'],
                ],
                [
                    'type' => 'color_group',
                    'title' => 'Color Group',
                    'description' => 'Named group of color values.',
                    'field' => Field::make('color_group', 'demo_color_group')->label('Brand colors')->options([
                        'primary' => 'Primary',
                        'secondary' => 'Secondary',
                        'accent' => 'Accent',
                    ])->value([
                        'primary' => '#111827',
                        'secondary' => '#1f2937',
                        'accent' => '#f59e0b',
                    ]),
                    'code' => "Field::make('color_group', 'brand')\n    ->label('Brand colors')\n    ->options([\n        'primary' => 'Primary',\n    ]);",
                    'props' => ['label', 'options'],
                ],
            ],
        ],

        // ── Custom type example ──
        [
            'id' => 'custom-fields',
            'title' => 'Custom Field Types',
            'description' => 'Demonstrates custom/unknown type fallback via LegacyWrapperField.',
            'fields' => [
                [
                    'type' => 'my_custom_type',
                    'title' => 'Custom Type (Fallback)',
                    'description' => 'Unknown types route through LegacyWrapperField with generic render baseline.',
                    'field' => Field::make('my_custom_type', 'demo_custom')->label('Custom field')->placeholder('Custom type fallback'),
                    'code' => "// Any unknown type falls back to LegacyWrapperField\nField::make('my_custom_type', 'custom')\n    ->label('Custom field');",
                    'props' => ['label', 'placeholder'],
                ],
            ],
        ],
    ];
}

/**
 * Returns a flat list of all field types covered by the catalog.
 *
 * @return array<int, string>
 */
function wp_field_get_demo_catalog_types(): array
{
    $types = [];
    foreach (wp_field_get_demo_catalog() as $section) {
        foreach ($section['fields'] as $fieldDef) {
            $types[] = $fieldDef['type'];
        }
    }

    return array_unique($types);
}
