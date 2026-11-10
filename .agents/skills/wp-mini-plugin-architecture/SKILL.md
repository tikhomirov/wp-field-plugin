---
name: wp-mini-plugin-architecture
description: Design and maintain small single-purpose WordPress mini-plugins using the compact architecture patterns from this repo. Reject this approach for multi-feature or scalability-driven plugins and prefer a wp-queue-style architecture instead.
---

# WordPress Mini Plugin Architecture

Use this skill to choose and apply the right architecture for small WordPress plugins in this repository.

This skill is intentionally narrow. It is for compact plugins that solve one isolated problem well. It must reject the mini-plugin approach when the plugin is becoming a multi-feature product, needs multiple subsystems, or requires a scalable internal architecture.

## When to Use

Use this skill when:
- Creating a new WordPress plugin with one small isolated responsibility.
- Maintaining an existing plugin that already behaves like a compact mini-plugin.
- Refactoring a simple plugin back to a smaller architecture after unnecessary growth.
- Deciding whether a plugin should stay compact or move to a scalable architecture.

Use these local plugins as the primary mini-plugin references:
- `wp-content/plugins/default-user-avatar/DefaultUserAvatar.php`
- `wp-content/plugins/wp-fancybox-plugin/wp-fancybox-plugin.php`

Use these local plugins as boundary examples that are already beyond a true mini-plugin:
- `wp-content/plugins/wp-postviews-plugin/wp-postviews-plugin.php`
- `wp-content/plugins/wp-likes-plugin/wp-likes-plugin.php`

Use this local plugin as the reference for a scalable architecture instead of a mini-plugin:
- `wp-content/plugins/wp-queue/`

## Do NOT use this skill when

Do not use this skill when the plugin:
- Solves several independent tasks.
- Has multiple subsystems such as admin UI, REST or AJAX endpoints, CLI commands, storage, cron, queue workers, or external integrations.
- Needs a stable internal API, contracts, drivers, or interchangeable implementations.
- Needs a PSR-4 `src/` architecture because complexity requires it, not because it looks cleaner.
- Needs a real test architecture as part of the plugin design.
- Is evolving into a reusable platform or product rather than a focused utility.

If those conditions apply, reject the mini-plugin approach and prefer the scalable style used by:
- `wp-content/plugins/wp-queue/wp-queue.php`
- `wp-content/plugins/wp-queue/composer.json`
- `wp-content/plugins/wp-queue/src/`
- `wp-content/plugins/wp-queue/tests/`

## Mini-plugin criteria

Treat a plugin as a mini-plugin only when most of these are true:
- It has one main responsibility.
- Its bootstrap fits in one main plugin file.
- Its logic fits in one main class or a very small set of simple functions.
- It uses a limited number of WordPress hooks and filters.
- It does not need multiple internal layers or modules.
- Assets, if any, are small and live directly inside the plugin.
- Settings are absent or minimal.
- The plugin has no complex domain model and no separate subsystems.

### Strong local examples

`wp-content/plugins/default-user-avatar/DefaultUserAvatar.php`
- One task: replace the default avatar.
- One class.
- Two filters.
- No modular framework.
- No separate service layer.

`wp-content/plugins/wp-fancybox-plugin/wp-fancybox-plugin.php`
- One task: enable Fancybox behavior for content images.
- One main class in the main plugin file.
- Small local assets in `assets/`.
- Minimal settings via `get_option()`.
- Direct hook registration without a larger framework.
- Theme-linked behavior is gated by theme support, so the theme must explicitly opt in with `add_theme_support()`.

### Boundary examples that should not be treated as true mini-plugins

`wp-content/plugins/wp-postviews-plugin/wp-postviews-plugin.php`
- Combines view counting, AJAX, shortcode output, admin UI, and query logic.
- Still compact in file count, but already too broad in responsibilities.

`wp-content/plugins/wp-likes-plugin/wp-likes-plugin.php`
- Combines AJAX flows, post and comment metadata, frontend assets, profile UI, admin logic, and multiple modes.
- This is no longer a single-purpose mini-plugin architecture.

## Decision Framework

Always make the architecture choice before implementation.

### Outcome A: Use mini-plugin architecture

Choose the mini-plugin architecture when all of these are effectively true:
1. The plugin has one small isolated goal.
2. One main plugin file can own bootstrap and hook registration.
3. One main class or a few simple functions can hold the logic.
4. Assets and settings are small side concerns, not subsystems.
5. The plugin does not need a modular or scalable internal design.

### Outcome B: Do not use mini-plugin architecture; prefer scalable architecture like wp-queue

