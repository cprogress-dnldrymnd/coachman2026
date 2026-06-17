# Coachman 2026 WordPress Theme

Custom WordPress theme for Glossop Caravans (a caravan/motorhome/campervan dealer), built by Digitally Disruptive. Theme version 1.0.0, text domain `glossop-caravans`.

## Architecture

Classic PHP WordPress theme using Bootstrap 5.3. Content blocks use native Gutenberg blocks (`coachman/*` namespace); custom meta fields use a standalone native framework (`CM_Meta`). Both replaced an earlier Carbon Fields implementation — the theme no longer depends on Carbon Fields at runtime (see **Meta fields** below). SCSS is compiled to `style.css` via `style.scss`.

### Directory structure

```
/                        # Theme root — standard WP template files
includes/                # PHP modules required by functions.php
assets/
  admin/                 # Admin-only JS/CSS (meta-fields.js, meta-fields.css for CM_Meta UI)
  javascripts/main.js    # Frontend JS entry point
  javascripts/blocks.js  # Block editor JS (build-less; registered as coachman-blocks)
  stylesheets/           # SCSS partials (base, sections, utils, plugins)
  vendors/bootstrap/     # Bootstrap 5.3.3 source (scss + dist)
  vendors/swiper/        # Swiper slider
  vendors/fancybox/      # Fancybox lightbox
wpsl-templates/          # WP Store Locator custom templates & markers
```

### Includes loaded by functions.php

| File | Purpose |
|------|---------|
| `post-types.php` | Registers all CPTs and taxonomies via `newPostType`/`newTaxonomy` classes |
| `meta-fields.php` | Standalone native meta-field framework (`CM_Meta`): post meta boxes, term meta, options pages; renders native inputs (incl. nested repeaters & media pickers); saves to `_{name}` |
| `post-meta.php` | Meta-field **definitions** (declarative `CM_Meta::add_box/add_term_box/add_options_page` calls); also defines `get_posts_by_taxonomy_wpdb` |
| `meta-migration.php` | One-time Carbon Fields → native meta migration: **Tools → Migrate Carbon Meta** (dry-run / run / revert); reads the 4 complex/association fields via the Carbon API, writes native `_{name}`, backs up to `_cm_meta_premigrate` |
| `customizer.php` | WordPress Customizer options |
| `menus.php` | Menu registration |
| `theme-widgets.php` | Widget areas |
| `bootstrap-navwalker.php` | Bootstrap-compatible nav walker |
| `shortcodes.php` | Custom shortcodes |
| `custom-functions.php` | Misc helper functions |
| `listing-functions.php` | Caravan/stock listing helpers; `__listing_buttons()` renders the gallery trigger and Fancybox anchor group |
| `hooks.php` | Action/filter hooks |
| `wpsl.php` | WP Store Locator customisation |
| `ajax.php` | AJAX handlers (dealer details) |
| `gutenberg-blocks.php` | Native `coachman/*` Gutenberg block registration & render callbacks |
| `block-migration.php` | Bulk migrator: rewrites `carbon-fields/*` block markup to `coachman/*` in `post_content`; adds **Tools → Migrate Carbon Blocks** admin page (dry-run / run / revert); backs up originals to `_cm_premigration_content` post meta |

## Custom Post Types & Taxonomies

| CPT key | Taxonomy | Notes |
|---------|----------|-------|
| `caravan` | `caravan_model` | slug: `caravan-model` |
| `motorhome` | `motorhome_model` | slug: `motorhome-model` |
| `campervan` | `campervan_model` | slug: `campervan-model` |
| `reviews_post_type` | — | Press reviews; slug: `press-reviews` |
| `events_post_type` | — | Events; slug: `events` |
| `careers` | `careers_category` | |
| `downloads` | `downloads_category` | Has CSV importer under Tools menu |
| `faqs` | `faqs_category` | |
| `partners` | — | |
| `teams` | — | |
| `timeline` | — | |
| `template` | `template_category` | Reusable content blocks |

## Meta fields (standalone `CM_Meta` framework)

Custom meta fields were migrated off **Carbon Fields** to a hand-rolled native framework. There is no longer a runtime Carbon Fields dependency (the plugin is only needed transitionally to run the data migration — see below).

