# AGENTS.md

## Project overview
- WordPress child theme for Divi 5 (OA). Custom Divi 5 modules live under `inc/divi-extensions/`.
- ACF JSON is stored in `acf-json/` and may be used for field definitions.

## Repository layout
- Theme root: `wp-content/themes/OA/`
- Divi 5 extension (client-logos): `inc/divi-extensions/client-logos/`
- Other extension: `inc/divi-extensions/halt-advanced-tabs/`
- ACF field groups: `acf-json/`
- Theme bootstrap: `functions.php`, `style.css`

## Working directory policy
- Always run commands from the repo root. Do not `cd` into subfolders.
- Use `npm --prefix inc/divi-extensions/client-logos ...` for extension scripts.

## Setup and build commands
- Build Divi 5 extension bundle:
  - `npm --prefix inc/divi-extensions/client-logos run build`
- Start Divi 5 extension dev:
  - `npm --prefix inc/divi-extensions/client-logos run start`

## Coding conventions
- Prefer small, targeted edits; avoid reformatting unrelated code.
- Use `rg` for searching.
- Use `apply_patch` for single-file edits when practical.
- Default to ASCII in files unless the file already uses Unicode.

## PHP guidance
- Guard against missing array keys (e.g., when Divi block context is absent).
- Avoid output before headers in admin contexts.
- Use WordPress escaping helpers (`esc_url`, `esc_attr`, `esc_html`) in render callbacks.

## Divi 5 module guidance
- Register module JSON in `inc/divi-extensions/*/modules-json/`.
- Register server render callback in the extension PHP (`*-extension.php`).
- Register the module in the builder bundle (`src/index.ts`) and rebuild bundles.

## Module creation quickstart
- Always work from repo root; use `npm --prefix inc/divi-extensions/client-logos ...` for builds.
- Minimum new module files:
  - `inc/divi-extensions/client-logos/src/components/<module-name>/` (module.json, edit.tsx, index.ts, types.ts, module.scss)
  - `inc/divi-extensions/client-logos/modules-json/<module-name>/` (module.json + default attrs)
  - `inc/divi-extensions/client-logos/modules/<ModuleName>/` (PHP class + RenderCallbackTrait)
- Registration checklist:
  - Add module to `inc/divi-extensions/client-logos/src/index.ts`
  - Add module JSON path + render callback in `inc/divi-extensions/client-logos/client-logos-extension.php`
  - Add dependency in `inc/divi-extensions/client-logos/modules/Modules.php`
- Generator:
  - `scripts/new-divi-module.py <slug> --title "My Module"` (creates skeleton files)
- Build checklist:
  - `npm --prefix inc/divi-extensions/client-logos run build`

## ACF and dynamic content
- Use ACF field names exactly as defined in `acf-json/`.
- Prefer Divi dynamic content mapping for field values when appropriate.
- Keep `ACF-TREE.md` in sync with `acf-json/`.
  - Update it any time ACF JSON changes:
    - `scripts/generate-acf-tree.py`
- Keep `ACF-TREE-DIVI.md` in sync with `acf-json/`.
  - Update it any time ACF JSON changes:
    - `scripts/generate-acf-tree-divi.py`
- Be careful changing ACF image `return_format`: the Client Logos module expects `client_logo` to return an array (needs `url` key). Switching it to `url` will break the carousel unless the module is updated.

## Testing and verification
- No automated test suite is currently configured.
- If changes affect the builder bundle, rebuild with `npm run build`.

## Change safety
- Do not delete or rename files unless explicitly requested.
- Do not run destructive git commands unless explicitly requested.
