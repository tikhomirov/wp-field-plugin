# WP_Field — пошаговый план полной миграции `Field::make()`

_Обновлено: 2026-04-06_

## Цель

Довести `Field::make()` до полного паритета с legacy-реестром `WP_Field::init_field_types()`:

1. каждый legacy-тип имеет OOP-представление в `src/Field/Types/*`;
2. `Field::make($type, $name)` использует OOP-класс для всех типов реестра и алиасов;
3. `LegacyWrapperField` остаётся только fallback-слоем совместимости;
4. legacy baseline (`WP_Field.php`) не ломается до отдельного cutover-решения.

---

## 0) Текущее состояние (факт)

### 0.1 Уже закрыто

Сделано в текущих итерациях:

- исправлен маппинг conditions в legacy bridge:
  - `LegacyWrapperField` корректно переводит flat-conditions в legacy `dependency`;
  - поддержан `OR` relation;
  - `LegacyAdapterBridge` синхронизирован с flat-структурой `HasConditionals`.

- добавлены/используются OOP-классы:
  - `TextField` / `InputField`
  - `TextareaField`
  - `SelectField`
  - `CheckboxField`
  - `CheckboxGroupField`
  - `GroupField`
  - `HeadingField`
  - `SubheadingField`
  - `NoticeField`
  - `ContentField`
  - `RadioField` (через bridge)
  - `MediaField` (через bridge)
  - `FieldsetField` (через bridge)
  - `ColorField` (через bridge)
  - `EditorField` (через bridge)
  - `ImageField` (через bridge)
  - `FileField` (через bridge)
  - `GalleryField` (через bridge)
  - `RepeaterField` (native)
  - `FlexibleContentField` (native)

- `Field::make()` уже нативно покрывает:
  - `text`
  - `password`, `email`, `url`, `tel`, `number`, `range`, `hidden`, `date`, `time`, `datetime-local`
  - `textarea`
  - `select`, `multiselect`
  - `radio`
  - `checkbox`, `checkbox_group`
  - `color`, `editor`, `media`, `image`, `file`, `gallery`
  - `fieldset`
  - `group`
  - `heading`, `subheading`, `notice`, `content`
  - `repeater`, `flexible_content`
  - алиасы `date_time`/`datetime` нормализуются в `datetime-local`

- контракт условной логики формализован:
  - `orWhen()` добавлен в `FieldInterface`;
  - `API.md` синхронизирован с фактическим контрактом.

### 0.2 Что ещё не мигрировано (legacy-only через fallback)

На текущем этапе официальные типы legacy registry и официальные алиасы закрыты через native/bridge-классы.

`LegacyWrapperField` остаётся только fallback-маршрутом для truly-unknown custom типов вне registry.

---

## 1) Стратегия завершения миграции (итерации)

Ниже — полный план до финального окончания процесса.

### Статус итераций (срез на 2026-04-06)

- [x] Итерация 1 — закрыта (матрица зафиксирована в `docs/wp-field/supported-matrix.md`)
- [x] Итерация 2 — закрыта
- [x] Итерация 3 — закрыта (волна A2 переведена на native OOP-типы)
- [x] Итерация 4 — закрыта (волна B1 переведена на native OOP-типы)
- [x] Итерация 5 — закрыта (добавлены bridge-классы `AccordionField` и `TabbedField`, подключены в `Field::make()`)
- [x] Итерация 6 — закрыта (типы C1 переведены в bridge-классы и подключены в `Field::make()`)
- [x] Итерация 7 — закрыта (типы C2 переведены в bridge-классы и подключены в `Field::make()`)
- [x] Итерация 8 — закрыта (alias-map финализирован, `image_picker/imagepicker` выведены из fallback)
- [x] Итерация 9 — закрыта (добавлен regression gate: целевые unit/feature проверки и точечные контракты)
- [x] Итерация 10 — закрыта (API/README/examples синхронизированы, финальный статус зафиксирован)

## Итерация 1 — Supported matrix (источник работ) ✅

### Что сделать
- [x] Создать/обновить матрицу `тип -> OOP-класс -> статус(native/bridge/legacy-only)`.
- [x] Для каждого типа зафиксировать:
  - [x] обязательные атрибуты;
  - [x] формат `value`;
  - [x] required CSS/JS hooks (`class`, `data-*`), если тип JS-зависимый;
  - [x] expected sanitize/validate behavior.
- [x] Зафиксировать alias-map отдельно.

  ✅ Создан отдельный артефакт матрицы:
- docs/wp-field/supported-matrix.md

### DoD
- [x] матрица покрывает все 52 unique + 4 alias;
- [x] нет «неопределённых» типов.

