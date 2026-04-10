# AGENTS.md — WP_Field

## Кратко о проекте
- **Что это за репозиторий:** библиотека WordPress для создания мета-полей с fluent API. Поддерживает legacy-режим и modern migration layer.
- **Основные языки:** PHP 8.3+, JavaScript/React для AdminShell/Wizard.
- **Стиль архитектуры:** монолитная библиотека с чётким разделением legacy- и modern-слоёв.

## Как запускать локально
- **Установка зависимостей:** `composer install && npm install`
- **Сборка JS:** `npm run build`
- **JS/SCSS quality gate:** `npm run lint`, `npm run lint:fix`, `npm run format`, `npm run format:check`
- **Тесты:** `composer test`, `composer test:unit`, `composer test:feature`
- **Проверка стиля и анализа:** `composer lint:check`, `composer analyse`
- **Полная проверка всего:** `./.agents/skills/qa-gate/scripts/verify.sh`

## Важные точки репозитория
- **Точка входа legacy:** `WP_Field.php`
- **Современный API:** `src/Field/Field.php`
- **Типы полей:** `src/Field/Types/`
- **Контейнеры WordPress:** `src/Container/`
- **Хранилища значений:** `src/Storage/`
- **UI-слой:** `src/UI/`
- **Демо и примеры:** `examples/`
- **Тесты:** `tests/`
- **Конфигурация:** `composer.json`, `package.json`

## Правила работы
- Держать изменения небольшими и локальными.
- Не добавлять новые зависимости без согласования.
- Если поведение неясно — сначала задать 1–5 уточняющих вопросов.
- Всегда сохранять код в рабочем состоянии.
- Не ломать legacy API без отдельного решения.
- Не расширять fluent API без необходимости, пока не закрыты критичные интеграционные баги.
- Не доверять демо-коду вслепую — сверять с `src/Field/Field.php` и реальными классами.
- При изменении поведения сразу обновлять документацию.
- Для изменений в JS/JSX/SCSS обязательно прогонять frontend quality gate через `npm run lint`; для автоисправлений использовать `npm run lint:fix` и `npm run format`.
- Общий источник правды по качеству кода для всех агентов (Windsurf, Codex, Claude Code, PI) — `AGENTS.md`, а технический enforcement — `./.agents/skills/qa-gate/scripts/verify.sh`.
- Вести диалог и отвечать пользователю на русском языке.
- Сам файл `AGENTS.md` тоже вести на русском языке.

## Минимальное определение готовности
- `./.agents/skills/qa-gate/scripts/verify.sh` проходит успешно без пропуска обязательных шагов.
- Для изменений в JS/JSX/SCSS успешно проходит `npm run lint`.
- Поведение соответствует задаче и ожиданиям проекта.
- Изменения кратко отражены в документации задачи, если она есть.

---

## Ключевые сведения о проекте

### Источники правды
- `README*`, `CHANGELOG.md`, `examples/*` полезны, но не всегда актуальны.
- `tree.md` — актуальное дерево структуры кодовой базы.
- `API.md` — сводка публичного API и контрактов библиотеки.

### Важные детали
- `WP_Field.php` — основной legacy baseline и runtime.
- `src/` — основной modern API слоя v4, работающий рядом с legacy compatibility layer.
- Fluent API покрывает только часть типов.
- `RepeaterField`, `FlexibleContentField` и `UIManager` требуют осторожности: там есть связка PHP-рендера, React-mount и ассетов.
- `AdminShell` и `Wizard` выглядят как отдельный reusable UI toolkit.

### Документация
- Документацию хранить в `docs/`, исключение — этот `AGENTS.md`.
- Для фронтенд-части этой библиотеки держать отдельные скиллы в `.claude/skills/`: один для legacy/vanilla слоя, второй для modern/React слоя.
