<?php

use Carbon_Fields\Container;
use Carbon_Fields\Complex_Container;
use Carbon_Fields\Field;
use Carbon_Fields\Block;

Container::make('post_meta', __('Page Settings'))
    ->where('post_type', '=', 'page')
    ->set_context('side')
    ->add_fields(array(
        Field::make('select', 'header_style', __('Header Style'))
            ->set_options(array(
                'header-default' => 'Default',
                'header-transparent' => 'Transparent',
            )),
    ));
Container::make('post_meta', __('Caravan Properties'))
    ->where('post_type', '=', 'caravan')
    ->add_fields(array(
        Field::make('text', 'price', __('Price'))->set_width(25)->set_attribute('type', 'number'),
        Field::make('text', 'length', __('Length'))->set_width(25),
        Field::make('text', 'layout', __('Layout'))->set_width(25),
        Field::make('text', 'axles', __('Axles'))->set_width(25),
        Field::make('select', 'berths', __('Berths'))->set_width(25)
            ->set_options(array(
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6',
            )),
        Field::make('text', 'interior_length', __('Interior Length'))->set_width(25),
        Field::make('text', 'overall_length', __('Overall Length'))->set_width(25),
        Field::make('text', 'overall_width', __('Overall Width'))->set_width(25),
        Field::make('text', 'overall_height_incl_tv', __('Overall Height (including T.V Aerial)'))->set_width(25),
        Field::make('text', 'overall_height_incl_aircon', __('Overall Height (including Air Conditioning)'))->set_width(25),
        Field::make('text', 'maximum_headroom', __('Maximum Headroom'))->set_width(25),
        Field::make('text', 'wheel_rim', __('Wheel Rim'))->set_width(25),
        Field::make('text', 'tyre_size', __('Tyre Size'))->set_width(25),
        Field::make('text', 'tyre_pressure', __('Tyre Pressure (bar / psi at quoted MTPLM)'))->set_width(25),
        Field::make('textarea', 'bed_sizes', __('Bed Sizes'))->set_width(25),
        Field::make('text', 'mtplm', __('MTPLM'))->set_width(25),
        Field::make('text', 'mass', __('Mass in Running Order'))->set_width(25),
        Field::make('text', 'personal_payload', __('Personal Payload'))->set_width(25),
        Field::make('text', 'max_payload', __('Total / Maximum User Payload'))->set_width(25),
        Field::make('text', 'max_hitch_weight', __('Maximum Hitch Weight'))->set_width(25),
        Field::make('text', 'awning_size', __('Awning Size (Approx. for reference only)'))->set_width(25),
        Field::make('text', 'upper_mtplm', __('Upper MTPLM (Optional weight plate upgrade'))->set_width(75),

        Field::make('oembed', '360_walkthrough', __('360째 Walkthrough'))->set_width(50),
        Field::make('oembed', 'video', __('Video tour'))->set_width(50),
    ));


Container::make('post_meta', __('Motorhome Properties'))
    ->where('post_type', '=', 'motorhome')
    ->add_fields(array(
        Field::make('text', 'price', __('Price'))->set_width(25)->set_attribute('type', 'number'),
        Field::make('text', 'length', __('Length'))->set_width(25),
        Field::make('text', 'layout', __('Layout'))->set_width(25),
        Field::make('text', 'axles', __('Axles'))->set_width(25),
        Field::make('select', 'berths', __('Berths'))->set_width(25)
            ->set_options(array(
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6',
            )),
        Field::make('text', 'travelling_seats', __('Travelling Seats'))->set_width(25),
        Field::make('text', 'overall_length', __('Overall Length'))->set_width(25),
        Field::make('text', 'overall_width', __('Overall Width'))->set_width(25),
        Field::make('text', 'overall_width_incl_mirrors', __('Overall Width (including mirrors extended)'))->set_width(25),
        Field::make('text', 'track_width_front', __('Track Width (Front)'))->set_width(25),
        Field::make('text', 'track_width_rear', __('Track Width (Rear)'))->set_width(25),
        Field::make('text', 'overall_height_incl_aircon', __('Overall Height (including Air Conditioning)'))->set_width(25),
        Field::make('text', 'wheelbase', __('Wheelbase'))->set_width(25),
        Field::make('text', 'tyre_size', __('Tyre Size'))->set_width(25),
        Field::make('text', 'tyre_pressure', __('Tyre Pressure (bar / psi at quoted MTPLM)'))->set_width(25),
        Field::make('textarea', 'bed_sizes', __('Bed Sizes'))->set_width(25),
        Field::make('text', 'mtplm', __('MTPLM'))->set_width(25),
        
        Field::make('text', 'mass', __('Mass in Running Order'))->set_width(25),
        Field::make('text', 'personal_payload', __('Personal Payload'))->set_width(25),
        Field::make('text', 'max_gross_weight', __('Max Gross Weight'))->set_width(25),
        Field::make('text', 'mass_available_for_optional_payload', __('Mass Available for Optional Payload'))->set_width(25),

        Field::make('text', 'conventional_load', __('Conventional Load'))->set_width(25),
        Field::make('text', 'essential_habitation_equipment', __('Essential Habitation Equipment'))->set_width(25),
        Field::make('text', 'optional_equipment', __('Optional Equipment'))->set_width(25),
        Field::make('text', 'personal_effects', __('Personal Effects'))->set_width(25),

        Field::make('text', 'base_vehicle', __('Base Vehicle'))->set_width(100),
        Field::make('oembed', '360_walkthrough', __('360째 Walkthrough'))->set_width(50),
        Field::make('oembed', 'video', __('Video tour'))->set_width(50),
    ));

