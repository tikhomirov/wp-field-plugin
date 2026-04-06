# WP_Field — журнал решений (сжатая версия)

_Обновлено: 2026-04-07_

## Зачем файл
Короткий реестр **актуальных архитектурных решений**. Детали реализации и длинная история — в git/blame и связанных docs.

## Правило приоритета
1. Код (`WP_Field.php`, `legacy/*`, `src/*`)
2. Этот `decision-log.md`
3. `analysis.md`
4. `plan.md`
5. README/CHANGELOG/examples

---

## 2026-04-07 — Demo Pages Realignment: Vanilla / Components / UI Demo

### Решение
- **`wp-field-examples`** (slug сохранён) переименован из "Legacy" в **"Vanilla"**. Удалён маркетинговый текст, inline CSS вынесен в `assets/css/wp-field-examples-vanilla.css`. Страница — чистая документация `WP_Field::make()` API с jQuery + WP built-in components.
- **`wp-field-v3-demo`** (slug) → **`wp-field-components`**. Класс `WP_Field_V3_Demo` → `WP_Field_Components`. PHP-рендер с sidebar, code examples, props badges. JS-бандл (vanilla, без React) для sidebar search и scroll tracking. Не зависит от jQuery и `wp.*` UI scripts.
- **`examples/shared-catalog.php`** — единый каталог demo-полей (single source of truth) на основе `Field::make()` fluent API. Используется `wp-field-components`, готов к подключению в `wp-field-ui-demo`.
- **`wp-field-ui-demo`** — не трогался в этой итерации (по явному требованию пользователя). `shared-catalog.php` доступен для будущей интеграции.
- Добавлен `vite.components.config.js` и `build:components` script в `package.json`.

### Почему
Три demo-страницы смешивали роли (документация vs showcase vs marketing). Разделение:
1. Vanilla — стабильная документация для классического WP admin.
2. Components — React-независимая документация modern API, publishable на GitHub Pages.
3. UI Demo — Flux UI showcase поверх того же каталога (Phase 5 плана).

### Затронутые файлы
- `legacy/example.php` — rewrite header, remove inline CSS, rename to Vanilla
- `examples/v3-demo.php` — полная перезапись → `WP_Field_Components`
- `examples/shared-catalog.php` — новый файл
- `assets/css/wp-field-components.css` — новый файл
- `assets/src/wp-field-components.jsx` — новый entrypoint (vanilla JS)
- `assets/dist/wp-field-components.js` — pre-built bundle
- `vite.components.config.js` — новый Vite config
- `package.json` — добавлен `build:components`
- `wp-field.php` — обновлён комментарий
- `tree.md` — обновлено дерево

### Тесты
140 passed (737 assertions). PHP syntax check OK для всех изменённых файлов.

---

## 2026-04-06 — `API.md` сокращён до contract-first формата

### Решение
- `API.md` переписан в короткий формат: оставлены только стабильные публичные контракты и практические правила интеграции.
- Удалены длинные перечисления всех field-type методов и подробные повторяющиеся описания runtime-деталей.
- Для полного supported matrix и детальных value-shape ссылка теперь идёт в профильные docs, в первую очередь `docs/wp-field/supported-matrix.md`.

### Почему
Старый `API.md` разросся и смешивал:
- стабильный публичный контракт;
- внутренние детали реализации;
- длинные инвентари типов, которые быстрее устаревают.

Короткий contract-first документ проще поддерживать синхронно с кодом и безопаснее использовать как входную точку для интеграции.

### Совместимость
Поведение библиотеки не менялось. Изменён только формат документации и граница того, что в ней считается обязательным публичным контрактом.

## 2026-04-06 — Этап 1 bridge-миграции закрыт (simple types)

### Решение
Переведены на native render (без `LegacyAdapterBridge`) типы:
- `radio`
- `fieldset`
- `image_picker` / `imagepicker`
- `palette`
- `link`
- `backup`

