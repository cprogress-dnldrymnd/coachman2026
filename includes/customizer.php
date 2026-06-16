<?php

/**
 * Add site logo field to customizer site identity.
 *
 * @package glossop_caravans (Replace with your theme's name)
 */

if (! function_exists('glossop_caravans_customize_register')) { // Use a unique function name
    /**
     * Register customizer options.
     *
     * @param WP_Customize_Manager $wp_customize Theme Customizer object.
     */
    function glossop_caravans_customize_register($wp_customize)
    { // Use a unique function name

        // Add site logo.
        $wp_customize->add_setting(
            'site_logo',
            array(
                'capability'  => 'edit_theme_options',
                'sanitize_callback' => 'absint', // Sanitize as an integer (attachment ID).
            )
        );
        $wp_customize->add_control(
            new WP_Customize_Media_Control(
                $wp_customize,
                'site_logo',
                array(
                    'label'       => __('Site Logo', 'glossop_caravans'), // Replace with your theme's text domain
                    'section'     => 'title_tagline',
                    'description' => __('Select the logo for your site.', 'glossop_caravans'), // Replace with your theme's text domain
                )
            )
        );
    }
    add_action('customize_register', 'glossop_caravans_customize_register'); // Use the same unique function name
}

if (! function_exists('glossop_caravans_get_site_logo')) {
    /**
     * Get site logo.
     *
     * @return string|false URL of the site logo or false if not set.
     */
    function glossop_caravans_get_site_logo()
    {
        $logo_id = get_theme_mod('site_logo');
        if ($logo_id) {
            $logo_url = wp_get_attachment_image_src($logo_id, 'full'); // 'full' size or any other size you need
            if ($logo_url) {
                return $logo_url[0]; // Return the URL.
            }
        }
        return false;
    }
}

if (! function_exists('glossop_caravans_display_site_logo')) {
    /**
     * Display site logo.  You would call this function in your theme's template files.
     */
    function glossop_caravans_display_site_logo($site_url = false)
    {
        $logo_url = glossop_caravans_get_site_logo();
        if ($logo_url) {
            if ($site_url == true) {
                echo '<a href="' . esc_url(home_url('/')) . '" class="site-logo">';
            } else {
                echo '<div class="site-logo">';
            }
?>
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="site-logo-img">
<?php
            if ($site_url == true) {
                echo '</a>';
            } else {
                echo '</div>';
            }
        } else {

            // You might want to display the site title if no logo is set.
            echo '<h1 class="site-title">' . esc_html(get_bloginfo('name')) . '</h1>';
        }
    }
}