Container::make('post_meta', __('Brochure Settings'))
    ->where('post_type', '=', 'downloads')
    ->add_fields(array(
        Field::make('file', 'file', __('File'))
            ->set_type(array('application/pdf'))
    ));

$style = 'style="font-weight: bold;  background-color: #45c324; color: #fff; padding: 15px; border-radius: 5px; font-family: Pennypacker; text-transform: uppercase; letter-spacing: 1px; font-size: 20px;"';

Block::make(__('Icon'))
    ->add_fields(array(
        Field::make('html', 'html_start')->set_html("<div $style>Icon</div>"),
        Field::make('color', 'icon_color', __('Color')),
        Field::make('select', 'icon_alignment', __('Alignment'))->set_options(array(
            '' => 'Default',
            'text-center' => 'Center',
            'text-start' => 'Left',
            'text-end' => 'Right',
        ))->set_width(33),
        Field::make('text', 'icon_width', __('Width'))->set_width(33),
        Field::make('text', 'icon_height', __('Height'))->set_width(33),
        Field::make('image', 'icon', __('Icon')),

    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $icon = $fields['icon'];
        $icon_color = $fields['icon_color'];
        $icon_width = $fields['icon_width'];
        $icon_height = $fields['icon_height'];
        $icon_alignment = $fields['icon_alignment'];
?>

    <div class="svg-box <?= $icon_alignment ?> <?= $attributes['className'] ?>" style="color: <?= $icon_color ?>; --svg-width: <?= $icon_width ?>; --svg-height: <?= $icon_height ?>">
        <?= get__media_libray_icons($icon) ?>
    </div>
<?php
    });

Block::make(__('Video Gallery'))
    ->add_fields(array(
        Field::make('html', 'html_start')->set_html("<div $style>Video Gallery Block</div>"),
        Field::make('html', 'html_end')->set_html("<div style='text-align: center'><a class='components-button is-primary target='_blank' href='/wp-admin/edit.php?post_type=videos'>Manage Videos</a></div>"),
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $args = array(
            'post_type' => 'videos',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);
?>

    <div class="video-gallery-box <?= $attributes['className'] ?>">
        <div class="row g-4">
            <?php while ($query->have_posts()) { ?>
                <?php $query->the_post() ?>
                <div class="col-sm-6 col-lg-4">
                    <div class="video-box rounded overflow-hidden position-relative">
                        <?php the_content() ?>
                    </div>
                </div>
            <?php } ?>
            <?php wp_reset_postdata() ?>
        </div>
    </div>
<?php
    });


