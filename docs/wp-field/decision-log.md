# WP_Field — журнал решений

_Обновлено: 2026-04-06_

## Формат

Каждое решение фиксируется коротко:
- что решили;
- почему;
- что сохраняем для обратной совместимости;
- что считаем deprecated или нежелательным.

---

## 2026-04-06 — Уточнён контракт entrypoint: `WP_Field.php` = deprecated loader, `legacy/WP_Field.php` = класс

### Решение
Финально закреплена структура загрузки legacy:
- `WP_Field.php` в корне — deprecated entrypoint-лоадер (с admin warning), который подключает `legacy/WP_Field.php`;
- `legacy/WP_Field.php` содержит сам класс `WP_Field`;
- `legacy/bootstrap.php` содержит legacy hooks/enqueues;
- `wp-field.php` остаётся canonical entrypoint.

Дополнительно в `woo2iiko.php` подключение `lib/wp-field/wp-field.php` переведено на абсолютный путь через `IIKO_PLUGIN_DIR` и добавлен safety-net `require_once lib/wp-field/vendor/autoload.php`, если `WpField\UI\NavItem` ещё не загружен.

### Почему
Это устраняет неоднозначность по «где класс, где entrypoint» и снижает риск фатала `Class "WpField\\UI\\NavItem" not found` из-за относительных include-путей/порядка загрузки.

### Backward compatibility
Старые include-пути через `WP_Field.php` продолжают работать; при этом появляется предупреждение о deprecated entrypoint.

### Deprecation
Для новых интеграций использовать `wp-field.php` как основную точку входа.

---

## 2026-04-06 — План миграции `Field::make()` архивирован, активирован новый bugfix-план

### Решение
После закрытия Итераций 1–10 миграционный план перенесён в архив:
- `docs/реализовано/2026-04-06-field-make-migration-plan.md`.

`docs/wp-field/plan.md` переключён в режим активного плана следующей фазы:
1) фикс интеграционной ошибки `WpField\\UI\\NavItem not found` в `woo2iiko`;
2) стабилизация demo-страниц `wp-field-v3-demo` / `wp-field-ui-demo`;
3) выход на 100% test coverage с coverage gate.

### Почему
Текущий план миграции выполнен; дальнейшая работа — это отдельный цикл стабилизации и качества.

### Backward compatibility
Runtime не менялся, изменена только структура планирования в документации.

### Deprecation
Не вести новые задачи в закрытом миграционном плане Iteration 1–10.

---

## 2026-04-06 — v3-demo отвязан от обязательных legacy assets при выключенном legacy runtime

### Решение
`examples/v3-demo.php` обновлён так, чтобы при `wp_field_enable_legacy = false`:
- не подключать `legacy/assets/js/wp-field.js` и `legacy/assets/css/wp-field.css`;
- принудительно использовать `react` режим вместо `vanilla`;
- блокировать выбор vanilla в UI-переключателе;
- оставлять legacy-секцию как информационную (через `class_exists('WP_Field')`).

### Почему
Нужно иметь возможность отключать legacy runtime по feature-flag без поломки modern demo и без загрузки устаревших зависимостей.

### Backward compatibility
При `wp_field_enable_legacy = true` поведение не меняется: legacy assets и vanilla fallback продолжают работать.

### Deprecation
Не считать `v3-demo` жёстко завязанным на legacy assets.

---

## 2026-04-06 — Legacy runtime вынесен в `legacy/`, точка входа плагина переведена на `wp-field.php`

### Решение
Сделан структурный перенос legacy-слоя:
- новый WordPress entrypoint: `wp-field.php` (plugin header + bootstrap);
- файл `WP_Field.php` приведён к формату «только класс» (без bootstrap/хуков);
- legacy assets перенесены в `legacy/assets/js/wp-field.js` и `legacy/assets/css/wp-field.css`;
- legacy demo перенесён в `legacy/example.php`;
- `legacy/WP_Field.php` оставлен как compat-loader на корневой `WP_Field.php` для include-путей, где уже используется `legacy/...`;
- удалён лишний alias-файл `wp-filed.php`.

Оркестрация разделена так:
- central entrypoint `wp-field.php` выполняет только bootstrap и условное подключение legacy;
- legacy-хук `admin_enqueue_scripts` вынесен в отдельный `legacy/bootstrap.php`;
- загрузка demo-страниц разделена: legacy demo из `legacy/`, modern demo из `examples/` (v3-demo может работать и при выключенном legacy runtime).

