# Changelog

Все важные изменения в проекте WP_Field документируются в этом файле.

## [4.0.0] - 2026-04-10

### Изменено
- **Финализирован релиз v4** — выровнены релизные метаданные пакета, README и changelog под версию `4.0.0`
- **Modern API объявлен основным слоем для нового кода** — fluent field builder и server-rendered интеграция с WordPress теперь описаны как основной public-facing путь
- **Legacy runtime формализован как compatibility layer** — legacy-код остаётся доступным через `WP_Field.php` и `vanilla/`, включая standalone vanilla export
- **README.md и README.ru.md полностью актуализированы** — удалены устаревшие маркеры `v3.0`, `48 field types` и slug `wp-field-v3-demo`
- **Demo pages синхронизированы с текущей структурой проекта** — зафиксированы актуальные страницы `wp-field-examples`, `wp-field-components` и `wp-field-ui-demo`
- **Список возможностей v4 обновлён по реальному коду** — отражены `52` поддерживаемых типа полей, conditional logic, containers, storage strategies, standalone vanilla build и map provider contract
- **Package metadata обновлены** — синхронизированы `package.json`, `package-lock.json` и `composer.json`

### Добавлено
- **Map provider contract в публичном API** — `MapField` поддерживает `provider()`, `apiKey()`, `center()` и `zoom()`
- **Standalone vanilla build задокументирован как релизный артефакт** — сборка создаёт отдельный installable plugin archive `dist/wp-field-vanilla.zip`
- **Разделение demo/documentation surfaces** — отдельно описаны legacy examples page, modern components/docs page и UI showcase page

## [3.0.1] - 2026-04-06

### Изменено (Реструктуризация файловой структуры)
- **Vanilla runtime изолирован** — все legacy/vanilla файлы перемещены в `vanilla/`:
  - `vanilla/WP_Field.php` — legacy класс
  - `vanilla/bootstrap.php` — загрузчик vanilla assets
  - `vanilla/example.php` — demo страница (slug: wp-field-examples)
  - `vanilla/assets/css/wp-field.css` — vanilla стили
  - `vanilla/assets/css/wp-field-examples-vanilla.css` — demo page стили
  - `vanilla/assets/js/wp-field.js` — vanilla JS
  - `vanilla/assets/scss/wp-field-examples-vanilla.scss` — SCSS source

- **Demo pages реорганизованы** — co-located структура:
  - `examples/components/index.php` — WP_Field Components page (slug: wp-field-components)
  - `examples/components/assets/wp-field-components.css` — стили components page
  - `examples/components/assets/wp-field-components.js` — vanilla JS бандл (sidebar, search)
  - `examples/ui-demo/index.php` — Flux UI showcase (slug: wp-field-ui-demo)
  - `examples/shared-catalog.php` — единый каталог полей для всех demo pages

- **Удалены orphaned/legacy файлы**:
  - `assets/css/wp-field-v3-demo.css`
  - `assets/css/wp-field-demo.css`
  - `assets/css/wp-field-examples-vanilla.css` (перенесён в vanilla/)
  - `assets/css/imagepicker.css`
  - `assets/dist/v3-demo.js`
  - `assets/dist/client.js`
  - `assets/dist/wp-field-components.js` (перенесён в examples/components/)
  - `assets/js/imagepicker.js`
  - `assets/js/wp-field.js` (перенесён в vanilla/)
  - `assets/scss/demos/vanilla-demo.scss` → `vanilla/assets/scss/wp-field-examples-vanilla.scss`
  - `assets/scss/demos/v3-demo.scss`
  - `assets/src/v3-demo-app.jsx`
  - `examples/v3-demo.php` → `examples/components/index.php`
  - `examples/ui-demo.php` → `examples/ui-demo/index.php`
  - `legacy/` директория (перенесена в `vanilla/`)

- **Vite конфиги обновлены**:
  - `vite.components.config.js` — outDir теперь `examples/components/assets/`

- **wp-field.php** — обновлены пути подключения demo pages

## [3.0.0] - 2025-02-24