Block::make(__('Tabs Navigation'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Tabs Navigation</div>")->set_width(50),
        Field::make('text', 'tab_id', '')->set_width(50)->set_classes('crb-field-style-1')
            ->set_attribute('placeholder', 'Tab ID'),
        Field::make('checkbox', 'is_swiper', __('Is Swiper')),
        Field::make('select', 'direction', __('Direction'))
            ->set_options(array(
                '' => 'Default',
                'flex-row' => 'Horizontal',
                'flex-column' => 'Vertical',
            )),
        Field::make('select', 'style', __('Style'))
            ->set_options(array(
                '' => 'Default',
                'style-1' => 'Style 1',
                'style-2' => 'Style 2',
            )),
    ))
    ->set_inner_blocks(true)
    ->set_inner_blocks_position('below')
    ->set_allowed_inner_blocks(array(
        'carbon-fields/tabs-navigation-item',
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
?>
    <div class="container">
        <?php
        if ($fields['is_swiper']) {
            $class1 = 'swiper swiper-nav-tabs-swiper nav-tabs-swiper';
            $class2 = 'swiper-wrapper nav nav-tabs';
        } else {
            $class1 = 'nav-tabs-holder';
            $class2 = 'nav nav-tabs gap-1';
        }
        ?>
        <div class="<?= $class1 ?> overflow-visible sm-margin-bottom nav-tabs-swiper-js <?= $attributes['className'] ?>">
            <ul class="<?= $class2 ?>  <?= $fields['direction'] ?> <?= $fields['style'] ?>" id="<?= $fields['tab_id'] ?>" role="tablist">
                <?= $inner_blocks ?>
            </ul>
        </div>
    </div>
<?php
    });

Block::make(__('Tabs Navigation Item'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Tab Navigation Item</div>")->set_width(50),
        Field::make('text', 'tab_item_id', __(''))->set_width(50)->set_classes('crb-field-style-1')
            ->set_attribute('placeholder', 'Tab Item ID')
    ))
    ->set_parent('carbon-fields/tabs-navigation')
    ->set_inner_blocks(true)
    ->set_inner_blocks_position('below')
    ->set_allowed_inner_blocks(array(
        'core/paragraph',
        'core/image'
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
?>
    <li class="swiper-slide nav-item <?= $attributes['className'] ?>" role="presentation">
        <button class="nav-link" id="<?= $fields['tab_item_id'] ?>" data-bs-toggle="tab" data-bs-target="#<?= $fields['tab_item_id'] ?>-pane" type="button" role="tab" aria-controls="<?= $fields['tab_item_title'] ?>-pane">
            <?= $inner_blocks ?>
        </button>
    </li>

<?php
    });



Block::make(__('Tabs Content'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Tabs Content</div>")->set_width(50),
        Field::make('text', 'tab_id', '')->set_width(50)->set_classes('crb-field-style-1')
            ->set_attribute('placeholder', 'Tab ID')


    ))
    ->set_inner_blocks(true)
    ->set_inner_blocks_position('below')
    ->set_allowed_inner_blocks(array(
        'carbon-fields/tabs-content-item',
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
?>
    <div class="tab-content" id="<?= $fields['tab_id'] ?>">
        <?= $inner_blocks ?>
    </div>
<?php
    });


Block::make(__('Tabs Content Item'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Tabs Content Item</div>")->set_width(50),
        Field::make('text', 'tab_content_id', '')->set_width(50)->set_classes('crb-field-style-1')
            ->set_attribute('placeholder', 'Tab ID')

    ))
    ->set_inner_blocks(true)
    ->set_inner_blocks_position('below')
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
?>
    <div class="tab-pane fade" id="<?= $fields['tab_content_id'] ?>-pane">
        <?= $inner_blocks ?>
    </div>
<?php
    });



