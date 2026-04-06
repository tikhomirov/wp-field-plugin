# AGENTS.md — WP_Field

## Цель

Этот файл нужен, чтобы **не терять контекст между диалогами** внутри `lib/wp-field`.

## Обязательное чтение перед любыми изменениями

1. `AGENTS.md`
2. `tree.md`
3. `API.md`

---

## Источник истины

`README*`, `CHANGELOG.md`, `examples/*`

Важно:
- README, CHANGELOG и demo-файлы здесь **полезны, но не всегда точны**;

---

## Ключевые факты о проекте

- `WP_Field.php` — главный legacy baseline и основной runtime.
- `tree.md` — актуальное дерево структуры кодовой базы; используем его как быстрый обзор файлов и слоёв.
- `API.md` — сводка публичного API и контрактов библиотеки; это главный документ для интеграции.
- `src/` — не полная замена legacy, а промежуточный migration layer.
- Native fluent API покрывает только часть типов.
- `RepeaterField` / `FlexibleContentField` и `UIManager` требуют осторожности: там есть несостыковки между PHP render, React mount и ассетами.
- `AdminShell` и `Wizard` выглядят как отдельный reusable UI toolkit.

---

## Правила изменений

1. **Не ломать legacy API без отдельного зафиксированного решения.**
2. **Не расширять fluent API вслепую**, пока не закрыты критичные интеграционные баги.
3. **Не верить demo-коду на слово** — всегда сверять с `src/Field/Field.php` и реальными классами.
4. **Если меняется supported behavior, обновлять документацию сразу.**
5. Документацию хранить в `docs/`, исключение — этот `AGENTS.md`.

---

## Команды проекта

### PHP
- `composer test`
- `composer test:unit`
- `composer test:feature`
- `composer analyse`
- `composer lint:check`

### Frontend
- `npm run build`
- `npm run build:shell`
- `npm run build:wizard`

Запускать только те команды, которые реально нужны задаче.
