<?php

/**
 * Meta-field definitions for the theme.
 *
 * Migrated off Carbon Fields to the standalone native framework in
 * includes/meta-fields.php. Field values are stored in native post/term meta
 * and options under the `_{name}` key convention the theme reads with
 * get__post_meta() / get__term_meta() / get__theme_option().
 *
 * @package Coachman
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Safely retrieves posts by a given taxonomy and term using WPDB.
 *
 * @param string $taxonomy  The taxonomy slug (e.g. 'template_category').
 * @param array  $terms     Term slugs (e.g. ['header']).
 * @param string $post_type The post type slug.
 * @return array [post ID => post title].
 */
function get_posts_by_taxonomy_wpdb($taxonomy, $terms, $post_type = 'post')
{
    global $wpdb;

    $terms_in_clause = implode(', ', array_fill(0, count($terms), '%s'));

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

    $query_args   = array_merge([$post_type, $taxonomy], $terms);
    $prepared_sql = $wpdb->prepare($sql, $query_args);
    $posts        = $wpdb->get_results($prepared_sql);

    $post_list = [];
    if ($posts) {
        foreach ($posts as $post) {
            $post_list[$post->ID] = $post->post_title;
        }
    }
    return $post_list;
}

/* -------------------------------------------------------------------------- */
/* Shared field sets                                                          */
/* -------------------------------------------------------------------------- */

$berths_options = array('2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6');

/* -------------------------------------------------------------------------- */
/* Page settings                                                              */
/* -------------------------------------------------------------------------- */

CM_Meta::add_box(array(
    'id'      => 'page_settings',
    'title'   => __('Page Settings', 'glossop-caravans'),
    'screen'  => 'page',
    'context' => 'side',
    'fields'  => array(
        array(
            'type'    => 'select',
            'name'    => 'header_style',
            'label'   => __('Header Style', 'glossop-caravans'),
            'options' => array(
                'header-default'     => 'Default',
                'header-transparent' => 'Transparent',
            ),
        ),
    ),
));

/* -------------------------------------------------------------------------- */
/* Caravan properties                                                         */
/* -------------------------------------------------------------------------- */

