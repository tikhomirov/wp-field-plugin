# Planning Supervisor — как пользоваться

Этот репозиторий содержит MVP-систему для повышения качества планов через фиксированный цикл:

`Planning → Critic → Planning → Verifier`

Система описана через:

- `skill`-файлы в `.agents/skills/*/SKILL.md`
- workflow в `.windsurf/workflows/planning-supervisor.md`

## 1) Быстрый старт

1. Создай файл `task.md` рядом с задачей (или в удобной тебе папке), со входными данными.
2. Запусти workflow **Planning Supervisor (MVP)**.
3. Получи на выходе `plan.md` (а в `debug_mode` ещё `critique.md` и `verify.md`).

## 2) Формат входа: `task.md`

Формат свободный, но обязательно укажи:

- **Цель**: что нужно сделать
- **Контекст**: где в коде/проекте это живёт, что важно не сломать
- **Ограничения**: что можно/нельзя делать
- **Критерии приёмки**: как понять, что готово

Опционально можно задать флаги прямо текстом:

- `debug_mode: on` (по умолчанию `off`)
- `iteration_budget: 2` (по умолчанию `2`)

Пример `task.md`:

```md
Цель: починить баг в сохранении мета-поля.

Контекст:
- Плагин wp-field
- Важно не ломать legacy API

Ограничения:
- Нельзя менять публичный API без явного решения
- Нельзя добавлять новые зависимости

Критерии приёмки:
- Тест воспроизводит баг и проходит после фикса
- Нет регрессий в существующих тестах

debug_mode: on
iteration_budget: 2
```

## 3) Выходные артефакты

### `plan.md` (всегда)

`plan.md` — финальный план (PlanArtifact).

Требования:

- Первая строка: `Protocol-Version: 1`
- Обязательные секции (точные заголовки):
  - `## Constraints`
  - `## Assumptions`
  - `## Phases`
  - `## Tests/Validation`
  - `## Rollback`
  - `## Risks`
  - `## Changelog`

Ключевые правила:

- В `## Phases` **не больше 5 фаз**
- Шаги — атомарные (один логический акт)
- Предположения не должны быть “тихими”: каждое — либо факт, либо вопрос/проверка
- Для ключевых изменений должны быть явные проверки/тесты
- Должен быть реалистичный `Rollback`

### `critique.md` (только при `debug_mode: on`)

Это CritiqueArtifact.

Требования:

- Первая строка: `Protocol-Version: 1`
- Секции:
  - `## Findings`
  - `## Clarifying Questions`
  - `## Verification Questions`
  - `## Stop Signal`

`Stop Signal` считается `true`, только если нет замечаний `Blocker` и `High`.

### `verify.md` (только при `debug_mode: on`)

Это VerificationReport.

Требования:

- Первая строка: `Protocol-Version: 1`
- Секции:
  - `## Plan Lint`
  - `## Release Ready`

`Plan Lint` = `passed|failed`.
`Release Ready` = `true` только если lint прошёл.

## 4) Как работает цикл (MVP)

1. `planning` генерирует `plan.md` v0.
2. `plan_critic` пишет `critique.md`.
3. `planning` ревизит план (v1), обновляет `## Changelog`.
4. `plan_verifier` прогоняет Plan-lint и пишет `verify.md`.

Стоп-условие:

- `Stop Signal = true`
- `Plan Lint = passed`
- итераций ≤ `iteration_budget`

Если критика содержит `Blocker/High` или lint упал — выполняется ещё одна ревизия в рамках бюджета.

## 5) Где смотреть “источник истины”

- `planning` — `.agents/skills/planning/SKILL.md`
- `plan_critic` — `.agents/skills/plan_critic/SKILL.md`
- `plan_verifier` — `.agents/skills/plan_verifier/SKILL.md`
- workflow — `.windsurf/workflows/planning-supervisor.md`

## 6) Примечание про hooks

MVP **не запускает** доменных критиков (Security/Data/Testing/Deploy), но предусматривает точки расширения.
Если задача попадает под триггер, это должно отражаться в `## Risks` и/или проверках в `## Tests/Validation`.
