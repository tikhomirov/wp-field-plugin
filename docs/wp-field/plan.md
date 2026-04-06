# WP_Field Demo Pages Realignment

_Обновлено: 2026-04-07_

## Progress

| Phase | Status | Notes |
|---|---|---|
| 1. Demo audit | ✅ done | Target matrix in decision-log |
| 2. Shared demo catalog | ✅ done | `examples/shared-catalog.php` |
| 3. Vanilla page rebuild | ✅ done | Inline CSS → external, marketing removed, renamed to Vanilla |
| 4. React components page | ✅ done | Slug `wp-field-components`, sidebar, code examples, props badges, vanilla JS bundle |
| 5. UI demo convergence | ⏳ pending | `wp-field-ui-demo` not yet on shared catalog (user: "пока не трогаем") |

## Goal

Привести demo-страницы к новой роли:
- `wp-field-examples` = стабильная Vanilla-документация для классической WP admin;
- `wp-field-components` = React-документация и demo нового модуля без jQuery и без зависимости от встроенных WP UI scripts;
- `wp-field-ui-demo` = отдельная showcase-страница admin framework, которая использует те же компоненты, но в Flux UI-оформлении.

## Expected Result

После завершения цикла:
- страница `tools.php?page=wp-field-examples` показывает все поддерживаемые поля старого API, выглядит как компактная документация и остаётся рабочей в классической админке;
- страница `tools.php?page=wp-field-components` заменяет `wp-field-v3-demo`, показывает не меньший набор поддерживаемых полей, поддерживает custom field/custom content и не требует jQuery или `wp.media` / `wp.editor` / `wp-color-picker`;
- страница `tools.php?page=wp-field-ui-demo` использует тот же каталог компонентов, что и `wp-field-components`, но остаётся витриной UI shell в стиле Flux UI;
- документация и demo coverage отражают реальное поведение.

## Fixed decisions

- Термин `Legacy` больше не использовать для `wp-field-examples`; это `Vanilla` реализация.
- `wp-field-examples` остаётся на jQuery + минимальном CSS и целится в классическую WordPress admin.
- `wp-field-v3-demo` переименовывается в `wp-field-components`.
- `wp-field-components` не использует jQuery и не опирается на встроенные WP UI scripts; если для отдельных типов нужен enhancement, он должен быть независимым от WordPress UI runtime.
- `wp-field-ui-demo` пока не проектируется заново как отдельный каталог; он должен стать визуальной оболочкой над тем же набором примеров, что и `wp-field-components`.
- Источник состава полей: `docs/wp-field/supported-matrix.md` + отдельно `flexible_content` + custom demo cases.

## In scope

- Аудит текущих demo entrypoints, slug-ов, asset pipeline и списка полей.
- Выделение общего каталога demo-примеров/секций, который смогут использовать и `wp-field-components`, и `wp-field-ui-demo`.
- Пересборка `wp-field-examples` как Vanilla docs page.
- Переименование и переработка `wp-field-v3-demo` в `wp-field-components`.
- Приведение `wp-field-ui-demo` к shared data model c `wp-field-components`.
- Обновление docs (`API.md`, `analysis.md`, `decision-log.md`, `demo-matrix-coverage.md`, при необходимости README).

## Out of scope

- Расширение публичного fluent API вне нужд demo/doc pages.
- Полный redesign `AdminShell`/`Wizard`.
- Полный перенос legacy runtime из `WP_Field.php`.
- GitHub Pages publish pipeline как отдельная инфраструктурная задача.

## Repo facts

- `legacy/example.php` уже регистрирует `wp-field-examples` и использует jQuery, `wp-color-picker`, `wp_enqueue_media`, `wp_enqueue_code_editor`, Prism CDN и большой inline CSS.
- `examples/v3-demo.php` сейчас регистрирует slug `wp-field-v3-demo`, но это PHP-rendered modern-only demo, а не React component documentation page.
- `examples/ui-demo.php` сейчас содержит отдельную вручную собранную Flux UI витрину и не использует общий каталог полей из `v3-demo`.
- `docs/wp-field/supported-matrix.md` фиксирует 52 official unique types + 4 aliases; `flexible_content` описан отдельно как поддерживаемый modern type вне legacy registry.
- `docs/wp-field/demo-matrix-coverage.md` уже требует полного покрытия demo-страниц, но фактическая архитектура страниц пока не соответствует новой задаче.
- `vite.config.js` собирает только `repeater` и `flexible-content`; отдельного entrypoint для `wp-field-components` сейчас нет.
- В `package.json` нет отдельной команды для сборки demo app.

## Constraints

- Не ломать `WP_Field` и старый API.
- Не уменьшать покрытие поддерживаемых полей на новой React-странице относительно Vanilla-страницы.
- Перед любыми claims о поддержке сверять поле с реальным кодом, а не со старыми README/demo.
- Для `wp-field-components` нельзя зависеть от jQuery и встроенных WP UI scripts.
- План должен быть выполнен максимум за 5 фаз.

## Scope

Allowed:
- менять demo PHP files, demo assets, Vite entries, shared demo config/data files, docs;
- выносить общие demo definitions в `src/` или `examples/` shared layer;
- переименовывать slug, titles, labels и тексты страниц;
- удалять со страниц лишний маркетинговый/исторический контент;
- добавлять targeted tests/smoke checks для demo routing и shared catalog.

