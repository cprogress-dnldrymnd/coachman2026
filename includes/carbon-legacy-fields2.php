<?php

/**
 * TRANSITIONAL Carbon Fields registrations — complex fields only.
 *
 * Carbon Fields can only *decode* a complex (repeater) value from its flattened
 * meta rows when the field's structure is registered. Since the live
 * definitions were moved to the native CM_Meta framework, the two complex
 * fields must be re-declared here so that:
 *
 *   1. Tools -> Migrate Carbon Meta can read them via carbon_get_*; and
 *   2. the front-end transitional fallbacks (get__post_complex / get__term_complex)
 *      keep rendering existing data until the migration has run.
 *
 * Only `technical_details` (model terms) and `stocks` (wpsl_stores) live here.
 * The association fields (`page`, `display_on`) are NOT registered — they are a
 * single serialized meta row that the migration and readers parse directly,
 * which also avoids two handlers fighting over the same `_page` / `_display_on`
 * key on save.
 *
 * These legacy boxes render alongside the native ones (clearly labelled
 * "legacy") and write to Carbon's own flattened keys, so they never collide with
 * the native `_technical_details` / `_stocks` values.
 *
 * REMOVE this file and its require in functions.php once the migration has been
 * run and verified, then uninstall the Carbon Fields plugin.
 *
 * @package Coachman
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

if (! defined('ABSPATH')) {
    exit;
}

add_action('carbon_fields_register_fields', 'cm_register_legacy_carbon_fields');
function cm_register_legacy_carbon_fields()
{
    if (! class_exists('Carbon_Fields\\Container')) {
        return;
    }

    // technical_details — model term meta (complex).
    Container::make('term_meta', __('Model Properties (legacy — migrate)', 'glossop-caravans'))
        ->where('term_taxonomy', '=', 'caravan_model')
        ->or_where('term_taxonomy', '=', 'motorhome_model')
        ->or_where('term_taxonomy', '=', 'campervan_model')
        ->add_fields(array(
            Field::make('complex', 'technical_details', 'Technical details')
                ->add_fields(array(
                    Field::make('text', 'heading', __('Heading')),
                    Field::make('rich_text', 'description', __('Description')),
                ))
                ->set_header_template('<%- heading %>'),
        ));

    // stocks — wpsl_stores post meta (nested complex).
    Container::make('post_meta', __('Dealer Settings (legacy — migrate)', 'glossop-caravans'))
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
                        ->set_header_template('<%- year %>'),
                ))
                ->set_layout('tabbed-vertical')
                ->set_header_template('<%- listing_name %>'),
        ));
}
