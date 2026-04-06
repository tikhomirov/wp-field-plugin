# WP_Field — анализ текущего состояния

_Обновлено: 2026-04-05_

## Зачем этот файл

Это рабочая карта состояния библиотеки. Его задача — быстро восстановить контекст без чтения всего репозитория.

## 1. Краткое резюме

`lib/wp-field` сейчас — **гибрид из двух слоёв**:

1. **Legacy runtime** — большой файл `WP_Field.php`, который реально обеспечивает основную совместимость и большую часть типов полей.
2. **Modern v3 API** — `src/` c PSR-4, fluent API, контейнерами, storage-стратегиями и отдельными UI-компонентами.

Важно: **v3 API пока не является полным заменителем legacy API**. На практике это промежуточное состояние, а не завершённый переход.

---

## 2. Структура библиотеки

### Корневые точки входа

- `WP_Field.php` — legacy API, реестр типов, рендеринг, получение значений, assets, demo bootstrap.
- `composer.json` — PHP-пакет `rwsite/wp-field`, PSR-4 namespace `WpField\\` → `src/`.
- `package.json` — Vite/React сборка фронтенд-части.
- `README.md`, `README.ru.md`, `CHANGELOG.md` — документация, местами расходится с фактическим кодом.

### Папка `src/`

#### Поля
- `src/Field/Field.php` — фабрика fluent API.
- `src/Field/AbstractField.php` — базовый класс поля.
- `src/Field/FieldInterface.php` — контракт поля.
- `src/Field/Types/*` — конкретные типы и адаптеры.

#### Контейнеры
- `src/Container/MetaboxContainer.php`
- `src/Container/SettingsContainer.php`
- `src/Container/TaxonomyContainer.php`
- `src/Container/UserContainer.php`

#### Storage
- `src/Storage/PostMetaStorage.php`
- `src/Storage/TermMetaStorage.php`
- `src/Storage/UserMetaStorage.php`
- `src/Storage/OptionStorage.php`
- `src/Storage/CustomTableStorage.php`

#### Доп. слой
- `src/Legacy/LegacyAdapter.php` — перевод array-конфига в fluent field.
- `src/Conditional/ConditionalLogic.php` — серверная оценка условий.
- `src/UI/*` — Admin Shell, Wizard, Alert, NavItem, конфиги, UIManager.
- `src/Traits/*` — атрибуты, валидация, conditional logic.

### Frontend assets

- `assets/js/wp-field.js` — основной legacy jQuery/vanilla runtime.
- `assets/src/repeater.jsx`, `assets/src/flexible-content.jsx` — React для repeater/flexible.
- `assets/src/admin-shell.jsx`, `assets/src/wizard.jsx` — React UI для shell/wizard.
- `assets/dist/*` — собранные файлы Vite.
- `assets/css/wp-field.css` — базовые стили полей.
- `assets/css/admin-shell.css`, `assets/css/wizard.css` — отдельные UI-слои.

### Тесты

Есть 14 PHP test files в `tests/`.

По факту покрытие смешанное:
- legacy API (`WP_Field`) покрыт сильнее;
- modern API покрыт частично;
- UI-интеграция React почти не проверяется end-to-end.

---

## 3. Компоненты и реальные зоны ответственности

## 3.1 Legacy слой (`WP_Field.php`)

Что делает:
- держит реестр типов полей;
- рендерит HTML для legacy array API;
- получает значения из разных storage-типов;
- подключает JS/CSS;
- содержит demo bootstrap для страниц примеров;
- остаётся главным источником обратной совместимости.

Сильные стороны:
- широкий набор типов;
- реальная совместимость;
- уже встроенный фронтенд runtime для зависимостей, media, color picker, repeater, accordion, tabbed и т.д.

Слабые стороны:
- монолит ~2800 строк;
- много ответственности в одном классе;
- сложно расширять и тестировать адресно.

## 3.2 Modern field API (`src/Field/*`)

Фабрика `Field` сейчас реально умеет создавать отдельными методами только:
- `text()`
- `repeater()`
- `flexibleContent()`
- `radio()`
- `media()`
- `fieldset()`
- `legacy()`
- `make()`

Реальные concrete classes в `src/Field/Types/`:
- `TextField`
- `RepeaterField`
- `FlexibleContentField`
- `RadioField`
- `MediaField`
- `FieldsetField`
- `LegacyWrapperField`

Важно:
- `RadioField`, `MediaField`, `FieldsetField` уже не полностью самостоятельные — они рендерятся через legacy bridge.
- Для большинства остальных типов fluent API пока работает только через `Field::make(...)->legacy(...)` маршрут.