### Добавлено
- **Laravel-style Fluent API** — Цепочка методов: `Field::text('name')->label('Имя')->required()`
- **Repeater Field** — Поле с бесконечной вложенностью, min/max ограничениями, 3 режима отображения (table, block, row)
- **Flexible Content Field** — Конструктор блоков в стиле ACF с множественными типами layouts
- **Conditional Logic Backend** — 14 операторов (`==`, `!=`, `>`, `<`, `contains`, `in`, `empty` и др.) с AND/OR логикой
- **React UI Components** — Современные React компоненты для Repeater и Flexible Content с автоматическим fallback на Vanilla JS
- **UI Manager** — Переключение между React и Vanilla JS режимами через `UIManager::setMode()`
- **Legacy Adapter** — 100% обратная совместимость с v2.x array-based API через `LegacyAdapter::make()`
- **SOLID Architecture** — Интерфейсы, трейты, абстрактные классы, dependency injection
- **Storage Strategies** — PostMetaStorage, TermMetaStorage, UserMetaStorage, OptionStorage, CustomTableStorage
- **GitHub Actions CI/CD** — Автоматические тесты (Pest), статический анализ (PHPStan Level 9), проверка стиля (Pint), сборка React
- **Vite Build System** — Современная сборка React компонентов с hot reload
- **Composer Package** — Готов к публикации на Packagist с полными метаданными

