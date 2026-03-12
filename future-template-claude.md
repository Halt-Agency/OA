# Future Project Template - Divi Child Theme with ACF + Custom PHP/HTML/CSS/JS Modules

This document captures the architectural patterns and conventions used in this project. Use it as a CLAUDE.md reference for future projects built in the same style.

---

## Stack Overview

- **CMS**: WordPress with Divi parent theme
- **Custom Fields**: Advanced Custom Fields (ACF) Pro - all content is managed through ACF, not the WordPress editor
- **Block Editor**: Gutenberg is DISABLED for all CPTs and posts
- **Front-end modules**: Vanilla PHP/HTML/CSS/JS - no build tools, no bundler, no Sass, no jQuery
- We do NOT build custom Divi Visual Builder modules. All custom front-end work is done with standalone PHP/HTML/CSS/JS modules inserted into Divi Code Modules on pages.

---

## Modularisation Style

Everything is highly modular. `functions.php` is ONLY a loader containing `require_once` statements - no logic, no hooks, no functions. Each concern lives in its own file under `inc/theme/`:

- **CPTs**: One file per post type in `inc/theme/cpt/`
- **ACF config**: JSON sync, options pages, and hooks each in their own file under `inc/theme/acf/`
- **ACF hooks**: A central `hooks.php` loader that requires feature-specific hook files like `hooks-team-members.php`, `hooks-hero-images.php`
- **AJAX handlers**: One file per feature in `inc/theme/ajax/`
- **Shortcodes**: One file per shortcode in `inc/theme/shortcodes/`
- **Admin tweaks**: Separate files for UI, menu order, editor config, etc. in `inc/theme/admin/`
- **Asset enqueue**: Single file at `inc/theme/assets/enqueue.php` registering all CSS/JS
- **Front-end modules**: Paired CSS + JS files in `assets/css/` and `assets/js/`

Function prefix: `dt_` for all theme functions. This is a namespace-style prefix to avoid collisions with WordPress core, plugins, or the Divi parent theme. Choose a short, project-specific prefix for each new project (e.g. `dt_`, `oa_`, `hq_`).

---

## Custom Post Type (CPT) Registration

Each CPT lives in its own file. Full labels array, `register_post_type()` on `init` at priority 0. Key conventions:

- `supports` omits `editor` - ACF handles all content
- Taxonomy metaboxes are removed from the sidebar (ACF handles taxonomy assignment via field groups)
- Taxonomies can be registered in the same file or a separate `<cpt>-taxonomies.php` file
- Initial taxonomy terms are seeded with a one-time function gated by `get_option()` / `update_option()`

---

## ACF Structure

### Local JSON Sync

Field groups auto-save as JSON files in an `acf-json/` directory in the theme root, configured via `acf/settings/save_json` and `acf/settings/load_json` filters.

**Naming convention:**
- CPT groups: `group_<cpt_name>.json`
- Page-specific groups: `group_page_<page_slug>.json`
- Option page groups: `group_<option_slug>.json`

**Key conventions:**
- Group keys: `group_<descriptive_name>`
- Field keys: `field_<group_prefix>_<field_name>`
- Fields within a CPT group use **tabs** (`type: "tab"`, `placement: "left"`) for organization
- ACF field **instructions** should include the Divi dynamic content key, e.g. `"Divi key: first_name"`. This tells content editors which key to reference when wiring up Divi dynamic content to ACF fields on the page.

### Page-Level ACF Pattern

Pages use a **group field** called `page_content` that wraps all page-specific fields. This lets the front-end JS access page data via `window.dtACFData.page_content.<field_name>`.

### ACF Options Pages

Registered via `acf_add_options_sub_page()` on `acf/init`. Used for global settings like header menus, team page globals, UK coverage contacts. Data accessed via `get_field('<field_name>', 'option')`.

### ACF Hooks