Добавлен feature-flag `wp_field_enable_legacy` (filter, default `true`) для поэтапного отключения legacy runtime в будущем.

### Почему
Нужно отделить legacy runtime от современного слоя и использовать стандартный для WP файл входа, чтобы архитектурно развести `modern` и `legacy`, а также упростить дальнейшее поэтапное отключение legacy-части.

### Backward compatibility
- класс `WP_Field` сохранён без изменения публичного API;
- старые include-пути через корневой `WP_Field.php` продолжают работать;
- пути legacy CSS/JS обновлены во всех enqueue-точках на `legacy/assets/*`.

### Deprecation
Новый canonical entrypoint — `wp-field.php`.
Прямое использование корневого `WP_Field.php` следует считать режимом совместимости.

### Дополнение
`examples/v3-demo.php` отвязан от обязательного `WP_Field::make(...)` в основных preview-блоках (repeater/flexible/conditional) и теперь может загружаться независимо от legacy runtime. Legacy-секция в демо показывает сообщение, если `wp_field_enable_legacy` выключен.

### Корректировка структуры entrypoint
- `WP_Field.php` в корне — legacy entrypoint-лоадер (deprecated), он загружает `legacy/WP_Field.php` и показывает предупреждение о переходе на `wp-field.php`.
- `legacy/WP_Field.php` содержит сам класс `WP_Field` (без bootstrap-логики entrypoint).
- legacy bootstrap-хуки остаются в `legacy/bootstrap.php`.

---

## 2026-04-06 — Итерация 10 (docs/examples/cutover) закрыта

### Решение
Завершён финальный документационный этап миграции:
- обновлён `API.md` по фактическому поведению `Field::make()` (registry + aliases + fallback policy);
- синхронизированы `README.md` и `README.ru.md` (52 unique + 4 aliases, корректные примеры методов);
- обновлён `examples/v3-demo.php`: удалены вызовы несуществующих fluent-методов (`Field::email`, `Field::textarea`, `Field::image`, `dependency()`) и заменены на фактические маршруты через `Field::make()` / `when()`.

### Почему
Итерация 10 требовала убрать расхождения между кодом и документацией и формально завершить цикл миграции `Field::make()`.

### Backward compatibility
- runtime-код не ломался;
- изменения касаются документации и демо-кода;
- fallback для официальных типов/алиасов больше не нужен, остаётся только для неизвестных custom-типов.

### Deprecation
Не использовать в документации/демо несуществующие fluent-методы.

---

## 2026-04-06 — Composer CI-пайплайн стандартизирован на `ci` / `ci:fix`

### Решение
В `composer.json` добавлены агрегирующие скрипты:
- `quality`
- `quality:fix`
- `ci`
- `ci:fix`

GitHub Actions workflow `.github/workflows/ci.yml` переведён на `composer ci` вместо разрозненных шагов `test/analyse/lint`.

### Почему
Нужен единый контракт запуска для локальной машины и CI, чтобы одна команда полностью описывала проверку качества, а в workflow не дублировалась логика из Composer.

### Backward compatibility
- отдельные команды `test`, `analyse`, `lint`, `rector` сохранены;
- workflow по-прежнему валидирует `composer.json` и ставит зависимости;
- coverage-отчёт сохранён для PHP 8.3.

### Deprecation
Не возвращаться к разрозненным шагам quality в workflow, если `composer ci` покрывает тот же набор проверок.

---

## 2026-04-06 — Rector подключён как инструмент качества кода

### Решение
Добавлен Rector 1.2.10 в dev-зависимости и базовая конфигурация `rector.php`, которая запускает правила code quality только для `src/`.

В `composer.json` добавлены скрипты:
- `composer rector:dry-run`
- `composer rector`

### Почему
Нужен отдельный автоматический инструмент для безопасной поддержки качества кода, не затрагивая legacy-runtime `WP_Field.php` и не смешивая Rector с Pint/PHPStan.

### Backward compatibility
- Rector ограничен папкой `src/`;
- legacy-файл `WP_Field.php` и demo/example-код не участвуют в прогоне;
- existing runtime не меняется.

### Deprecation
Не расширять Rector на legacy-runtime без отдельного решения о cutover и списке исключений.