Choose a scalable architecture when several of these are true:
1. The plugin solves more than one concern.
2. The plugin needs separate admin, runtime, transport, storage, or scheduling areas.
3. The plugin needs contracts, drivers, or multiple implementations.
4. The plugin needs composer autoloading and `src/` because the codebase is structurally large.
5. The plugin should be tested and evolved as a reusable system.

When Outcome B applies, use `wp-content/plugins/wp-queue/` as the reference architecture:
- Composer package metadata in `composer.json`
- PSR-4 autoloading
- `src/` split by responsibility
- explicit contracts
- separate admin and runtime areas
- dedicated tests

### Fast classification rule

Use mini-plugin architecture if the plugin can honestly be described as:
- one entrypoint
- one responsibility
- one main class or tiny helper set
- no need for a framework inside the plugin

Otherwise, do not use it.

## Recommended structure

For a new mini-plugin, prefer this structure:

```text
plugin-name/
├── plugin-name.php
├── assets/
│   ├── plugin.js
│   ├── plugin.css
│   └── images/
└── languages/
```

Keep even this structure minimal. Omit folders that are not needed.

### Structure rules

- Put the WordPress plugin header in the main plugin file.
- Add `defined('ABSPATH') || exit;` near the top.
- Keep bootstrap and hook registration in the main file.
- Keep application logic in one main class or a tiny function set.
- Add `assets/` only when the plugin truly needs local JS, CSS, or images.
- Add `languages/` only when localization is actually used.
- If the plugin behavior depends on theme integration, require explicit theme opt-in through `add_theme_support()` instead of assuming the feature should always run.
- In that case, the plugin should check `get_theme_support()` before enabling theme-coupled hooks or assets, following the pattern in `wp-content/plugins/wp-fancybox-plugin/wp-fancybox-plugin.php`.
- Prefer direct WordPress APIs over internal abstraction layers.
- Avoid `src/`, `Contracts/`, `Services/`, `Repositories/`, `Factories/`, and similar structure unless the plugin has clearly outgrown the mini-plugin class.

### Responsibility split inside a mini-plugin

- Main file: plugin header, guard clause, bootstrap, hooks.
- Main class or functions: the core behavior.
- Assets: small static files coupled directly to the plugin behavior.
- Settings: minimal and only when they materially improve the plugin.

## Instructions

When invoked, follow these rules:

1. Classify the plugin first.
   - Decide whether this is a mini-plugin or a scalable plugin.
   - Do not start coding before that choice is explicit.

2. If it is a mini-plugin, keep the design deliberately small.
   - Prefer one main file.
   - Prefer one main class.
   - Register hooks in one place.
   - Keep options and assets minimal.
   - If the feature is theme-coupled, require the theme to declare support with `add_theme_support()` and gate plugin activation with `get_theme_support()`.
   - Use the local patterns from `default-user-avatar` and `wp-fancybox-plugin`.

3. If it is not a mini-plugin, reject this architecture immediately.
   - State clearly that the plugin has outgrown the mini-plugin model.
   - Recommend a scalable architecture based on `wp-queue`.
   - Do not force a multi-subsystem plugin into a single-file design.

4. During maintenance, protect the plugin from architectural drift.
   - Do not add a subsystem for a tiny feature.
   - Do not introduce abstractions for hypothetical growth.
   - Re-evaluate the architecture if the plugin starts solving multiple tasks.

5. When the plugin crosses the boundary, say so explicitly.
   - If the change introduces multiple responsibilities, admin and runtime separation, storage layers, or growth pressure, recommend migrating away from the mini-plugin architecture.

## Common Pitfalls

- Mistaking low file count for low complexity.
  - A plugin can still be too complex for a mini-plugin architecture even if it fits in one file.

- Treating `wp-postviews-plugin` as a pure mini-plugin template.
  - It is useful as a boundary example, not as the target shape for new single-purpose plugins.

- Treating `wp-likes-plugin` as a mini-plugin template.
  - It already carries multiple concerns and should be used as a warning sign.

- Adding `src/` and service abstractions because they feel cleaner.
  - For mini-plugins, that usually adds maintenance cost without solving a real problem.

- Refusing to escalate architecture when the plugin grows.
  - Once the plugin becomes multi-feature or subsystem-heavy, move to a scalable design instead of stretching the mini-plugin model.

## Constraints

- This skill is only for WordPress plugins in this repository.
- This skill is only for simple, isolated plugin responsibilities.
- This skill must prefer the smallest stable architecture that matches the problem.
- This skill must explicitly reject the mini-plugin approach for multi-feature plugins and recommend a `wp-queue`-style architecture instead.
- This skill must not recommend framework-style internal structure unless the plugin complexity truly demands it.
- This skill must keep recommendations practical and grounded in the local plugin references listed above.