### Изменено
- **Архитектура** — Переход на PSR-4 autoloading с namespace `WpField\`
- **Type Safety** — Все файлы используют `declare(strict_types=1)`, полная типизация параметров и возвращаемых значений
- **Code Style** — Унификация через Laravel Pint, 100% соответствие PSR-12
- **Документация** — Полностью обновлены README.md и README.ru.md с примерами v3.0 API
- **Версия** — Обновлена до 3.0.0 в composer.json, package.json

### Технические детали
- **PHPStan Level 9** — Максимальная строгость статического анализа
- **Pest Tests** — Все существующие тесты проходят успешно
- **React 18** — Использование современных хуков и функциональных компонентов
- **Infinite Nesting** — Repeater и Flexible Content поддерживают неограниченную вложенность

## [2.5.0] - 2025-11-23

### Добавлено
- **Полная реализация 48 типов полей**
  - Базовые: 9 типов (text, password, email, url, tel, number, range, hidden, textarea)
  - Выборные: 5 типов (select, multiselect, radio, checkbox, checkbox_group)
  - Продвинутые: 9 типов (editor, media, image, file, gallery, color, date, time, datetime)
  - Композитные: 2 типа (group, repeater)
  - Простые v2.1: 9 типов (switcher, spinner, button_set, slider, heading, subheading, notice, content, fieldset)
  - Средней сложности v2.2: 10 типов (accordion, tabbed, typography, spacing, dimensions, border, background, link_color, color_group, image_select)
  - Высокой сложности v2.3: 4 типа (code_editor, icon, map, sortable, sorter, palette, link, backup)
  - Итого: 48 уникальных типов полей + 4 алиаса для обратной совместимости
- **Обратная совместимость** с предыдущими версиями
- **Расширяемость** через фильтры и хуки

### Удалено
- **Файл field-data.php** — удалён, все примеры встроены в example.php
  - Упрощение структуры проекта
  - Все примеры теперь в одном месте
  - Удалено подключение field-data.php из example.php

## [2.4.10] - 2025-11-23

### Добавлено
- **Расширенные примеры Icon на странице примеров**
  - Добавлены примеры в `example.php` для отображения на странице
  - Пример регистрации библиотеки через фильтр `wp_field_icon_library`
  - Примеры видны в разделе "🚀 Расширенные примеры" для Icon поля

## [2.4.9] - 2025-11-23

### Добавлено
- **Расширенные примеры для кастомных icon sets**
  - Пример регистрации библиотеки через фильтр `wp_field_icon_library`
  - Пример использования параметра `icons` напрямую
  - Подробная документация в `field-data.php`

## [2.4.8] - 2025-11-23

### Добавлено
- **Поддержка кастомных icon sets для Icon поля**
  - Новый параметр `icons` для передачи массива кастомных иконок
  - Поддержка любых библиотек иконок (Font Awesome, Material Icons и т.д.)
  - Примеры в `field-data.php` и `example.php`
  - Расширенные примеры на странице примеров

## [2.4.7] - 2025-11-23

### Добавлено
- **Расширенные примеры Accordion на странице примеров**
  - Добавлены примеры в `example.php` для отображения на странице
  - Примеры видны в разделе "🚀 Расширенные примеры"
  - Примеры: с полями, дефолтный раздел, кастомные иконки

## [2.4.6] - 2025-11-23

### Добавлено
- **Расширенные примеры и аргументы для Accordion** в `field-data.php`
  - Аккордеон с полями (редактируемые поля внутри разделов)
  - Сохранение состояния (localStorage)
  - Получение значений на фронтенде
  - Кастомные иконки
  - Дефолтный открытый раздел
  - Всего 8 расширенных примеров

## [2.4.5] - 2025-11-23

### Добавлено
- **Дефолтные открытые вкладки (Tabbed)**
  - Флаг `active: true` для указания вкладки по умолчанию
  - Приоритет: дефолтная вкладка > сохранённое состояние > первая вкладка
- **Дефолтные открытые разделы (Accordion)**
  - Флаг `open: true` для указания открытого раздела
  - Приоритет: явно открытые разделы > сохранённое состояние
- Расширенные примеры в `field-data.php` для обоих типов полей

## [2.4.4] - 2025-11-23

### Добавлено
- **Сохранение состояния вкладок (Tabbed)** в localStorage
  - Выбранная вкладка сохраняется при переходе на другую страницу
  - Восстанавливается при возврате на страницу
- **Сохранение состояния аккордеонов (Accordion)** в localStorage
  - Можно открыть несколько разделов одновременно
  - Состояние сохраняется при обновлении страницы
  - Поддержка множественного открытия элементов

## [2.4.3] - 2025-11-23

### Исправлено
- **Accordion field**: Полностью переработана функциональность
  - PHP: Поддержка обоих вариантов `items` и `sections` для обратной совместимости
  - JS: Добавлено плавное открытие/закрытие с расчётом высоты контента
  - JS: Исправлена анимация - теперь работает корректно при любом размере контента
  - CSS: Улучшены переходы и визуальное оформление
  - CSS: Добавлена подсветка открытого элемента синей линией
  - CSS: Лучшая адаптивность и отзывчивость
- Обновлены примеры в `example.php` с реальными FAQ вопросами

## [2.4.2] - 2025-11-23

### Добавлено
- Расширенные примеры и список аргументов для поля **Accordion** в `field-data.php`
  - Базовый аккордеон с текстовым содержимым
  - Аккордеон с HTML содержимым
  - Множественное открытие элементов
  - Получение значений на фронтенде
  - Кастомные иконки

## [2.4.1] - 2025-11-23

### Исправлено
- **Repeater field**: Полностью исправлена работа добавления элементов
  - PHP: Правильное формирование атрибута `name` для подполей: `parent_name[index][subfield_id]`
  - PHP: Исправлено дублирование шаблонов - шаблон рендерится всегда один раз
  - JS: Исправлен селектор - теперь ищет repeater контейнер внутри field wrapper
  - JS: Корректное обновление индексов в `name` и `id` при клонировании элементов
  - JS: Добавлена очистка значений в новых элементах
  - JS: Обновление атрибутов `for` в label при клонировании
  - JS: Элементы добавляются перед шаблоном, а не в конец контейнера
- **Color Picker**: Исправлена JavaScript ошибка "Cannot read properties of undefined (reading 'on')"
  - Добавлена проверка на существование `wpColorPicker('instance').picker` перед вызовом событий
- Добавлен тестовый файл `test-repeater.php` для проверки функциональности

## [2.1.0] - 2025-11-22

### Добавлено
- Полная документация для всех типов полей на странице примеров
- Файл `field-data.php` с аргументами и расширенными примерами для 19 типов полей
- Раскрывающиеся секции на странице примеров:
  - 📋 Список аргументов (таблица с параметрами)
  - 💻 Базовый пример (простой код)
  - 🚀 Расширенные примеры (реальные кейсы использования)
- Подробные описания (`example_desc`) для каждого поля
- Файл `placeholder.svg` для примеров Image Select
---

## [2.0.0] - 2025

### Добавлено
- Полная переработка класса WP_Field
- 25+ типов полей:
  - Базовые (9): text, password, email, url, tel, number, range, hidden, textarea
  - Выборные (5): select, multiselect, radio, checkbox, checkbox_group
  - Продвинутые (9): editor, media, image, file, gallery, color, date, time, datetime
  - Простые v2.1 (9): switcher, spinner, button_set, subheading, content, fieldset, group, icon, upload
  - Средней сложности v2.2 (10): accordion, tabbed, typography, spacing, dimensions, border, background, link_color, color_group, date_range
  - Высокой сложности v2.3 (8): code_editor, image_select, map, slider, sorter, backup, import, export

### Особенности
- Система зависимостей (12 операторов, AND/OR логика)
- Поддержка всех типов хранилищ (post, options, term, user, comment)
- Встроенные WordPress компоненты (wp_editor, wp-color-picker, wp.media)
- Без внешних зависимостей
- Полная обратная совместимость с v1.x

### Тестирование
- PHPUnit тесты
- Pest тесты
- Проверка синтаксиса PHP

---

## Формат

Формат основан на [Keep a Changelog](https://keepachangelog.com/ru/1.0.0/),
и этот проект придерживается [Semantic Versioning](https://semver.org/lang/ru/).

### Типы изменений
- **Добавлено** - для новых функций
- **Изменено** - для изменений в существующей функциональности
- **Устарело** - для функций, которые скоро будут удалены
- **Удалено** - для удалённых функций
- **Исправлено** - для исправления ошибок
- **Безопасность** - для обновлений безопасности
