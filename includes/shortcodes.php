<?php

function template($atts)
{
    extract(
        shortcode_atts(
            array(
                'template_id' => '',
            ),
            $atts
        )
    );

    $content_post = get_post($template_id);
    $content = $content_post->post_content;
    $content = apply_filters('the_content', $content);



    return $content;
}
add_shortcode('template', 'template');

function latest_deals()
{
    ob_start();
    get_template_part('template-parts/shortcodes/latest-deals');
    return ob_get_clean();
}

add_shortcode('latest_deals', 'latest_deals');


function listing_grid($atts)
{
    ob_start();
    extract(
        shortcode_atts(
            array(
                'style' => 'style-1',
                'image_id' => 47
            ),
            $atts
        )
    );
    $args['style'] = $style;
    $args['image_id'] = $image_id;
    get_template_part('template-parts/shortcodes/listing-grid', NULL, $args);
    return ob_get_clean();
}

add_shortcode('listing_grid', 'listing_grid');


function listing_grid_full_details($atts)
{
    ob_start();
    extract(
        shortcode_atts(
            array(
                'style' => 'style-1',
                'id'    => 'id'
            ),
            $atts
        )
    );
    $args['style'] = $style;
    $args['id'] = $id;
    get_template_part('template-parts/shortcodes/listing-grid-full-details', NULL, $args);
    return ob_get_clean();
}

add_shortcode('listing_grid_full_details', 'listing_grid_full_details');


function dealer_locator()
{
    ob_start();
    get_template_part('template-parts/shortcodes/dealer-locator');
    return ob_get_clean();
}

add_shortcode('dealer_locator', 'dealer_locator');


function modal($atts)
{
    ob_start();
    extract(
        shortcode_atts(
            array(
                'id'    => ''
            ),
            $atts
        )
    );
?>
    <div class="offcanvas offcanvas-end offcanvas-end---modal" tabindex="-1" id="offCanvas<?= $id ?>" aria-labelledby="offCanvas<?= $id ?>Label">
        <div class="offcanvas-body p-0 overflow-hidden">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"></path>
                </svg>
            </button>
            <div class="offcanvas-body--inner background-white rounded  p-3 p-lg-5 d-flex h-100 flex-column justify-content-between gap-3">
                <div>
                    <?= do_shortcode('[template template_id=' . $id . ']') ?>
                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('modal', 'modal');

function post_title()
{
    return get_the_title();
}
add_shortcode('post_title', 'post_title');


function pdf_file()
{
    $pdf_file = get__post_meta('pdf_file');
    if ($pdf_file) {
        return '<a href="' . esc_url(wp_get_attachment_url($pdf_file)) . '" target="_blank" class="wp-block-button__link has-white-theme-color has-maroon-background-color rounded-0 fs-15 has-text-color has-background wp-element-button d-inline-flex gap-3 flex-wrap"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-down" viewBox="0 0 16 16"> <path fill-rule="evenodd" d="M3.5 10a.5.5 0 0 1-.5-.5v-8a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 0 0 1h2A1.5 1.5 0 0 0 14 9.5v-8A1.5 1.5 0 0 0 12.5 0h-9A1.5 1.5 0 0 0 2 1.5v8A1.5 1.5 0 0 0 3.5 11h2a.5.5 0 0 0 0-1z"/> <path fill-rule="evenodd" d="M7.646 15.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 14.293V5.5a.5.5 0 0 0-1 0v8.793l-2.146-2.147a.5.5 0 0 0-.708.708z"/> </svg> Download PDF</a>';
    }
}
add_shortcode('pdf_file', 'pdf_file');


function motorhome_text() {
    return wpautop(get__theme_option('motorhome_text'));
}
add_shortcode('motorhome_text', 'motorhome_text');

function motorhome_text_long() {
    return wpautop(get__theme_option('motorhome_text_long'));
}
add_shortcode('motorhome_text_long', 'motorhome_text_long');