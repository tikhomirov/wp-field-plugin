---
name: qa-gate
description: Verify changes against the task spec and plan. Run verify.sh, check diff quality, and produce a final compliance report.
---

Steps:
1) Read docs/tasks/<task>.md and extract acceptance criteria.
2) Read the implementation plan (if present) and list the promised outcomes.
3) Run `./.agents/skills/qa-gate/scripts/verify.sh`.
4) Treat `verify.sh` as a blocking gate: success means exit code `0`; any failing PHP or frontend lint/test step means the task is not ready.
5) If it fails: fix issues, rerun until it passes or blockers are clear.
6) Review the git diff for:
   - correct scope (no unrelated edits)
   - safe error handling
   - tests and edge cases
   - rollback/flag safety when relevant
6) Output a short report:
   - Passed checks
   - Spec coverage (each criterion -> how it's verified)
   - Remaining risks / TODOs
