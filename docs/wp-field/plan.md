# WP_Field — план работ

_Обновлено: 2026-04-05_

## Цель текущего цикла

Довести `Field::make()` до функционального паритета с тем, что сегодня умеет `Field::legacy()`/`WP_Field::make(...)`,
но в **ООП-слое `src/Field/*`**.

Критерий цели:
- для каждого legacy-типа из реестра `WP_Field::init_field_types()` есть нативный OOP-класс;
- `Field::make($type, $name)` возвращает нативный класс (а не `LegacyWrapperField`) для всех поддерживаемых типов;
- legacy API (`WP_Field`) остаётся рабочим и обратно совместимым.

---

## 0) Фактическая точка старта (по аудиту)

### Что уже есть
- Реестр legacy: **52 уникальных типа + 4 алиаса**.
- `Field::make()` нативно покрывает только:
  - `text`
  - `repeater`
  - `flexible_content`
  - `radio` (через bridge)
  - `media` (через bridge)
  - `fieldset` (через bridge)
- Всё остальное идёт в `LegacyWrapperField`.

### Что мешает прямой миграции
1. Нет supported-matrix на уровне «тип → обязательные опции → формат value → HTML/JS контракт».
2. `LegacyWrapperField` некорректно маппит conditional logic (плоская структура vs ожидание вложенной).
3. `RepeaterField`/`FlexibleContentField` используют reflection-хак для имён вложенных полей.
4. `UIManager` не совпадает с реальным ассет-пайплайном.

---

## 1) План реализации паритета `Field::make()`

## Этап 1 — Зафиксировать контракт паритета (документационно)

Сделать перед кодом:
1. Сформировать матрицу типов (`legacy registry` → `OOP class`) с колонками:
   - тип/алиас;
   - required config keys;
   - формат значения;
   - required CSS/JS hooks;
   - статус: `native` / `bridge` / `legacy-only`.
2. Зафиксировать минимальный контракт совместимости:
   - совместимый HTML-каркас (классы/`data-*` для `assets/js/wp-field.js`);
   - совместимый формат `dependency`;
   - совместимый формат сохранённого value.
3. Уточнить целевой приоритет портирования (ниже).

**DoD этапа:** есть формальная матрица, по которой можно портировать без догадок.

---

## Этап 2 — Стабилизационный фундамент для миграции

Перед массовым добавлением новых классов:
1. Починить mapping conditions в `LegacyWrapperField` (чтобы fallback не ломал поведение).
2. Убрать reflection-хак из composite-полей (ввести явный механизм `withName()`/`cloneWithName()`).
3. Привести `UIManager` к фактическим ассетам (реальные файлы + корректный `type="module"` где нужно).
4. Синхронизировать поведение `FieldInterface` и traits (минимум: решить контракт `orWhen()`).

**DoD этапа:** fallback слой стабилен, и миграция новых типов не размножает техдолг.

---

## Этап 3 — Портирование типов в OOP (волнами)

### Волна A (базовые и массовые)
- `password`, `email`, `url`, `tel`, `number`, `range`, `hidden`, `textarea`, `date`, `time`, `datetime`, `color`.

### Волна B (choice и простые интерактивные)
- `select`, `multiselect`, `checkbox`, `checkbox_group`, `switcher`, `spinner`, `button_set`, `slider`, `image_select`.

### Волна C (медиа и редакторы)
- `editor`, `image`, `file`, `gallery`, `code_editor`, `icon`, `map`, `link`.

### Волна D (структурные и composition UI)
- `group`, `accordion`, `tabbed`, `typography`, `spacing`, `dimensions`, `border`, `background`, `link_color`, `color_group`, `sortable`, `sorter`, `palette`, `backup`.

### Волна E (алиасы + финализация фабрики)
- `date_time`, `datetime-local`, `image_picker`, `imagepicker`.
- финальный `Field::make()` map без fallback на `LegacyWrapperField` для покрытых типов.

**DoD этапа:** все типы из матрицы имеют нативные OOP-классы, `make()` использует их напрямую.

---

## Этап 4 — Тесты паритета и регрессия

Для каждой волны:
1. Unit-тесты на fluent-конфигурацию и `toArray()`.
2. Тесты `sanitize()`/`validate()` для value-формата типа.
3. Snapshot/contract тесты рендера (критичные CSS классы и `data-*`).
4. Интеграционные тесты «legacy vs oop» на одинаковый входной конфиг для ключевых сценариев.

Отдельно:
- тесты conditional logic для native + fallback;
- тесты composite-полей на корректные вложенные `name`.

**DoD этапа:** новый OOP-слой покрыт тестами не хуже legacy-критичных сценариев.

---

## Этап 5 — Документация и cutover

1. Обновить `API.md` и README под фактический API.
2. Обновить examples, убрать вызовы несуществующих sugar-методов.
3. Зафиксировать в `decision-log.md` статус cutover:
   - что считается fully-native;
   - что временно остаётся через bridge.

**DoD этапа:** документация = коду; нет «ложно поддерживаемых» примеров.

---

## 2) Приоритет выполнения (P0 → P3)

### P0
- матрица поддерживаемых типов;
- fix conditions в `LegacyWrapperField`;
- стабилизация name/runtime для composite полей.

### P1
- волны A + B (самый частый функционал форм);
- тесты паритета для A/B.

### P2
- волны C + D;
- выравнивание примеров/README/API.

### P3
- косметический рефакторинг legacy-монолита `WP_Field.php` после достижения паритета.

---

## 3) Definition of Done для задачи пользователя

Задачу «довести `Field::make()` до возможностей `Field::legacy()` в ООП» считаем завершённой, когда:

1. `Field::make()` покрывает все типы legacy-реестра (с алиасами).
2. Для каждого типа есть нативный класс в `src/Field/Types/*`.
3. Fallback на `LegacyWrapperField` остаётся только как совместимость, а не основной путь.
4. Рендер/сохранение/зависимости работают совместимо с текущим runtime.
5. Документация и примеры не расходятся с кодом.
