<p align="center">
  <img src="logo.svg" alt="WP_Field Logo" width="800">
</p>

<h1 align="center">WP_Field</h1>

<p align="center">
  <strong>HTML Fields Library for WordPress</strong><br>
  Final v4 release for building custom frameworks, settings systems, and admin UI.<br>
  Fluent API, 52 field types, legacy compatibility, and React/Vanilla integrations where needed.
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
  <a href="#architecture">Architecture</a> •
  <a href="#field-types">Field Types</a> •
  <a href="#demo-pages">Demo Pages</a> •
  <a href="#development">Development</a> •
  <a href="README.ru.md">RU version</a>
</p>

---

## Modern version

WP_Field v4 is the production-ready release of the modern field API. Use it for new integrations and for migrations away from ad-hoc legacy arrays. The modern layer gives you a typed, fluent field builder and a cleaner server-rendered integration model for WordPress forms.

This is not a WordPress-free browser runtime. Interactive fields still rely on core WordPress admin assets where that is the correct production integration.

## Features

### What's new in v4
- Modern API is the primary integration layer for new code.
- Legacy runtime is isolated in `vanilla/` and remains available for compatibility.
- Standalone vanilla export builds a separate installable plugin archive.
- Demo pages are split by purpose: legacy examples, modern field docs, and UI showcase.
- Map fields expose provider configuration through the public field API.
- Quality tooling is aligned across PHP, JS, and documentation updates.

### Core capabilities
- Fluent field builder through `WpField\Field\Field`
- 52 supported field types across simple, composite, layout, and WordPress-integrated controls
- Conditional logic with AND/OR relations
- Repeater and flexible content for nested data structures
- Containers for metaboxes, settings pages, taxonomies, and users
- Storage strategies for post meta, term meta, user meta, options, and custom tables
- React/Vanilla UI layer for admin shell, wizard, repeater, and flexible content flows
- Legacy compatibility layer for older integrations and unsupported custom field types

## Architecture

WP_Field has two layers:

- **Modern API** — `WpField\Field\Field` and classes under `src/Field/Types/`
- **Legacy compatibility layer** — `WP_Field.php` and `vanilla/`

Use the modern API for new code. Keep the legacy layer for existing integrations, unsupported custom field types, or the standalone vanilla package.

### How the field lifecycle works
1. Create a field with `Field::make()` or a typed shortcut.
2. Configure label, description, defaults, options, validation, and field-specific settings.
3. Render the field inside a WordPress admin form.
4. Save through a container or your own save flow.
5. Pass the stored value back into the same field definition to render the saved state.

## WordPress dependencies that remain

The modern layer still uses WordPress-provided functionality for these features:

- `wp.media` / Media Modal — `media`, `image`, `file`, `gallery`, `background`
- `wp-color-picker` — `color`, `palette`, and color-based composite controls
- `wp.editor` — classic editor fields
- `wp.codeEditor` — code editor fields
- `jquery-ui-sortable` — `sortable`, `sorter`, `repeater`, `flexible_content`
- `jquery-ui-datepicker` — date and time UI where WordPress-native pickers are expected
- `dashicons` or another icon library — `icon`
- provider-specific assets when a map provider requires them

Do not unload these assets on screens that render interactive fields unless you replace them with an equivalent setup.

## Installation

### Via Composer

```bash
composer require rwsite/wp-field
```

### Manual installation

1. Copy the package into `wp-content/plugins/wp-field-plugin`
2. Run `composer install --no-dev`
3. Activate the plugin in WordPress admin

### Build frontend assets

```bash
npm install
npm run build
```

## Standalone vanilla build

You can export the legacy runtime as a separate WordPress plugin that does not depend on the original `woo2iiko` plugin directory.

```bash
npm run build:standalone
```

The build script:
- copies the minimal runtime from `vanilla/`
- includes legacy assets and translations
- generates a dedicated plugin bootstrap
- writes the plugin to `dist/wp-field-vanilla/`
- creates `dist/wp-field-vanilla.zip`

The standalone package includes only the runtime required for the vanilla version:
- `wp-field-vanilla.php`
- `README.txt`
- `vanilla/bootstrap.php`
- `vanilla/WP_Field.php`
- `vanilla/assets/css/wp-field.css`
- `vanilla/assets/js/wp-field.js`
- `lang/wp-field.pot`
- `lang/wp-field-ru_RU.po`
- `lang/wp-field-ru_RU.mo`
- `lang/wp-field-ru_RU.l10n.php`