---

## 2026-04-06 — Итерация 9 (regression gate) закрыта целевым тестовым фризом

### Решение
Закрыт regression gate для миграционного цикла `Field::make()`:
- добавлены точечные unit-кейсы на alias routing, C1/C2 mapping и bridge-conditional mapping;
- добавлен nested render-contract для `accordion/tabbed`;
- стабилизированы unit-тесты composite-полей (`RepeaterField`/`FlexibleContentField`) через fallback при отсутствии `esc_html__`.

Выполнен целевой прогон релевантных unit/feature тестов по migrated-областям.

### Почему
Итерация 9 требовала зафиксировать рабочий regression gate после массового перевода типов в native/bridge и перед финальной документационной стабилизацией.

### Backward compatibility
- runtime и публичные сигнатуры не ломались;
- изменения в `RepeaterField`/`FlexibleContentField` только повышают совместимость test/runtime окружений без WordPress i18n helpers.

### Deprecation
Не завершать итерацию миграции без целевого regression-прогона и явных тест-контрактов на новые mapping-маршруты.

---

## 2026-04-06 — Итерация 8 (aliases) закрыта: `image_picker/imagepicker` выведены из fallback

### Решение
Финализирован alias-map в `Field::make()`:
- `date_time` / `datetime` нормализуются в `datetime-local`;
- добавлена нормализация `imagepicker` → `image_picker`.

Добавлен явный bridge-класс `ImagePickerField` и маппинг `image_picker`, поэтому оба alias-маршрута (`image_picker`, `imagepicker`) больше не попадают в `LegacyWrapperField` fallback.

### Почему
Итерация 8 требовала полностью закрыть официальные alias-ы и оставить fallback только для truly-unknown custom типов.

### Backward compatibility
- legacy renderer сохранён через `LegacyAdapterBridge` (`render_image_picker`);
- публичные сигнатуры не ломались;
- старые alias-вызовы продолжают работать, но через явный класс.

### Deprecation
Считать `image_picker/imagepicker` fallback-only alias-ами больше нельзя.

---

## 2026-04-06 — Итерация 7 (C2: advanced JS/integration fields) закрыта bridge-слоем

### Решение
Добавлены bridge-классы и make-мэппинг для типов:
- `code_editor` → `CodeEditorField`
- `icon` → `IconField`
- `map` → `MapField`
- `sortable` → `SortableField`
- `sorter` → `SorterField`
- `palette` → `PaletteField`
- `link` → `LinkField`
- `backup` → `BackupField`

Все 8 типов переведены из `legacy-only` в `bridge` и создаются через `Field::make()` без fallback.

### Почему
Это закрывает C2-этап миграции `make()` и убирает оставшиеся legacy-only типы из official legacy registry (52 unique).

### Backward compatibility
- `WP_Field.php` и legacy renderer не менялись;
- рендер по-прежнему идёт через `LegacyAdapterBridge`, поэтому текущие JS hooks сохраняются;
- поведение остаётся baseline-совместимым.

### Deprecation
Считать C2-типы fallback-only больше нельзя.

---

## 2026-04-06 — Итерация 6 (C1: settings object fields) закрыта bridge-слоем

### Решение
Добавлены bridge-классы и make-мэппинг для типов:
- `typography` → `TypographyField`
- `spacing` → `SpacingField`
- `dimensions` → `DimensionsField`
- `border` → `BorderField`
- `background` → `BackgroundField`
- `link_color` → `LinkColorField`
- `color_group` → `ColorGroupField`

Все типы рендерятся через `LegacyAdapterBridge`, что сохраняет текущий runtime и JS-hooks legacy-слоя.

### Почему
Нужно закрыть итерацию C1 без риска регрессий: вывести типы из `legacy-only` и одновременно не ломать рабочий legacy рендер.

### Backward compatibility
- `WP_Field.php` не менялся;
- сохранён legacy rendering pipeline;
- fallback остаётся для ещё не мигрированных типов итерации C2.

### Deprecation
Считать C1-типы `legacy-only` больше нельзя: они переведены в `bridge`.

---

## 2026-04-06 — Итерация 5 (B2: accordion/tabbed) закрыта bridge-реализацией

### Решение
Для типов layout/container UI добавлены OOP bridge-классы:
- `src/Field/Types/AccordionField.php`
- `src/Field/Types/TabbedField.php`