Добавлено покрытие `tests/Unit/Field/SimpleBridgeTypesNativeRenderTest.php` для render/value-contract.

### Зафиксированные контракты (legacy parity baseline)
- `radio`: `<div class="wp-field-radio-group">`, набор `<input type="radio">`, id с индексом.
- `fieldset`: `<fieldset>` + `<legend>`, поддержка вложенных modern field objects и array-config.
- `image_picker`: `<select class="wp-field-image-picker">` + `data-img-src` на `option`.
- `palette`: `<div class="wp-field-palette">`, `wp-field-palette-item`, `wp-field-palette-color`.
- `link`: value shape `['url', 'text', 'target']`, рендер 3 саб-полей.
- `backup`: export/import UI, textarea экспорт/импорт и JSON validate contract.

### Почему
Эти типы не требуют тяжёлой WP JS-интеграции и дают быстрый выигрыш в de-legacy без риска для media/editor pipeline.

### Совместимость
- `Field::make()` маршрутизацию не меняли (классы те же), но runtime теперь native.
- Для `palette` добавлен fluent alias `palettes()` при сохранении совместимости с `options`.
- `link` и `backup` получили явные `sanitize()/validate()` под их value-shape.
- `examples/v3-demo.php` обновлён: типы Этапа 1 перенесены в modern-only showcase и удалены из excluded bridge list.

## 2026-04-06 — Stage 5 закрыт: `map` переведён на native + завершён финальный cutover bridge-слоя

### Решение
- `MapField` переведён на native `render()/sanitize()/validate()` без `LegacyAdapterBridge`.
- Для `map` зафиксирован server-render baseline без внешнего провайдера (ручной ввод `lat/lng`) и enhancement-путь в `assets/js/wp-field-integrations.js` (геолокация + sync hidden/input).
- Добавлен regression-тест `tests/Unit/Field/BridgeCutoverAuditTest.php`:
  - все официальные registry-типы и alias-маршруты идут в native route;
  - `LegacyWrapperField` остаётся только для unknown/custom типов;
  - в `src/Field/Types/*` больше нет `use LegacyAdapterBridge`/`renderViaLegacy(...)` у официальных типов.
- Обновлены `API.md` и `docs/wp-field/supported-matrix.md` с финальным route-table `official -> native | legacy-only fallback`.

### Почему
Stage 5 закрывает последний bridge-тип и формализует правило: legacy wrapper — только compat fallback для неизвестных типов, не для official registry.

### Совместимость
Публичный API не сломан. `Field::make()` сохраняет прежние alias-маршруты (`image_picker`/`imagepicker`, `date_time`/`datetime-local`) и fallback-поведение для custom-типов.

### Статус LegacyAdapterBridge
`LegacyAdapterBridge` переведён в compat-only статус (служебный долг на удаление в отдельном cleanup PR после стабилизационного периода).

## 2026-04-06 — Custom/unknown fallback усилен: LegacyWrapperField поддерживает generic render baseline

### Решение
- `LegacyWrapperField` больше не теряет custom-атрибуты: в legacy-config теперь мержатся все fluent attributes + explicit config.
- Если legacy API не может отрендерить unknown type (или `WP_Field` недоступен), включается generic fallback render (`wp-field-legacy-fallback`) вместо пустой строки.
- Generic fallback поддерживает:
  - text-input baseline (label/description/placeholder/value/required/readonly/disabled/class)
  - options/select baseline для custom select-подобных полей.
- Добавлен feature-набор `tests/Feature/LegacyCustomFallbackTest.php`.

### Почему
После финального cutover official registry на native runtime legacy должен оставаться только как надёжный fallback для unknown/custom типов, а не как источник пустого рендера.

### Совместимость
Публичный API не менялся: `Field::make('my_custom_type', ...)` и `Field::legacy(...)` продолжают работать, но теперь имеют предсказуемый fallback даже без legacy registry-entry.

