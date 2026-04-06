# API библиотеки WP_Field

Этот файл описывает **публичный API** и основные **контракты** библиотеки `wp-field`.
Если нужно интегрировать библиотеку в плагин, тему или отдельный модуль — начинайте отсюда.

## Что считается публичным API

В этой библиотеке публичными считаются:

- фабрика полей `WpField\Field\Field`;
- интерфейсы `FieldInterface`, `ContainerInterface`, `StorageInterface`;
- контейнеры WordPress-интеграции (`MetaboxContainer`, `SettingsContainer`, `TaxonomyContainer`, `UserContainer`);
- UI-слой (`NavItem`, `AdminShell`, `Wizard`, `UIManager`);
- legacy-совместимость через `legacy/WP_Field.php` (с корневым compat-loader `WP_Field.php`).

> Правило: если метод объявлен в интерфейсе или в явной фабрике, его можно использовать как контракт. Остальные методы лучше считать реализационными, даже если они сейчас публичные.

---

## Быстрый сценарий использования

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

Общий поток работы:

1. создать поле через `Field`;
2. добавить поле в контейнер;
3. зарегистрировать контейнер в WordPress;
4. при сохранении контейнер сам вызовет `sanitize()` и `validate()`;
5. значение будет сохранено в нужное хранилище.

---

## 1. Фабрика полей `WpField\Field\Field`

Фабрика — основной способ создания полей в современном API.

### Методы

- `Field::text(string $name): TextField` — обычное текстовое поле.
- `Field::group(string $name): GroupField` — группа вложенных полей.
- `Field::repeater(string $name): RepeaterField` — повторяющаяся группа строк.
- `Field::flexibleContent(string $name): FlexibleContentField` — гибкие блоки с разными layout.
- `Field::heading(string $name): HeadingField` — статический заголовок.
- `Field::subheading(string $name): SubheadingField` — статический подзаголовок.
- `Field::notice(string $name): NoticeField` — информационный/предупреждающий блок.
- `Field::content(string $name): ContentField` — произвольный HTML-блок.
- `Field::radio(string $name): RadioField` — radio-контрол.
- `Field::media(string $name): MediaField` — поле выбора медиа.
- `Field::fieldset(string $name): FieldsetField` — группировка полей.
- `Field::legacy(string $type, string $name): LegacyWrapperField` — compat-обёртка для неизвестных/custom типов вне официального registry.
- `Field::make(string $type, string $name): FieldInterface` — универсальный вход.

`Field::make()` покрывает весь legacy registry (52 unique типа) и официальные алиасы (`date_time`, `datetime-local`, `image_picker`, `imagepicker`) через native-классы.

### Поведение `make()`

`Field::make()` выбирает современную native-реализацию для всех официальных типов и алиасов.
`LegacyWrapperField` используется только для неизвестных/кастомных типов вне registry.

Пример:

```php
Field::make('text', 'email');
Field::make('switcher', 'enabled');
Field::make('imagepicker', 'layout_style'); // alias -> ImagePickerField
Field::make('my_custom_type', 'payload'); // unknown type -> LegacyWrapperField fallback
```

### Когда использовать `legacy()`

Используйте `Field::legacy()` только для явно кастомных/нестандартных legacy-типов, которых нет в официальном registry.
Для всех стандартных типов и алиасов используйте `Field::make()`: он уже маршрутизирует в соответствующий OOP-класс.

---

## 2. Контракт поля `WpField\Field\FieldInterface`

Это базовый контракт любого современного поля.

### Идентификация

- `getName(): string` — имя поля.
- `getType(): string` — тип поля.

### Значение и представление

- `value(mixed $value): static` — установить текущее значение.
- `getValue(): mixed` — получить значение с учётом default.
- `toArray(): array` — сериализовать поле в массив конфигурации.
- `render(): string` — вернуть HTML.

### Работа с данными

