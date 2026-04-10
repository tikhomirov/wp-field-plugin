# API библиотеки WP_Field

Этот документ описывает **стабильный публичный API** библиотеки `wp-field`.
Если вы интегрируете библиотеку в плагин или тему, начинайте отсюда.

## Что считается публичным API

Стабильными считаются только те контракты, которые зафиксированы в коде и используются как точка интеграции:

- фабрика `WpField\Field\Field`;
- интерфейсы `FieldInterface`, `ContainerInterface`, `StorageInterface`;
- контейнеры `MetaboxContainer`, `SettingsContainer`, `TaxonomyContainer`, `UserContainer`;
- storage-классы `PostMetaStorage`, `OptionStorage`, `TermMetaStorage`, `UserMetaStorage`, `CustomTableStorage`;
- conditional API `ConditionalLogic`;
- UI API `NavItem`, `AdminShell`, `AdminShellConfig`, `Wizard`, `WizardConfig`, `UIManager`;
- legacy entrypoint `WP_Field.php` и `vanilla/WP_Field.php` как слой совместимости.

Если контракт не описан здесь или не зафиксирован интерфейсом, считайте его внутренней реализацией.

---

## Быстрый пример

```php
use WpField\Field\Field;
use WpField\Container\MetaboxContainer;

$field = Field::text('email')
    ->label('Email')
    ->placeholder('user@example.com')
    ->required()
    ->email();

$container = new MetaboxContainer('contact_box', [
    'title' => 'Контактные данные',
    'post_types' => ['post'],
]);

$container->addField($field);
$container->register();
```

Базовый поток такой:

1. создайте поле через `Field`;
2. добавьте его в контейнер;
3. вызовите `register()`;
4. контейнер встроит поле в WordPress lifecycle и сохранит значение через нужное storage;
5. поле отвечает за описание, санитизацию и валидацию значения.

---

## 1. Фабрика `WpField\Field\Field`

`Field` — основной вход для нового кода.

### Доступные фабрики

- `Field::text(string $name)`
- `Field::group(string $name)`
- `Field::repeater(string $name)`
- `Field::flexibleContent(string $name)`
- `Field::heading(string $name)`
- `Field::subheading(string $name)`
- `Field::notice(string $name)`
- `Field::content(string $name)`
- `Field::radio(string $name)`
- `Field::media(string $name)`
- `Field::fieldset(string $name)`
- `Field::legacy(string $type, string $name)`
- `Field::make(string $type, string $name)`

### `Field::make()`

`Field::make()` создаёт поддерживаемые типы и алиасы.
Сейчас он нормализует следующие значения:

- `date_time` и `datetime` → `datetime-local`
- `imagepicker` → `image_picker`

Затем фабрика маршрутизирует типы к реальным классам, включая:

- `InputField` для `password`, `email`, `url`, `tel`, `number`, `range`, `hidden`, `date`, `time`, `datetime-local`;
- `TextareaField` для `textarea`;
- `SelectField` и `multiple()` для `select` и `multiselect`;
- `CheckboxField`, `CheckboxGroupField`, `SwitcherField`, `SpinnerField`, `ButtonSetField`, `SliderField`;
- `ImageSelectField`, `ImagePickerField`, `ColorField`, `EditorField`, `ImageField`, `FileField`, `GalleryField`;
- `AccordionField`, `TabbedField`, `TypographyField`, `SpacingField`, `DimensionsField`, `BorderField`, `BackgroundField`, `LinkColorField`, `ColorGroupField`, `CodeEditorField`, `IconField`, `MapField`, `SortableField`, `SorterField`, `PaletteField`, `LinkField`, `BackupField`.

Для неизвестных или кастомных legacy-типов используйте `Field::legacy()`.

### Правило использования

- используйте `Field::make()` для поддерживаемых типов и алиасов;
- используйте `Field::legacy()` только для типов, которые не покрывает modern API;
- не опирайтесь на внутренние классы как на публичный контракт, если их не возвращает фабрика.

---

## 2. Контракт поля `FieldInterface`

Это минимальный стабильный контракт поля.

### Методы

- `getName(): string`
- `getType(): string`
- `toArray(): array`
- `render(): string`
- `sanitize(mixed $value): mixed`
- `validate(mixed $value): bool`
- `getAttribute(string $key, mixed $default = null): mixed`
- `value(mixed $value): static`
- `getValue(): mixed`

### Fluent-методы

На них можно опираться при интеграции:

- `label(string $label): static`
- `placeholder(string $placeholder): static`
- `description(string $description): static`
- `default(mixed $default): static`
- `class(string $class): static`
- `id(string $id): static`
- `disabled(bool $disabled = true): static`
- `readonly(bool $readonly = true): static`
- `setAttribute(string $key, mixed $value): static`
- `attribute(string $key, mixed $value): static`
- `required(bool $required = true): static`
- `min(int|float $min): static`
- `max(int|float $max): static`
- `pattern(string $pattern): static`
- `email(): static`
- `url(): static`
- `when(string $field, string $operator, mixed $value): static`
- `orWhen(string $field, string $operator, mixed $value): static`