## 2026-04-06 — Stage 4 закрыт полностью: native render + WP asset pipeline + modern JS init

### Решение
- `MediaField`, `ColorField`, `EditorField`, `ImageField`, `FileField`, `GalleryField`, `CodeEditorField`, `IconField` переведены на native `render()` без `renderViaLegacy()`.
- Добавлен отдельный modern-enhancement runtime: `assets/js/wp-field-integrations.js` (color picker, media frame, gallery, code editor, wp.editor, icon fallback).
- `UIManager::enqueueAssets()` теперь централизованно подключает WP интеграционные зависимости:
  - `wp_enqueue_media()`
  - `wp_enqueue_style('wp-color-picker')`
  - `wp_enqueue_script('wp-color-picker')`
  - `wp_enqueue_script('jquery-ui-slider')`
  - `wp_enqueue_editor()`
  - `wp_enqueue_code_editor(['type' => 'text/html'])`
  - + `wp-field-integrations` script.
- Добавлены test-stubs для editor/code-editor enqueue в `tests/bootstrap.php`.
- Добавлен regression-набор `tests/Unit/Field/WordPressIntegrationFieldsNativeRenderTest.php`.
- Обновлён `tests/Unit/UI/UIComponentsTest.php` для проверки enqueue-contract и single-enqueue поведения.

### Почему
Stage 4 требовал не только native HTML baseline, но и отдельный современный enhancement-pipeline без жёсткой привязки к legacy JS.

### Совместимость
Сохранены legacy DOM-классы (`wp-field-*-wrapper`, `wp-field-*-button`, `wp-color-picker-field`, `wp-editor-area`, `wp-field-gallery-ids`), поэтому baseline совместим с текущими экранами и постепенной заменой JS-инициализаторов.

## 2026-04-06 — Stage 3 закрыт: settings-object типы переведены на native runtime

### Решение
- `TypographyField`, `SpacingField`, `DimensionsField`, `BorderField`, `BackgroundField`, `LinkColorField` переведены на native `render()` без `renderViaLegacy()`.
- Для каждого типа зафиксирован canonical value-shape и дефолты в `normalizeValue()`.
- Для составных саб-ключей добавлены явные `sanitize()` и `validate()` с поддержкой partially-filled payload.
- Добавлен regression-набор `tests/Unit/Field/SettingsObjectFieldsNativeRenderTest.php` (render + sanitize/validate round-trip).

### Почему
Эти типы оставались главным блокером Stage 3: API уже был class-based, но runtime рендер зависел от legacy-пайплайна. Перевод на native уменьшает bridge-зону и стабилизирует контракт составных значений.

### Совместимость
Сохранены legacy value-ключи (`font_family`, `top/right/bottom/left`, `width/height`, `style/width/color`, `background-*`, `normal/hover/active`). Публичные fluent-методы (`units`, `sides`, `styles`, `backgroundFields`, `states`) не ломались.

## 2026-04-06 — Stage 2 закрыт: layout/choice контейнеры без LegacyAdapterBridge

### Решение
- `AccordionField`, `TabbedField`, `SortableField`, `SorterField`, `ColorGroupField` переведены на native `render()` без `renderViaLegacy()`.
- Добавлен общий internal-trait `src/Field/Types/Concerns/HandlesNestedFieldConfigs.php` для nested field-конфигов (`sections/tabs` + `fields`).
- Для `sortable`/`sorter` принят baseline «server-render only» (скрытые inputs + корректная сериализация), JS drag-and-drop оставлен как optional enhancement.
- Добавлены regression-тесты в `tests/Unit/Field/FieldTest.php` на nested render, active-state, hidden input contract и sanitize для `color_group`.

### Почему
Эти типы мешали продвигать cutover из-за зависимости от legacy layout-renderer, хотя их базовый контракт может работать в чистом PHP-render без legacy JS.