- `sanitize(mixed $value): mixed` — очистить значение перед сохранением.
- `validate(mixed $value): bool` — проверить корректность значения.
- `getAttribute(string $key, mixed $default = null): mixed` — прочитать атрибут.

### Fluent API атрибутов

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

### Валидация

- `required(bool $required = true): static`
- `min(int|float $min): static`
- `max(int|float $max): static`
- `pattern(string $pattern): static`
- `email(): static`
- `url(): static`

### Условная логика

- `when(string $field, string $operator, mixed $value): static`
- `orWhen(string $field, string $operator, mixed $value): static`

> На текущей реализации через trait также доступны `getConditions()`, `hasConditions()`, `getAttributes()`, `isRequired()`, `getValidationRules()`. Эти методы считаются runtime-утилитами и не входят в минимальный стабильный контракт интеграции.

---

## 3. Базовые типы полей

### `TextField`

Текстовое поле для простого ввода.

### `RadioField`

Одиночный выбор из набора значений.

### `MediaField`

Поле для выбора медиафайла.

Дополнительный метод:

- `library(string $type): static` — ограничить библиотеку медиа по типу.

### `FieldsetField`

Группировка полей в один визуальный блок.

Дополнительный метод:

- `fields(array $fields): static` — задать вложенные поля.

### `GroupField`

Группа вложенных полей с именами вида `parent[child]`.

Методы:

- `fields(array $fields): static` — задать вложенные поля.
- `addField(FieldInterface $field): static` — добавить одно вложенное поле.
- `getFields(): array` — получить вложенные поля.
- `toArray(): array` — сериализация с вложенными полями.
- `sanitize(mixed $value): mixed` — очистка значения по вложенным полям.
- `validate(mixed $value): bool` — проверка вложенных полей.
- `render(): string` — HTML-рендер.

### `HeadingField`

Статический заголовок.

Дополнительные методы:

- `tag(string $tag): static`

### `SubheadingField`

Статический подзаголовок.

Дополнительные методы:

- `tag(string $tag): static`

### `NoticeField`

Информационный или предупреждающий блок.

Дополнительный метод:

- `noticeType(string $type): static`

### `ContentField`

Произвольный HTML-блок.

Дополнительный метод:

- `content(string $content): static`

### `SwitcherField`

On/off переключатель.

Дополнительные методы:

- `textOn(string $text): static`
- `textOff(string $text): static`
- `checkedValue(string $value): static`

### `SpinnerField`

Числовой счётчик с кнопками увеличения и уменьшения.

Дополнительные методы:

- `step(int|float $step): static`
- `unit(string $unit): static`

`min()` и `max()` в этом типе используются и как render-атрибуты, и как validation-границы.

### `ButtonSetField`

Группа кнопок для выбора одного или нескольких значений.

Дополнительный метод:

- `multiple(bool $multiple = true): static`

### `SliderField`

Ползунок диапазона.

Дополнительные методы:

- `step(int|float $step): static`
- `showValue(bool $show = true): static`

`min()` и `max()` в этом типе используются и как render-атрибуты, и как validation-границы.

### `ImageSelectField`

Выбор значения через карточки изображений.

Этот тип наследует `options()` от `ChoiceField`.

### `ChoiceField`

Абстрактная база для полей выбора.

Дополнительные методы:

- `options(array $options): static`
- `getOptions(): array`

> Этот класс сам по себе не создаётся напрямую, но задаёт общий контракт для choice-подобных полей.

### `ImagePickerField`

Селект с визуальным контрактом `data-img-src` для каждой опции.

Дополнительный метод:

- `options(array $options): static`

### `PaletteField`

Выбор палитры через набор цветовых свотчей.

Дополнительные методы:

- `options(array $options): static`
- `palettes(array $palettes): static`

### `LinkField`

Составное поле ссылки.

Value shape:

- `url: string`
- `text: string`
- `target: "_self"|"_blank"`

### `TypographyField`

Составное поле типографики.

Value shape:

- `font_family: string`
- `font_size: string` (numeric-string or empty)
- `font_weight: string`
- `line_height: string` (numeric-string or empty)
- `text_align: string`
- `text_transform: string`
- `color: string`

### `SpacingField`

Составное поле отступов.

Value shape:

- `top: string` (numeric-string or empty)
- `right: string` (numeric-string or empty)
- `bottom: string` (numeric-string or empty)
- `left: string` (numeric-string or empty)
- `unit: string`

### `DimensionsField`

Составное поле размеров.

Value shape:

- `width: string` (numeric-string or empty)
- `height: string` (numeric-string or empty)
- `unit: string`

### `BorderField`

Составное поле границы.

Value shape:

- `style: string`
- `width: string` (numeric-string or empty)
- `color: string`

### `BackgroundField`

Составное поле фона.

Value shape:

- `color: string`
- `image: string`
- `position: string`
- `size: string`
- `repeat: string`
- `attachment: string`

### `LinkColorField`

Составное поле цветов ссылок.

Value shape:

- `normal: string`
- `hover: string`
- `active: string`
- и дополнительные custom states при использовании `states([...])`

### `BackupField`

UI поля экспорта/импорта JSON-настроек.

Особенности:

- `sanitize()` возвращает trimmed JSON-string;
- `validate()` проверяет валидность JSON (или пустое значение).

---

## 4. Составные поля

### `RepeaterField`

Повторяющиеся строки или группы.

Методы:

- `fields(array $fields): static` — задать набор вложенных полей целиком.
- `addField(FieldInterface $field): static` — добавить одно вложенное поле.
- `getFields(): array` — получить вложенные поля.
- `min(int|float $min): static` — минимальное количество строк.
- `max(int|float $max): static` — максимальное количество строк.
- `buttonLabel(string $label): static` — подпись кнопки добавления.
- `layout(string $layout): static` — layout рендера (`table`, `block`, `row`).
- `toArray(): array` — сериализация с вложенными полями.
- `sanitize(mixed $value): mixed` — очистка каждой строки по вложенным полям.
- `validate(mixed $value): bool` — проверка min/max и валидности вложенных полей.
- `render(): string` — HTML-рендер.

### `FlexibleContentField`

Набор layout-блоков, где каждый блок может иметь свою структуру.

Методы:

- `addLayout(string $name, string $label, array $fields): static` — добавить layout.
- `getLayouts(): array` — получить список layout-ов.
- `min(int|float $min): static` — минимальное число блоков.
- `max(int|float $max): static` — максимальное число блоков.
- `buttonLabel(string $label): static` — подпись кнопки добавления.
- `toArray(): array` — сериализация layouts и их полей.
- `sanitize(mixed $value): mixed` — очистка блоков по активному layout.
- `validate(mixed $value): bool` — проверка структуры, layout и вложенных полей.
- `render(): string` — HTML-рендер.

### `MapField`

Поле карты с baseline-режимом без внешнего провайдера.

Value shape:

- `lat: string` (numeric-string в диапазоне `-90..90` или пусто)
- `lng: string` (numeric-string в диапазоне `-180..180` или пусто)

Особенности:

- server-render baseline всегда доступен (ручной ввод координат);
- optional JS enhancement использует `wp-field-integrations.js` (геолокация/интерактив).

### `LegacyWrapperField`

Compat-обёртка для неизвестных/custom типов вне официального registry.

Методы:

- `config(array $config): static` — добавить legacy-конфигурацию.
- `render(): string` — делегировать вывод старому рендереру.

---

## 5. Контейнеры WordPress-интеграции

Все контейнеры реализуют `WpField\Container\ContainerInterface`.

### Контракт `ContainerInterface`

- `addField(FieldInterface $field): static`
- `getFields(): array`
- `register(): void`
- `render(): void`
- `save(int|string $id): void`

### Базовая реализация `AbstractContainer`

Общий API, доступный во всех контейнерах:

- `getField(string $name): ?FieldInterface`
- `getId(): string`
- `getConfig(?string $key = null, mixed $default = null): mixed`