Block::make(__('Swiper'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Swiper</div>"),
        Field::make('text', 'swiper_id', __('Swiper ID')),
        Field::make('complex', 'swiper_options')
            ->add_fields('autoplay', array(
                Field::make('text', 'delay', __('delay'))->set_attribute('type', 'number'),
                Field::make('checkbox', 'disableoninteraction', __('disableOnInteraction')),
            ))
            ->add_fields('spacebetween', array(
                Field::make('text', 'spacebetween', __('spaceBetween'))->set_attribute('type', 'number'),
            ))
            ->add_fields('slidesperview', array(
                Field::make('text', 'slidesperview', __('slidesPerView')),
            ))
            ->add_fields('pagination_navigation', array(
                Field::make('checkbox', 'has_pagination', __('Has Pagination')),
                Field::make('checkbox', 'has_navigation', __('Has Navigation')),
                Field::make('select', 'style', __('Pagination & Navigation Style'))
                    ->set_options(array(
                        '' => 'Default',
                        'style-2' => 'Style 2',
                    )),
            ))
            ->set_duplicate_groups_allowed(false)
            ->set_collapsed(true)
    ))
    ->set_inner_blocks(true)
    ->set_inner_blocks_position('below')
    ->set_allowed_inner_blocks(array(
        'carbon-fields/swiper-wrapper',
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $atts = [];
        $swiper_id = $fields['swiper_id'];
        $swiper_options = $fields['swiper_options'];
        $style = '';
        foreach ($swiper_options as $swiper_option) {
            $type = $swiper_option['_type'];
            switch ($type) {
                case 'autoplay':
                    $delay = isset($swiper_option['delay']) ? $swiper_option['delay'] : 3000;
                    $disableoninteraction = isset($swiper_option['disableoninteraction']) ? 'true' : 'false';
                    $atts['autoplay'] = array(
                        'delay' => $delay,
                        'disableOnInteraction' => $disableoninteraction,
                    );
                    break;
                case 'spacebetween':
                    $atts['spaceBetween'] = $swiper_option['spacebetween'];
                    break;
                case 'slidesperview':
                    $atts['slidesPerView'] = $swiper_option['slidesperview'] ? $swiper_option['slidesperview'] : 1;
                    break;
                case 'pagination_navigation':
                    $style = isset($swiper_option['style']) ? $swiper_option['style'] : '';
                    if ($swiper_option['has_pagination']) {
                        $atts['pagination'] = array(
                            'el' => '#' . $swiper_id . ' .swiper-pagination',
                            'clickable' => 'true',
                        );
                    }
                    if ($swiper_option['has_navigation']) {
                        $atts['navigation'] = array(
                            'nextEl' => '#' . $swiper_id . ' .swiper-button-next',
                            'prevEl' => '#' . $swiper_id . ' .swiper-button-prev',
                        );
                    }
                    break;
            }
        }
        $atts_json = json_encode($atts);
?>
    <div class="swiper-slider-holder swiper-nav-<?= $style ?>" <?= $attributes['className'] ?> swiper_atts='<?= $atts_json ?>'>
        <div class="swiper swiper-slider-block" id="<?= $swiper_id ?>">
            <?= $inner_blocks ?>

            <?php if ($style == 'style-2') { ?>
                <div class="swiper-pagination-navigation-style-2">

                </div>
            <?php } ?>
        </div>

    </div>
<?php
    });


Block::make(__('Swiper Wrapper'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>-Swipper Wrapper</div>"),
    ))
    ->set_parent('carbon-fields/swiper')
    ->set_inner_blocks(true)
    ->set_inner_blocks_position('below')
    ->set_allowed_inner_blocks(array(
        'carbon-fields/swiper-slide',
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
?>
    <div class="swiper-wrapper">
        <?= $inner_blocks ?>
    </div>

<?php
    });
Block::make(__('Swiper Pagination'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>-Swipper Pagination</div>"),
    ))
    ->set_parent('carbon-fields/swiper')
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
?>
    <div class="swiper-pagination-holder">
        <div class="container">
            <div class="swiper-pagination"> </div>
        </div>
    </div>
<?php
    });

Block::make(__('Swiper Navigation'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>-Swipper Navigation</div>"),
    ))
    ->set_parent('carbon-fields/swiper')
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
?>
    <div class="swiper-navigation-holder">
        <div class="container">
            <div class="swiper-button-prev"> </div>
            <div class="swiper-button-next"> </div>
        </div>
    </div>
<?php
    });


Block::make(__('Swiper Slide'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>--Swiper Slide</div>"),
    ))
    ->set_parent('carbon-fields/swiper-wrapper')
    ->set_inner_blocks(true)
    ->set_inner_blocks_position('below')
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
?>
    <div class="swiper-slide <?= $attributes['className'] ?>">
        <div class="swiper-slide--inner">
            <?= $inner_blocks ?>
        </div>
    </div>

<?php
    });


