---
name: plan_verifier
description: "Deterministically lint a PlanArtifact against invariants and produce a VerificationReport (Protocol-Version: 1)."
---

# Plan Verifier Skill

## Output Format (VerificationReport)

The output MUST be a Markdown document:

- First line: `Protocol-Version: 1`
- Must contain these headings (exact text):
  - `## Plan Lint`
  - `## Release Ready`

## Plan-lint Rules (Deterministic)

Fail if any rule is violated:

- Phases: ≤ 5 phases.
- Atomicity: steps are single logical actions (no multi-action steps).
- Testability: `## Tests/Validation` exists and includes checks for key changes.
- Rollback: `## Rollback` exists and is plausible for the change type.
- Assumptions: no silent assumptions; each is fact or has a question/verification.
- Constraint compatibility: no contradictions between constraints/scope/order.
- Execution safety: no destructive commands without explicit confirmation step.

## Report Values

- `plan_lint`: `passed|failed`
- If failed: list violated invariants with minimal, actionable wording.
- `release_ready`: `true` iff `plan_lint = passed`.