## 3.3 Контейнеры

Контейнеры уже выглядят полезной основой:
- metabox
- settings page
- taxonomy form
- user profile

Они загружают значения через storage abstraction и сохраняют через `sanitize()`/`validate()` поля.

## 3.4 Storage слой

Storage слой простой и понятный:
- post meta
- term meta
- user meta
- options
- custom table

Это одна из самых зрелых частей v3-слоя.

## 3.5 UI слой

Есть два разных UI-направления:

1. **Поля** — legacy JS + частично React для repeater/flexible.
2. **Админ-оболочки** — `AdminShell`, `Wizard`, `Alert`, стили и отдельные React entry points.

`AdminShell` и `Wizard` уже выглядят как самостоятельный reusable toolkit.

---

## 4. Список полей

## 4.1 Legacy registry: фактическое состояние

В `WP_Field::init_field_types()` зарегистрировано **56 ключей**:
- **52 уникальных типа**
- **4 алиаса**

### Базовые
- `text`
- `password`
- `email`
- `url`
- `tel`
- `number`
- `range`
- `hidden`
- `textarea`

### Выбор
- `select`
- `multiselect`
- `radio`
- `checkbox`
- `checkbox_group`

### Продвинутые
- `editor`
- `media`
- `image`
- `file`
- `gallery`
- `color`
- `date`
- `time`
- `datetime`

### Композитные
- `group`
- `repeater`

### Простые
- `switcher`
- `spinner`
- `button_set`
- `slider`
- `heading`
- `subheading`
- `notice`
- `content`
- `fieldset`

### Средней сложности
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

### Высокой сложности
- `code_editor`
- `icon`
- `map`
- `sortable`
- `sorter`
- `palette`
- `link`
- `backup`

### Алиасы
- `date_time`
- `datetime-local`
- `image_picker`
- `imagepicker`

## 4.2 Native fluent API: фактическая поддержка

Нативно реализованы:
- `text`
- `group`
- `heading`
- `subheading`
- `notice`
- `content`
- `repeater`
- `flexible_content`
- `switcher`
- `spinner`
- `button_set`
- `slider`
- `image_select`

Частично через legacy bridge:
- `radio`
- `media`
- `fieldset`

Через общий fallback:
- почти всё остальное через `LegacyWrapperField`

Вывод: **modern v3 слой растёт поэтапно, но ещё не покрывает весь legacy registry**.

---

## 5. React vs classic: реальные различия

## 5.1 Classic / legacy режим

Источник:
- `WP_Field.php`
- `assets/js/wp-field.js`
- `assets/css/wp-field.css`

Поведение:
- PHP рендерит готовый HTML;
- jQuery/vanilla инициализирует зависимости, media, color picker, repeater, accordion, tabbed, sorter и т.д.;
- этот слой реально используется и связан с legacy markup.

Плюсы:
- покрывает больше возможностей;
- ближе к боевому состоянию;
- совместим с существующим array API.

Минусы:
- монолитный JS;
- много UI-логики завязано на DOM-структуру legacy renderer.

## 5.2 React режим

Есть четыре React entry point:
- `repeater.jsx`
- `flexible-content.jsx`
- `admin-shell.jsx`
- `wizard.jsx`

Что реально хорошо выглядит:
- `AdminShell`
- `Wizard`

Что проблемно интегрировано:
- `RepeaterField`
- `FlexibleContentField`

Причина:
- React-компоненты ждут mount point вида `data-wp-field-repeater` / `data-wp-field-flexible`;
- PHP-рендеры `RepeaterField` и `FlexibleContentField` такие атрибуты **не выводят**;
- в обычном использовании React UI не может автоматически смонтироваться.

Итог:
- React-поведение сейчас подтверждено в demo-страницах;
- для реального runtime библиотека по-прежнему сильнее опирается на classic/legacy слой.

---

## 6. Технический долг

## 6.1 Архитектурный долг

1. **Двойная архитектура без чёткого cutover**
   - legacy слой остаётся главным runtime;
   - modern слой уже есть, но не завершён.

2. **Один класс `WP_Field` делает слишком много**
   - registry
   - render
   - storage access
   - assets
   - examples bootstrap

3. **Нет единого supported matrix**
   - README/CHANGELOG говорят одно;
   - код умеет другое;
   - demos местами обходят реальные ограничения вручную.

## 6.2 Документационный долг