### Совместимость
API не ломали: `sections/items`, `tabs`, `options/groups`, `colors/options` сохранены. Старые значения продолжают читаться; поведение без JS теперь предсказуемо.

---

## 2026-04-06 — Закрыт Приоритет 3 (тесты + coverage gate)

### Решение
- Добавлен unit-набор `tests/Unit/UI/UIComponentsTest.php` для `NavItem`, `AdminShell`, `Wizard`, `Alert`, `UIManager`.
- Добавлен feature-набор `tests/Feature/BootstrapFilesTest.php` для guard/fallback веток `wp-field.php`, `legacy/bootstrap.php`, `WP_Field.php`.
- Расширен `tests/bootstrap.php` WordPress-стабами под UI/bootstrap сценарии.
- `composer test:coverage` переведён на gate `--min=100`.
- CI (PHP 8.3) использует этот gate.

### Почему
Нужна автоматическая защита от деградации покрытия.

### Совместимость
Runtime/API не менялись.

### Ограничение
Локально может отсутствовать coverage driver; контроль 100% выполняется в CI.

---

## 2026-04-06 — Контракт entrypoint/legacy зафиксирован

### Решение
- `wp-field.php` — **canonical entrypoint**.
- `WP_Field.php` — **deprecated loader** (совместимость старых include-путей).
- `legacy/WP_Field.php` — класс `WP_Field`.
- `legacy/bootstrap.php` — legacy hooks/enqueues.
- В `woo2iiko.php` подключение переведено на абсолютный путь + safety-net автолоадера.

### Почему
Убрать неоднозначность загрузки и снизить риск `Class ... not found`.

### Совместимость
Старые include-пути через `WP_Field.php` продолжают работать.

---

## 2026-04-06 — Legacy runtime структурно отделён

### Решение
- Legacy assets перенесены в `legacy/assets/*`.
- Legacy demo — `legacy/example.php`.
- Modern demo — `examples/v3-demo.php`, `examples/ui-demo.php`.
- Добавлен feature-flag `wp_field_enable_legacy` (default `true`).

### Почему
Подготовка к поэтапному отключению legacy без резкого cutover.

### Совместимость
При `wp_field_enable_legacy=true` поведение legacy сохранено.

---

## 2026-04-06 — Demo-стратегия уточнена

### Решение
- `v3-demo` сделан как modern-focused витрина для `legacy disabled`.
- Mixed-mode сценарии и ложные ожидания по bridge/legacy убраны.

### Почему
Демо должно отражать фактический runtime, а не «оптимистичную» картину.

### Совместимость
Slug `wp-field-v3-demo` сохранён.

---

## 2026-04-06 — Цикл миграции `Field::make()` закрыт

### Решение
- Итерации 1–10 миграции закрыты и архивированы.
- Официальные типы и alias-ы переведены в native/bridge-маршруты.
- Fallback `LegacyWrapperField` оставлен только для неизвестных custom-типов.

### Почему
Достигнут паритет заявленной маршрутизации `make()` с согласованной матрицей.

### Совместимость
Legacy runtime не ломался; bridge использует прежний render pipeline.

---

## 2026-04-06 — Политика качества унифицирована

### Решение
- В Composer введён единый CI-контракт: `quality`, `quality:fix`, `ci`, `ci:fix`.
- CI workflow использует `composer ci` как основной запуск.
- Rector подключён как quality-инструмент и ограничен `src/`.

### Почему
Единая точка запуска локально и в CI снижает дрейф проверок.

### Совместимость
Отдельные команды сохранены; runtime не затронут.

---

## 2026-04-06 — Hotfix: восстановлен runtime FlexibleContentField (non-React path)

