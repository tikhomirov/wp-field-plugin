---
name: plan_critic
description: "Critique a PlanArtifact using a severity rubric and produce a CritiqueArtifact (Protocol-Version: 1)."
---

# Plan Critic Skill

## Output Format (CritiqueArtifact)

The output MUST be a Markdown document:

- First line: `Protocol-Version: 1`
- Must contain these headings (exact text):
  - `## Findings`
  - `## Clarifying Questions`
  - `## Verification Questions`
  - `## Stop Signal`

## Findings Rubric

Each finding MUST include:

- `severity`: `Blocker|High|Medium|Low`
- `issue`
- `why_it_matters`
- `suggested_fix`
- `evidence_needed`

## Clarifying Questions

Provide 0–5 questions only if the plan would otherwise be guessing.

## Verification Questions

List concrete checks to perform in repo/config/docs to reduce risk.

## Stop Signal

Set `stop_signal = true` only if there are no `Blocker` and no `High` severity findings.