It does not include the modern React layer, demo pages, tests, source SCSS, or repository-only development files.

## Quick Start

### Modern API (v4.0)

```php
use WpField\Container\MetaboxContainer;
use WpField\Field\Field;

$field = Field::text('email')
    ->label('Email Address')
    ->placeholder('user@example.com')
    ->required();

echo $field->render();

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

### Repeater field

```php
Field::repeater('team_members')
    ->label('Team Members')
    ->fields([
        Field::text('name')->label('Name')->required(),
        Field::text('position')->label('Position'),
        Field::make('email', 'email')->label('Email'),
    ])
    ->min(1)
    ->max(10)
    ->buttonLabel('Add Member')
    ->layout('table');
```

### Flexible content field

```php
Field::flexibleContent('page_sections')
    ->label('Page Sections')
    ->addLayout('text_block', 'Text Block', [
        Field::text('heading')->label('Heading'),
        Field::make('textarea', 'content')->label('Content'),
    ])
    ->addLayout('image_block', 'Image Block', [
        Field::make('image', 'image')->label('Image'),
        Field::text('caption')->label('Caption'),
    ])
    ->min(1)
    ->buttonLabel('Add Section');
```

### Map field provider contract

```php
Field::make('map', 'location')
    ->label('Location')
    ->provider('google')
    ->apiKey('your-api-key')
    ->zoom(12)
    ->center(['lat' => 55.7558, 'lng' => 37.6173]);
```

## Containers and storage

Containers integrate fields with WordPress screens and persistence:

- `MetaboxContainer` — post meta and metaboxes
- `SettingsContainer` — settings pages and options
- `TaxonomyContainer` — taxonomy forms and term meta
- `UserContainer` — user profile forms and user meta

Storage strategies:

- `PostMetaStorage`
- `TermMetaStorage`
- `UserMetaStorage`
- `OptionStorage`
- `CustomTableStorage`

## Field Types

### Basic (9)
- `text`
- `password`
- `email`
- `url`
- `tel`
- `number`
- `range`
- `hidden`
- `textarea`

### Choice (5)
- `select`
- `multiselect`
- `radio`
- `checkbox`
- `checkbox_group`

### WordPress-integrated (9)
- `editor`
- `media`
- `image`
- `file`
- `gallery`
- `color`
- `date`
- `time`
- `datetime-local`

### Composite (2)
- `group`
- `repeater`

### Simple UI (9)
- `switcher`
- `spinner`
- `button_set`
- `slider`
- `heading`
- `subheading`
- `notice`
- `content`
- `fieldset`

### Medium complexity (10)
- `accordion`
- `tabbed`
- `typography`
- `spacing`
- `dimensions`
- `border`
- `background`
- `link_color`
- `color_group`
- `image_select`

### High complexity (8)
- `code_editor`
- `icon`
- `map`
- `sortable`
- `sorter`
- `palette`
- `link`
- `backup`

### Compatibility aliases
- `date_time` → `datetime-local`
- `datetime` → `datetime-local`
- `imagepicker` → `image_picker`

## Demo Pages

In WordPress admin debug mode, the plugin registers demo pages under **Tools**:

- **WP_Field Examples** — legacy/classic API reference page
- **WP_Field Components** — modern field documentation and live examples
- **Admin Shell UI Demo** — UI showcase for the shell and wizard layer

Routes:

- `/wp-admin/tools.php?page=wp-field-examples`
- `/wp-admin/tools.php?page=wp-field-components`
- `/wp-admin/tools.php?page=wp-field-ui-demo`

## Development

### Frontend

```bash
npm run dev
npm run lint
npm run build
```

`npm run dev` starts Vite and watches `vanilla/assets/scss/wp-field-examples-vanilla.scss` to rebuild `vanilla/assets/css/wp-field-examples-vanilla.css`.

Edit SCSS sources under `vanilla/assets/scss/`. Do not edit generated CSS manually.

### PHP and repository checks

```bash
composer test
composer analyse
composer lint:check
./.agents/skills/qa-gate/scripts/verify.sh
```

## Limitations

- The modern layer is not fully standalone in the browser.
- Removing WordPress admin runtime or required UI assets breaks interactive fields.
- Some interactions are intended only for backend screens.
- Demo pages are reference implementations, not a replacement for the normal plugin bootstrap in production.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## License

GPL v2 or later

## Author

Aleksei Tikhomirov (https://rwsite.ru)