Не используйте методы traits как внешний контракт, если они не нужны для интеграции.

---

## 3. Типы полей и значения

Для интеграции важен не полный список всех классов, а shape значения.

### Простые поля

- `TextField`
- `RadioField`
- `MediaField`
- `FieldsetField`

### Составные поля

- `RepeaterField` — массив элементов;
- `FlexibleContentField` — массив блоков, где layout хранится в `acf_fc_layout`;
- `MapField` — массив вида `['lat' => string, 'lng' => string]`.

### Типы WordPress-интеграции

Поля вроде `image`, `file`, `gallery`, `editor`, `color`, `link`, `icon` и похожих могут подключать assets UI-слоя, но серверный контракт значения задаёт само поле.

### Legacy fallback

`LegacyWrapperField` нужен как compat-обёртка для типов, которые ещё не переведены на modern API.

Если нужен полный список поддерживаемых типов, смотрите код `Field::make()` и матрицу поддерживаемых типов в документации проекта.

---

## 4. Контейнеры `ContainerInterface`

### Контракт

- `addField(FieldInterface $field): static`
- `getFields(): array`
- `register(): void`
- `render(): void`
- `save(int|string $id): void`

### Реализации

- `MetaboxContainer` — post meta;
- `SettingsContainer` — options;
- `TaxonomyContainer` — term meta;
- `UserContainer` — user meta.

### Что делает контейнер

Контейнер:

- подключает поля к WordPress hooks;
- читает входные данные;
- вызывает `sanitize()` и `validate()` в метабоксах, taxonomy и user flow;
- использует `register_setting(..., sanitize_callback ...)` в settings flow;
- сохраняет значение через соответствующий storage.

Если вы используете контейнер, не сохраняйте поле вручную из `$_POST`.

---

## 5. Storage `StorageInterface`

### Контракт

- `get(string $key, int|string $id): mixed`
- `set(string $key, mixed $value, int|string $id): bool`
- `delete(string $key, int|string $id): bool`
- `exists(string $key, int|string $id): bool`

### Реализации

- `PostMetaStorage`
- `OptionStorage`
- `TermMetaStorage`
- `UserMetaStorage`
- `CustomTableStorage`

### Правило

Storage хранит данные. Поле и контейнер отвечают за санитизацию и валидацию.

---

## 6. UI API

UI-слой нужен для сложных админ-экранов. Для обычных metabox и settings экранов он не обязателен.

### `NavItem`

`NavItem` строит дерево навигации для `AdminShell`.
Поддерживаются:

- `NavItem::leaf(string $id, string $label, ?array $panels = null)`
- `NavItem::group(string $id, string $label, array $children)`
- `NavItem::flatSections(array $sections)`
- `NavItem::collectLeaves(array $items)`
- `NavItem::firstLeafId(array $items)`
- `NavItem::findLeaf(array $items, string $id)`
- `NavItem::toJsonArray(array $items)`

`group` служит контейнером для дочерних узлов. `leaf` может содержать вкладки через `panels`.

### `AdminShell`

`AdminShell::render()` рисует admin layout с sidebar, tabs, панелями и одной формой вокруг всего экрана.

Поддерживаемые точки входа:

- `render(array $nav, string $active_segment, string $active_panel, string $page_title, string $action_url, string $nonce_field, callable $panel_renderer, AdminShellConfig $config = new AdminShellConfig): void`
- `resolveFromRequest(array $nav, AdminShellConfig $config = new AdminShellConfig): array{segment: string, panel: string}`

### `Wizard`

`Wizard::render()` рисует линейный пошаговый flow.

Поддерживаемые точки входа:

- `render(array $steps, string $active_step, string $action_url, string $nonce_field, callable $step_renderer, WizardConfig $config = new WizardConfig): void`
- `resolveFromRequest(array $steps, WizardConfig $config = new WizardConfig): string`

### `UIManager`

`UIManager` управляет режимом UI и подключением ассетов.

Поддерживаемые методы:

- `setMode(string $mode): void`
- `getMode(): string`
- `isReactMode(): bool`
- `enqueueAssets(): void`
- `init(): void`

Допустимые режимы:

- `vanilla`
- `react`

`enqueueAssets()` подключает только те файлы, которые реально существуют на диске.

---

## 7. Legacy API

Legacy-слой нужен для совместимости с существующим кодом.

### Точки входа

- `wp-field.php` — canonical plugin bootstrap;
- `WP_Field.php` — compat-loader;
- `vanilla/WP_Field.php` — legacy runtime.

### Когда использовать legacy

Используйте legacy только если:

- у вас уже есть старые массивы конфигурации;
- нужно поддержать существующий код без миграции;
- вы работаете с кастомным legacy-типом, который modern API не покрывает.

Для нового кода выбирайте `Field` и контейнеры.

---

## 8. Минимальный контракт интеграции

Для внешнего кода достаточно трёх уровней:

1. `Field` описывает поле.
2. `Container` встраивает его в WordPress lifecycle и сохраняет значение.
3. `Storage` определяет, где лежит значение.

Это и есть стабильная публичная поверхность библиотеки.
