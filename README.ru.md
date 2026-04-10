<p align="center">
  <img src="logo.svg" alt="WP_Field Logo" width="800">
</p>

<h1 align="center">WP_Field</h1>

<p align="center">
  <strong>Библиотека HTML-полей для WordPress</strong><br>
  Финальный релиз v4 для создания собственных фреймворков, систем настроек и admin UI.<br>
  Fluent API, 52 типа полей, legacy-совместимость и React/Vanilla интеграции там, где они нужны.
</p>

<p align="center">
  <a href="https://packagist.org/packages/rwsite/wp-field"><img src="https://img.shields.io/packagist/v/rwsite/wp-field.svg?style=flat-square" alt="Latest Version"></a>
  <img src="https://img.shields.io/badge/PHP-8.3+-blue.svg?style=flat-square" alt="PHP Version">
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-GPL--2.0--or--later-blue.svg?style=flat-square" alt="License"></a>
</p>

<p align="center">
  <a href="#features">Возможности</a> •
  <a href="#installation">Установка</a> •
  <a href="#quick-start">Быстрый старт</a> •
  <a href="#architecture">Архитектура</a> •
  <a href="#field-types">Типы полей</a> •
  <a href="#demo-pages">Демо-страницы</a> •
  <a href="#development">Разработка</a> •
  <a href="README.md">EN version</a>
</p>

---

## Modern version

WP_Field v4 — production-ready релиз современного field API. Используйте его для новых интеграций и для миграции с разрозненных legacy-массивов. Modern-слой даёт typed fluent builder и более чистую server-rendered модель интеграции с WordPress-формами.

Это не WordPress-free browser runtime. Интерактивные поля по-прежнему опираются на штатные WordPress admin assets там, где это является корректной production-интеграцией.

## Features

### Что нового в v4
- Modern API стал основным слоем интеграции для нового кода.
- Legacy runtime изолирован в `vanilla/` и сохранён для совместимости.
- Standalone vanilla export собирает отдельный installable plugin archive.
- Demo pages разделены по назначению: legacy examples, modern field docs и UI showcase.
- Map fields получили provider-конфигурацию в публичном field API.
- Quality tooling выровнен для PHP, JS и релизной документации.

### Основные возможности
- Fluent builder полей через `WpField\Field\Field`
- 52 поддерживаемых типа полей для простых, составных, layout- и WordPress-integrated controls
- Conditional logic с AND/OR отношениями
- Repeater и flexible content для вложенных структур данных
- Контейнеры для metaboxes, settings pages, taxonomies и users
- Storage strategies для post meta, term meta, user meta, options и custom tables
- React/Vanilla UI layer для admin shell, wizard, repeater и flexible content сценариев
- Legacy compatibility layer для старых интеграций и unsupported custom field types

## Architecture

WP_Field состоит из двух слоёв:

- **Modern API** — `WpField\Field\Field` и классы в `src/Field/Types/`
- **Legacy compatibility layer** — `WP_Field.php` и `vanilla/`

Для нового кода используйте modern API. Legacy-слой оставляйте для существующих интеграций, unsupported custom field types или standalone vanilla package.

### Как работает жизненный цикл поля
1. Создайте поле через `Field::make()` или typed shortcut.
2. Настройте label, description, default, options, validation и field-specific параметры.
3. Отрендерьте поле внутри WordPress admin form.
4. Сохраняйте через container или собственный save flow.
5. Передавайте сохранённое значение обратно в ту же конфигурацию поля, чтобы показать сохранённое состояние.

## WordPress dependencies that remain

Modern-слой по-прежнему использует штатные WordPress-зависимости для этих возможностей:

- `wp.media` / Media Modal — `media`, `image`, `file`, `gallery`, `background`
- `wp-color-picker` — `color`, `palette` и color-based composite controls
- `wp.editor` — classic editor fields
- `wp.codeEditor` — code editor fields
- `jquery-ui-sortable` — `sortable`, `sorter`, `repeater`, `flexible_content`
- `jquery-ui-datepicker` — date/time UI там, где ожидается WordPress-native picker
- `dashicons` или другая icon library — `icon`
- provider-specific assets, если они нужны выбранному map provider

Не отключайте эти assets на экранах с интерактивными полями, если не заменяете их эквивалентной схемой подключения.

## Installation

### Через Composer

```bash
composer require rwsite/wp-field
```

### Ручная установка

1. Скопируйте пакет в `wp-content/plugins/wp-field-plugin`
2. Выполните `composer install --no-dev`
3. Активируйте плагин в WordPress admin

### Сборка frontend assets

```bash
npm install
npm run build
```

## Standalone vanilla build

Вы можете экспортировать legacy runtime как отдельный WordPress-плагин, который не зависит от исходного каталога `woo2iiko`.

```bash
npm run build:standalone
```

Скрипт сборки:
- копирует минимальный runtime из `vanilla/`
- включает legacy assets и переводы
- генерирует отдельный plugin bootstrap
- записывает плагин в `dist/wp-field-vanilla/`
- создаёт `dist/wp-field-vanilla.zip`

В standalone package входит только runtime, необходимый для vanilla-версии:
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

Он не включает modern React layer, demo pages, tests, source SCSS и repository-only development files.

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

Контейнеры встраивают поля в WordPress-экраны и отвечают за сохранение:

- `MetaboxContainer` — post meta и metaboxes
- `SettingsContainer` — settings pages и options
- `TaxonomyContainer` — taxonomy forms и term meta
- `UserContainer` — user profile forms и user meta

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

В WordPress admin debug mode плагин регистрирует demo pages в меню **Tools**:

- **WP_Field Examples** — reference page для legacy/classic API
- **WP_Field Components** — modern field documentation и live examples
- **Admin Shell UI Demo** — UI showcase для shell и wizard слоя

Маршруты:

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

`npm run dev` запускает Vite и следит за `vanilla/assets/scss/wp-field-examples-vanilla.scss`, пересобирая `vanilla/assets/css/wp-field-examples-vanilla.css`.

Редактируйте SCSS-исходники в `vanilla/assets/scss/`. Не правьте generated CSS вручную.

### PHP и общие проверки

```bash
composer test
composer analyse
composer lint:check
./.agents/skills/qa-gate/scripts/verify.sh
```

## Limitations

- Modern-слой не является полностью автономным browser runtime.
- Удаление WordPress admin runtime или нужных UI assets ломает интерактивные поля.
- Часть взаимодействий рассчитана только на backend screens.
- Demo pages — это reference implementations, а не замена штатному plugin bootstrap в production.

## Changelog

История версий находится в [CHANGELOG.md](CHANGELOG.md).

## License

GPL v2 или выше

## Author

Aleksei Tikhomirov (https://rwsite.ru)
