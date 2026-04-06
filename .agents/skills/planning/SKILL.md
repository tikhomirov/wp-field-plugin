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