Forbidden:
- менять supported behavior полей без отдельной фиксации;
- оставлять на `wp-field-components` скрытую зависимость от `wp.*` UI API;
- дублировать два независимых каталога примеров для `wp-field-components` и `wp-field-ui-demo`;
- смешивать Vanilla docs page и UI framework showcase в одну страницу.

## Assumptions

- Сохранение старого slug `wp-field-v3-demo` не требуется, если будет добавлен прозрачный rename/migration path.
- Для `wp-field-components` допустим серверный PHP bootstrap страницы, но визуальный и интерактивный слой должен идти через React bundle.
- Для сложных полей, которые в runtime сейчас используют WP-specific enhancement, потребуется отдельный React demo adapter, а не прямой reuse текущего admin enhancement pipeline.

## Risks

- Требование “без встроенных wp scripts” конфликтует с текущими native integration types (`media`, `editor`, `gallery`, `code_editor`, `color`) и потребует либо независимых demo adapters, либо явно ограниченного demo mode.
- Если shared demo catalog не будет введён первым, `wp-field-components` и `wp-field-ui-demo` быстро разойдутся по составу и документация снова устареет.
- Переименование slug без compat-редиректа может сломать существующие ссылки из README и внутренних заметок.
- Vanilla-страница сейчас тянет внешние CDN assets и лишний explanatory content; без отдельной чистки она не подходит под роль публикуемой документации.

## Phases

### Phase 1. Demo audit and target contract

Цель:
Зафиксировать, как именно должны выглядеть и чем должны отличаться три страницы.

Шаги:
1. Сверить `legacy/example.php`, `examples/v3-demo.php`, `examples/ui-demo.php`, `docs/wp-field/demo-matrix-coverage.md`, `docs/wp-field/supported-matrix.md`.
2. Составить target matrix:
   - slug;
   - title;
   - runtime (`jQuery` / `React`);
   - источник данных;
   - список поддерживаемых типов;
   - допускаемые зависимости.
3. Отдельно отметить типы, для которых у `wp-field-components` нет независимого React demo adapter.
4. Зафиксировать решение в `decision-log.md`.

Проверка:
- есть таблица ролей и ограничений для всех трёх страниц;
- нет неявных конфликтов по slug, runtime и источнику данных.

### Phase 2. Shared demo catalog

Цель:
Сделать единый источник секций и примеров для нового component demo и UI demo.

Шаги:
1. Вынести definitions секций, карточек, custom demos и coverage metadata из `examples/v3-demo.php` в shared PHP/JSON-like layer.
2. Разделить shared data и page-specific presentation.
3. Добавить в shared catalog:
   - все official supported types;
   - alias coverage;
   - `flexible_content`;
   - custom field/custom content examples.
4. Добавить smoke-проверку, что `wp-field-components` catalog не меньше Vanilla coverage по числу/списку типов.

Проверка:
- `wp-field-components` и `wp-field-ui-demo` читают один каталог;
- расхождение состава полей ловится автоматической проверкой или явным audit script/test.

### Phase 3. Vanilla page rebuild

Цель:
Превратить `wp-field-examples` в чистую Vanilla docs page.

Шаги:
1. Очистить `legacy/example.php` от лишнего маркетингового текста и нерелевантных блоков.
2. Переименовать язык страницы с `Legacy` на `Vanilla`.
3. Сохранить jQuery + минимальный CSS как baseline для классической админки.
4. Убрать всё, что мешает публикации как документации:
   - лишний inline visual noise;
   - неточные claims;
   - внешние зависимости без явной необходимости.
5. Проверить покрытие:
   - все official types;
   - alias examples;
   - custom type/custom content example;
   - документационный код рядом с preview.

Проверка:
- страница остаётся рабочей в классической WP admin;
- страница действительно документирует `WP_Field`, а не рекламирует migration path.

### Phase 4. React components page

Цель:
Построить `wp-field-components` как новую React demo/docs page.

Шаги:
1. Переименовать menu slug/title из `wp-field-v3-demo` в `wp-field-components`.
2. Добавить отдельный Vite entrypoint для components demo app.
3. Построить React page shell в стиле component documentation:
   - sidebar/table of contents;
   - section anchors;
   - preview + usage API;
   - краткие notes по поведению и value shape.
4. Исключить jQuery и WP UI runtime dependencies из demo implementation.
5. Для полей с WP-specific runtime сделать независимый React demo adapter или явно documented fallback, который не ломает страницу.
6. Обновить docs/links, где фигурирует `wp-field-v3-demo`.

Проверка:
- страница открывается по `tools.php?page=wp-field-components`;
- React bundle собирается отдельным entrypoint;
- нет `jquery`, `wp.media`, `wp.editor`, `wp-color-picker` как обязательных зависимостей страницы.

### Phase 5. UI demo convergence and docs sync

Цель:
Оставить `wp-field-ui-demo` как Flux UI showcase поверх того же component catalog и синхронизировать документацию.

Шаги:
1. Перевести `wp-field-ui-demo` на использование shared catalog из Phase 2.
2. Оставить Flux UI styling как presentation layer, не как отдельный набор компонентов.
3. Обновить `analysis.md`, `decision-log.md`, `demo-matrix-coverage.md`, `API.md`, README при необходимости.
4. Прогнать нужные проверки:
   - targeted PHP tests/smoke;
   - `npm run build` или отдельную demo build команду после добавления entrypoint;
   - ручную проверку трёх admin pages.

Проверка:
- `wp-field-ui-demo` и `wp-field-components` показывают одинаковый каталог примеров;
- различается только presentation/runtime role;
- docs не спорят с кодом и slug-ами.
