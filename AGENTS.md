# AGENTS.md — WP_Field

## Цель

Этот файл нужен, чтобы **не терять контекст между диалогами** внутри `lib/wp-field`.

## Обязательное чтение перед любыми изменениями

1. `AGENTS.md`
2. `tree.md`
3. `API.md`
4. `docs/wp-field/analysis.md`
5. `docs/wp-field/plan.md`
6. `docs/wp-field/decision-log.md`

Если задача про конкретный слой, затем читать код этого слоя:
- runtime/legacy → `WP_Field.php`, `assets/js/wp-field.js`
- fluent API → `src/Field/*`
- containers/storage → `src/Container/*`, `src/Storage/*`
- UI shell/wizard → `src/UI/*`, `assets/src/*`, `assets/css/admin-shell.css`, `assets/css/wizard.css`

---

## Источник истины

При конфликте использовать такой приоритет:
1. код (`WP_Field.php`, `src/`, `assets/`)
2. `docs/wp-field/decision-log.md`
3. `docs/wp-field/analysis.md`
4. `docs/wp-field/plan.md`
5. `README*`, `CHANGELOG.md`, `examples/*`

Важно:
- README, CHANGELOG и demo-файлы здесь **полезны, но не всегда точны**;
- не принимать маркетинговые формулировки за фактическую спецификацию.

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

## Обязательный workflow для сессий

### Перед работой
- проверить `docs/wp-field/analysis.md` на актуальные риски;
- проверить `docs/wp-field/plan.md`, чтобы не делать работу вне приоритетов;
- проверить `docs/wp-field/decision-log.md`, чтобы не ломать уже принятое решение.

### Во время работы
- если найдено новое системное расхождение — обновить `analysis.md`;
- если меняется порядок задач — обновить `plan.md`;
- если принято архитектурное решение — добавить запись в `decision-log.md`.

### После работы
Нельзя завершать значимую сессию без обновления хотя бы одного из файлов:
- `docs/wp-field/analysis.md`
- `docs/wp-field/plan.md`
- `docs/wp-field/decision-log.md`
- `AGENTS.md`

---

## Правила изменений

1. **Не ломать legacy API без отдельного зафиксированного решения.**
2. **Не расширять fluent API вслепую**, пока не закрыты критичные интеграционные баги.
3. **Не верить demo-коду на слово** — всегда сверять с `src/Field/Field.php` и реальными классами.
4. **Если меняется supported behavior, обновлять документацию сразу.**
5. Документацию хранить в `docs/`, исключение — этот `AGENTS.md`.

---

## Что сейчас считается приоритетом

1. Зафиксировать реальный supported matrix.
2. Починить `LegacyWrapperField` для conditional logic.
3. Определить и стабилизировать runtime-стратегию для `RepeaterField` / `FlexibleContentField`.
4. Привести `UIManager` к реальному pipeline ассетов.
5. Только потом расширять native field API.

---

## Минимальный чеклист перед merge / завершением сессии

- [ ] Решение сверено с `decision-log.md`
- [ ] Приоритет сверён с `plan.md`
- [ ] Новые риски/факты задокументированы в `analysis.md`
- [ ] Если менялся API/поведение — обновлены docs и examples при необходимости

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