Feature-specific hook files (e.g. `hooks-team-members.php`) loaded from a central `hooks.php`. Common patterns:
- `acf/save_post` - Sync data to post meta, featured images, or other posts on save
- `acf/load_value/name=<field>` - Compute values on load
- `acf/update_value/name=<field>` - Override values on save
- `acf/prepare_field` - Lock/modify fields in the admin UI

---

## Custom Module Creation (The Core Workflow)

This is the primary method for building interactive front-end components. Each module has up to 3 parts:

### 1. CSS: `assets/css/oa-<module-name>.css`

Self-contained CSS using BEM naming: `.oa-<module>__<element>--<modifier>`

### 2. JS: `assets/js/oa-<module-name>.js`

Self-contained vanilla JS wrapped in an IIFE. Reads data from `window` globals injected by PHP, builds HTML strings, injects into a container element, attaches event listeners. Auto-initializes by querying for containers via `document.querySelectorAll('[data-oa-<module>]')` or by class.

Because JS modules build HTML from JSON data, every module should include two standard helpers:
- `escapeHtml(value)` - Escapes `& < > " '` to prevent XSS when inserting user/ACF data into HTML strings
- `decodeEntities(value)` - Decodes HTML entities (e.g. `&amp;` back to `&`) that WordPress/ACF may encode in stored field values, so text displays correctly

These are defined locally inside each module's IIFE (not shared globally).

### 3. Enqueue in `inc/theme/assets/enqueue.php`

Each CSS/JS pair is registered with `wp_enqueue_style` / `wp_enqueue_script`, wrapped in `file_exists()`, using `filemtime()` for cache busting. CSS depends on `['child-style']`, JS loads in footer with no dependencies.

### Inserting into pages

Place a container div inside a **Divi Code Module** on the page:

```html
<div data-oa-<module-name>></div>
```

The globally-enqueued JS finds these containers and renders the module. If no container exists on a page, the JS does nothing. Shortcodes can also be placed inside Divi Code Modules.

---

## Data Flow: PHP to JavaScript

### Global Data Injection

A `wp_footer` action in `inc/theme/ajax/acf-data.php` injects ACF data and computed datasets as `window` globals on every page:

- `window.dtACFData` - All ACF fields for the current page/post
- `window.dtACFData.page_content` - The page-level content group
- `window.dtPostId` - Current post ID
- `window.dtAjaxUrl` - WordPress AJAX URL
- `window.oa<DatasetName>` - Computed datasets built from WP_Query + ACF (e.g. team members, client logos, job listings)

Computed datasets are built server-side (querying CPTs, resolving ACF image fields, gathering taxonomy terms) and JSON-encoded into the page. JS modules read from these globals rather than making their own API calls.

### AJAX (for dynamic filtering)

Features requiring server-side filtering (e.g. job board with search/filter) use WordPress AJAX endpoints registered with `wp_ajax_` / `wp_ajax_nopriv_` hooks. JS calls via `XMLHttpRequest` to `window.dtAjaxUrl`. Responses use `wp_send_json_success()`.

---

## Shortcode Pattern

Shortcodes use `ob_start()` / `ob_get_clean()` for PHP-rendered HTML output. A separate render function allows the markup to also be exposed via a REST API endpoint for AJAX loading.

---

## Page Auto-Creation

Pages can be auto-created on theme activation (`after_switch_theme`) with a helper that checks for existing pages by slug/title before creating. Supports hierarchical parent/child page structures.

---

## Conventions Summary

- **CSS**: BEM naming, self-contained per module, no shared utility classes
- **JS**: Vanilla only, IIFE-wrapped, each module includes local `escapeHtml()` and `decodeEntities()` helpers for safe HTML string building, data from `window` globals
- **PHP**: `dt_` prefix, one concern per file, ACF for all content management
- **Modules inserted via**: Divi Code Modules containing a container div that JS targets
- **Data flow**: PHP builds datasets server-side -> JSON into `window` globals -> JS reads and renders
