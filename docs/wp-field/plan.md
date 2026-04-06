# WP_Field — активный план де-легасизации bridge-типов

_Обновлено: 2026-04-06_

## Статус

План миграции `Field::make()` (Итерации 1–10) завершён и перенесён в архив:
- `docs/реализовано/2026-04-06-field-make-migration-plan.md`

Следующий главный этап — **максимально перевести bridge-типы с `LegacyAdapterBridge` на полноценный native runtime**.

Источник списка для миграции:
- `radio`
- `media`
- `fieldset`
- `color`
- `editor`
- `image`
- `file`
- `gallery`
- `accordion`
- `tabbed`
- `typography`
- `spacing`
- `dimensions`
- `border`
- `background`
- `link_color`
- `color_group`
- `code_editor`
- `icon`
- `map`
- `sortable`
- `sorter`
- `palette`
- `link`
- `backup`
- `image_picker` / `imagepicker`

Ограничение текущего цикла:
- **не более 5 этапов**;
- порядок — **от простого к сложному**;
- каждый этап считается закрытым только когда типы удалены из `LegacyAdapterBridge`-маршрута, добавлены тесты и обновлена документация.

---

## Этап 1 — Простые bridge-типы без тяжёлой JS-интеграции

### Цель
Перевести типы, у которых низкая стоимость native-рендера и понятный HTML-контракт.

### Объём этапа
- [x] `radio`
- [x] `fieldset`
- [x] `image_picker` / `imagepicker`
- [x] `palette`
- [x] `link`
- [x] `backup`

### Подзадачи
- [x] Для каждого типа описать фактический render-contract из legacy (`markup`, `name/id`, `value shape`, CSS-классы, data-атрибуты).
- [x] Проверить, нужны ли для типа legacy-only fluent-атрибуты, которых ещё нет в native-классе.
- [x] Реализовать `render()` без `LegacyAdapterBridge`.
- [x] Явно реализовать/проверить `sanitize()` и `validate()` там, где простого поведения `AbstractField` недостаточно.
- [x] Обновить `Field::make()` так, чтобы типы шли в native route, а не в legacy bridge.
- [x] Добавить unit/feature-тесты на HTML и value-contract.
- [x] Перенести типы из блока "excluded bridge types" в supported modern-only список демо, если они реально работают без legacy assets.

### DoD этапа
- [x] Ни один тип этапа не использует `renderViaLegacy()`.
- [x] Есть тесты на рендер и базовое поведение каждого типа.
- [x] Типы этапа можно безопасно показывать в `WP_Field Demo` как `legacy disabled`-совместимые.

---

## Этап 2 — Поля выбора и layout-контейнеры средней сложности

### Цель
Закрыть типы, где основная сложность — структура вложенного UI, а не глубокая WP-интеграция.

### Объём этапа
- [x] `accordion`
- [x] `tabbed`
- [x] `sortable`
- [x] `sorter`
- [x] `color_group`

### Подзадачи
- [x] Зафиксировать минимальный HTML/API-контракт для вложенных секций, вкладок, элементов сортировки и grouped-values.
- [x] Убрать предположение, что layout должен строиться legacy renderer-ом.
- [x] Выделить общие внутренние хелперы для nested collections / section config, чтобы не дублировать код.
- [x] Для `sortable`/`sorter` определить, можно ли оставить поведение как "server-render only" без drag-and-drop JS на первом native-шаге.
- [x] Для `accordion`/`tabbed` определить минимальный modern baseline: корректный HTML + доступность без обязательного JS enhancement.
- [x] Добавить regression-тесты на вложенные элементы, labels, active-state и serialized config.
- [x] Обновить supported matrix и demo-страницу после закрытия реальной поддержки.

### DoD этапа
- [x] Типы этапа рендерятся native-классами без bridge.
- [x] Вложенные конфиги не теряются при `toArray()` / `render()`.
- [x] Базовое поведение работает даже без legacy JS.

---

## Этап 3 — Settings-object типы с составным value shape

### Цель
Перевести типы, где главное препятствие — сложная структура значения и набор связанных саб-полей.

### Объём этапа
- [x] `typography`
- [x] `spacing`
- [x] `dimensions`
- [x] `border`
- [x] `background`
- [x] `link_color`