---

## Итерация 2 — Инфраструктурная стабилизация перед массовым переносом ✅

### Что сделано
- [x] Убран reflection-хак в `RepeaterField`/`FlexibleContentField`:
  - [x] используется явный механизм `withName()` / `cloneWithName()`.
- [x] `UIManager` приведён к фактическому пайплайну ассетов:
  - [x] используются реальные пути;
  - [x] добавлен `type="module"` для Vite bundles, где это требуется;
  - [x] исключены ссылки на несуществующие файлы.
- [x] Решён контракт `orWhen()`:
  - [x] `orWhen()` добавлен в `FieldInterface`;
  - [x] контракт отражён в `API.md`.

### DoD
- [x] composite-поля не используют Reflection для смены имени;
- [x] UIManager не ссылается на отсутствующие ассеты;
- [x] контракт условий формально зафиксирован.

---

## Итерация 3 — Волна A2 (простые интерактивные) ✅

### Типы
- [x] `switcher`
- [x] `spinner`
- [x] `button_set`
- [x] `slider`
- [x] `image_select`

### Что сделано
- [x] Добавлены native OOP-классы.
- [x] Сохранён legacy-совместимый HTML-каркас и `data-*` для `assets/js/wp-field.js`.
- [x] Добавлены fluent-методы только для реально поддерживаемых атрибутов (`min/max/step/unit/...`).
- [x] Подключены в `Field::make()`.

### DoD
- [x] типы не уходят в fallback;
- [x] UI работает в текущем runtime без regressions.

---

## Итерация 4 — Волна B1 (структурные/контентные простые) ✅

### Типы
- [x] `group`
- [x] `heading`
- [x] `subheading`
- [x] `notice`
- [x] `content`

### Что сделано
1. [x] Реализованы OOP-классы с сохранением legacy-render контракта.
2. [x] Для `group` — корректная вложенность имён и сериализация `toArray()`.
3. [x] Для статических контентных типов — чёткая политика sanitize/escape.
4. [x] Подключены в `Field::make()`.

### DoD
- [x] все 5 типов покрыты нативными классами;
- [x] `group` корректно работает в контейнерах и nested-сценариях.

---

## Итерация 5 — Волна B2 (layout/container UI) ✅

### Типы
- [x] `accordion`
- [x] `tabbed`

### Что сделано
1. [x] Добавлены OOP bridge-классы `AccordionField` и `TabbedField` с fluent-методами для контрактов `sections/items/tabs`.
2. [x] Сохранён legacy runtime через `LegacyAdapterBridge` (JS hooks и поведение раскрытия/переключения остаются baseline-совместимыми).
3. [x] Типы подключены в `Field::make()`.
4. [x] Добавлен unit-тест на маппинг `Field::make('accordion'|'tabbed')`.

### DoD
- [x] `accordion` и `tabbed` создаются через `make()` без fallback в `LegacyWrapperField`;
- [x] runtime-поведение остаётся legacy-baseline за счёт bridge-рендера.

---

## Итерация 6 — Волна C1 (настройки-объекты) ✅

### Типы
- [x] `typography`
- [x] `spacing`
- [x] `dimensions`
- [x] `border`
- [x] `background`
- [x] `link_color`
- [x] `color_group`

### Что сделано
1. [x] Добавлены OOP bridge-классы для всех 7 типов C1 в `src/Field/Types/*`.
2. [x] Добавлены fluent-методы под ключевые legacy-атрибуты (`options/units/sides/styles/background_fields/states`).
3. [x] Типы подключены в `Field::make()` и больше не уходят в fallback `LegacyWrapperField`.
4. [x] Добавлен unit-тест на маппинг C1-типов.

### DoD
- [x] C1-типы создаются через `Field::make()` как bridge-классы;
- [x] рендер/поведение совместимы с legacy baseline за счёт `LegacyAdapterBridge`.
- [ ] отдельная глубокая `sanitize()/validate()` по подполям отложена на следующий этап hardening (сейчас используется базовый контракт `AbstractField`).

---

## Итерация 7 — Волна C2 (продвинутые JS/внешние интеграции) ✅

### Типы
- [x] `code_editor`
- [x] `icon`
- [x] `map`
- [x] `sortable`
- [x] `sorter`
- [x] `palette`
- [x] `link`
- [x] `backup`

### Что сделано
1. [x] Добавлены bridge-классы в `src/Field/Types/*` для всех 8 C2-типов.
2. [x] Типы подключены в `Field::make()` и больше не уходят в `LegacyWrapperField`.
3. [x] Добавлен unit-тест на маппинг C2-типов.
4. [x] В `supported-matrix.md` статусы C2 обновлены на `bridge`.

