# OA Theme Codebase Review

**Scope**
- Theme root: `wp-content/themes/OA/`
- Focused files/areas: `functions.php`, `acf-json/`, `inc/divi-extensions/`, `inc/rolecall-halt/`, `assets/`, `style.css`, `custom.js`, `pseudo-classes.css`.
- Note: Repo is large and contains vendor/build artifacts (e.g. many JS/TS/Map files). Review prioritizes app-relevant theme code and structure.

## High-level Summary
This codebase is a pragmatic WordPress child theme for Divi with custom extensions and ACF-driven content. It’s functional and structured enough for ongoing work, but the theme layer (especially `functions.php`) is accruing mixed responsibilities. The ACF JSON workflow is robust, but manual timestamp updates and ad‑hoc logic increases operational risk. Overall: workable, but needs clearer separation of concerns, a small amount of infrastructure cleanup, and some guardrails for ACF + Divi integrations.

## Strengths
- **ACF JSON usage**: Field groups are in `acf-json/`, enabling versioned configuration.
- **Divi extensions**: Custom modules are separated under `inc/divi-extensions/`.
- **Asset enqueues**: A single enqueue function in `functions.php` with `filemtime` versions supports cache busting.
- **Domain-specific logic**: RoleCall/Tracker integration is isolated under `inc/rolecall-halt/`.

## Risks and Pain Points
1. **`functions.php` is a catch-all**
   - It contains asset enqueues, admin UI tweaks, ACF hooks, post type logic, and custom integrations.
   - This makes it harder to reason about side effects and debug regressions.

2. **ACF JSON sync fragility**
   - Manual timestamp updates are required to force sync.
   - JSON changes can drift from DB state without visible sync prompts.

3. **ACF update hooks risk unintended data writes**
   - Any logic that updates full ACF groups can overwrite unrelated fields if not done with raw values.
   - Even with raw values, rewriting entire groups is fragile if structure changes.

4. **Mix of runtime environments**
   - Theme includes WordPress + Divi + custom Divi modules + separate RoleCall plugin code.
   - Blended concerns increase coupling and risk of breaking unrelated features.

5. **Asset sprawl**
   - `custom.js`, `style.css`, and `pseudo-classes.css` are all active.
   - It’s unclear which styles/scripts are canonical vs. experimental.

## Suggested Structure Improvements
### 1) Split `functions.php` into modules
**Goal:** isolate responsibilities and reduce regressions.

Suggested split (example):
- `inc/theme/enqueue.php` (front-end assets)
- `inc/theme/acf.php` (ACF hooks & JSON path config)
- `inc/theme/admin.php` (admin UI tweaks)
- `inc/theme/filters.php` (filters like title placeholders)
- `inc/theme/integrations.php` (RoleCall + Divi module bootstraps)

Then in `functions.php`:
```php
require_once get_stylesheet_directory() . '/inc/theme/enqueue.php';
require_once get_stylesheet_directory() . '/inc/theme/acf.php';
require_once get_stylesheet_directory() . '/inc/theme/admin.php';
require_once get_stylesheet_directory() . '/inc/theme/filters.php';
require_once get_stylesheet_directory() . '/inc/theme/integrations.php';
```

### 2) ACF JSON workflow guardrails
- Document a strict edit flow (edit in WP → sync JSON OR edit JSON → sync in WP).
- Consider a small script to touch ACF JSON modified times when files change.
- Add a CI check or pre-commit hook to validate JSON.

### 3) ACF write hooks should target sub-fields
- Prefer `update_sub_field()` to avoid rewriting entire groups.
- Only update the smallest necessary subset to reduce collateral data loss.

### 4) Normalize assets
- Consolidate “component” CSS into a clear pattern (e.g., `assets/css/` with imports).
- Decide whether `pseudo-classes.css` is a stable stylesheet or a temporary workspace.
- Ensure `custom.js` only contains persistent, production-ready code.

## Specific Findings (Actionable)
### A) `functions.php` complexity
- Multiple concerns in one file make it hard to audit.
- Strong candidate for modularization (see structure above).

### B) ACF & Divi integration coupling
- ACF fields map directly to Divi keys; changes to ACF schema require consistent updates to Divi modules and templates.
- Introduce a versioned “schema map” in docs to track which modules use which fields.

### C) RoleCall integration lives inside theme
- `inc/rolecall-halt/` behaves like a plugin but is bundled into the theme.
- Consider moving to a plugin for separation and portability.

### D) Large volume of build artifacts
- The repo contains many large JS/TS/MAP files (likely `node_modules` or build artifacts).
- Consider excluding or isolating these to reduce repo weight.

## Immediate Recommendations (Low Effort, High ROI)
1. **Move `functions.php` sections into `inc/theme/*.php`.**
2. **Add a doc to `README.md` on ACF JSON sync process.**
3. **Use `update_sub_field()` in ACF write hooks wherever possible.**
4. **Create a small index file for CSS/JS assets to define ownership.**

## Testing Gaps
- No automated tests; changes rely on manual verification.
- Recommend documenting minimal manual checklists for:
  - ACF changes (field visibility + save behavior)
  - Divi module rendering
  - RoleCall sync admin workflows

## Overall Assessment
This is a functional codebase with clear custom functionality, but it’s trending toward a monolithic “theme as app” structure. The main risk is maintenance and side effects from centralized logic in `functions.php` and ACF write hooks. With modularization and clearer ACF workflows, this can become a stable, senior‑grade setup.

---

If you want, I can:
- Propose a concrete file split for `functions.php` and implement it.
- Add a JSON validation/check script.
- Add docs for ACF/Divi field mapping.