CM_Meta::add_box(array(
    'id'     => 'caravan_properties',
    'title'  => __('Caravan Properties', 'glossop-caravans'),
    'screen' => 'caravan',
    'fields' => array(
        array('type' => 'text', 'input' => 'number', 'name' => 'price', 'label' => __('Price', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'length', 'label' => __('Length', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'layout', 'label' => __('Layout', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'axles', 'label' => __('Axles', 'glossop-caravans'), 'width' => 25),
        array('type' => 'select', 'name' => 'berths', 'label' => __('Berths', 'glossop-caravans'), 'width' => 25, 'options' => $berths_options),
        array('type' => 'text', 'name' => 'interior_length', 'label' => __('Interior Length', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'overall_length', 'label' => __('Overall Length', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'overall_width', 'label' => __('Overall Width', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'overall_height_incl_tv', 'label' => __('Overall Height (including T.V Aerial)', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'overall_height_incl_aircon', 'label' => __('Overall Height (including Air Conditioning)', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'maximum_headroom', 'label' => __('Maximum Headroom', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'wheel_rim', 'label' => __('Wheel Rim', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'tyre_size', 'label' => __('Tyre Size', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'tyre_pressure', 'label' => __('Tyre Pressure (bar / psi at quoted MTPLM)', 'glossop-caravans'), 'width' => 25),
        array('type' => 'textarea', 'name' => 'bed_sizes', 'label' => __('Bed Sizes', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'mtplm', 'label' => __('MTPLM', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'mass', 'label' => __('Mass in Running Order', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'personal_payload', 'label' => __('Personal Payload', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'max_payload', 'label' => __('Total / Maximum User Payload', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'max_hitch_weight', 'label' => __('Maximum Hitch Weight', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'awning_size', 'label' => __('Awning Size (Approx. for reference only)', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'upper_mtplm', 'label' => __('Upper MTPLM (Optional weight plate upgrade)', 'glossop-caravans'), 'width' => 75),
        array('type' => 'oembed', 'name' => '360_walkthrough', 'label' => __('360 Walkthrough', 'glossop-caravans'), 'width' => 50),
        array('type' => 'oembed', 'name' => 'video', 'label' => __('Video tour', 'glossop-caravans'), 'width' => 50),
    ),
));

/* -------------------------------------------------------------------------- */
/* Motorhome properties                                                       */
/* -------------------------------------------------------------------------- */

CM_Meta::add_box(array(
    'id'     => 'motorhome_properties',
    'title'  => __('Motorhome Properties', 'glossop-caravans'),
    'screen' => 'motorhome',
    'fields' => array(
        array('type' => 'text', 'input' => 'number', 'name' => 'price', 'label' => __('Price', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'length', 'label' => __('Length', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'layout', 'label' => __('Layout', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'axles', 'label' => __('Axles', 'glossop-caravans'), 'width' => 25),
        array('type' => 'select', 'name' => 'berths', 'label' => __('Berths', 'glossop-caravans'), 'width' => 25, 'options' => $berths_options),
        array('type' => 'text', 'name' => 'travelling_seats', 'label' => __('Travelling Seats', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'overall_length', 'label' => __('Overall Length', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'overall_width', 'label' => __('Overall Width', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'overall_width_incl_mirrors', 'label' => __('Overall Width (including mirrors extended)', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'track_width_front', 'label' => __('Track Width (Front)', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'track_width_rear', 'label' => __('Track Width (Rear)', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'overall_height_incl_aircon', 'label' => __('Overall Height (including Air Conditioning)', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'wheelbase', 'label' => __('Wheelbase', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'tyre_size', 'label' => __('Tyre Size', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'tyre_pressure', 'label' => __('Tyre Pressure (bar / psi at quoted MTPLM)', 'glossop-caravans'), 'width' => 25),
        array('type' => 'textarea', 'name' => 'bed_sizes', 'label' => __('Bed Sizes', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'mtplm', 'label' => __('MTPLM', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'mass', 'label' => __('Mass in Running Order', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'personal_payload', 'label' => __('Personal Payload', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'max_gross_weight', 'label' => __('Max Gross Weight', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'mass_available_for_optional_payload', 'label' => __('Mass Available for Optional Payload', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'conventional_load', 'label' => __('Conventional Load', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'essential_habitation_equipment', 'label' => __('Essential Habitation Equipment', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'optional_equipment', 'label' => __('Optional Equipment', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'personal_effects', 'label' => __('Personal Effects', 'glossop-caravans'), 'width' => 25),
        array('type' => 'text', 'name' => 'base_vehicle', 'label' => __('Base Vehicle', 'glossop-caravans'), 'width' => 100),
        array('type' => 'oembed', 'name' => '360_walkthrough', 'label' => __('360 Walkthrough', 'glossop-caravans'), 'width' => 50),
        array('type' => 'oembed', 'name' => 'video', 'label' => __('Video tour', 'glossop-caravans'), 'width' => 50),
    ),
));

/* -------------------------------------------------------------------------- */
/* Brochure (downloads)                                                       */
/* -------------------------------------------------------------------------- */

CM_Meta::add_box(array(
    'id'     => 'brochure_settings',
    'title'  => __('Brochure Settings', 'glossop-caravans'),
    'screen' => 'downloads',
    'fields' => array(
        array('type' => 'file', 'name' => 'file', 'label' => __('File', 'glossop-caravans'), 'mime' => 'application/pdf'),
    ),
));

/* -------------------------------------------------------------------------- */
/* Model properties (term meta)                                               */
/* -------------------------------------------------------------------------- */

CM_Meta::add_term_box(array(
    'id'         => 'model_properties',
    'title'      => __('Model Properties', 'glossop-caravans'),
    'taxonomies' => array('caravan_model', 'motorhome_model', 'campervan_model'),
    'fields'     => array(
        array('type' => 'image', 'name' => 'logo', 'label' => __('Logo', 'glossop-caravans'), 'width' => 50),
        array('type' => 'image', 'name' => 'image', 'label' => __('Image', 'glossop-caravans'), 'width' => 50),
        array('type' => 'association', 'name' => 'page', 'label' => __('Page', 'glossop-caravans'), 'post_type' => 'page', 'max' => 1),
        array(
            'type'   => 'complex',
            'name'   => 'technical_details',
            'label'  => __('Technical details', 'glossop-caravans'),
            'header' => 'heading',
            'button' => __('Add technical detail', 'glossop-caravans'),
            'fields' => array(
                array('type' => 'text', 'name' => 'heading', 'label' => __('Heading', 'glossop-caravans')),
                array('type' => 'rich_text', 'name' => 'description', 'label' => __('Description', 'glossop-caravans')),
            ),
        ),
    ),
));

/* -------------------------------------------------------------------------- */
/* Dealer settings (nested repeater)                                          */
/* -------------------------------------------------------------------------- */

CM_Meta::add_box(array(
    'id'     => 'dealer_settings',
    'title'  => __('Dealer Settings', 'glossop-caravans'),
    'screen' => 'wpsl_stores',
    'fields' => array(
        array(
            'type'   => 'complex',
            'name'   => 'stocks',
            'label'  => __('Stocks', 'glossop-caravans'),
            'header' => 'listing_name',
            'button' => __('Add stock', 'glossop-caravans'),
            'fields' => array(
                array('type' => 'text', 'name' => 'listing_name', 'label' => __('Listing Name', 'glossop-caravans')),
                array(
                    'type'   => 'complex',
                    'name'   => 'years',
                    'label'  => __('Years', 'glossop-caravans'),
                    'header' => 'year',
                    'button' => __('Add year', 'glossop-caravans'),
                    'fields' => array(
                        array('type' => 'number', 'name' => 'year', 'label' => __('Year', 'glossop-caravans'), 'width' => 50),
                    ),
                ),
            ),
        ),
    ),
));

/* -------------------------------------------------------------------------- */
/* Partner settings                                                           */
/* -------------------------------------------------------------------------- */

CM_Meta::add_box(array(
    'id'      => 'partner_settings',
    'title'   => __('Partner Settings', 'glossop-caravans'),
    'screen'  => 'partners',
    'context' => 'side',
    'fields'  => array(
        array('type' => 'image', 'name' => 'logo', 'label' => __('Logo', 'glossop-caravans')),
        array('type' => 'url', 'name' => 'website', 'label' => __('Website', 'glossop-caravans')),
    ),
));

/* -------------------------------------------------------------------------- */
/* Template settings                                                          */
/* -------------------------------------------------------------------------- */

CM_Meta::add_box(array(
    'id'     => 'template_settings',
    'title'  => __('Template Settings', 'glossop-caravans'),
    'screen' => 'template',
    'fields' => array(
        array('type' => 'association', 'name' => 'display_on', 'label' => __('Display Template On', 'glossop-caravans'), 'post_type' => 'page'),
    ),
));

/* -------------------------------------------------------------------------- */
/* Press reviews                                                              */
/* -------------------------------------------------------------------------- */

CM_Meta::add_box(array(
    'id'     => 'press_review_settings',
    'title'  => __('Press Review Settings', 'glossop-caravans'),
    'screen' => 'reviews_post_type',
    'fields' => array(
        array('type' => 'file', 'name' => 'pdf_file', 'label' => __('PDF File', 'glossop-caravans')),
    ),
));

/* -------------------------------------------------------------------------- */
/* Events                                                                     */
/* -------------------------------------------------------------------------- */

CM_Meta::add_box(array(
    'id'     => 'events_settings',
    'title'  => __('Events Settings', 'glossop-caravans'),
    'screen' => 'events_post_type',
    'fields' => array(
        array('type' => 'date', 'name' => 'event_date', 'label' => __('Event Start Date', 'glossop-caravans'), 'width' => 50),
        array('type' => 'date', 'name' => 'event_end_date', 'label' => __('Event End Date', 'glossop-caravans'), 'width' => 50),
    ),
));

/* -------------------------------------------------------------------------- */
/* Theme options pages                                                        */
/* -------------------------------------------------------------------------- */

CM_Meta::add_options_page(array(
    'id'     => 'theme_options',
    'title'  => __('Theme Options', 'glossop-caravans'),
    'fields' => array(
        // Lazy options so the lookups run only when the page renders, not on
        // every front-end request when these definitions are loaded.
        array('type' => 'select', 'name' => 'header', 'label' => __('Default Header', 'glossop-caravans'), 'options' => function () {
            return get_posts_by_taxonomy_wpdb('template_category', ['header'], 'template');
        }),
        array('type' => 'select', 'name' => 'footer', 'label' => __('Default Footer', 'glossop-caravans'), 'options' => function () {
            return get_posts_by_taxonomy_wpdb('template_category', ['footer'], 'template');
        }),
    ),
));

CM_Meta::add_options_page(array(
    'id'     => 'caravan_settings',
    'title'  => __('Caravan Settings', 'glossop-caravans'),
    'parent' => 'edit.php?post_type=caravan',
    'fields' => array(
        array('type' => 'textarea', 'name' => 'caravan_text', 'label' => __('Caravan Text', 'glossop-caravans'), 'width' => 100),
    ),
));

CM_Meta::add_options_page(array(
    'id'     => 'motorhome_settings',
    'title'  => __('Motorhome Settings', 'glossop-caravans'),
    'parent' => 'edit.php?post_type=motorhome',
    'fields' => array(
        array('type' => 'rich_text', 'name' => 'motorhome_text', 'label' => __('Motorhome Text Short', 'glossop-caravans'), 'width' => 100),
        array('type' => 'rich_text', 'name' => 'motorhome_text_long', 'label' => __('Motorhome Text Long', 'glossop-caravans'), 'width' => 100),
    ),
));