- **Framework**: `includes/meta-fields.php` — `CM_Meta::add_box()` (post meta boxes), `CM_Meta::add_term_box()` (term meta), `CM_Meta::add_options_page()` (theme options pages). Renders native inputs for: `text` (with `input` = text/number/url), `textarea`, `select`, `rich_text` (client-side TinyMCE so it works inside repeaters), `oembed` (URL), `image`/`file` (media picker → attachment ID), `date`, `association` (post picker → array of IDs), `complex` (repeater, supports **nesting** — e.g. `stocks → years`). Admin UI in `assets/admin/meta-fields.{js,css}`.
- **Definitions**: `includes/post-meta.php` — declarative field config only.
- **Storage** (matches the theme's existing readers `get__post_meta()` / `get__term_meta()` / `get__theme_option()`, which prepend `_`): simple fields → `_{name}` scalar; image/file → `_{name}` attachment ID; association → `_{name}` array of int IDs; complex → `_{name}` array of rows (WP serialises). Repeater rows use client-side index tokens (`{{path}}`) unique per nesting level; the save handler reindexes with `array_values`.
- **Reading complex/association** in PHP: use `get__post_complex($id,$field)` / `get__term_complex($term_id,$field)` / `get__term_page_id($term_id)` (in `functions.php`). These read native first and **fall back to the Carbon API only while the plugin is still installed** (so there's no front-end gap before the migration runs); the fallback is inert once Carbon Fields is removed.

### Migration (Carbon Fields → native)

Only **4 fields** used Carbon's special storage and need migrating: `technical_details` + `page` (model terms), `stocks` (wpsl_stores), `display_on` (template). Everything else (all simple fields + theme options) was already stored by Carbon at `_{name}` and needs no migration. Sequence: deploy → **Tools → Migrate Carbon Meta** (run) → verify → remove the Carbon Fields plugin. Revert restores each object from its `_cm_meta_premigrate` backup (pair a revert with a code rollback).

## Key dependencies (plugins required)

- **WP Store Locator (WPSL)** — dealer/store finder; custom templates in `wpsl-templates/`
- **Carbon Fields** — *transitional only*: required to be active to run **Tools → Migrate Carbon Meta**; can be removed once the migration is verified.

## Styling

- Source: `style.scss` → compiled output: `style.css`
- SCSS partials under `assets/stylesheets/`: `utils/`, `base/`, `sections/`, `plugins/`
- Bootstrap imported from `assets/vendors/bootstrap/scss/bootstrap`
- Fonts: Adobe Typekit (`rlp0swo` kit), Proxima Nova as primary font family
- `style.css` is also loaded inside the block editor canvas via `add_editor_style('style.css')` (editor-styles theme support enabled in `functions.php`)

## JavaScript

- `assets/javascripts/main.js` — frontend entry point; vendors loaded via `wp_enqueue_script` (jQuery, Bootstrap, Swiper, Fancybox); AJAX nonce localised as `ajax_params`
- `assets/javascripts/blocks.js` — block editor only; build-less (uses global `wp.*` dependencies); localised as `window.coachmanBlocks` with taxonomy term lists for the selectors. Uses three factory helpers to reduce boilerplate: `registerServerBlock` (SSR leaf blocks), `registerContainerBlock` (InnerBlocks wrappers), `registerPlaceholderBlock` (child blocks with no save markup, e.g. swiper-pagination/navigation — supports optional `inspector` option for per-block settings). `registerServerBlock` accepts `usePostContext: true` to declare `usesContext: ['postId', 'postType']` and pass the Query Loop's per-iteration `props.context.postId` to the SSR preview — falling back to `currentPostId()` outside a query loop.

### Sticky header / `--header-height`

`setHeaderHeight()` sets `--header-height` on `<body>` from `#masthead`'s measured height. `updateScrollStatus()` toggles `sticky--header` on scroll and **re-measures after 500 ms** whenever the sticky state changes, because the sticky transition shrinks the header's padding over 500 ms. `.sticky-element` uses `top: calc(var(--header-height, 75px) + var(--admin-bar-height, 0px))`. Do not call `setHeaderHeight()` synchronously after toggling the sticky class — the transition hasn't settled yet.

### Listing gallery (Fancybox)

`__listing_buttons()` in `listing-functions.php` outputs a hidden `.listing-gallery-items` div containing `<a data-fancybox="gallery-{post_id}">` anchors for each gallery image. The "Gallery" button carries `data-gallery-trigger="gallery-{post_id}"` — clicking it is intercepted in `fancybox()` (delegated `[data-gallery-trigger]` handler), which finds and clicks the first anchor in that group to open Fancybox with `Thumbs: { type: "modern" }`. There is no offcanvas or Swiper panel for the listing gallery.

### Mega-menu / offcanvas gotcha

`#offCanvasMenu` carries the class `mega-menu--not-active` by default. The class is stripped **only when the offcanvas already has the `show` class** (i.e. a real user interaction), not during the auto-click that activates the first listing tab. It is re-added on `hidden.bs.offcanvas`. Do not remove this guard — without it the mega panel reveals itself on page load.

## Gutenberg Blocks (`coachman/*`)

Registered in `includes/gutenberg-blocks.php` (loaded by `functions.php`); editor JS in `assets/javascripts/blocks.js` (handle `coachman-blocks`). These replaced the old Carbon Fields `Block::make()` blocks (now removed); `block-migration.php` rewrites any legacy `carbon-fields/*` markup in `post_content`. All blocks use **apiVersion 3** (set in the PHP `$defaults` array and each JS factory helper).

PHP helper functions in `gutenberg-blocks.php`: `cm_block_classname($attributes)` (reads `className`), `cm_term_options($taxonomy)` (builds `{value, label}` option arrays for selectors), `cm_listing_models_posts($attributes)` (reshapes flat block attributes into the per-vehicle post structure).

| Block | Type | Notes |
|-------|------|-------|
| `coachman/icon` | ServerSideRender | Media-library icon with color/size/alignment |
| `coachman/video-gallery` | ServerSideRender | Queries `videos` CPT (registration commented out in `post-types.php` — block returns empty) |
| `coachman/tabs-navigation` + `tabs-navigation-item` | InnerBlocks container | Optional Swiper mode; `tabs-navigation-item` has a `noSubmenu` toggle that adds `no--submenu` class to the `<li>` |
| `coachman/tabs-content` + `tabs-content-item` | InnerBlocks container | |
| `coachman/swiper` + `swiper-wrapper` + `swiper-slide` + `swiper-pagination` + `swiper-navigation` | InnerBlocks container | Full Swiper config via inspector; pagination/navigation presence is **auto-detected from child `WP_Block->inner_blocks`** in the PHP render callback — no manual flag needed; `swiper-pagination` and `swiper-navigation` each carry their own `style` attribute (Default / Style 2) set via a shared `swiperStyleInspector` panel |
| `coachman/listing-models` | ServerSideRender | Multi-taxonomy model grid/swiper; model selection uses `FormTokenField` (searchable token UI) via `modelTokenField` helper in `blocks.js` — maps term IDs ↔ labels on the way in/out; `displayModelLayouts` toggle expands inline model grids below |
| `coachman/listing-title`, `listing-feature`, `listing-buttons` | ServerSideRender | Use current post context (`usePostContext: true`); previews correctly track the Query Loop's per-iteration post |
| `coachman/video-tour-carousel` | ServerSideRender | Swiper carousel of video tours for a specific model; inspector offers post-type (caravan/motorhome/campervan) then model selectors; styled via `.video-tour-carousel` |
| `coachman/model-technical-details` | ServerSideRender | Off-canvas technical spec drawer; model selector lists caravan + motorhome terms only |
| `coachman/partner` | ServerSideRender | Logo + website link (toggleable via `showLogo`/`showWebsite`); uses current post context |
| `coachman/event-date` | ServerSideRender | Uses `event_date`/`event_end_date` meta |

All blocks are grouped under the **Coachman** inserter category.

## Theme globals (defined in functions.php)

```php
define('version', 1);
define('theme_dir', get_template_directory_uri() . '/');
define('assets_dir', theme_dir . 'assets/');
define('image_dir', assets_dir . 'images/');
define('vendor_dir', assets_dir . 'vendors/');
```

Helper functions: `get__post_meta($value)`, `get__post_meta_by_id($id, $value)`, `get__term_meta($term_id, $value)`, `get__theme_option($value)` — all prepend `_` to the key.

## Build

No build tooling config is committed (no `package.json` at root). SCSS compilation is done externally (likely a separate build step or IDE plugin). The compiled `style.css` and `style.css.map` are committed.
