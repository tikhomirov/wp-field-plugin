<p align="center">
  <img src="logo.svg" alt="WP_Field Logo" width="800">
</p>

<h1 align="center">WP_Field</h1>

<p align="center">
  <strong>HTML Fields Library for WordPress</strong><br>
  A foundation for building custom frameworks, settings systems, and admin UIs.<br>
  Fluent API, 52 unique field types (+4 aliases), React/Vanilla UI, and modern v3 architecture.
</p>

<p align="center">
  <a href="https://packagist.org/packages/rwsite/wp-field"><img src="https://img.shields.io/packagist/v/rwsite/wp-field.svg?style=flat-square" alt="Latest Version"></a>
  <img src="https://img.shields.io/badge/PHP-8.3+-blue.svg?style=flat-square" alt="PHP Version">
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-GPL--2.0--or--later-blue.svg?style=flat-square" alt="License"></a>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#installation">Installation</a> •
  <a href="#quick-start">Quick Start</a> •
  <a href="#field-types">Field Types</a> •
  <a href="#examples">Examples</a> •
  <a href="#dependencies">Dependencies</a> •
  <a href="README.ru.md">RU version</a>
</p>

---

## Features

### v3.0 — Modern Laravel-Style API
- ✨ **Fluent Interface** — Chain methods like Laravel: `Field::text('name')->label('Name')->required()`
- 🔁 **Repeater Fields** — Infinite nesting support with min/max constraints
- 🎨 **Flexible Content** — ACF-style layout builder with multiple block types
- ⚛️ **React UI** — Modern React components with Vanilla JS fallback
- 🏗️ **SOLID Architecture** — Interfaces, traits, dependency injection
- 📦 **Storage Strategies** — PostMeta, TermMeta, UserMeta, Options, CustomTable
- 🛡️ **Type Safety** — PHPStan Level 9, strict types, full PHPDoc

### Core Features
- 🚀 **52 Unique Field Types** — Text, select, repeater, flexible content, and more
- ♻️ **4 Compatibility Aliases** — `date_time`, `datetime-local`, `image_picker`, `imagepicker`
- ✅ **Official registry runs in native runtime** — `LegacyWrapperField` is now only unknown/custom fallback
- 🔗 **Conditional Logic** — 14 operators with AND/OR relations
- 🧪 **Full Test Coverage** — Pest/PHPUnit tests with 100% pass rate
- 🎨 **WP Components** — Native WordPress UI integration
- 🌍 **i18n Ready** — Multilingual support

## Requirements

- PHP 8.3+
- WordPress 6.0+
- Composer (for installation)

## Installation

### Via Composer (Recommended)

```bash
composer require rwsite/wp-field
```

### Manual Installation

1. Clone or download to `wp-content/plugins/wp-field-plugin`
2. Run `composer install --no-dev`
3. Activate the plugin in WordPress admin

### Build React Components (Optional)

```bash
npm install
npm run build
```

## Quick Start

### Modern API (v3.0)

```php
use WpField\Field\Field;
use WpField\Container\MetaboxContainer;

// Fluent interface
$field = Field::text('email')
    ->label('Email Address')
    ->placeholder('user@example.com')
    ->required()
    ->email()
    ->class('regular-text');

// Render field
echo $field->render();

// Create metabox with fields
$metabox = new MetaboxContainer('product_details', [
    'title' => 'Product Details',
    'post_types' => ['product'],
]);

$metabox->addField(
    Field::text('sku')->label('SKU')->required()
);

$metabox->addField(
    Field::text('price')->label('Price')->required()
);

$metabox->register();
```

### Repeater Field

```php
$repeater = Field::repeater('team_members')
    ->label('Team Members')
    ->fields([
        Field::text('name')->label('Name')->required(),
        Field::text('position')->label('Position'),
        Field::text('email')->label('Email')->email(),
    ])
    ->min(1)
    ->max(10)
    ->buttonLabel('Add Member')
    ->layout('table');
```

### Flexible Content Field

