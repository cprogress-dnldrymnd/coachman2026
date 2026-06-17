<?php

/**
 * Native Gutenberg blocks (namespace: coachman/*)
 *
 * These are hand-written, editor-friendly replacements for the Carbon Fields
 * `Block::make()` blocks registered in includes/post-meta.php. They render a
 * live preview inside the block editor (via ServerSideRender for leaf blocks
 * and native InnerBlocks for container blocks) instead of the green Carbon
 * placeholder boxes.
 *
 * The original carbon-fields/* blocks are intentionally left untouched so both
 * sets remain available; new content should use these coachman/* blocks.
 *
 * Frontend markup mirrors the Carbon render callbacks 1:1. Editor JS lives in
 * assets/javascripts/blocks.js.
 */

if (! defined('ABSPATH')) {
    exit;
}

/* -------------------------------------------------------------------------- */
/* Small helpers                                                              */
/* -------------------------------------------------------------------------- */

/**
 * Safely pull the (optional) "Additional CSS class(es)" value a block author
 * may have set in the Advanced panel.
 */
function cm_block_classname($attributes)
{
    return isset($attributes['className']) ? $attributes['className'] : '';
}

/**
 * Build the taxonomy term option list used by the Listing Models /
 * Model Technical Details selectors, in the shape the JS controls expect:
 * [ [ 'value' => '12', 'label' => 'Name' ], ... ].
 */
function cm_term_options($taxonomy)
{
    $options = array();
    foreach (get_taxonomy_terms_wpdb($taxonomy) as $value => $label) {
        if ($value === '') { // drop the "Select model" placeholder row
            continue;
        }
        $options[] = array(
            'value' => (string) $value,
            'label' => $label,
        );
    }
    return $options;
}

/* -------------------------------------------------------------------------- */
/* Editor assets + block category                                             */
/* -------------------------------------------------------------------------- */

/**
 * Group all coachman/* blocks under their own category in the inserter.
 */
function cm_block_category($categories)
{
    return array_merge(
        array(
            array(
                'slug'  => 'coachman',
                'title' => __('Coachman', 'glossop-caravans'),
                'icon'  => null,
            ),
        ),
        $categories
    );
}
add_filter('block_categories_all', 'cm_block_category', 10, 1);

/**
 * Register (and, in the admin, localise) the shared editor script. The script
 * is attached to every block below via the `editor_script` arg, so WordPress
 * only enqueues it inside the block editor.
 */
function cm_register_block_assets()
{
    $path = get_template_directory() . '/assets/javascripts/blocks.js';

    wp_register_script(
        'coachman-blocks',
        assets_dir . 'javascripts/blocks.js',
        array(
            'wp-blocks',
            'wp-element',
            'wp-block-editor',
            'wp-components',
            'wp-server-side-render',
            'wp-i18n',
            'wp-data',
        ),
        file_exists($path) ? filemtime($path) : version,
        true
    );

    if (is_admin()) {
        wp_localize_script('coachman-blocks', 'coachmanBlocks', array(
            'caravanModels'   => cm_term_options('caravan_model'),
            'motorhomeModels' => cm_term_options('motorhome_model'),
            'campervanModels' => cm_term_options('campervan_model'),
        ));
    }
}

/* -------------------------------------------------------------------------- */
/* Block registration                                                         */
/* -------------------------------------------------------------------------- */