Оба типа подключены в `Field::make()` и больше не уходят в fallback `LegacyWrapperField`.
Рендер оставлен через `LegacyAdapterBridge`, чтобы сохранить совместимость с текущим legacy runtime и JS hooks.

### Почему
Итерация 5 требует вывести `accordion`/`tabbed` из legacy-only статуса без риска для существующего поведения. Bridge-подход даёт управляемый шаг миграции с минимальным риском регрессий.

### Backward compatibility
- `WP_Field.php` не изменён;
- HTML/JS-контракты продолжают задаваться legacy-render слоем;
- API расширен безопасно: добавлены fluent-методы `sections()/items()/tabs()`.

### Deprecation
Считать `accordion`/`tabbed` legacy-only больше нельзя: теперь это bridge-типы в modern API.

---

## 2026-04-06 — Итерация 1 (Supported matrix) закрыта отдельным артефактом

### Решение
Создан отдельный документ `docs/wp-field/supported-matrix.md` с полной матрицей по legacy-реестру:
- 52 unique types;
- 4 aliases;
- для каждого типа зафиксированы: `OOP-класс`, статус (`native/bridge/legacy-only`), обязательные атрибуты, формат `value`, required CSS/JS hooks, ожидаемое sanitize/validate поведение.

Также в `docs/wp-field/plan.md` обновлён статус: Итерация 1 отмечена как завершённая.

### Почему
Итерация 1 требовала отдельный источник истины по покрытию типов, чтобы дальше выполнять миграционные волны без «неопределённых» типов.

### Backward compatibility
Runtime и legacy API не менялись. Изменения только в документации и управлении миграцией.

### Deprecation
Не хранить supported matrix только в тексте `plan.md` и чатах.

---

## 2026-04-06 — Зафиксирован статус итераций миграционного плана

### Решение
В `docs/wp-field/plan.md` введён явный статус-трекер по итерациям с чекбоксами:
- Итерация 2 отмечена как завершённая;
- Итерация 3 отмечена как следующий фокус;
- оставшиеся итерации зафиксированы как незавершённые.

### Почему
Нужно синхронизировать фактическое состояние кода и плана между сессиями и убрать двусмысленность «где остановились».

### Backward compatibility
Runtime и legacy API не меняются, изменена только управленческая документация.

### Deprecation
Не фиксировать статус итераций только в чате.

---

## 2026-04-05 — Контекст сессий хранится в репозитории

### Решение
Контекст по `wp-field` хранится в файлах:
- `docs/wp-field/analysis.md`
- `docs/wp-field/plan.md`
- `docs/wp-field/decision-log.md`
- `AGENTS.md`

### Почему
Чат-контекст ненадёжен между сессиями. Репозиторий — самый устойчивый источник памяти.

### Backward compatibility
Ничего не ломает.

### Deprecation
Не полагаться только на историю чата.

---

## 2026-04-05 — Источник истины: код выше README и demo

### Решение
При конфликте приоритет такой:
1. код в `WP_Field.php` и `src/`
2. этот `decision-log.md`
3. `analysis.md`
4. `plan.md`
5. README / CHANGELOG / examples

### Почему
В проекте уже есть расхождения между маркетинговым описанием и реальным API.

### Backward compatibility
Поведение существующего runtime оцениваем по коду, а не по текстовым обещаниям.

### Deprecation
Считать README и demo-файлы безусловно точной спецификацией.

---

## 2026-04-06 — Волна B1 переведена на native OOP-типы

### Решение
Типы `group`, `heading`, `subheading`, `notice` и `content` теперь создаются через native OOP-классы в `src/Field/Types/*` и маппятся из `Field::make()` без перехода в `LegacyWrapperField`.

### Почему
Это закрывает следующий блок простых структурных и контентных полей, делает API более симметричным и уменьшает зависимость от legacy fallback.

### Backward compatibility
- legacy runtime `WP_Field.php` не тронут;
- формат рендера сохранён в совместимом виде для текущего runtime;
- fallback остаётся для остальных не перенесённых типов и alias-ов.

### Deprecation
Не считать B1-типы legacy-only после этой сессии.

---

## 2026-04-06 — Волна A2 переведена на native OOP-типы