```php
$flexible = Field::flexibleContent('page_sections')
    ->label('Page Sections')
    ->addLayout('text_block', 'Text Block', [
        Field::text('heading')->label('Heading'),
        Field::text('content')->label('Content'),
    ])
    ->addLayout('image', 'Image', [
        Field::text('image_url')->label('Image URL')->url(),
        Field::text('caption')->label('Caption'),
    ])
    ->min(1)
    ->buttonLabel('Add Section');
```

## Field Types (52 unique + 4 aliases)

### Basic (9)
- `text` — Text input
- `password` — Password field
- `email` — Email input
- `url` — URL input
- `tel` — Telephone input
- `number` — Number input
- `range` — Range slider
- `hidden` — Hidden field
- `textarea` — Multi-line text

### Choice (5)
- `select` — Dropdown list
- `multiselect` — Multiple selection
- `radio` — Radio buttons
- `checkbox` — Single checkbox
- `checkbox_group` — Checkbox group

### Advanced (9)
- `editor` — wp_editor
- `media` — Media library (ID or URL)
- `image` — Image with preview
- `file` — File upload
- `gallery` — Image gallery
- `color` — Color picker
- `date` — Date picker
- `time` — Time picker
- `datetime` — Date and time

### Composite (2)
- `group` — Nested fields
- `repeater` — Repeating elements

### Simple Fields (9)
- `switcher` — On/off switcher
- `spinner` — Number spinner
- `button_set` — Button selection
- `slider` — Value slider
- `heading` — Heading
- `subheading` — Subheading
- `notice` — Notice (info/success/warning/error)
- `content` — Custom HTML content
- `fieldset` — Field grouping

### Medium Complexity Fields (10)
- `accordion` — Collapsible sections
- `tabbed` — Tabs
- `typography` — Typography settings
- `spacing` — Spacing controls
- `dimensions` — Size controls
- `border` — Border settings
- `background` — Background options
- `link_color` — Link colors
- `color_group` — Color group
- `image_select` — Image selection

### High Complexity Fields (8)
- `code_editor` — Code editor with syntax highlighting
- `icon` — Icon picker from library
- `map` — Coordinates map field (native baseline + optional provider enhancement)
- `sortable` — Drag & drop sorting
- `sorter` — Enabled/disabled sorting
- `palette` — Color palette
- `link` — Link field (URL + text + target)
- `backup` — Settings export/import

## Examples

### Dependencies

```php
// Show field only if another field has specific value
Field::text('courier_address')
    ->label('Delivery Address')
    ->when('delivery_type', '==', 'courier');

// Multiple conditions (AND)
Field::text('special_field')
    ->label('Special Field')
    ->when('field1', '==', 'value1')
    ->when('field2', '!=', 'value2');

// Multiple conditions (OR)
Field::text('notification')
    ->label('Notification')
    ->when('type', '==', 'sms')
    ->orWhen('type', '==', 'email');
```

### Repeater

```php
Field::repeater('work_times')
    ->label('Work Times')
    ->min(1)
    ->max(7)
    ->buttonLabel('Add Day')
    ->fields([
        Field::make('select', 'day')
            ->label('Day')
            ->options(['mon' => 'Mon', 'tue' => 'Tue']),
        Field::make('time', 'from')
            ->label('From'),
        Field::make('time', 'to')
            ->label('To'),
    ]);
```

### Group

```php
Field::make('group', 'address')
    ->label('Address')
    ->fields([
        Field::text('city')->label('City'),
        Field::text('street')->label('Street'),
        Field::text('number')->label('Number'),
    ]);
```

### Code Editor

```php
Field::make('code_editor', 'custom_css')
    ->label('Custom CSS')
    ->mode('css') // css, javascript, php, html
    ->attribute('settings', ['height' => '400px']);
```

### Icon Picker

```php
Field::make('icon', 'menu_icon')
    ->label('Menu Icon')
    ->library('dashicons');
```

### Map

```php
Field::make('map', 'location')
    ->label('Location')
    ->zoom(12)
    ->attribute('center', ['lat' => 55.7558, 'lng' => 37.6173]);
```

### Sortable