1. README пишет про `48 unique field types`, но фактически legacy registry содержит `52 unique + 4 aliases`.
2. `examples/v3-demo.php` содержит примеры `Field::email()`, `Field::textarea()`, `Field::image()`, которых в `Field.php` нет.
3. CHANGELOG и README выглядят более оптимистично, чем реальная интеграция.

## 6.3 Интеграционный долг

1. `UIManager` не совпадает с реальным Vite pipeline.
2. React mounts для repeater/flexible не встроены в PHP-render слой.
3. demo-страницы частично живут отдельной жизнью от основного API.

## 6.4 Тестовый долг

1. Много тестов всё ещё крутятся вокруг `WP_Field.php`.
2. Native field classes покрыты точечно.
3. Нет нормального integration/e2e покрытия React UI.
4. Нет тестов на согласованность README/examples vs code surface.

---

## 7. Список багов и рисков

## Критичные баги

### 1. `LegacyWrapperField` теряет conditional logic
Файл: `src/Field/Types/LegacyWrapperField.php`

Проблема:
- `HasConditionals` хранит условия как плоский массив условий;
- `LegacyWrapperField::render()` проходит по ним как по вложенным группам;
- в итоге `dependency` почти наверняка не маппится в legacy config.

Следствие:
- fluent `->when()`/`->orWhen()` для legacy-wrapped полей работает ненадёжно или не работает.

### 2. `UIManager` указывает на несуществующие vanilla assets
Файл: `src/UI/UIManager.php`

Проблема:
- ожидаются `assets/js/repeater.js` и `assets/js/flexible-content.js`;
- этих файлов в репозитории нет.

Следствие:
- vanilla mode в `UIManager` не соответствует фактической структуре проекта.

### 3. `UIManager` подключает React-скрипты не как ES modules
Файл: `src/UI/UIManager.php`

Проблема:
- `assets/dist/repeater.js` и `assets/dist/flexible-content.js` импортируют `./client.js`;
- это модульные сборки Vite;
- `UIManager` подключает их обычным `wp_enqueue_script`, без `type="module"`.

Следствие:
- React mode через `UIManager` ненадёжен.

### 4. Native `RepeaterField`/`FlexibleContentField` не публикуют mount config для React
Файлы:
- `src/Field/Types/RepeaterField.php`
- `src/Field/Types/FlexibleContentField.php`

Проблема:
- React entrypoints ищут `data-wp-field-repeater` и `data-wp-field-flexible`;
- render-методы выводят обычный HTML, но не mount payload.

Следствие:
- реальный React runtime для этих полей не активируется сам.

## Средние риски

### 5. Reflection-хак для вложенных имён полей
Файлы:
- `src/Field/Types/RepeaterField.php`
- `src/Field/Types/FlexibleContentField.php`

Проблема:
- имя поля меняется через reflection и `setAccessible(true)`.

Следствие:
- хрупкая реализация;
- сложнее безопасно эволюционировать field internals.

### 6. Глобальное подключение assets на всех admin pages
Файл: `WP_Field.php`

Проблема:
- внизу файла есть fallback `admin_enqueue_scripts`, который грузит общий JS/CSS даже без явного использования полей.

Следствие:
- лишняя нагрузка и размытые границы подключения.

### 7. `examples/modern-api-examples.php` делает `echo` внутри `init`
Проблема:
- это не нормальный production usage.

Следствие:
- пример скорее демонстрационный, чем безопасный шаблон интеграции.

---

## 8. Что уже можно считать стабильным

Относительно стабильные части:
- legacy array API через `WP_Field`;
- storage-абстракции в `src/Storage/*`;
- контейнеры metabox/settings/taxonomy/user;
- `AdminShell` и `Wizard` как отдельные UI-конструкции;
- базовый `TextField` и общая идея fluent field API;
- native B1/A2 простые типы: `group`, `heading`, `subheading`, `notice`, `content`, `switcher`, `spinner`, `button_set`, `slider`, `image_select`.

---

## 9. Что нельзя считать завершённым

Не считать завершённым без доп. работ:
- полный переход на fluent API;
- заявленный React/Vanilla mode switching как прозрачную runtime-фичу;
- полное соответствие README/examples фактическому API;
- “v3 как основной runtime” без оговорок.

---

## 10. Практический вывод для следующих сессий

Если нужно быстро ориентироваться:

1. **Для существующего боевого поведения сначала смотреть `WP_Field.php`.**
2. **Для новой архитектуры смотреть `src/`.**
3. **Для UI-shell/wizard смотреть `src/UI/*` + `assets/src/*`.**
4. **README и demo-файлы считать полезными, но не источником истины.**
5. **Перед изменениями сверять решение с `decision-log.md` и `plan.md`.**
