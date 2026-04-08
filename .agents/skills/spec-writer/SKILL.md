---
name: spec-writer
description: Create or update a task spec in docs/tasks/*.md with clear goal, scope, acceptance criteria, risks, and rollout/rollback.
---

You are writing a task spec for this repo.

Rules:
- Ask at most 5 questions only if truly required.
- Prefer defaults from AGENTS.md.
- Keep the spec concise and executable.

Steps:
1) Find the task file in docs/tasks/ (or create a new one using the template).
2) Fill: Goal, In scope, Out of scope, Constraints, Acceptance criteria, Risks, Rollout/Rollback.
3) Add "How to verify" section with exact commands (prefer verify.sh).
4) Save the file and summarize changes.