function cm_register_blocks()
{
    cm_register_block_assets();

    $defaults = array(
        'api_version'   => 3,
        'editor_script' => 'coachman-blocks',
        'supports'      => array('html' => false),
        'category'      => 'coachman',
    );

    // --- Icon ------------------------------------------------------------- //
    register_block_type('coachman/icon', array_merge($defaults, array(
        'attributes'      => array(
            'iconId'        => array('type' => 'number', 'default' => 0),
            'iconColor'     => array('type' => 'string', 'default' => ''),
            'iconAlignment' => array('type' => 'string', 'default' => ''),
            'iconWidth'     => array('type' => 'string', 'default' => ''),
            'iconHeight'    => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'cm_render_icon',
    )));

    // --- Video Gallery ---------------------------------------------------- //
    register_block_type('coachman/video-gallery', array_merge($defaults, array(
        'render_callback' => 'cm_render_video_gallery',
    )));

    // --- Tabs Navigation -------------------------------------------------- //
    register_block_type('coachman/tabs-navigation', array_merge($defaults, array(
        'attributes'      => array(
            'tabId'     => array('type' => 'string', 'default' => ''),
            'isSwiper'  => array('type' => 'boolean', 'default' => false),
            'direction' => array('type' => 'string', 'default' => ''),
            'tabStyle'  => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'cm_render_tabs_navigation',
    )));

    register_block_type('coachman/tabs-navigation-item', array_merge($defaults, array(
        'parent'          => array('coachman/tabs-navigation'),
        'attributes'      => array(
            'tabItemId' => array('type' => 'string', 'default' => ''),
            'noSubmenu' => array('type' => 'boolean', 'default' => false),
        ),
        'render_callback' => 'cm_render_tabs_navigation_item',
    )));

    // --- Tabs Content ----------------------------------------------------- //
    register_block_type('coachman/tabs-content', array_merge($defaults, array(
        'attributes'      => array(
            'tabId' => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'cm_render_tabs_content',
    )));

    register_block_type('coachman/tabs-content-item', array_merge($defaults, array(
        'parent'          => array('coachman/tabs-content'),
        'attributes'      => array(
            'tabContentId' => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'cm_render_tabs_content_item',
    )));

    // --- Swiper ----------------------------------------------------------- //
    register_block_type('coachman/swiper', array_merge($defaults, array(
        'attributes'      => array(
            'swiperId'             => array('type' => 'string', 'default' => ''),
            'enableAutoplay'       => array('type' => 'boolean', 'default' => false),
            'autoplayDelay'        => array('type' => 'number', 'default' => 3000),
            'disableOnInteraction' => array('type' => 'boolean', 'default' => false),
            'spaceBetween'         => array('type' => 'string', 'default' => ''),
            'slidesPerView'        => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'cm_render_swiper',
    )));

    register_block_type('coachman/swiper-wrapper', array_merge($defaults, array(
        'parent'          => array('coachman/swiper'),
        'render_callback' => 'cm_render_swiper_wrapper',
    )));

    register_block_type('coachman/swiper-slide', array_merge($defaults, array(
        'parent'          => array('coachman/swiper-wrapper'),
        'render_callback' => 'cm_render_swiper_slide',
    )));

    register_block_type('coachman/swiper-pagination', array_merge($defaults, array(
        'parent'          => array('coachman/swiper'),
        'attributes'      => array(
            'style' => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'cm_render_swiper_pagination',
    )));

    register_block_type('coachman/swiper-navigation', array_merge($defaults, array(
        'parent'          => array('coachman/swiper'),
        'attributes'      => array(
            'style' => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'cm_render_swiper_navigation',
    )));

    // --- Listing blocks --------------------------------------------------- //
    register_block_type('coachman/listing-models', array_merge($defaults, array(
        'attributes'      => array(
            'isSwiper'            => array('type' => 'boolean', 'default' => false),
            'displayModelLayouts' => array('type' => 'boolean', 'default' => false),
            'caravanModels'      => array('type' => 'array', 'default' => array()),
            'motorhomeModels'    => array('type' => 'array', 'default' => array()),
            'campervanModels'    => array('type' => 'array', 'default' => array()),
        ),
        'render_callback' => 'cm_render_listing_models',
    )));

    register_block_type('coachman/listing-title', array_merge($defaults, array(
        'render_callback' => 'cm_render_listing_title',
    )));

    register_block_type('coachman/listing-feature', array_merge($defaults, array(
        'render_callback' => 'cm_render_listing_feature',
    )));

    register_block_type('coachman/listing-buttons', array_merge($defaults, array(
        'render_callback' => 'cm_render_listing_buttons',
    )));

    // --- Video Tour Carousel ---------------------------------------------- //
    register_block_type('coachman/video-tour-carousel', array_merge($defaults, array(
        'attributes'      => array(
            'postType' => array('type' => 'string', 'default' => ''),
            'modelId'  => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'cm_render_video_tour_carousel',
    )));

    // --- Model Technical Details ------------------------------------------ //
    register_block_type('coachman/model-technical-details', array_merge($defaults, array(
        'attributes'      => array(
            'buttonText' => array('type' => 'string', 'default' => 'View all features'),
            'modelId'    => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'cm_render_model_technical_details',
    )));

    // --- Partner ---------------------------------------------------------- //
    register_block_type('coachman/partner', array_merge($defaults, array(
        'attributes'      => array(
            'showLogo'    => array('type' => 'boolean', 'default' => true),
            'showWebsite' => array('type' => 'boolean', 'default' => true),
        ),
        'render_callback' => 'cm_render_partner',
    )));

    // --- Event Date ------------------------------------------------------- //
    register_block_type('coachman/event-date', array_merge($defaults, array(
        'render_callback' => 'cm_render_event_date',
    )));
}
add_action('init', 'cm_register_blocks');

/* -------------------------------------------------------------------------- */
/* Render callbacks — frontend markup (mirrors the Carbon Fields versions)    */
/* -------------------------------------------------------------------------- */

function cm_render_icon($attributes)
{
    $icon      = isset($attributes['iconId']) ? $attributes['iconId'] : 0;
    $color     = isset($attributes['iconColor']) ? $attributes['iconColor'] : '';
    $width     = isset($attributes['iconWidth']) ? $attributes['iconWidth'] : '';
    $height    = isset($attributes['iconHeight']) ? $attributes['iconHeight'] : '';
    $alignment = isset($attributes['iconAlignment']) ? $attributes['iconAlignment'] : '';
    $classname = cm_block_classname($attributes);

    ob_start(); ?>
    <div class="svg-box <?= esc_attr($alignment) ?> <?= esc_attr($classname) ?>" style="color: <?= esc_attr($color) ?>; --svg-width: <?= esc_attr($width) ?>; --svg-height: <?= esc_attr($height) ?>">
        <?php if ($icon) {
            echo get__media_libray_icons($icon);
        } ?>
    </div>
<?php
    return ob_get_clean();
}

function cm_render_video_gallery($attributes)
{
    $classname = cm_block_classname($attributes);

    $query = new WP_Query(array(
        'post_type'      => 'videos',
        'posts_per_page' => -1,
    ));

    ob_start(); ?>
    <div class="video-gallery-box <?= esc_attr($classname) ?>">
        <div class="row g-4">
            <?php while ($query->have_posts()) {
                $query->the_post(); ?>
                <div class="col-sm-6 col-lg-4">
                    <div class="video-box rounded overflow-hidden position-relative">
                        <?php the_content() ?>
                    </div>
                </div>
            <?php }
            wp_reset_postdata(); ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function cm_render_tabs_navigation($attributes, $content)
{
    $classname = cm_block_classname($attributes);
    $tab_id    = isset($attributes['tabId']) ? $attributes['tabId'] : '';
    $direction = isset($attributes['direction']) ? $attributes['direction'] : '';
    $style     = isset($attributes['tabStyle']) ? $attributes['tabStyle'] : '';

    if (! empty($attributes['isSwiper'])) {
        $class1 = 'swiper swiper-nav-tabs-swiper nav-tabs-swiper';
        $class2 = 'swiper-wrapper nav nav-tabs';
    } else {
        $class1 = 'nav-tabs-holder';
        $class2 = 'nav nav-tabs gap-1';
    }

    ob_start(); ?>
    <div class="container">
        <div class="<?= esc_attr($class1) ?> overflow-visible sm-margin-bottom nav-tabs-swiper-js <?= esc_attr($classname) ?>">
            <ul class="<?= esc_attr($class2) ?> <?= esc_attr($direction) ?> <?= esc_attr($style) ?>" id="<?= esc_attr($tab_id) ?>" role="tablist">
                <?= $content ?>
            </ul>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function cm_render_tabs_navigation_item($attributes, $content)
{
    $classname   = cm_block_classname($attributes);
    $item_id     = isset($attributes['tabItemId']) ? $attributes['tabItemId'] : '';
    $no_submenu  = ! empty($attributes['noSubmenu']) ? 'no--submenu' : '';

    ob_start(); ?>
    <li class="swiper-slide nav-item <?= esc_attr($no_submenu) ?> <?= esc_attr($classname) ?>" role="presentation">
        <button class="nav-link" id="<?= esc_attr($item_id) ?>" data-bs-toggle="tab" data-bs-target="#<?= esc_attr($item_id) ?>-pane" type="button" role="tab" aria-controls="<?= esc_attr($item_id) ?>-pane">
            <?= $content ?>
        </button>
    </li>
<?php
    return ob_get_clean();
}

function cm_render_tabs_content($attributes, $content)
{
    $tab_id = isset($attributes['tabId']) ? $attributes['tabId'] : '';

    ob_start(); ?>
    <div class="tab-content" id="<?= esc_attr($tab_id) ?>">
        <?= $content ?>
    </div>
<?php
    return ob_get_clean();
}

function cm_render_tabs_content_item($attributes, $content)
{
    $content_id = isset($attributes['tabContentId']) ? $attributes['tabContentId'] : '';

    ob_start(); ?>
    <div class="tab-pane fade" id="<?= esc_attr($content_id) ?>-pane">
        <?= $content ?>
    </div>
<?php
    return ob_get_clean();
}

function cm_render_swiper($attributes, $content, $block = null)
{
    $classname = cm_block_classname($attributes);
    $swiper_id = isset($attributes['swiperId']) ? $attributes['swiperId'] : '';

    // Pagination / navigation are auto-detected from the child blocks present;
    // the pagination & navigation style is read off whichever child carries it.
    $has_pagination = false;
    $has_navigation = false;
    $style          = '';

    if ($block instanceof WP_Block && ! empty($block->inner_blocks)) {
        foreach ($block->inner_blocks as $inner) {
            if ($inner->name === 'coachman/swiper-pagination') {
                $has_pagination = true;
            } elseif ($inner->name === 'coachman/swiper-navigation') {
                $has_navigation = true;
            } else {
                continue;
            }
            if ($style === '' && ! empty($inner->attributes['style'])) {
                $style = $inner->attributes['style'];
            }
        }
    }

    $atts = array();

    if (! empty($attributes['enableAutoplay'])) {
        $atts['autoplay'] = array(
            'delay'                => ! empty($attributes['autoplayDelay']) ? $attributes['autoplayDelay'] : 3000,
            'disableOnInteraction' => ! empty($attributes['disableOnInteraction']) ? 'true' : 'false',
        );
    }
    if (isset($attributes['spaceBetween']) && $attributes['spaceBetween'] !== '') {
        $atts['spaceBetween'] = $attributes['spaceBetween'];
    }
    if (isset($attributes['slidesPerView']) && $attributes['slidesPerView'] !== '') {
        $atts['slidesPerView'] = $attributes['slidesPerView'];
    }
    if ($has_pagination) {
        $atts['pagination'] = array(
            'el'        => '#' . $swiper_id . ' .swiper-pagination',
            'clickable' => 'true',
        );
    }
    if ($has_navigation) {
        $atts['navigation'] = array(
            'nextEl' => '#' . $swiper_id . ' .swiper-button-next',
            'prevEl' => '#' . $swiper_id . ' .swiper-button-prev',
        );
    }

    $atts_json = json_encode($atts);

    ob_start(); ?>
    <div class="swiper-slider-holder swiper-nav-<?= esc_attr($style) ?> <?= esc_attr($classname) ?>" swiper_atts='<?= esc_attr($atts_json) ?>'>
        <div class="swiper swiper-slider-block" id="<?= esc_attr($swiper_id) ?>">
            <?= $content ?>
            <?php if ($style == 'style-2') { ?>
                <div class="swiper-pagination-navigation-style-2"></div>
            <?php } ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function cm_render_swiper_wrapper($attributes, $content)
{
    ob_start(); ?>
    <div class="swiper-wrapper">
        <?= $content ?>
    </div>
<?php
    return ob_get_clean();
}

function cm_render_swiper_slide($attributes, $content)
{
    $classname = cm_block_classname($attributes);

    ob_start(); ?>
    <div class="swiper-slide <?= esc_attr($classname) ?>">
        <div class="swiper-slide--inner">
            <?= $content ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function cm_render_swiper_pagination($attributes)
{
    ob_start(); ?>
    <div class="swiper-pagination-holder">
        <div class="container">
            <div class="swiper-pagination"> </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function cm_render_swiper_navigation($attributes)
{
    ob_start(); ?>
    <div class="swiper-navigation-holder">
        <div class="container">
            <div class="swiper-button-prev"> </div>
            <div class="swiper-button-next"> </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Rebuild the per-vehicle "posts" structure the Listing Models markup expects
 * from the flat block attributes.
 */
function cm_listing_models_posts($attributes)
{
    $map = array(
        'caravan'   => array('attr' => 'caravanModels',   'taxonomy' => 'caravan_model'),
        'motorhome' => array('attr' => 'motorhomeModels', 'taxonomy' => 'motorhome_model'),
        'campervan' => array('attr' => 'campervanModels', 'taxonomy' => 'campervan_model'),
    );

    $posts = array();
    foreach ($map as $type => $info) {
        if (! empty($attributes[$info['attr']]) && is_array($attributes[$info['attr']])) {
            $posts[] = array(
                '_type'    => $type,
                'taxonomy' => $info['taxonomy'],
                'model'    => array_values($attributes[$info['attr']]),
            );
        }
    }
    return $posts;
}

function cm_render_listing_models($attributes)
{
    $is_swiper             = ! empty($attributes['isSwiper']);
    $display_model_layouts = ! empty($attributes['displayModelLayouts']);
    $posts                 = cm_listing_models_posts($attributes);

    if ($is_swiper) {
        $class1 = 'swiper swiper-listings-taxonomy';
        $class2 = 'swiper-wrapper';
        $class3 = 'swiper-slide h-auto';
    } else {
        $class1 = 'listings-taxonomy-holder';
        $class2 = 'listings-taxonomy-wrapper row g-3';
        $class3 = 'col-lg-12';
    }

    ob_start(); ?>
    <div class="listings listings-style-1" style="--padding: 50% 0; --fit: contain;">
        <div class="container">
            <div class="<?= $class1 ?>">
                <div class="<?= $class2 ?>">
                    <?php foreach ($posts as $post) { ?>
                        <?php foreach ($post['model'] as $key => $model) { ?>
                            <?php
                            $logo  = get__term_meta($model, 'logo', true);
                            $image = get__term_meta($model, 'image', true);
                            $page_id = get__term_page_id($model);
                            $args  = array(
                                'post_type'  => $post['_type'],
                                'numberposts' => -1,
                                'tax_query'  => array(
                                    array(
                                        'taxonomy' => $post['taxonomy'],
                                        'field'    => 'term_id',
                                        'terms'    => $model,
                                    ),
                                ),
                            );
                            $posts_listings = get_posts($args);
                            ?>
                            <div class="<?= $class3 ?> ">
                                <div class="listings--inner h-100 p-4  <?= $display_model_layouts ? 'listings--inner--js has-model-layout' : '' ?>" listing-target=".listings--posts-<?= $key ?>-<?= $post['_type'] ?>-<?= $model ?>">
                                    <?php if ($page_id) { ?>
                                        <a href="<?= get_the_permalink($page_id) ?>" class="listing--model-link"></a>
                                    <?php } ?>
                                    <?php if ($logo) { ?>
                                        <div class="logo-box">
                                            <?= wp_get_attachment_image($logo, 'medium') ?>
                                        </div>
                                    <?php } ?>
                                    <?php if ($image) { ?>
                                        <div class="image-box image-style">
                                            <?= wp_get_attachment_image($image, 'medium') ?>
                                        </div>
                                    <?php } ?>
                                    <div class="model-num d-flex gap-2 align-items-center justify-content-between fs-15">
                                        <span> <?= count($posts_listings) ?> Model<?= count($posts_listings) > 1 ? 's' : '' ?></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4" />
                                        </svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash" viewBox="0 0 16 16">
                                            <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
                <?php if ($is_swiper) { ?>
                    <div class="swiper-button-prev"> </div>
                    <div class="swiper-button-next"> </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php if ($display_model_layouts) { ?>
        <?php foreach ($posts as $type_key => $post) { ?>
            <?php foreach ($post['model'] as $key => $model) { ?>
                <?php
                $args = array(
                    'post_type'   => $post['_type'],
                    'numberposts' => -1,
                    'orderby'     => 'title',
                    'order'       => 'ASC',
                    'tax_query'   => array(
                        array(
                            'taxonomy' => $post['taxonomy'],
                            'field'    => 'term_id',
                            'terms'    => $model,
                        ),
                    ),
                );
                $posts_listings = get_posts($args);
                $page_id        = get__term_page_id($model);
                ?>
                <div class="listings--posts bg-lightgray-2 listings--posts-<?= $key ?>-<?= $post['_type'] ?>-<?= $model ?>">
                    <div class="container  py-5">
                        <div class="row g-3">
                            <?php foreach ($posts_listings as $posts_listing) { ?>
                                <div class="col-lg-3">
                                    <div class="listings--posts--grid bg-white p-4">
                                        <h3 class="fs-24"><?= __listing_title($posts_listing->ID) ?></h3>
                                        <div class="image-box image-style image-style-2 mb-3" style="--fit: contain">
                                            <?= get_the_post_thumbnail($posts_listing->ID, 'medium') ?>
                                        </div>
                                        <?= __listing_features($posts_listing->ID) ?>
                                        <?php if ($page_id) { ?>
                                            <div class="listing--buttons mt-2">
                                                <ul class="d-flex gap-3 m-0 fs-15 p-0 w-100 justify-content-between align-items-center list-inline">
                                                    <li>
                                                        <a class="py-2 px-0 text-decoration-none" href="<?= get_the_permalink($page_id) ?>">
                                                            Explore
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <?php $text = get__theme_option($post['_type'] . '_text') ?>
                        <p class="otr-price mt-4">
                            <?= $text ?>
                        </p>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    <?php } ?>
<?php
    return ob_get_clean();
}

function cm_render_listing_title($attributes)
{
    return __listing_title(get_the_ID(), 'h3', 'fs-24 fw-semibold mb-0');
}

function cm_render_listing_feature($attributes)
{
    return __listing_features(get_the_ID());
}

function cm_render_listing_buttons($attributes)
{
    return __listing_buttons(get_the_ID());
}

/**
 * Video Tour Carousel — a Swiper carousel of the "Video tour" oembed for every
 * post of a chosen post type filed under a chosen model term. Each slide mirrors
 * the offCanvasVideo embed markup from __listing_buttons().
 */
function cm_render_video_tour_carousel($attributes)
{
    $classname = cm_block_classname($attributes);
    $post_type = isset($attributes['postType']) ? $attributes['postType'] : '';
    $model_id  = isset($attributes['modelId']) ? $attributes['modelId'] : '';

    $taxonomy_map = array(
        'caravan'   => 'caravan_model',
        'motorhome' => 'motorhome_model',
        'campervan' => 'campervan_model',
    );

    if (! $post_type || ! isset($taxonomy_map[$post_type]) || ! $model_id) {
        return '';
    }

    $listings = get_posts(array(
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => $taxonomy_map[$post_type],
                'field'    => 'term_id',
                'terms'    => $model_id,
            ),
        ),
    ));

    // Keep only the listings that actually have a video tour, resolving the
    // YouTube embed URL up front (drop anything that doesn't resolve).
    $videos = array();
    foreach ($listings as $listing) {
        $video = get__post_meta_by_id($listing->ID, 'video');
        if (! $video) {
            continue;
        }
        $embed = getYoutubeEmbedUrl($video);
        if (! $embed) {
            continue;
        }
        $videos[] = array('id' => $listing->ID, 'embed' => $embed);
    }

    if (empty($videos)) {
        return '';
    }

    $has_multiple = count($videos) > 1;
    $swiper_id    = 'videoTourCarousel-' . $post_type . '-' . $model_id;
    $atts         = array(
        'slidesPerView' => 1,
        'spaceBetween'  => 25,
    );

    // Only wire up looping + controls when there's more than one video to show.
    if ($has_multiple) {
        $atts['loop']       = true;
        $atts['pagination'] = array(
            'el'        => '#' . $swiper_id . ' .swiper-pagination',
            'clickable' => 'true',
        );
        $atts['navigation'] = array(
            'nextEl' => '#' . $swiper_id . ' .swiper-button-next',
            'prevEl' => '#' . $swiper_id . ' .swiper-button-prev',
        );
    }
    $atts_json = json_encode($atts);

    ob_start(); ?>
    <div class="video-tour-carousel swiper-slider-holder <?= esc_attr($classname) ?>" swiper_atts='<?= esc_attr($atts_json) ?>'>
        <div class="container">
            <div class="swiper swiper-slider-block" id="<?= esc_attr($swiper_id) ?>">
                <div class="swiper-wrapper">
                    <?php foreach ($videos as $video) { ?>
                        <div class="swiper-slide">
                            <div class="video-tour-carousel--slide">
                                <h3 class="video-tour-carousel--title fs-24 mb-3"><?= __listing_title($video['id']) ?></h3>
                                <div class="embed-holder position-relative">
                                    <iframe src="<?= esc_url($video['embed']) ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php if ($has_multiple) { ?>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-pagination"></div>
                <?php } ?>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function cm_render_model_technical_details($attributes)
{
    $button_text = isset($attributes['buttonText']) && $attributes['buttonText'] !== ''
        ? $attributes['buttonText']
        : 'View all features';
    $model_id = isset($attributes['modelId']) ? $attributes['modelId'] : '';

    if (! $model_id) {
        return '';
    }

    $logo              = get__term_meta($model_id, 'logo', true);
    $technical_details = get__term_complex($model_id, 'technical_details');

    ob_start(); ?>
    <div class="wp-block-button is-style-fill">
        <button class="wp-block-button__link w-auto has-white-theme-color has-maroon-background-color has-text-color has-background has-link-color wp-element-button offCanvasModelSpecs" style="border-radius:0px" data-bs-toggle="offcanvas" data-bs-target="#offCanvasModelSpecs-<?= $model_id ?>" aria-controls="offCanvasModelSpecs-<?= $model_id ?>">
            <?= $button_text ?>
        </button>
    </div>
    <div class="offcanvas offcanvas--technical-details offcanvas-end" tabindex="-1" id="offCanvasModelSpecs-<?= $model_id ?>" aria-labelledby="offCanvasModelSpecs-<?= $model_id ?>Label" aria-modal="true" role="dialog">
        <div class="offcanvas-body p-0 overflow-hidden">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"></path>
                </svg>
            </button>
            <div class="offcanvas-body--inner background-white rounded overflow-hidden p-3 p-lg-5 d-flex h-100 flex-column justify-content-between gap-3">
                <div class="top">
                    <div class="title-box d-flex gap-3 align-items-center">
                        <h2><?= wp_get_attachment_image($logo, 'medium') ?></h2>
                    </div>
                    <p class="fs-22 mb-4">Technical details</p>
                    <div class="accordion" id="accordionTechnicalDetails">
                        <?php foreach ((array) $technical_details as $key => $technical_detail) { ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-17 fw-semibold <?= $key == 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $key ?>" aria-expanded="<?= $key == 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $key ?>">
                                        <?= $technical_detail['heading'] ?>
                                    </button>
                                </h2>
                                <div id="collapse<?= $key ?>" class="accordion-collapse collapse <?= $key == 0 ? 'show' : '' ?>" data-bs-parent="#accordionTechnicalDetails">
                                    <div class="accordion-body checklists-holder bg-lightgray-2 fs-14">
                                        <?= wpautop($technical_detail['description']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="bottom">
                    <?= do_shortcode('[template template_id=26276]'); ?>
                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function cm_render_partner($attributes)
{
    $show_logo    = ! isset($attributes['showLogo']) || ! empty($attributes['showLogo']);
    $show_website = ! isset($attributes['showWebsite']) || ! empty($attributes['showWebsite']);

    ob_start();
    if ($show_logo) {
        $attachment_id = get__post_meta('logo');
        echo '<div class="partner-logo">';
        echo wp_get_attachment_image($attachment_id, 'medium');
        echo '</div>';
    }
    if ($show_website) {
        $website = get__post_meta('website');
        echo '<a class="border wp-block-read-more" href="' . esc_url($website) . '" target="_blank">Visit ' . get_the_title() . '</a>';
    }
    return ob_get_clean();
}

function cm_render_event_date($attributes)
{
    $event_date     = get__post_meta('event_date');
    $event_end_date = get__post_meta('event_end_date');

    ob_start();
    if ($event_date) {
        echo date('F j, Y', strtotime($event_date));
    }
    if ($event_end_date && $event_end_date != $event_date) {
        echo ' - ' . date('F j, Y', strtotime($event_end_date));
    }
    return ob_get_clean();
}
