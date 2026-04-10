---
name: planning
description: Generate structured, step-by-step execution plans for coding and technical tasks. Ensures plans are atomic, testable, and ready for execution by AI agents.
---

# Planning Skill

## Purpose

This skill generates **high-quality, execution-ready plans** for technical and coding tasks.

It transforms vague or high-level tasks into:
- structured phases
- atomic steps
- verifiable actions
- clear constraints and scope

The output is designed to be directly usable by other AI agents or automation pipelines.

---

## When to Use

Use this skill when:
- A task is complex or multi-step
- You need structured execution before coding
- Requirements are unclear or partially defined
- You want to avoid hallucinations and rework
- You are orchestrating other agents

---

## Instructions

When invoked, follow these rules strictly:

### 1. Structure

Always produce the following sections:

1. Goal
2. Expected Result
3. Context (if available)
4. Constraints
5. Scope (Allowed / Forbidden changes)
6. Assumptions (if needed)
7. Execution Plan (phases + steps)

---

### 2. Execution Plan Rules

- Use **phases**:
    - Preparation
    - Implementation
    - Integration
    - Validation / Testing

- Each step must:
    - Be atomic (one action only)
    - Be actionable (no vague wording)
    - Be testable (clear outcome)
    - Be dependency-ordered

- Format:
  ```md
  - [ ] Step description
```

---

### 3. PlanArtifact Protocol (Protocol-Version: 1)

When the output is intended to be consumed as `plan.md` by a supervisor/workflow, it MUST follow this Markdown protocol:

- First line: `Protocol-Version: 1`
- Must contain these headings (exact text):
  - `## Constraints`
  - `## Assumptions`
  - `## Phases`
  - `## Tests/Validation`
  - `## Rollback`
  - `## Risks`
  - `## Changelog`

Additional rules:

- `## Phases` must have **≤ 5** phases.
- Each phase must contain atomic steps (one logical act each).
- Every assumption must be either:
  - backed by a repo fact, or
  - turned into a clarifying question / verification step.
- `## Changelog` is mandatory for revisions (v1+) and must list what changed vs previous version.
- Include explicit validation steps (unit/integration/manual smoke) appropriate to the changes.
- Include rollback steps appropriate to the change type (config/db/feature flag).