### Решение
Типы `switcher`, `spinner`, `button_set`, `slider` и `image_select` теперь создаются через native OOP-классы в `src/Field/Types/*` и маппятся из `Field::make()` без перехода в `LegacyWrapperField`.

### Почему
Это закрывает следующий приоритетный блок простых интерактивных полей из миграционного плана и уменьшает зависимость от legacy fallback.

### Backward compatibility
- legacy runtime `WP_Field.php` не тронут;
- HTML-каркас сохранён в совместимом виде для текущего JS runtime;
- fallback остаётся для остальных не перенесённых типов и alias-ов.

### Deprecation
Не считать A2-типы legacy-only после этой сессии.

---

## 2026-04-05 — Legacy API остаётся главным baseline

### Решение
До отдельного зафиксированного cutover `WP_Field` считается основным runtime baseline.

### Почему
Именно legacy слой сейчас:
- покрывает большую часть типов;
- содержит реальные интеграции с WordPress UI;
- лучше соответствует исторической совместимости.

### Backward compatibility
- `WP_Field::make([...])`
- legacy array config
- существующие типы и алиасы

всё это сохраняем как supported baseline.

### Deprecation
Нельзя считать legacy API уже выведенным из эксплуатации только потому, что есть `src/`.

---

## 2026-04-05 — Modern API трактуем как incremental layer, а не как завершённую замену

### Решение
`src/` рассматривается как **слой постепенной миграции**, а не как полностью завершённая замена `WP_Field.php`.

### Почему
Native field coverage пока частичная, а часть классов всё ещё рендерится через legacy bridge.

### Backward compatibility
Можно продолжать использовать hybrid-подход:
- native where possible;
- legacy wrapper where needed.

### Deprecation
Нежелательно называть текущий `src/` “полной v3 реализацией” без оговорок.

---

## 2026-04-05 — Поддерживаем hybrid API

### Решение
Официально допускается стратегия:
- `Field::text()/repeater()/flexibleContent()` для реально поддержанных native кейсов;
- `Field::make($type, $name)` и `LegacyWrapperField` для остальных типов.

### Почему
Это соответствует текущему коду и позволяет мигрировать постепенно без слома экосистемы.

### Backward compatibility
Сохраняем возможность использовать legacy-типы через fluent facade.

### Deprecation
Нежелательно обещать, что каждый тип уже имеет самостоятельную native-реализацию.

---

## 2026-04-05 — Первым делом стабилизируем интеграцию, а не расширяем surface API

### Решение
Следующий цикл работ должен сначала закрывать интеграционные проблемы:
1. conditions в `LegacyWrapperField`
2. runtime стратегия для repeater/flexible
3. `UIManager` и ассеты

И только потом — новые sugar methods и новые native field classes.

### Почему
Сейчас главный риск — не нехватка методов, а несогласованность слоёв.

### Backward compatibility
Никаких резких cutover до стабилизации runtime.

### Deprecation
Откладывается расширение API ради количества, если не закрыта базовая надёжность.

---

## 2026-04-05 — Обратную совместимость сохраняем на уровне runtime, не на уровне неточных обещаний

### Решение
Сохраняем совместимость с тем, что реально выполняется в коде, но не обязуемся поддерживать ошибочные примеры и завышенные claims из документации.

### Почему
Некоторые примеры уже используют методы, которых нет в `Field.php`.

### Backward compatibility
Сохраняем:
- legacy array API
- storage behaviors
- основные hooks/filters legacy-слоя

### Deprecation
Можно и нужно исправлять:
- misleading examples
- неточные формулировки в README/CHANGELOG
- pseudo-supported API, которого нет в коде

---

## 2026-04-05 — После каждой сессии обновлять минимум один из context-файлов

### Решение
Любая значимая сессия должна завершаться обновлением хотя бы одного файла:
- `analysis.md` — если изменилось понимание состояния/рисков;
- `plan.md` — если изменились приоритеты;
- `decision-log.md` — если принято решение;
- `AGENTS.md` — если изменился обязательный workflow.

### Почему
Иначе документация быстро превратится в мёртвый артефакт.

### Backward compatibility
Не влияет на runtime.

### Deprecation
Запрещено оставлять важные решения только в чате.

---

## 2026-04-05 — `Field::make()` расширен нативными OOP-типами (первая волна)

### Решение
Сделан первый шаг к паритету `Field::make()` с legacy-реестром без изменений `WP_Field.php`:

- добавлены нативные OOP-реализации:
  - `InputField` (input-based типы),
  - `TextareaField`,
  - `SelectField`,
  - `CheckboxField`,
  - `CheckboxGroupField`;
- `Field::make()` теперь создаёт нативные объекты для:
  - `password`, `email`, `url`, `tel`, `number`, `range`, `hidden`, `date`, `time`, `datetime`, `datetime-local`,
  - `textarea`, `select`, `multiselect`, `checkbox`, `checkbox_group`;
- алиасы `date_time`/`datetime` нормализуются в `datetime-local`.

### Почему
До этого `make()` почти всегда уходил в `LegacyWrapperField`, из-за чего новый OOP-слой не покрывал массовые типы.

### Backward compatibility
- `WP_Field.php` не трогался;
- unsupported типы всё так же идут в `LegacyWrapperField` (fallback сохранён);
- существующие вызовы `Field::text()` не изменены.

### Deprecation
На этом шаге не вводилось. Полный отказ от fallback отложен до закрытия оставшихся типов.

---

## 2026-04-05 — Вторая волна make-мэппинга + фикс conditions в legacy bridge

### Решение
Выполнены шаги «1 и 2» из плана реализации:

1) `Field::make()` дополнительно переведён на нативные классы для типов:
- `color`
- `editor`
- `image`
- `file`
- `gallery`

2) Исправлен перенос fluent conditions в legacy-runtime:
- `LegacyWrapperField` теперь корректно преобразует **плоский** список условий `HasConditionals` в legacy `dependency`;
- поддержан OR-relation через `relation => OR`;
- `LegacyAdapterBridge` больше не предполагает вложенную структуру условий и корректно применяет `when()`/`orWhen()`.

### Почему
До фикса `LegacyWrapperField` и `LegacyAdapterBridge` ожидали структуру conditions «группы условий», хотя `HasConditionals` хранит «плоские условия». Это ломало/теряло dependency при рендере через legacy.

### Backward compatibility
- `WP_Field.php` не изменялся;
- fallback-модель сохранена;
- новые классы не меняют старые публичные сигнатуры.

### Deprecation
Не вводилось.

---

## 2026-04-05 — Введена project-local конфигурация `.pi` для wp-field

### Решение
Добавлен локальный набор файлов `.pi/`:
- `.pi/settings.json` — базовые настройки работы агента в проекте;
- `.pi/prompts/wp-field-session.md` — шаблон типовой рабочей сессии;
- `.pi/prompts/wp-field-bugfix.md` — шаблон точечного bugfix-потока.

### Почему
Нужно ускорить повторяемые сессии и уменьшить ручной пропуск обязательных шагов (чтение контекста, минимальные правки, обновление context-файлов).

### Backward compatibility
Runtime и публичный API библиотеки не затронуты.

### Deprecation
Не отменяет `AGENTS.md` и документы `docs/wp-field/*`: `.pi` выступает только как ускоритель рабочего процесса.

---

## 2026-04-05 — Добавлен универсальный шаблон промпта для wp-field

### Решение
Добавлен шаблон `.pi/prompts/wp-field-universal.md` для типового end-to-end потока:
контекст → диагностика → план → реализация → проверки → фиксация контекста → финальный отчёт.

### Почему
Нужен единый вход для большинства задач, чтобы не переключаться между отдельными шаблонами и не пропускать обязательные шаги из `AGENTS.md`.

### Backward compatibility
Код и runtime библиотеки не меняются.

### Deprecation
Не заменяет специализированные шаблоны (`wp-field-session`, `wp-field-bugfix`), а дополняет их как универсальный сценарий.

---

## 2026-04-05 — `orWhen()` закреплён в формальном `FieldInterface`

### Решение
Добавлен метод `orWhen(string $field, string $operator, mixed $value): static` в `src/Field/FieldInterface.php`.

### Почему
В runtime метод уже поддерживался через `HasConditionals`, но в формальном контракте интерфейса отсутствовал. Это создавало рассинхрон между реальным API и контрактом для IDE/static analysis.

### Backward compatibility
- Поведение runtime не меняется: метод и раньше работал во всех полях на базе `AbstractField`.
- Legacy API (`WP_Field.php`) не затронут.

### Deprecation
Старый статус `orWhen()` как «неинтерфейсного метода trait» больше не актуален.