### `MetaboxContainer`

Используется для metabox в редакторе записи.

Конфиг:

- `title`
- `post_types`
- `context`
- `priority`

Поведение:

- регистрирует metabox через `add_meta_boxes`;
- сохраняет значения через `save_post`;
- защищает сохранение nonce и capability check;
- пишет данные в `post meta`.

### `SettingsContainer`

Используется для страницы настроек в админке.

Конфиг:

- `page_title`
- `menu_title`
- `capability`
- `menu_slug`
- `icon`
- `position`
- `parent_slug`

Поведение:

- регистрирует страницу через `admin_menu`;
- регистрирует настройки через `admin_init`;
- сохраняет значения через механизм `options.php`;
- пишет данные в `wp_options`.

### `TaxonomyContainer`

Используется для форм терминов таксономий.

Конфиг:

- `taxonomies`

Поведение:

- рендерит форму добавления и редактирования термина;
- сохраняет данные через `created_{$taxonomy}` и `edited_{$taxonomy}`;
- пишет данные в `term meta`.

### `UserContainer`

Используется для полей профиля пользователя.

Конфиг:

- `title`

Поведение:

- рендерит поля в `show_user_profile` и `edit_user_profile`;
- сохраняет данные через `personal_options_update` и `edit_user_profile_update`;
- пишет данные в `user meta`.

---

## 6. Контракт хранилища `WpField\Storage\StorageInterface`

Хранилище отвечает только за сохранение и получение значения.

Методы:

- `get(string $key, int|string $id): mixed`
- `set(string $key, mixed $value, int|string $id): bool`
- `delete(string $key, int|string $id): bool`
- `exists(string $key, int|string $id): bool`

### Реализации

- `PostMetaStorage` — запись в `post meta`.
- `OptionStorage` — запись в `wp_options`.
- `TermMetaStorage` — запись в `term meta`.
- `UserMetaStorage` — запись в `user meta`.
- `CustomTableStorage` — запись в пользовательскую таблицу.

### Важное соглашение

Контейнер вызывает `sanitize()` и `validate()` у поля до записи, а само хранилище остаётся максимально простым.

---

## 7. Условная логика

Класс `WpField\Conditional\ConditionalLogic` — статический помощник для проверки зависимостей.

### Методы

- `evaluate(array $conditions, array $values, string $relation = 'AND'): bool`
- `shouldDisplay(array $conditions, array $values, string $relation = 'AND'): bool`
- `shouldSave(array $conditions, array $values, string $relation = 'AND'): bool`

### Поддерживаемые операторы

- сравнение: `==`, `!=`, `===`, `!==`
- числа: `>`, `>=`, `<`, `<=`
- строки: `contains`, `not_contains`, `starts_with`, `ends_with`
- множества: `in`, `not_in`
- пустота: `empty`, `not_empty`

### Смысл

- `shouldDisplay()` — использовать, когда нужно скрыть/показать поле.
- `shouldSave()` — использовать, когда нужно решить, сохранять ли значение.

---

## 8. UI API

Этот слой нужен для построения более сложной админ-оболочки и мастера настройки.

### `NavItem`

Строит дерево навигации для `AdminShell`.

Методы:

- `leaf(string $id, string $label, ?array $panels = null): self`
- `group(string $id, string $label, array $children): self`
- `isGroup(): bool`
- `isLeaf(): bool`
- `flatSections(array $sections): array`
- `collectLeaves(array $items): array`
- `firstLeafId(array $items): string`
- `findLeaf(array $items, string $id): ?self`
- `toJsonArray(array $items): array`

### `AdminShellConfig`

Конфигурация shell-оболочки.

Поля конструктора:

- `section_query_key`
- `tab_query_key`
- `post_action`
- `save_label`
- `wrapper_extra_class`

### `AdminShell`

Методы:

- `render(array $nav, string $active_segment, string $active_panel, string $page_title, string $action_url, string $nonce_field, callable $panel_renderer, AdminShellConfig $config = new AdminShellConfig()): void`
- `resolveFromRequest(array $nav, AdminShellConfig $config = new AdminShellConfig()): array{segment: string, panel: string}`

Используйте, когда нужен:

- sidebar + tabs;
- несколько панелей внутри одного раздела;
- единый form wrapper для нескольких экранов.

### `WizardConfig`

Конфигурация мастера.

Поля конструктора:

- `post_action`
- `step_query_key`
- `next_label`
- `back_label`
- `skip_label`
- `finish_label`
- `wrapper_extra_class`

### `Wizard`

Методы:

- `render(array $steps, string $active_step, string $action_url, string $nonce_field, callable $step_renderer, WizardConfig $config = new WizardConfig()): void`
- `resolveFromRequest(array $steps, WizardConfig $config = new WizardConfig()): string`

Используйте, когда нужен линейный пошаговый сценарий настройки.

### `UIManager`

Служебный менеджер подключения ассетов.

Методы:

- `setMode(string $mode): void`
- `getMode(): string`
- `isReactMode(): bool`
- `enqueueAssets(): void`
- `init(): void`

Дополнительно:

- режим можно переопределить фильтром `wp_field_ui_mode`;
- допустимые значения режима: `vanilla` и `react`.

---

## 9. Legacy API `legacy/WP_Field.php`

Legacy-класс находится в `legacy/WP_Field.php`.
WordPress entrypoint плагина — `wp-field.php`, а корневой `WP_Field.php` оставлен как compat-loader для старых include-путей.

### Что он даёт

- автозагрузку библиотеки;
- legacy-класс `WP_Field`;
- совместимость с конфигурациями старого формата;
- fallback для типов, которые ещё не переведены на новый OOP-слой.

### Основные публичные элементы

- `WP_Field::$allowed_storage_types` — список разрешённых типов хранилищ.
- `WP_Field::make(array $params, bool $output = true)` — универсальный legacy-вход.
- `render($output = true)` — вывести или вернуть HTML.
- `get_value(string $key, $id = null)` — прочитать значение из нужного хранилища.
- `enqueue_assets()` — подключить JS/CSS legacy-слоя.

### Когда использовать legacy

Только если:

- у вас уже есть старые массивы конфигурации;
- нужно быстро поддержать старый код без переписывания;
- нужный тип поля ещё не переведён в современный API.

Для нового кода лучше использовать `WpField\Field\Field` и контейнеры.

---

## 10. Рекомендуемая схема интеграции

### Для метабокса

1. создать поля через `Field::*`;
2. собрать их в `MetaboxContainer`;
3. вызвать `register()` на `plugins_loaded` или раньше, когда уже доступен WordPress;
4. не вызывать `render()` вручную без контейнера, если нужен полноценный save flow.

### Для страницы настроек

1. создать `SettingsContainer`;
2. добавить поля;
3. зарегистрировать контейнер;
4. использовать `options.php` для сохранения.

### Для сложного админ-интерфейса

1. собрать дерево через `NavItem`;
2. отдать его в `AdminShell::render()`;
3. рендерить содержимое через callback `panel_renderer`;
4. использовать `Wizard` для линейных сценариев.

---

## 11. Практические правила

- Новые поля создавайте через фабрику `Field`.
- Для поддержки старых типов используйте `Field::legacy()` или `Field::make()`.
- В контейнеры добавляйте только объекты `FieldInterface`.
- Не сохраняйте данные напрямую из `$_POST` — пусть это делает контейнер.
- Если добавляете новый публичный метод, документируйте его здесь и в `README`.

---

## 12. Минимальный контракт для внешней интеграции

Если вы подключаете библиотеку из другого плагина, достаточно соблюдать три уровня контракта:

1. **Field** — создать и настроить поле.
2. **Container** — зарегистрировать поле в WordPress и сохранить значение.
3. **Storage** — выбрать, где хранить данные.

Это и есть минимальная стабильная точка взаимодействия с библиотекой.
