# WP_Field — активный план после завершения миграции `Field::make()`

_Обновлено: 2026-04-06_

## Статус

План миграции `Field::make()` (Итерации 1–10) завершён и перенесён в архив:
- `docs/реализовано/2026-04-06-field-make-migration-plan.md`

Текущий документ — рабочий план следующего этапа (bugfix + стабилизация).

---

## Приоритет 1 — исправить ошибку UI в woo2iiko (`NavItem not found`)

### Симптом
`Uncaught Error: Class "WpField\UI\NavItem" not found`
в `src/Features/AdminUI/Services/AdminUINavigationBuilder.php:37` (плагин `woo2iiko`).

### Гипотеза
Проблема в загрузке/интеграции wp-field (autoload/runtime bootstrap), а не в самом классе `src/UI/NavItem.php`.

### Шаги
1. Проверить bootstrap-путь подключения wp-field в `woo2iiko`.
2. Проверить момент вызова `AdminUINavigationBuilder` относительно загрузки автолоадера.
3. Добавить безопасный guard/fallback в точке использования `NavItem`.
4. Добавить интеграционный тест на построение nav в контексте Woo settings.

### DoD
- ошибка `Class ... NavItem not found` не воспроизводится;
- есть тест/смоук на сценарий построения Admin UI навигации.

---

## Приоритет 2 — стабилизировать demo-страницы

### Цели
- `wp-field-v3-demo` должен демонстрировать modern API полностью;
- `wp-field-ui-demo` должен покрывать ключевые возможности framework-а, без частичного рендера.

### Шаги
1. Разделить секции demo по режимам (`legacy enabled` / `legacy disabled`).
2. Убрать оставшиеся скрытые зависимости от legacy runtime в modern demo блоках.
3. Добавить явные индикаторы доступности функций (react build / legacy runtime).
4. Добавить smoke-тест чеклист по обоим demo pages.

### DoD
- обе страницы рендерятся без фаталов;
- все заявленные секции либо работают, либо явно помечены как недоступные с причиной.

---

## Приоритет 3 — покрытие тестами до 100%

### Шаги
1. Зафиксировать базовую линию coverage (unit/feature раздельно).
2. Добрать покрытие для:
   - `src/UI/*` (NavItem/AdminShell/Wizard/UIManager);
   - bridge-типов с кастомными fluent-методами;
   - веток fallback/guards в bootstrap-файлах (`wp-field.php`, `legacy/bootstrap.php`, `WP_Field.php`).
3. Добавить обязательный coverage gate в CI (минимальный порог с ростом до 100%).

### DoD
- coverage достигает 100% по согласованной метрике;
- CI падает при снижении покрытия.

---

## Минимальные команды проверок по этапу

```bash
./vendor/bin/pest --configuration phpunit.xml tests/Unit
./vendor/bin/pest --configuration phpunit.xml tests/Feature
composer analyse
composer lint:check
```

(полный прогон `composer ci` — перед финальным merge этапа)