Block::make(__('Listing Models'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Caravan/Motohomes Models</div>"),
        Field::make('checkbox', 'is_swiper', __('Is Swiper')),
        Field::make('checkbox', 'display_model_layouts', __('Display Model Layouts')),
        Field::make('complex', 'posts')
            ->add_fields('caravan', array(
                Field::make('text', 'taxonomy', __('Caravan Model'))->set_default_value('caravan_model')->set_classes('hidden'),
                Field::make('multiselect', 'model', __('Caravan Model'))
                    ->add_options(get_taxonomy_terms_wpdb('caravan_model'))
            ))
            ->add_fields('motorhome', array(
                Field::make('text', 'taxonomy', __('Motorhome Model'))->set_default_value('motorhome_model')->set_classes('hidden'),
                Field::make('multiselect', 'model', __('Motorhome Model'))
                    ->add_options(get_taxonomy_terms_wpdb('motorhome_model'))
            ))
		->add_fields('campervan', array(
                Field::make('text', 'taxonomy', __('Campervan Model'))->set_default_value('campervan_model')->set_classes('hidden'),
                Field::make('multiselect', 'model', __('Campervan Model'))
                    ->add_options(get_taxonomy_terms_wpdb('campervan_model'))
            ))
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {

        if ($fields['is_swiper']) {
            $class1 = 'swiper swiper-listings-taxonomy';
            $class2 = 'swiper-wrapper';
            $class3 = 'swiper-slide h-auto';
        } else {
            $class1 = 'listings-taxonomy-holder';
            $class2 = 'listings-taxonomy-wrapper row g-3';
            $class3 = 'col-lg-12';
        }
?>

    <div class="listings listings-style-1" style="--padding: 50% 0; --fit: contain;">
        <div class="container">
            <div class="<?= $class1 ?>">
                <div class="<?= $class2 ?>">
                    <?php foreach ($fields['posts'] as $post) { ?>
                        <?php foreach ($post['model'] as $key => $model) { ?>
                            <?php
                            $logo = get__term_meta($model, 'logo', true);
                            $image = get__term_meta($model, 'image', true);
                            $page = carbon_get_term_meta($model, 'page');
                            $args = array(
                                'post_type' => $post['_type'],
                                'numberposts' => -1,
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => $post['taxonomy'],
                                        'field' => 'term_id',
                                        'terms' => $model,
                                    ),
                                ),
                            );
                            $posts_listings = get_posts($args);
                            ?>
                            <div class="<?= $class3 ?> ">
                                <div class="listings--inner h-100 p-4  <?= $fields['display_model_layouts'] ? 'listings--inner--js has-model-layout' : '' ?>" listing-target=".listings--posts-<?= $key ?>-<?= $post['_type'] ?>-<?= $model ?>">
                                    <?php if ($page) { ?>
                                        <a href="<?= get_the_permalink($page[0]['id']) ?>" class="listing--model-link"></a>
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
                <?php if ($fields['is_swiper']) { ?>
                    <div class="swiper-button-prev"> </div>
                    <div class="swiper-button-next"> </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php if ($fields['display_model_layouts']) { ?>
        <?php foreach ($fields['posts'] as $type_key => $post) { ?>

            <?php foreach ($post['model'] as $key => $model) { ?>
                <?php
                    $args = array(
                        'post_type' => $post['_type'],
                        'numberposts' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC',
                        'tax_query' => array(
                            array(
                                'taxonomy' => $post['taxonomy'],
                                'field' => 'term_id',
                                'terms' => $model,
                            ),
                        ),
                    );
                    $posts_listings = get_posts($args);
                    $page = carbon_get_term_meta($model, 'page');

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
                                        <?php if ($page) { ?>
                                            <div class="listing--buttons mt-2">
                                                <ul class="d-flex gap-3 m-0 fs-15 p-0 w-100 justify-content-between align-items-center list-inline">
                                                    <li>
                                                        <a class="py-2 px-0 text-decoration-none" href="<?= get_the_permalink($page[0]['id']) ?>">
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
                        <?php $text = carbon_get_theme_option($post['_type'] . '_text') ?>
                        <p class="otr-price mt-4">
                            <?= $text ?>
                        </p>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    <?php } ?>
<?php
    });
Block::make(__('Listing Title'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Listing Title</div>"),
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
?>
    <?= __listing_title(get_the_ID(), 'h3', 'fs-24 fw-semibold mb-0') ?>
<?php
    });

Block::make(__('Listing Feature'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Listing Feature</div>"),
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
?>
    <?= __listing_features(get_the_ID()) ?>
<?php
    });

Block::make(__('Listing Buttons'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Listing Buttons</div>"),
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
?>
    <?= __listing_buttons(get_the_ID()) ?>
<?php
    });


Block::make(__('Model Technical Details'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Model Technical Details</div>"),
        Field::make('text', 'button_text', __('Button Text'))->set_default_value('View all technical details'),
        Field::make('complex', 'model')
            ->add_fields('caravan', array(
                Field::make('text', 'taxonomy', __('Caravan Model'))->set_default_value('caravan_model')->set_classes('hidden'),
                Field::make('select', 'model', __('Caravan Model'))
                    ->add_options(get_taxonomy_terms_wpdb('caravan_model'))
            ))
            ->add_fields('motorhome', array(
                Field::make('text', 'taxonomy', __('Motorhome Model'))->set_default_value('motorhome_model')->set_classes('hidden'),
                Field::make('select', 'model', __('Motorhome Model'))
                    ->add_options(get_taxonomy_terms_wpdb('motorhome_model'))
            ))
            ->set_max(1)
            ->set_duplicate_groups_allowed(false)
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $model_id = $fields['model'][0]['model'];
        $logo = get__term_meta($model_id, 'logo', true);
        $technical_details = carbon_get_term_meta($model_id, 'technical_details');


?>
    <div class="wp-block-button is-style-fill">
        <button class="wp-block-button__link w-auto has-white-theme-color has-maroon-background-color has-text-color has-background has-link-color wp-element-button" style="border-radius:0px" data-bs-toggle="offcanvas" data-bs-target="#offCanvasModelSpecs-<?= $model_id ?>" aria-controls="offCanvasModelSpecs-<?= $model_id ?>">
            <?= $fields['button_text'] ?>
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
                        <?php foreach ($technical_details as $key => $technical_detail) { ?>
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
    });

Block::make(__('Partner'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Partner Blocks</div>"),
        Field::make('complex', 'partner_blocks')
            ->add_fields('partner_logo', array(
                Field::make('html', 'html_start')->set_html("<div $style>Partner Logo Block</div>"),
            ))
            ->add_fields('partner_website', array(
                Field::make('html', 'html_start')->set_html("<div $style>Partner Website</div>"),
            ))
            ->set_duplicate_groups_allowed(false)
            ->set_collapsed(true)
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $partner_blocks = $fields['partner_blocks'];
        foreach ($partner_blocks as $partner_block) {
            $type = $partner_block['_type'];
            switch ($type) {
                case 'partner_logo':
                    $attachment_id = get__post_meta('logo');
                    $size = 'medium';
                    echo '<div class="partner-logo">';
                    echo wp_get_attachment_image($attachment_id, $size);
                    echo '</div>';
                    break;
                case 'partner_website':
                    $website = get__post_meta('website');
                    $size = 'medium';
                    echo '<a class="border wp-block-read-more" href="' . $website . '" target="_blank">Visit ' . get_the_title() . '</a>';
                    break;
            }
        }
    });



Container::make('term_meta', __('Model Properties'))
    ->where('term_taxonomy', '=', 'caravan_model')
    ->or_where('term_taxonomy', '=', 'motorhome_model')
    ->or_where('term_taxonomy', '=', 'campervan_model')
    ->add_fields(array(
        Field::make('image', 'logo', __('Logo')),
        Field::make('image', 'image', __('Image')),
        Field::make('association', 'page', __('Page'))
            ->set_types(array(
                array(
                    'type'      => 'post',
                    'post_type' => 'page',
                )
            ))
            ->set_max(1),
        Field::make('complex', 'technical_details', 'Technical details')
            ->add_fields(array(
                Field::make('text', 'heading', __('Heading')),
                Field::make('rich_text', 'description', __('Description')),
            ))
            ->set_header_template('<%- heading %>')
    ));


/**
 * Safely retrieves posts by a given taxonomy and term using WPDB.
 *
 * @param string $taxonomy The taxonomy slug (e.g., 'category', 'product_cat').
 * @param array $terms An array of term slugs (e.g., ['electronics', 'apparel']).
 * @param string $post_type The post type slug (e.g., 'post', 'product').
 * @return array An array of post IDs as keys and post titles as values, or an empty array if no posts are found.
 */
function get_posts_by_taxonomy_wpdb($taxonomy, $terms, $post_type = 'post')
{
    global $wpdb;

    // Sanitize the input to prevent SQL injection.
    // The implode and array_fill create a string of placeholders for the IN clause.
    $terms_in_clause = implode(', ', array_fill(0, count($terms), '%s'));

    // Build the query string with placeholders.
    // We use aliases (p, tr, tt, t) to make the query more readable.
    $sql = "
        SELECT p.ID, p.post_title
        FROM {$wpdb->posts} AS p
        INNER JOIN {$wpdb->term_relationships} AS tr ON (p.ID = tr.object_id)
        INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        INNER JOIN {$wpdb->terms} AS t ON (t.term_id = tt.term_id)
        WHERE 1=1
        AND p.post_type = %s
        AND p.post_status = 'publish'
        AND tt.taxonomy = %s
        AND t.slug IN ({$terms_in_clause})
    ";

    // Prepare the arguments for the query.
    // The first argument is the post type, second is the taxonomy,
    // and the rest are the term slugs.
    $query_args = array_merge([$post_type, $taxonomy], $terms);

    // Prepare the SQL statement for security.
    $prepared_sql = $wpdb->prepare($sql, $query_args);

    // Get the results as an array of objects.
    $posts = $wpdb->get_results($prepared_sql);

    $post_list = [];
    if ($posts) {
        foreach ($posts as $post) {
            $post_list[$post->ID] = $post->post_title;
        }
    }

    return $post_list;
}





Container::make('theme_options', __('Theme Options'))
    ->add_fields(array(
        Field::make('select', 'header', __('Default Header'))
            ->set_options(get_posts_by_taxonomy_wpdb('template_category', ['header'], 'template')),
        Field::make('select', 'footer', __('Default Footer'))
            ->set_options(get_posts_by_taxonomy_wpdb('template_category', ['footer'], 'template')),
    ));

Container::make('theme_options', __('Caravan Settings'))
    ->set_page_parent('edit.php?post_type=caravan')
    ->add_fields(array(
        Field::make('textarea', 'caravan_text', __('Caravan Text'))
    ));

Container::make('theme_options', __('Motorhome Settings'))
    ->set_page_parent('edit.php?post_type=motorhome')
    ->add_fields(array(
        Field::make('rich_text', 'motorhome_text', __('Motorhome Text Short')),
        Field::make('rich_text', 'motorhome_text_long', __('Motorhome Text Long')),
    ));


Container::make('post_meta', __('Dealer Settings'))
    ->where('post_type', '=', 'wpsl_stores')
    ->add_fields(array(
        Field::make('complex', 'stocks', __('Stocks'))
            ->add_fields(array(
                Field::make('text', 'listing_name', __('Listing Name')),
                Field::make('complex', 'years', __('Years'))
                    ->add_fields(array(
                        Field::make('text', 'year', __('Year'))->set_attribute('type', 'number'),
                    ))
                    ->set_layout('tabbed-horizontal')
                    ->set_header_template('<%- year %>')
            ))
            ->set_layout('tabbed-vertical')
            ->set_header_template('<%- listing_name %>')

    ));


Container::make('post_meta', __('Partner Settings'))
    ->where('post_type', '=', 'partners')
    ->set_context('side')
    ->add_fields(array(
        Field::make('image', 'logo', __('Logo')),
        Field::make('text', 'website', __('Website'))->set_attribute('type', 'url'),
    ));


Container::make('post_meta', __('Template Settings'))
    ->where('post_type', '=', 'template')
    ->add_fields(array(
        Field::make('association', 'display_on', __('Display Template On'))
            ->set_types(array(
                array(
                    'type'      => 'post',
                    'post_type' => 'page',
                ),
            ))
    ));


Container::make('post_meta', __('Press Review Settings'))
    ->where('post_type', '=', 'reviews_post_type')
    ->add_fields(array(
        Field::make('file', 'pdf_file', __('PDF File'))
    ));


Container::make('post_meta', __('Events Settings'))
    ->where('post_type', '=', 'events_post_type')
    ->add_fields(array(
        Field::make('date', 'event_date', __('Event Start Date')),
        Field::make('date', 'event_end_date', __('Event End Date'))
    ));


Block::make(__('Event Date'))
    ->add_fields(array(
        Field::make('html', 'html_1')->set_html("<div $style>Event Date</div>"),
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $event_date = get__post_meta('event_date');
        $event_end_date = get__post_meta('event_end_date');
        if ($event_date) {
            echo date('F j, Y', strtotime($event_date));
        }

        if ($event_end_date && $event_end_date != $event_date) {
            echo ' - ' . date('F j, Y', strtotime($event_end_date));
        }
    });