### Решение
- В `src/Field/Types/FlexibleContentField.php` добавлены runtime-атрибуты контейнера: `data-min`, `data-max`.
- Кнопка добавления получила явный селектор `wp-field-flexible-add`.
- В `legacy/assets/js/wp-field.js` добавлена инициализация `initFlexibleContent()`:
  - открытие/закрытие списка layout-ов;
  - добавление блока из template (`{{INDEX}}`);
  - удаление блока с учётом `min`;
  - collapse/expand блока;
  - контроль лимитов add/remove через `checkFlexibleLimit()`.

### Почему
Без JS-инициализации flexible content в legacy/non-React runtime не имел рабочего add/remove flow, из-за чего поле фактически «не работало» в админке.

### Совместимость
- Публичный API не менялся.
- Изменения обратно совместимы: улучшен только runtime-поведение существующей разметки.

## 2026-04-06 — UI polish: Accordion приведён к таб-подобному визуальному стилю

### Решение
- В `src/Field/Types/AccordionField.php` удалён рендер `wp-field-accordion-icon` (`▶/▼`).
- В `legacy/assets/js/wp-field.js` убрана логика обновления текстовых иконок и добавлено корректное обновление `aria-expanded` у header-кнопки.
- В `legacy/assets/css/wp-field.css` аккордеон стилизован **максимально идентично** текущим tabs (с опорой на значения из `.wp-field-tabbed-nav-item`):
  - базовый header: `background: #f9f9f9`, `padding: 12px 15px`, `font-size: 14px`, `font-weight: 500`, `color: #666`;
  - hover: `background: #f0f0f0`, `color: #333`;
  - active/open-state: `background: #fff`, `color: #0073aa`, без нижней границы;
  - справа добавлен стандартный chevron-индикатор аккордеона на CSS (`::before`), без текстовых символов;
  - контент: `padding: 15px` как у `.wp-field-tabbed-content`;
  - оставшиеся legacy-иконки принудительно скрыты (safety).

### Почему
Нужно было сделать аккордеон визуально согласованным с tabbed UI, чтобы оба компонента выглядели как одна система, а не как разные эпохи.

### Совместимость
- API не менялся.
- Структура аккордеона сохранена, затронут только визуал и JS-поведение заголовка.

## 2026-04-06 — Hotfix: Slider track снова видим

### Решение
- В `legacy/assets/css/wp-field.css` переписан visual contract для `.wp-field-slider`.
- Убрано проблемное `height: 0px`, из-за которого дорожка range-инпута визуально пропадала.
- Добавлены явные стили track/progress для WebKit/Firefox:
  - `::-webkit-slider-runnable-track`
  - `::-moz-range-track`
  - `::-moz-range-progress`
- Для WebKit thumb добавлен `margin-top`, чтобы кружок корректно центрировался на дорожке.
- Дополнительно выровнены slider и value badge по одной горизонтали: для range-инпута задан `display: block; margin: 0; vertical-align: middle`, для `.wp-field-slider-value` — `inline-flex` + `align-self: center`.

### Почему
Старый CSS рисовал только thumb, а сама дорожка у некоторых браузеров была фактически невидимой.

### Совместимость
- API не менялся.
- Изменён только визуальный runtime slider-поля.

## 2026-04-05 — Базовые долгоживущие принципы

1. **Legacy API (`WP_Field`) — baseline до явного cutover.**
2. **`src/` — incremental migration layer, не «полная замена» legacy.**
3. **Сначала стабилизация интеграции, потом расширение API.**
4. **Поддерживаем hybrid-стратегию:** native where possible, legacy/bridge where needed.
5. **После значимой сессии обновлять минимум один context-файл** (`analysis.md` / `plan.md` / `decision-log.md` / `AGENTS.md`).

---

## Что считается deprecated/нежелательным

- Использовать `WP_Field.php` как primary entrypoint для нового кода.
- Опираться на README/examples как на единственный источник истины.
- Расширять fluent API без закрытия критичных интеграционных расхождений.
- Завершать этапы без regression gate и фиксации в docs.
