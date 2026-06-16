# Coachman 2026 WordPress Theme

Custom WordPress theme for Glossop Caravans (a caravan/motorhome/campervan dealer), built by Digitally Disruptive. Theme version 1.0.0, text domain `glossop-caravans`.

## Architecture

Classic PHP WordPress theme using Bootstrap 5.3. Native Gutenberg blocks (`coachman/*` namespace) are registered alongside the original Carbon Fields blocks. SCSS is compiled to `style.css` via `style.scss`.

### Directory structure

```
/                        # Theme root — standard WP template files
includes/                # PHP modules required by functions.php
assets/
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
| `post-meta.php` | Carbon Fields meta field registration |
| `customizer.php` | WordPress Customizer options |
| `menus.php` | Menu registration |
| `theme-widgets.php` | Widget areas |
| `bootstrap-navwalker.php` | Bootstrap-compatible nav walker |
| `shortcodes.php` | Custom shortcodes |
| `custom-functions.php` | Misc helper functions |
| `listing-functions.php` | Caravan/stock listing helpers |
| `hooks.php` | Action/filter hooks |
| `wpsl.php` | WP Store Locator customisation |
| `ajax.php` | AJAX handlers (dealer details) |
| `woocommerce.php` | WooCommerce integration |
| `gutenberg-blocks.php` | Native `coachman/*` Gutenberg block registration & render callbacks |

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

## Key dependencies (plugins required)

- **Carbon Fields** — all custom meta fields (`carbon_get_post_meta`)
- **WP Store Locator (WPSL)** — dealer/store finder; custom templates in `wpsl-templates/`
- **WooCommerce** — integrated via `includes/woocommerce.php`

## Styling

- Source: `style.scss` → compiled output: `style.css`
- SCSS partials under `assets/stylesheets/`: `utils/`, `base/`, `sections/`, `plugins/`
- Bootstrap imported from `assets/vendors/bootstrap/scss/bootstrap`
- Fonts: Adobe Typekit (`rlp0swo` kit), Proxima Nova as primary font family
- `style.css` is also loaded inside the block editor canvas via `add_editor_style('style.css')` (editor-styles theme support enabled in `functions.php`)

## JavaScript

- `assets/javascripts/main.js` — frontend entry point; vendors loaded via `wp_enqueue_script` (jQuery, Bootstrap, Swiper, Fancybox); AJAX nonce localised as `ajax_params`
- `assets/javascripts/blocks.js` — block editor only; build-less (uses global `wp.*` dependencies); localised as `window.coachmanBlocks` with taxonomy term lists for the selectors. Uses three factory helpers to reduce boilerplate: `registerServerBlock` (SSR leaf blocks), `registerContainerBlock` (InnerBlocks wrappers), `registerPlaceholderBlock` (child blocks with no save markup, e.g. swiper-pagination/navigation — supports optional `inspector` option for per-block settings).

### Mega-menu / offcanvas gotcha

`#offCanvasMenu` carries the class `mega-menu--not-active` by default. The class is stripped **only when the offcanvas already has the `show` class** (i.e. a real user interaction), not during the auto-click that activates the first listing tab. It is re-added on `hidden.bs.offcanvas`. Do not remove this guard — without it the mega panel reveals itself on page load.

## Gutenberg Blocks (`coachman/*`)

Registered in `includes/gutenberg-blocks.php` (loaded by `functions.php`); editor JS in `assets/javascripts/blocks.js` (handle `coachman-blocks`). These are editor-friendly replacements for the Carbon Fields `Block::make()` blocks in `post-meta.php`. **Both sets coexist**; new content should use `coachman/*`.

PHP helper functions in `gutenberg-blocks.php`: `cm_block_classname($attributes)` (reads `className`), `cm_term_options($taxonomy)` (builds `{value, label}` option arrays for selectors), `cm_listing_models_posts($attributes)` (reshapes flat block attributes into the per-vehicle post structure).

| Block | Type | Notes |
|-------|------|-------|
| `coachman/icon` | ServerSideRender | Media-library icon with color/size/alignment |
| `coachman/video-gallery` | ServerSideRender | Queries `videos` CPT (registration commented out in `post-types.php` — block returns empty) |
| `coachman/tabs-navigation` + `tabs-navigation-item` | InnerBlocks container | Optional Swiper mode; `tabs-navigation-item` has a `noSubmenu` toggle that adds `no--submenu` class to the `<li>` |
| `coachman/tabs-content` + `tabs-content-item` | InnerBlocks container | |
| `coachman/swiper` + `swiper-wrapper` + `swiper-slide` + `swiper-pagination` + `swiper-navigation` | InnerBlocks container | Full Swiper config via inspector; pagination/navigation presence is **auto-detected from child `WP_Block->inner_blocks`** in the PHP render callback — no manual flag needed; `swiper-pagination` and `swiper-navigation` each carry their own `style` attribute (Default / Style 2) set via a shared `swiperStyleInspector` panel |
| `coachman/listing-models` | ServerSideRender | Multi-taxonomy model grid/swiper; term IDs selected in inspector; `displayModelLayouts` toggle expands inline model grids below |
| `coachman/listing-title`, `listing-feature`, `listing-buttons` | ServerSideRender | Use current post context |
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
