---
name: planning_supervisor
description: Orchestrate a lightweight plan review loop (Planning → Critic → Planning → Verifier) with iteration budget and optional hooks.
---

# Planning Supervisor Skill (MVP)

## Purpose

Coordinate a fixed planning loop that produces a single final `plan.md` artifact and (optionally) supporting review artifacts.

## Inputs

- Task intake (goal/context/constraints/acceptance criteria)
- `debug_mode`: `on|off` (default: `off`)
- `iteration_budget`: integer (default: `2`)

## Outputs

- `plan.md` (final `PlanArtifact`)
- `critique.md` (only if `debug_mode=on`)
- `verify.md` (only if `debug_mode=on`)

## Orchestration Contract

You MUST run the following loop in order:

1. Intake normalization
2. Generate `PlanArtifact v0` (delegate to `planning`)
3. Generate `CritiqueArtifact` (delegate to `plan_critic`)
4. Revise to `PlanArtifact v1` (delegate to `planning` with critique)
5. Run `Plan-lint` and produce `VerificationReport` (delegate to `plan_verifier`)

Stop conditions:

- Stop if:
  - no `Blocker` and no `High` in critique
  - verifier returns `plan_lint = passed`
  - iterations ≤ `iteration_budget`
- If critique has `Blocker` or `High` OR verifier returns `failed`:
  - attempt one more revision (within budget)
  - if still failing or budget exceeded: output 1–5 clarifying questions (only those that block correctness)

## Hook Points (MVP: define, do not execute)

You MAY mark escalation requests in the plan’s `Risks` section, but MUST NOT run extra critics in MVP.

Hooks (future):

- `SecurityCritic` (auth/permissions/secrets/external IO/file upload)
- `DataMigrationsCritic` (schema/migrations/mass updates/backups)
- `TestingCritic` (critical layer changes without tests)
- `DeployOpsCritic` (env/CI/CD/release flags)
