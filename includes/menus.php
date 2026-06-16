<?php
/*-----------------------------------------------------------------------------------*/
/* Register main menu for Wordpress use
/*-----------------------------------------------------------------------------------*/
function menu_locations()
{

    register_nav_menus(
        array(

            'header-menu'    =>    __('Header Menu'),
            'footer-menu'    =>    __('Footer Menu'),
        )

    );
}

add_action('init', 'menu_locations');



/**
 * Registers the custom submenu block variation.
 */
function custom_submenu_content_block_variation() {
    // Register the block variation.  This JavaScript will run in the editor.
    wp_register_script(
        'custom-submenu-content-variation',
        plugins_url( 'custom-submenu-content-variation.js', __FILE__ ), // Path to your JS file
        array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-navigation' ), // Dependencies
        filemtime( plugin_dir_path( __FILE__ ) . 'custom-submenu-content-variation.js' ), // Gets file modification time
        true // Enqueue in the footer.
    );

    // Pass any data to the JavaScript file.  In this case, we don't need to pass any data, but this is how you would do it.
    wp_localize_script(
        'custom-submenu-content-variation',
        'customSubmenuContent', // Object name in JS
        array(
            'exampleData' => 'Hello from PHP!',
        )
    );

    // Enqueue the script.
    wp_enqueue_script( 'custom-submenu-content-variation' );
}
add_action( 'init', 'custom_submenu_content_block_variation' );

/**
 * Renders the custom submenu content.  This PHP runs when the page is displayed.
 *
 * @param array $block_content The block content.
 * @param array $block The block data.
 * @return string The modified block content.
 */
function render_custom_submenu_content( $block_content, $block ) {
    // Check if this is a navigation submenu block AND it's our custom variation.
    if ( 'core/navigation-submenu' === $block['blockName'] && isset( $block['attrs']['isCustomContent'] ) && $block['attrs']['isCustomContent'] === true ) {
        // Get the custom content from the block attributes.
        $custom_content = isset( $block['attrs']['customContent'] ) ? $block['attrs']['customContent'] : '';

        // Build the output.  This is where you can add your custom HTML.
        $output = '<div class="custom-submenu-content">';
        $output .= do_blocks( $custom_content ); // Render any blocks that might be in the custom content.  VERY IMPORTANT.
        $output .= '</div>';

        // Find the closing tag of the <ul> element.  We want to insert our content *before* it.  This is crucial for correct HTML structure.
        $ul_close_tag_position = strrpos( $block_content, '</ul>' );

        if ( false !== $ul_close_tag_position ) {
            // Insert the custom content before the closing </ul> tag.
            $block_content = substr_replace( $block_content, $output, $ul_close_tag_position, 0 );
        } else {
            //If for some reason the closing tag isn't found, append to the end.
             $block_content .= $output;
        }
    }
    return $block_content;
}
add_filter( 'render_block', 'render_custom_submenu_content', 10, 2 );