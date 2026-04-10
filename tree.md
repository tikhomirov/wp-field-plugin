# Дерево кодовой базы

```text
.
├── .agents
│   └── skills
│       └── qa-gate
│           ├── SKILL.md — описание skill для финальной QA-проверки задачи
│           └── scripts
│               └── verify.sh — единый quality gate проекта: PHP syntax, Pint, PHPStan, Pest, npm lint
├── .claude
├── .github
├── .pi
├── .windsurf
│   └── workflows
│       ├── planning-supervisor.md — planning workflow с опорой на AGENTS.md и verify.sh
│       └── review.md — review workflow с опорой на AGENTS.md и verify.sh
├── .gitignore
├── .pint.json
├── .prettierignore — исключения Prettier для build/vendor/generated артефактов
├── .prettierrc.json — базовая конфигурация Prettier для JS/JSX/SCSS
├── AGENTS.md — проектные правила для всех агентов, команды и DoD
├── API.md — сводка публичного API и контрактов библиотеки
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
│   │   └── wp-field-integrations.js — jQuery glue для WordPress UI integrations: media, color picker, editor и related hooks
│   └── src
│       ├── admin-shell.jsx — entry point React-оболочки админки
│       ├── flexible-content.jsx — entry point UI flexible content
│       ├── repeater.jsx — entry point UI repeater
│       ├── wizard.jsx — entry point React-мастера
│       ├── wp-field-components.jsx — source entrypoint страницы components demo
│       └── components
│           ├── Alert.jsx — компактный UI alert-компонент
│           ├── HeaderBar.jsx — верхняя панель shell-интерфейса
│           ├── SidebarNav.jsx — боковая навигация shell-интерфейса
│           ├── TabBar.jsx — панель вкладок shell-интерфейса
│           └── WizardProgress.jsx — индикатор шагов мастера
├── CHANGELOG.md
├── composer.json — PHP runtime/test/analysis/lint scripts
├── composer.lock
├── docs
├── eslint.config.js — flat ESLint config для React/Vite/legacy JS частей проекта
├── examples
│   ├── components
│   │   ├── assets
│   │   │   ├── wp-field-components.css — стили страницы components demo
│   │   │   └── wp-field-components.js — JS bundle страницы components demo
│   │   └── index.php — страница WP_Field Components (slug: wp-field-components)
│   ├── modern-api-examples.php — примеры современного fluent API
│   ├── shared-catalog.php — единый каталог demo-полей для examples/components и ui-demo
│   └── ui-demo
│       └── index.php — showcase Admin Shell / UI demo
├── lang
│   ├── wp-field-ru_RU.l10n.php
│   ├── wp-field-ru_RU.mo
│   ├── wp-field-ru_RU.po
│   └── wp-field.pot
├── logo.svg
├── node_modules
├── package-lock.json
├── package.json — frontend build/lint/format scripts и npm-зависимости проекта
├── pest.php — bootstrap-конфиг Pest
├── phpstan.neon
├── phpunit.xml
├── placeholder.svg
├── README.md
├── README.ru.md
├── rector.php
├── sessions
├── src
│   ├── Conditional
│   │   └── ConditionalLogic.php — вычисление условной логики полей
│   ├── Container
│   │   ├── AbstractContainer.php — базовый контейнер WordPress-интеграций
│   │   ├── ContainerInterface.php — контракт контейнеров
│   │   ├── MetaboxContainer.php — контейнер metabox-ов
│   │   ├── SettingsContainer.php — контейнер settings pages
│   │   ├── TaxonomyContainer.php — контейнер term meta форм
│   │   └── UserContainer.php — контейнер user meta форм
│   ├── Field
│   │   ├── AbstractField.php — базовая реализация поля
│   │   ├── Field.php — фабрика fluent API и статические хелперы
│   │   ├── FieldInterface.php — контракт поля
│   │   └── Types
│   │       ├── AccordionField.php — accordion layout field
│   │       ├── BackgroundField.php — background settings field
│   │       ├── BackupField.php — backup import/export field
│   │       ├── BorderField.php — border settings field
│   │       ├── ButtonSetField.php — button set field
│   │       ├── CheckboxField.php — checkbox field
│   │       ├── CheckboxGroupField.php — checkbox group field
│   │       ├── ChoiceField.php — общий выбор из набора вариантов
│   │       ├── CodeEditorField.php — code editor field
│   │       ├── ColorField.php — color field
│   │       ├── ColorGroupField.php — grouped color settings field
│   │       ├── Concerns
│   │       ├── ContentField.php — content/static markup field
│   │       ├── DimensionsField.php — dimensions settings field
│   │       ├── EditorField.php — classic editor field
│   │       ├── FieldsetField.php — fieldset field
│   │       ├── FileField.php — file picker field
│   │       ├── FlexibleContentField.php — flexible content field
│   │       ├── GalleryField.php — gallery field
│   │       ├── GroupField.php — grouped child fields
│   │       ├── HeadingField.php — heading field
│   │       ├── IconField.php — icon picker field
│   │       ├── ImageField.php — image picker field
│   │       ├── ImagePickerField.php — image picker field variant
│   │       ├── ImageSelectField.php — image select field
│   │       ├── InputField.php — базовый input-derived field
│   │       ├── LegacyAdapterBridge.php — мост нового API к legacy runtime
│   │       ├── LegacyWrapperField.php — адаптер legacy custom fields
│   │       ├── LinkColorField.php — link color settings field
│   │       ├── LinkField.php — link field
│   │       ├── MapField.php — map field with coordinates/provider config
│   │       ├── MediaField.php — media picker field
│   │       ├── NoticeField.php — notice field
│   │       ├── PaletteField.php — palette choice field
│   │       ├── RadioField.php — radio field
│   │       ├── RepeaterField.php — repeater field
│   │       ├── SelectField.php — select field
│   │       ├── SliderField.php — slider field
│   │       ├── SortableField.php — sortable list field
│   │       ├── SorterField.php — dual-column sorter field
│   │       ├── SpacingField.php — spacing settings field
│   │       ├── SpinnerField.php — spinner field
│   │       ├── SubheadingField.php — subheading field
│   │       ├── SwitcherField.php — switcher/toggle field
│   │       ├── TabbedField.php — tabbed layout field
│   │       ├── TextareaField.php — textarea field
│   │       ├── TextField.php — text field
│   │       └── TypographyField.php — typography settings field
│   ├── Legacy
│   │   └── LegacyAdapter.php — маршрутизация и совместимость legacy API
│   ├── Storage
│   │   ├── CustomTableStorage.php — custom table storage strategy
│   │   ├── OptionStorage.php — wp_options storage strategy
│   │   ├── PostMetaStorage.php — post meta storage strategy
│   │   ├── StorageInterface.php — контракт storage strategies
│   │   ├── TermMetaStorage.php — term meta storage strategy
│   │   └── UserMetaStorage.php — user meta storage strategy
│   ├── Traits
│   │   ├── HasAttributes.php — работа с HTML/data-* атрибутами
│   │   ├── HasConditionals.php — подключение условной логики
│   │   └── HasValidation.php — базовая валидация и ошибки
│   └── UI
│       ├── AdminShell.php — сборщик admin shell
│       ├── AdminShellConfig.php — конфиг admin shell
│       ├── Alert.php — модель/DTO уведомлений UI-слоя
│       ├── NavItem.php — элемент навигации UI-слоя
│       ├── UIManager.php — менеджер UI-ассетов и режимов
│       ├── Wizard.php — реализация мастера настройки
│       └── WizardConfig.php — конфиг шагов и поведения мастера
├── tests
│   ├── bootstrap.php — bootstrap тестовой среды
│   ├── Concerns
│   │   └── InteractsWithWordPress.php — helpers для WordPress test doubles
│   ├── Feature
│   │   ├── BootstrapFilesTest.php — проверки plugin/bootstrap/legacy hook wiring
│   │   ├── ChoiceFieldsTest.php — feature-тесты choice fields
│   │   ├── CompositeFieldsTest.php — feature-тесты composite fields
│   │   ├── DependencyTest.php — тесты conditional dependencies
│   │   ├── FieldRenderingTest.php — тесты HTML-рендера полей
│   │   ├── LegacyCustomFallbackTest.php — тесты fallback для legacy custom fields
│   │   └── LegacyWrapperConditionsTest.php — тесты адаптации legacy conditions
│   ├── README.md
│   ├── run-tests.sh
│   ├── test-wp-field-v2.4.php — legacy smoke tests старой версии
│   ├── test-wp-field.php — legacy smoke tests базовой функциональности
│   └── Unit
│       ├── Field
│       │   ├── BridgeCutoverAuditTest.php — аудит cutover с legacy bridge на native classes
│       │   ├── FieldTest.php — unit-тесты fluent factory и field contracts
│       │   ├── SettingsObjectFieldsNativeRenderTest.php — рендер settings object fields
│       │   ├── SimpleBridgeTypesNativeRenderTest.php — native render simple bridge types
│       │   └── WordPressIntegrationFieldsNativeRenderTest.php — native render WP integration fields
│       ├── FieldInitializationTest.php — инициализация factory и alias map
│       ├── SimpleFieldsTest.php — unit-тесты simple fields
│       ├── Storage
│       │   └── PostMetaStorageTest.php — unit-тесты post meta storage
│       ├── StorageTypesTest.php — выбор storage type по контексту
│       └── UI
│           └── UIComponentsTest.php — unit-тесты AdminShell/Wizard/UIManager
├── tree.md — актуальное дерево структуры кодовой базы
├── vanilla
│   ├── assets
│   │   ├── css
│   │   │   ├── wp-field.css — стили legacy runtime
│   │   │   └── wp-field-examples-vanilla.css — стили vanilla docs/demo страницы
│   │   ├── js
│   │   │   └── wp-field.js — legacy JS runtime для vanilla UI и enhancement-хуков
│   │   └── scss
│   │       └── wp-field-examples-vanilla.scss — SCSS source vanilla docs styles
│   ├── bootstrap.php — enqueue legacy assets и fallback integrations
│   ├── example.php — legacy examples page
│   └── WP_Field.php — legacy baseline класса WP_Field
├── vendor
├── vite.admin-shell.config.js — сборка admin-shell bundle
├── vite.components.config.js — сборка examples/components/assets bundle
├── vite.config.js — сборка repeater/flexible-content bundles
├── vite.wizard.config.js — сборка wizard bundle
├── wp-field.php — canonical plugin bootstrap
└── WP_Field.php — legacy-compatible entrypoint и bridge к modern API
```
