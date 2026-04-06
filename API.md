# API библиотеки WP_Field

Короткая сводка **стабильного публичного API** библиотеки `wp-field`.
Если нужно интегрировать библиотеку в плагин или тему, начинать стоит отсюда.

## Что считать публичным API

Публичными считаются:

- фабрика `WpField\Field\Field`;
- интерфейсы `FieldInterface`, `ContainerInterface`, `StorageInterface`;
- контейнеры `MetaboxContainer`, `SettingsContainer`, `TaxonomyContainer`, `UserContainer`;
- UI API `NavItem`, `AdminShell`, `Wizard`, `UIManager`;
- legacy entrypoint `WP_Field.php` и класс `vanilla/WP_Field.php` только как compat-слой.

Правило:
- если контракт зафиксирован интерфейсом, фабрикой или явно описан ниже, его можно использовать;
- остальные публичные методы лучше считать реализационными деталями.

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

Базовый поток:

1. создать поле через `Field`;
2. добавить его в контейнер;
3. вызвать `register()`;
4. для metabox/taxonomy/user-контейнеров сохранение идёт через `sanitize()` и `validate()`;
5. для `SettingsContainer` сохранение идёт через `options.php` и `sanitize_callback`;
6. значение уходит в соответствующее хранилище.

---

## 1. Фабрика `WpField\Field\Field`

Это основной вход для нового кода.

### Основные методы

- `Field::text(string $name)`
- `Field::radio(string $name)`
- `Field::media(string $name)`
- `Field::fieldset(string $name)`
- `Field::repeater(string $name)`
- `Field::flexibleContent(string $name)`
- `Field::make(string $type, string $name)`
- `Field::legacy(string $type, string $name)`

### Правило маршрутизации

- `Field::make()` нужно использовать для официальных типов и алиасов.
- `Field::legacy()` нужен только для неизвестных или кастомных legacy-типов.
- `LegacyWrapperField` не считается основным runtime для supported matrix; это compat fallback.

---

## 2. Контракт поля `FieldInterface`

Минимальный стабильный контракт поля:

- `getName(): string`
- `getType(): string`
- `getAttribute(string $key, mixed $default = null): mixed`
- `value(mixed $value): static`
- `getValue(): mixed`
- `toArray(): array`
- `render(): string`
- `sanitize(mixed $value): mixed`
- `validate(mixed $value): bool`

### Fluent-методы, на которые можно опираться

- `label(string $label): static`
- `description(string $description): static`
- `placeholder(string $placeholder): static`
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

Замечание:
- служебные getter-методы traits не стоит использовать как внешний контракт, если они не нужны для интеграции.

---

## 3. Типы полей, на которые безопасно опираться

### Простые

- `TextField`
- `RadioField`
- `MediaField`
- `FieldsetField`

### Составные

- `RepeaterField`
- `FlexibleContentField`
- `MapField`

### Compat

- `LegacyWrapperField` для unknown/custom fallback.

### Практическое правило

Для интеграции важно не перечисление всех 50+ типов, а следующее:

- официальные типы создаются через `Field::make()`;
- сложные поля имеют собственный value-shape и вложенную санитизацию;
- custom/unknown типы идут через legacy fallback.

---

## 4. Особые value-shape контракты

Эти формы данных стоит учитывать при интеграции:

- `RepeaterField` -> массив строк/элементов.
- `FlexibleContentField` -> массив блоков, где layout хранится в ключе `acf_fc_layout`.
- `MapField` -> `['lat' => string, 'lng' => string]`.
- `Media`/`image`/`file`/`gallery` и похожие WP-интеграционные типы могут требовать asset enhancement, но серверный контракт значения задаётся самим полем.
- settings-object типы (`typography`, `spacing`, `dimensions`, `border`, `background`, `link_color`) используют массивы с фиксированными под-ключами и санитизируются внутри поля.

Если нужен полный список supported типов, смотреть `docs/wp-field/supported-matrix.md`.

---

## 5. Контейнеры `ContainerInterface`

Контракт контейнера:

- `addField(FieldInterface $field): static`
- `getFields(): array`
- `register(): void`
- `render(): void`
- `save(int|string $id): void`

### Реализации

- `MetaboxContainer` -> `post meta`
- `SettingsContainer` -> `wp_options`
- `TaxonomyContainer` -> `term meta`
- `UserContainer` -> `user meta`

### Смысл контейнера

Контейнер:

- встраивает поля в WordPress-хуки;
- читает входные данные;
- в metabox/taxonomy/user flow вызывает `sanitize()` и `validate()`;
- в settings flow использует `register_setting(... sanitize_callback ...)`;
- сохраняет результат в нужное storage.

Не нужно сохранять данные поля вручную из `$_POST`, если используется контейнер.

---

## 6. Хранилища `StorageInterface`

Минимальный контракт:

- `get(string $key, int|string $id): mixed`
- `set(string $key, mixed $value, int|string $id): bool`
- `delete(string $key, int|string $id): bool`
- `exists(string $key, int|string $id): bool`

Реализации:

- `PostMetaStorage`
- `OptionStorage`
- `TermMetaStorage`
- `UserMetaStorage`
- `CustomTableStorage`

Правило:
- storage хранит данные;
- ответственность за sanitize/validate лежит на поле и контейнере.

---

## 7. Conditional Logic

Класс `WpField\Conditional\ConditionalLogic` используется для проверки условий.

Основные методы:

- `evaluate(array $conditions, array $values, string $relation = 'AND'): bool`
- `shouldDisplay(array $conditions, array $values, string $relation = 'AND'): bool`
- `shouldSave(array $conditions, array $values, string $relation = 'AND'): bool`

Поддерживаются базовые операторы сравнения, диапазонов, строк, множеств и проверки пустоты.

---

## 8. UI API

Этот слой нужен для сложных админ-экранов, но не обязателен для обычных полей.

### `NavItem`

Строит дерево навигации для shell-интерфейса.

### `AdminShell`

Рендерит layout вида sidebar + tabs + panels.

### `Wizard`

Рендерит пошаговый flow.

### `UIManager`

Отвечает за подключение ассетов и режим UI:

- `vanilla`
- `react`

Если нужен обычный metabox/settings экран, UI API можно не использовать.

---

## 9. Legacy API

Legacy-слой нужен только для совместимости.

Точки входа:

- `wp-field.php` -> canonical entrypoint;
- `WP_Field.php` -> compat-loader;
- `vanilla/WP_Field.php` -> vanilla/legacy class `WP_Field`.

Использовать legacy имеет смысл только если:

- уже есть старые массивы конфигурации;
- нужно быстро поддержать существующий код без миграции;
- используется кастомный legacy-тип вне modern matrix.

Для нового кода предпочтительны `Field` и контейнеры.

---

## 10. Минимальный контракт интеграции

Для внешнего кода достаточно понимать три уровня:

1. `Field` создаёт и описывает поле.
2. `Container` встраивает его в WordPress lifecycle и сохраняет значение.
3. `Storage` определяет, где это значение лежит.

Это и есть основная стабильная точка интеграции с библиотекой.
