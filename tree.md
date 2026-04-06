# Дерево кодовой базы

```text
.
├── .gitignore
├── .pint.json
├── AGENTS.md
├── CHANGELOG.md
├── composer.json
├── composer.lock
├── example.php — демонстрационный файл подключения библиотеки: показывает базовое создание поля, загрузку автолоадера и стартовую инициализацию
├── logo.svg
├── package.json
├── package-lock.json
├── pest.php — bootstrap-конфиг Pest для запуска тестов, подключения окружения WordPress и общих хелперов
├── phpstan.neon
├── phpunit.xml
├── placeholder.svg
├── README.md
├── README.ru.md
├── vite.admin-shell.config.js
├── vite.components.config.js — Vite build для examples/components/assets/ (sidebar search, scroll)
├── vite.config.js
├── vite.wizard.config.js
├── WP_Field.php — точка входа библиотеки: подключает автозагрузку, содержит legacy-совместимый класс WP_Field и мост к старому API
├── assets
│   ├── css
│   │   ├── admin-shell.css
│   │   └── wizard.css
│   ├── dist
│   │   ├── admin-shell.js
│   │   ├── flexible-content.js
│   │   ├── repeater.js
│   │   └── wizard.js
│   ├── js
│   │   └── wp-field-integrations.js — jQuery glue для WP компонентов (wp-color-picker, sliders)
│   └── src
│       ├── admin-shell.jsx — entry point React-оболочки админки: собирает shell-интерфейс, навигацию и общие layout-компоненты
│       ├── flexible-content.jsx — entry point для UI flexible content: управляет списком блоков, их добавлением, удалением и порядком
│       ├── repeater.jsx — entry point для UI repeater: рендерит повторяющиеся группы полей и синхронизирует их состояние
│       ├── wizard.jsx — entry point React-мастера настройки: связывает шаги, прогресс и действия пользователя
│       ├── wp-field-components.jsx — source entrypoint компонент-страницы; собирается в examples/components/assets/
│       └── components
│           ├── Alert.jsx — компактный компонент уведомления с выводом текста, типа сообщения и визуального статуса
│           ├── HeaderBar.jsx — верхняя панель админ-интерфейса с заголовком, контекстом страницы и служебными действиями
│           ├── SidebarNav.jsx — боковая навигация по разделам UI, поддерживает список пунктов и активное состояние
│           ├── TabBar.jsx — горизонтальная панель вкладок для переключения между секциями или подэкранами
│           └── WizardProgress.jsx — индикатор прогресса мастера, отображает текущий шаг и общее число этапов
├── examples
│   ├── components
│   │   ├── assets
│   │   │   ├── wp-field-components.css — стили страницы WP_Field Components
│   │   │   └── wp-field-components.js  — JS-бандл (sidebar search, scroll tracking)
│   │   └── index.php — WP_Field Components page (slug: wp-field-components)
│   ├── ui-demo
│   │   └── index.php — Flux UI Admin Shell showcase (slug: wp-field-ui-demo)
│   ├── modern-api-examples.php — набор примеров современного API: fluent-синтаксис, контейнеры и создание полей
│   └── shared-catalog.php — единый каталог demo-полей (single source of truth) для components и ui-demo
├── vanilla
│   ├── WP_Field.php — legacy WP_Field class (Vanilla runtime)
│   ├── bootstrap.php — enqueue vanilla assets (jQuery, wp-color-picker, wp-field.css/js)
│   ├── example.php — WP_Field Vanilla documentation page (slug: wp-field-examples)
│   └── assets
│       ├── css
│       │   ├── wp-field.css — Vanilla WP_Field styles
│       │   └── wp-field-examples-vanilla.css — Vanilla docs page styles
│       ├── js
│       │   └── wp-field.js — Vanilla WP_Field JS
│       └── scss
│           └── wp-field-examples-vanilla.scss — SCSS source для Vanilla docs styles
├── lang
│   ├── wp-field-ru_RU.l10n.php — PHP-кэш локализации WordPress с быстрым загрузочным массивом переводов
│   ├── wp-field-ru_RU.mo
│   ├── wp-field-ru_RU.po
│   └── wp-field.pot
├── src
│   ├── Conditional
│   │   └── ConditionalLogic.php — вычисляет правила условного показа полей: операторы, группы условий и итоговую проверку
│   ├── Container
│   │   ├── AbstractContainer.php — базовый класс контейнеров с общей логикой регистрации, конфигурации и рендера
│   │   ├── ContainerInterface.php — контракт для всех контейнеров: единый набор методов и обязательных точек интеграции
│   │   ├── MetaboxContainer.php — контейнер для WordPress metabox-ов с привязкой к post types и полям
│   │   ├── SettingsContainer.php — контейнер страницы настроек, связывает поля, секции и сохранение опций
│   │   ├── TaxonomyContainer.php — контейнер для форм редактирования терминов таксономий
│   │   └── UserContainer.php — контейнер для профиля пользователя и user meta полей
│   ├── Field
│   │   ├── AbstractField.php — базовая реализация поля: хранение конфигурации, значений, атрибутов и общего рендера
│   │   ├── Field.php — фабрика и точка создания полей, предоставляет fluent API и статические хелперы
│   │   ├── FieldInterface.php — контракт поля с общим набором методов для настройки и вывода
│   │   └── Types
│   │       ├── ChoiceField.php — поле выбора с вариантами, активным значением и поддержкой списков
│   │       ├── FieldsetField.php — группирующее поле для объединения нескольких дочерних полей в один блок
│   │       ├── FlexibleContentField.php — flexible content: набор блоков с разными типами и настраиваемой структурой
│   │       ├── LegacyAdapterBridge.php — мост между новым API и legacy-реализацией, помогает плавно поддерживать старые вызовы
│   │       ├── LegacyWrapperField.php — обёртка legacy-поля, адаптирует старые структуры к новому интерфейсу
│   │       ├── MediaField.php — поле выбора медиафайла или изображения с привязкой к WordPress media modal
│   │       ├── RadioField.php — radio-поле для выбора одного значения из набора
│   │       ├── RepeaterField.php — repeater-поле для повторяющихся строк или групп с динамическим количеством элементов
│   │       └── TextField.php — текстовое поле для одиночного ввода, базовый строительный блок форм
│   ├── Legacy
│   │   └── LegacyAdapter.php — адаптер старого API, обеспечивает совместимость и маршрутизацию в современную реализацию
│   ├── Storage
│   │   ├── CustomTableStorage.php — хранилище значений в пользовательской таблице БД
│   │   ├── OptionStorage.php — хранилище в wp_options для глобальных настроек
│   │   ├── PostMetaStorage.php — хранилище в post meta для значений, привязанных к записи
│   │   ├── StorageInterface.php — контракт для всех стратегий хранения значений поля
│   │   ├── TermMetaStorage.php — хранилище в term meta для категорий, меток и других терминов
│   │   └── UserMetaStorage.php — хранилище в user meta для пользовательских профилей
│   ├── Traits
│   │   ├── HasAttributes.php — трейt для работы с HTML-атрибутами и пользовательскими data-* значениями
│   │   ├── HasConditionals.php — трейt для подключения и управления условной логикой
│   │   └── HasValidation.php — трейt для правил валидации, ошибок и ограничений ввода
│   └── UI
│       ├── AdminShell.php — основной сборщик админ-оболочки: подключает навигацию, контент и визуальный каркас
│       ├── AdminShellConfig.php — объект конфигурации для настройки админ-оболочки и её поведения
│       ├── Alert.php — модель или компонент уведомления с типом, текстом и параметрами отображения
│       ├── NavItem.php — объект пункта навигации для меню и боковых списков
│       ├── UIManager.php — менеджер UI-слоя: координирует рендер, интеграции и состояние интерфейса
│       ├── Wizard.php — реализация мастера настройки с шагами, состоянием и переходами
│       └── WizardConfig.php — конфиг мастера: шаги, заголовки, опции и поведение
└── tests
    ├── bootstrap.php — bootstrap тестовой среды: подключает WordPress-окружение и общие фикстуры
    ├── Concerns
    │   └── InteractsWithWordPress.php — набор вспомогательных методов для тестов, работающих с WordPress API
    ├── Feature
    │   ├── ChoiceFieldsTest.php — feature-тесты для полей выбора, их рендера и сохранения состояния
    │   ├── CompositeFieldsTest.php — feature-тесты составных полей и взаимодействия вложенных элементов
    │   ├── DependencyTest.php — проверка условных зависимостей между полями и реакций на изменение значений
    │   └── FieldRenderingTest.php — проверка HTML-рендера полей и базовых сценариев отображения
    ├── README.md
    ├── run-tests.sh
    ├── test-wp-field-v2.4.php — legacy smoke-тесты старой версии библиотеки с проверкой обратной совместимости
    ├── test-wp-field.php — старый smoke-тест для базовой функциональности WP_Field
    └── Unit
        ├── FieldInitializationTest.php — unit-тест инициализации фабрики и базовых алиасов полей
        ├── SimpleFieldsTest.php — набор unit-тестов для простых полей и их базовых свойств
        ├── SimpleFieldsTestFixed.php — исправленная версия тестов простых полей для стабильного прогона
        └── StorageTypesTest.php — unit-тесты стратегий хранения и выбора правильного storage по типу
```
