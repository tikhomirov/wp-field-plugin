---
auto_execution_mode: 0
description: Planning Supervisor (MVP) — Planning → Critic → Planning → Verifier
---

Run a lightweight planning review loop and persist Markdown artifacts.

## Inputs

- `task.md` (required): goal/context/constraints/acceptance criteria.
- Optional flags inside `task.md` (plain text):
  - `debug_mode: on|off` (default: off)
  - `iteration_budget: 2` (default: 2)

## Outputs

- `plan.md` (final)
- `critique.md` (only if debug_mode=on)
- `verify.md` (only if debug_mode=on)

## Protocol

All artifacts must begin with:

`Protocol-Version: 1`

### `plan.md` required sections

- `## Constraints`
- `## Assumptions`
- `## Phases`
- `## Tests/Validation`
- `## Rollback`
- `## Risks`
- `## Changelog`

### `critique.md` required sections

- `## Findings`
- `## Clarifying Questions`
- `## Verification Questions`
- `## Stop Signal`

### `verify.md` required sections

- `## Plan Lint`
- `## Release Ready`

## Execution (Supervisor steps)

1. Read `task.md` and normalize:
   - goal
   - context
   - constraints (allowed/forbidden)
   - acceptance criteria
   - debug_mode, iteration_budget
2. Ask `planning` to produce `plan.md` v0.
3. Ask `plan_critic` to produce `critique.md`.
4. Ask `planning` to revise into `plan.md` v1 using critique; update `## Changelog`.
5. Ask `plan_verifier` to produce `verify.md`.
6. Stop if:
   - `Stop Signal = true`
   - `Plan Lint = passed`
   - iterations ≤ budget
7. If `Stop Signal = false` or `Plan Lint = failed`:
   - perform one more revise + verify (within budget)
   - if still failing: output 1–5 blocking clarifying questions.

## Notes

- Follow the project policy in `AGENTS.md` when planning implementation and validation.
- If the task touches JS/JSX/SCSS, the validation section should include `npm run lint` and the repo-level `./.agents/skills/qa-gate/scripts/verify.sh`.
- MVP defines hook points (Security/Data/Testing/Deploy critics) but does not execute them.
- Default behavior (vibe): show only final `plan.md` to the user.