```php
Field::make('sortable', 'menu_order')
    ->label('Menu Order')
    ->options([
        'home'     => 'Home',
        'about'    => 'About',
        'services' => 'Services',
        'contact'  => 'Contact',
    ]);
```

### Palette

```php
Field::make('palette', 'color_scheme')
    ->label('Color Scheme')
    ->options([
        'blue' => '#0073aa',
        'green' => '#28a745',
        'red' => '#dc3545',
    ]);
```

### Link

```php
Field::make('link', 'cta_button')
    ->label('CTA Button');

// Get value:
// $link = get_post_meta($post_id, 'cta_button', true);
// ['url' => '...', 'text' => '...', 'target' => '_blank']
```

### Accordion

```php
Field::make('accordion', 'settings_accordion')
    ->label('Settings')
    ->sections([
        [
            'title'  => 'General',
            'open'   => true,
            'fields' => [
                Field::text('title')->label('Title'),
            ],
        ],
        [
            'title'  => 'Advanced',
            'fields' => [
                Field::make('textarea', 'desc')->label('Description'),
            ],
        ],
    ]);
```

### Typography

```php
Field::make('typography', 'heading_typography')
    ->label('Heading Typography');

// Saved as:
// [
//     'font_family' => 'Arial',
//     'font_size' => '24',
//     'font_weight' => '700',
//     'line_height' => '1.5',
//     'text_align' => 'center',
//     'color' => '#333333'
// ]
```

## Dependency Operators

- `==` — Equal
- `!=` — Not equal
- `>`, `>=`, `<`, `<=` — Comparison
- `in` — In array
- `not_in` — Not in array
- `contains` — Contains
- `not_contains` — Not contains
- `empty` — Empty
- `not_empty` — Not empty

## Interactive Demo

**See all 48 field types in action:**

👉 **Tools → WP_Field Examples** (Classic API demo)  
👉 `/wp-admin/tools.php?page=wp-field-examples`

👉 **Tools → WP_Field Demo** (modern-only / legacy disabled baseline)  
👉 `/wp-admin/tools.php?page=wp-field-v3-demo`

The demo pages include:
- ✅ All 48 field types with live examples
- ✅ Code for each field
- ✅ Fluent API demonstrations (v3.0)
- ✅ Repeater and Flexible Content examples
- ✅ Conditional Logic with 14 operators
- ✅ React/Vanilla UI mode switching
- ✅ Dependency system demonstration
- ✅ Ability to save and test

## Extensibility

### Adding Custom Field Types

```php
add_filter('wp_field_types', function($types) {
    $types['custom_type'] = ['render_custom', ['default' => 'value']];
    return $types;
});
```

### Adding Icon Libraries

```php
add_filter('wp_field_icon_library', function($icons, $library) {
    if ($library === 'fontawesome') {
        return ['fa-home', 'fa-user', 'fa-cog', ...];
    }
    return $icons;
}, 10, 2);
```

### Custom Value Retrieval

```php
add_filter('wp_field_get_value', function($value, $storage_type, $key, $id, $field) {
    if ($storage_type === 'custom') {
        return get_custom_value($key, $id);
    }
    return $value;
}, 10, 5);
```

## Changelog

See **[CHANGELOG.md](CHANGELOG.md)** for detailed version history.

## Project Stats

- **PHP Lines:** 2705 (legacy/WP_Field.php)
- **JS Lines:** 1222 (wp-field.js)
- **CSS Lines:** 1839 (wp-field.css)
- **Field Types:** 52
- **Dependency Operators:** 14
- **Storage Types:** 5
- **External Dependencies:** 0

## Compatibility

- **WordPress:** 6.0+
- **PHP:** 8.3+
- **Dependencies:** jQuery, jQuery UI Sortable, WordPress built-in components
- **Browsers:** Chrome, Firefox, Safari, Edge (latest 2 versions)

## Performance

- Minimal CSS size: ~20KB
- Minimal JS size: ~15KB
- Lazy loading for heavy components (CodeMirror, Google Maps)
- Optimized selectors and events

## License

GPL v2 or later

## Author

Aleksei Tikhomirov (https://rwsite.ru)
