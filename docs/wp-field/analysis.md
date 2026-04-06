# WP_Field — анализ текущего состояния (сжатая версия)

_Обновлено: 2026-04-06_

## Зачем файл
Короткая «карта местности» по фактическому состоянию `lib/wp-field` без длинной исторической хроники.

---

## 1) Snapshot

`wp-field` остаётся **гибридом**:
- **Legacy runtime** (`legacy/WP_Field.php`, bootstrap в `legacy/bootstrap.php`) — основной слой обратной совместимости.
- **Modern layer** (`src/*`) — incremental API/архитектура, но не полный cutover.

Ключевой факт: modern-слой уже зрелее, чем раньше (широкий `Field::make()` mapping, UI API, контейнеры, storage), но legacy всё ещё важен для runtime-паритета.

Дополнительно после закрытия Stage 2/3/4/5 (2026-04-06):
- `accordion`, `tabbed`, `sortable`, `sorter`, `color_group` переведены на native render;
- settings-object типы (`typography`, `spacing`, `dimensions`, `border`, `background`, `link_color`) переведены на native render с canonical value-shape и explicit sanitize/validate;
- media/editor integration типы (`media`, `color`, `editor`, `image`, `file`, `gallery`, `code_editor`, `icon`) переведены на native render;
- `map` переведён на native baseline (manual coordinates) + optional enhancement в `assets/js/wp-field-integrations.js`;
- добавлен modern enhancement runtime `assets/js/wp-field-integrations.js`;
- в `UIManager` добавлено централизованное подключение WP asset APIs (`wp_enqueue_media`, color picker, editor/code editor);
- для nested-конфигов добавлен общий helper `HandlesNestedFieldConfigs`;
- для `sortable`/`sorter` зафиксирован server-render baseline (без обязательного drag-and-drop JS).

---

## 2) Точки входа и загрузка

Актуальный контракт:
- `wp-field.php` — **canonical entrypoint**.
- `WP_Field.php` — **deprecated compat-loader** для старых include-путей.
- `legacy/WP_Field.php` — класс `WP_Field`.
- `legacy/bootstrap.php` — legacy hooks/enqueues.

Feature-flag:
- `wp_field_enable_legacy` (default `true`) позволяет сценарий поэтапного отключения legacy.

---

## 3) Состояние подсистем

## 3.1 Поля (`src/Field/*`)
- `Field::make()` покрывает официальный реестр типов и alias-маршруты.
- Fallback `LegacyWrapperField` оставлен для неизвестных custom-типов.
- Bridge-классы используются там, где рендер по-прежнему завязан на legacy-pipeline.
- Закрыт Этап 1 simple bridge migration: `radio`, `fieldset`, `image_picker`, `palette`, `link`, `backup` переведены на native render.

Вывод: surface API заметно стабилизирован; bridge-зона сузилась, но в части типов runtime по факту остаётся bridge/legacy.

## 3.2 Контейнеры (`src/Container/*`)
- `MetaboxContainer`, `SettingsContainer`, `TaxonomyContainer`, `UserContainer` — рабочая базовая интеграция с WP.
- Санитизация/валидация делегируется полям, хранение — через storage abstraction.

## 3.3 Storage (`src/Storage/*`)
- Один из самых стабильных слоёв: post/term/user/options/custom table.
- Простой контракт, минимум скрытой логики.

## 3.4 UI API (`src/UI/*`)
- `NavItem`, `AdminShell`, `Wizard`, `Alert`, `UIManager` — выделены как самостоятельный слой.
- Покрытие unit-тестами добавлено (см. раздел тестов).

## 3.5 Frontend assets
- Legacy assets: `legacy/assets/js/wp-field.js`, `legacy/assets/css/wp-field.css`.
- React bundles: `assets/dist/*`.
- Отдельные стили shell/wizard: `assets/css/admin-shell.css`, `assets/css/wizard.css`.

---

## 4) Тестовое состояние

Текущее состояние:
- Полный прогон: **114 passed**.
- Добавлены:
  - `tests/Unit/UI/UIComponentsTest.php`
  - `tests/Feature/BootstrapFilesTest.php`
- `tests/bootstrap.php` расширен WP-стабами для UI/bootstrap сценариев.

Coverage gate:
- `composer test:coverage` запускается с `--min=100`.
- CI (PHP 8.3) использует этот gate.

Ограничение локальной машины:
- в текущем окружении может отсутствовать coverage driver (`xdebug/phpdbg`), поэтому «официальное» подтверждение 100% — в CI.

---

## 5) Что сейчас считать стабильным

1. Контракт загрузки (`wp-field.php` как canonical).
2. Hybrid-стратегию runtime (modern + bridge + legacy).
3. `Field::make()` как основной вход для типов/alias-ов.
4. Storage-слой и контейнеры.
5. UI API (`NavItem/AdminShell/Wizard/Alert/UIManager`) на уровне unit-контрактов.
6. Regression gate по покрытию в CI.

---

## 6) Актуальные риски/долг

1. **Полный cutover от legacy ещё не завершён**.
   - После Stage 5 официальный registry больше не использует bridge-маршруты; legacy остаётся для compat-loader, legacy runtime и unknown/custom fallback.

2. **React runtime для сложных полей требует аккуратной проверки в реальных WP-экранах**.
   - Unit/feature покрытие есть, но это не заменяет e2e-проверки JS-пайплайна в админке.

3. **Документация должна продолжать синхронизироваться с фактическим кодом**.
   - Источник истины: код и зафиксированные решения, не маркетинговые claims.

---

## 7) Практический режим для следующих сессий

1. При любом споре: сначала код (`wp-field.php`, `legacy/*`, `src/*`).
2. Для legacy-поведения: смотреть `legacy/WP_Field.php` + `legacy/bootstrap.php`.
3. Для modern API: `src/Field/*`, `src/Container/*`, `src/UI/*`.
4. Для Stage 4 smoke-проверок в WP-админке проходить минимум: color picker, media/image/file/gallery frame, editor/code_editor init, icon fallback (prompt).
5. Перед финалом задачи обновлять минимум один context-файл (`analysis.md` / `plan.md` / `decision-log.md` / `AGENTS.md`).