### Подзадачи
- [x] Для каждого типа описать canonical value shape из legacy-реализации.
- [x] Зафиксировать список саб-ключей: обязательные, опциональные, дефолтные.
- [x] Реализовать native-рендер через композицию простых полей (`select`, `checkbox`, `color`, `text`, `number`, `group`), а не через отдельный монолитный HTML.
- [x] Нормализовать `sanitize()` по каждому саб-ключу.
- [x] Явно описать `validate()` для частично заполненных конфигов.
- [x] Добавить тесты на round-trip: `value -> render -> sanitize/validate -> expected shape`.
- [x] Проверить, что resulting HTML не требует `WP_Field` для базового отображения.

### DoD этапа
- [x] Все settings-object типы имеют documented native value shape.
- [x] Значения этих типов больше не зависят от legacy wrapper при рендере.
- [x] Тесты покрывают и пустые, и частично заполненные, и полные конфиги.

---

## Этап 4 — WordPress media/editor integration типы

### Цель
Перевести типы, завязанные на WP admin-интеграции и client-side enhancement.

### Объём этапа
- [x] `media`
- [x] `color`
- [x] `editor`
- [x] `image`
- [x] `file`
- [x] `gallery`
- [x] `code_editor`
- [x] `icon`

### Подзадачи
- [x] Разделить для каждого типа 2 слоя: server-render baseline и optional JS enhancement.
- [x] Вынести общую native-стратегию подключения WP assets (`wp_enqueue_media`, color picker, code editor APIs).
- [x] Реализовать degraded mode: поле должно иметь usable HTML даже если enhancement-скрипт не подключен.
- [x] Определить единый контракт `data-*` атрибутов для modern JS вместо legacy DOM-зависимостей.
- [x] Добавить/обновить modern JS-инициализацию там, где без неё тип нефункционален.
- [x] Добавить integration-тесты на enqueue contract и render contract.
- [x] Расширить demo/smoke checklist для страниц, где нужны WP-admin assets.

### DoD этапа
- [x] Типы этапа используют native PHP render и modern asset contract.
- [x] Нет прямой зависимости от `legacy/assets/js/wp-field.js`.
- [x] При отсутствии enhancement JS поле деградирует предсказуемо, а не ломается.

---

## Этап 5 — Высокая сложность: map + финальный cutover bridge-слоя

### Цель
Закрыть самые сложные интеграционные типы и подготовить системный отказ от `LegacyAdapterBridge` для официального registry.

### Объём этапа
- [x] `map`
- [x] Проверка остаточных bridge routes в `Field::make()`
- [x] Финальный audit alias-маршрутов (`image_picker`, `imagepicker`, `date_time`)
- [x] Подготовка плана удаления/сужения `LegacyAdapterBridge`

### Подзадачи
- [x] Для `map` определить минимальный native baseline без внешних провайдеров и enhancement-путь с JS.
- [x] Проверить, не тянут ли оставшиеся типы скрытые legacy hooks/enqueues.
- [x] Составить финальную таблицу: `official registry type -> native | legacy-only fallback`.
- [x] Оставить через fallback только truly-custom/unknown типы вне registry.
- [x] Обновить `API.md`, `README*`, `analysis.md`, `decision-log.md`, demo-страницы и supported matrix.
- [x] Добавить regression-тест, который подтверждает: официальный registry больше не уходит в `LegacyAdapterBridge`.
- [x] Зафиксировать отдельным решением, можно ли сужать `LegacyAdapterBridge` до режима compat-only.

### DoD этапа
- [x] Все официальные bridge-типы либо переведены в native, либо явно помечены как сознательно отложенные с причиной.
- [x] `LegacyAdapterBridge` перестаёт быть основным маршрутом для официального registry.
- [x] Документация синхронизирована с фактическим runtime.

---

## Сквозные правила для всех этапов

- [ ] Не менять `WP_Field.php` без крайней необходимости; приоритет — перенос поведения в native runtime.
- [ ] Каждый тип переносить по схеме: `contract capture -> native render -> sanitize/validate -> tests -> docs -> demo`.
- [ ] Не переносить следующий этап, пока не закрыт regression gate текущего.
- [ ] Если тип требует JS, сначала обеспечить server-render baseline, потом enhancement.
- [ ] После завершения значимой сессии обновлять минимум один из файлов: `analysis.md`, `plan.md`, `decision-log.md`.

---

## Минимальные команды проверок по этапу

```bash
./vendor/bin/pest --configuration phpunit.xml tests/Unit
./vendor/bin/pest --configuration phpunit.xml tests/Feature
composer analyse
composer lint:check
```

Полный прогон перед merge этапа:

```bash
composer ci
```