### DoD
- [x] все 8 типов выходят из fallback;
- [ ] критические JS-сценарии и graceful fallback для внешних зависимостей (`map`, `code_editor`) вынесены в отдельный integration/smoke этап (текущий шаг закрыл make-мэппинг и bridge-слой).

---

## Итерация 8 — Алиасы и финализация `make()` ✅

### Что сделано
1. [x] Финализирован alias-map в `Field::make()`:
   - `date_time` / `datetime` → `datetime-local`
   - `imagepicker` → `image_picker`
2. [x] Добавлен явный класс/маппинг для `image_picker` (`ImagePickerField`), alias `imagepicker` больше не идёт в fallback.
3. [x] Добавлен точечный unit-тест на alias-маршруты.
4. [x] Fallback оставлен только для неизвестных custom-типов (официальные alias-ы закрыты).

### DoD
- [x] все официальные алиасы покрыты явным маппингом и не используют `LegacyWrapperField` fallback.

---

## Итерация 9 — Тестовый фриз и regression gate ✅

### Что сделано
1. [x] Расширены unit regression-кейсы в `tests/Unit/Field/FieldTest.php`:
   - alias routing;
   - C1/C2 make-мэппинг;
   - nested render-contract для `accordion/tabbed`;
   - conditional logic mapping для bridge-поля.
2. [x] Стабилизирован unit runtime для composite-полей: в `RepeaterField`/`FlexibleContentField` добавлен fallback при отсутствии `esc_html__`.
3. [x] Выполнен целевой прогон unit/feature тестов по migrated-областям (`FieldTest`, `LegacyWrapperConditionsTest`, `DependencyTest`, `CompositeFieldsTest`).
4. [x] Проверены nested-names сценарии для `group/repeater/flexible/accordion/tabbed` (через unit-кейсы и существующие feature-тесты composities).

### DoD
- [x] зелёный прогон релевантных unit/feature тестов;
- [x] регрессии по ключевым migrated-контрактам не обнаружены в целевом прогоне.

---

## Итерация 10 — Документация, examples, финальный cutover-отчёт ✅

### Что сделано
1. [x] `API.md` синхронизирован с фактическим состоянием `Field::make()` (полное покрытие registry + alias-map + fallback policy).
2. [x] `README.md` / `README.ru.md` обновлены по фактическим метрикам и API-примерам.
3. [x] `examples/v3-demo.php` очищен от вызовов несуществующих методов (`Field::email/textarea/image`, `dependency()`).
4. [x] В `decision-log.md` зафиксирован финальный статус миграции и правила fallback.

### DoD
- [x] документация соответствует коду по ключевому публичному API;
- [x] завершение миграции формально зафиксировано.

---

## 2) Контрольный список типов (до полного завершения)

## Уже покрыты (native или bridge)
- `text`, `password`, `email`, `url`, `tel`, `number`, `range`, `hidden`, `textarea`
- `select`, `multiselect`, `radio`, `checkbox`, `checkbox_group`
- `editor`, `media`, `image`, `file`, `gallery`, `color`, `date`, `time`, `datetime`/`datetime-local`
- `fieldset`
- `group`
- `heading`, `subheading`, `notice`, `content`
- `repeater`, `flexible_content`
- `switcher`, `spinner`, `button_set`, `slider`, `image_select`
- alias: `date_time`

## Ещё перенести (legacy-only на сейчас)
- Нет официальных типов/алиасов для переноса.
- `LegacyWrapperField` используется только для неизвестных кастомных типов.

---

## 3) Проверки по этапам

Минимальный набор команд после каждой итерации:

- targeted unit/feature: `./vendor/bin/pest --configuration phpunit.xml <пути тестов>`
- этапный прогон unit: `./vendor/bin/pest --configuration phpunit.xml tests/Unit`
- при изменениях runtime/рендера: `./vendor/bin/pest --configuration phpunit.xml tests/Feature`

Полный прогон перед финальным закрытием миграции:
- `composer test`
- `composer lint:check`
- `composer analyse`

---

## 4) Финальный Definition of Done (миграция завершена)

Миграция считается полностью завершённой, когда одновременно выполнено:

1. `Field::make()` покрывает весь legacy registry + алиасы.
2. Для каждого типа есть OOP-класс и тесты.
3. `LegacyWrapperField` используется только для неизвестных/кастомных типов.
4. Нет reflection-хаков для вложенных имён полей.
5. UIManager и ассеты соответствуют фактическому runtime.
6. Документация и examples совпадают с кодом.
