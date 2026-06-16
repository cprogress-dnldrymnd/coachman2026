<?php
add_filter('wpsl_templates', 'custom_templates');

function custom_templates($templates)
{

    /**
     * The 'id' is for internal use and must be unique ( since 2.0 ).
     * The 'name' is used in the template dropdown on the settings page.
     * The 'path' points to the location of the custom template,
     * in this case the folder of your active theme.
     */
    $templates[] = array(
        'id'   => 'custom',
        'name' => 'Custom template',
        'path' => get_stylesheet_directory() . '/wpsl-templates/custom.php',
    );

    return $templates;
}

define( 'WPSL_MARKER_URI', dirname( get_bloginfo( 'stylesheet_url') ) . '/wpsl-templates/wpsl-markers/' );

function custom_admin_marker_dir()
{

    $admin_marker_dir = get_stylesheet_directory() . '/wpsl-templates/wpsl-markers/';

    return $admin_marker_dir;
}

add_filter('wpsl_listing_template', 'custom_listing_template');

function custom_listing_template()
{

    global $wpsl_settings, $wpsl;

    $listing_template = '<li class="store--listing"  data-store-id="<%= id %>">' . "\r\n";
    $listing_template .= "\t\t" . '<div>' . "\r\n";
    $listing_template .= "\t\t\t" . '<%= thumb %>' . "\r\n";
    $listing_template .= "\t\t\t\t" . '<h4>' . wpsl_store_header_template('listing') . '</h4>' . "\r\n";
    $listing_template .= "\t\t" . '<div class="address">' . "\r\n";
    $listing_template .= "\t\t\t\t" . '<span class="wpsl-street"><%= address %></span>' . "\r\n";
    $listing_template .= "\t\t\t\t" . '<% if ( address2 ) { %>' . "\r\n";
    $listing_template .= "\t\t\t\t" . '<span class="wpsl-street"><%= address2 %></span>' . "\r\n";
    $listing_template .= "\t\t\t\t" . '<% } %>' . "\r\n";
    $listing_template .= "\t\t\t\t" . '<span>' . wpsl_address_format_placeholders() . '</span>' . "\r\n";
    $listing_template .= "\t\t\t\t" . '<span class="wpsl-country"><%= country %></span>' . "\r\n";
    $listing_template .= "\t\t\t" . '</div>' . "\r\n";

    if ($wpsl_settings['show_contact_details']) {
        $listing_template .= "\t\t\t" . '<div class="wpsl-contact-details">' . "\r\n";
        $listing_template .= "\t\t\t" . '<% if ( phone ) { %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<span><strong>' . esc_html($wpsl->i18n->get_translation('phone_label', __('Phone', 'wpsl'))) . '</strong>: <%= formatPhoneNumber( phone ) %></span>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% } %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% if ( fax ) { %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<span><strong>' . esc_html($wpsl->i18n->get_translation('fax_label', __('Fax', 'wpsl'))) . '</strong>: <%= fax %></span>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% } %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% if ( email ) { %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<span><strong>' . esc_html($wpsl->i18n->get_translation('email_label', __('Email', 'wpsl'))) . '</strong>: <span class="email"><%= email %></span></span>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% } %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% if ( url ) { %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<span><strong>' . esc_html($wpsl->i18n->get_translation('url_label', __('Website', 'wpsl'))) . '</strong>: <%= url %></span>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% } %>' . "\r\n";
        $listing_template .= "\t\t\t" . '</div>' . "\r\n";
    }



    $listing_template .= "\t\t" . '</div>' . "\r\n";

    // Check if we need to show the distance.
    if (!$wpsl_settings['hide_distance']) {
        $listing_template .= "\t\t" . '<div class="distance"><svg xmlns="http://www.w3.org/2000/svg" width="31.226" height="41.617" viewBox="0 0 31.226 41.617"> <path id="pin-coachman" d="M4,15.606A15.421,15.421,0,0,1,11.785,2.123,15.76,15.76,0,0,1,19.609,0a14.689,14.689,0,0,1,7.824,2.122,16.149,16.149,0,0,1,5.7,5.66,15,15,0,0,1,2.081,7.824,12.29,12.29,0,0,1-.874,4.162,34.891,34.891,0,0,1-2.206,4.786Q30.8,26.967,29.1,29.464t-3.329,4.661q-1.623,2.164-3.038,3.829t-2.247,2.705l-.874.957q-.333-.333-.874-1T16.53,38q-1.665-1.956-3.08-3.912t-3.288-4.578a39.468,39.468,0,0,1-3.08-4.994q-1.207-2.372-2.206-4.7A9.223,9.223,0,0,1,4,15.606Zm5.2,0a10.031,10.031,0,0,0,3.038,7.366,10.031,10.031,0,0,0,7.366,3.038,10.031,10.031,0,0,0,7.366-3.038,10.031,10.031,0,0,0,3.038-7.366,9.894,9.894,0,0,0-3.038-7.324A10.4,10.4,0,0,0,19.609,5.2a9.562,9.562,0,0,0-7.366,3.08A10.255,10.255,0,0,0,9.205,15.606Z" transform="translate(-3.989 0.001)" fill="currentColor"/> </svg> <%= distance %> ' . esc_html($wpsl_settings['distance_unit']) . '</div>' . "\r\n";
    }
    $listing_template .= "<div class='listing--buttons'>";
    $listing_template .= "<div class='btn btn-appointment'><a data-bs-toggle='offcanvas' data-bs-target='#offCanvas25605' aria-controls='offCanvas25605'>Request Appointment</a></div>";
    $listing_template .= "\t\t" . '<div class="btn btn-direction"><%= createDirectionUrl() %></div>' . "\r\n";
    $listing_template .= "<div class='btn btn-stock'><a>View Stock <span class='wpcf7-spinner'></span></a></div>";
    $listing_template .= "</div>";

    $listing_template .= "\t" . '</li>' . "\r\n";

    return $listing_template;
}

add_filter('wpsl_admin_marker_dir', 'custom_admin_marker_dir');

add_filter( 'wpsl_info_window_template', 'custom_wpsl_info_window_template_with_website' );

function custom_wpsl_info_window_template_with_website() {

    $info_window_template = '<div data-store-id="<%= id %>" class="wpsl-info-window">' . "\r\n";

    // 1. Store Name and Address Section
    $info_window_template .= '    <div class="wpsl-info-window-title">' . "\r\n";
    $info_window_template .= '        <strong><%= store %></strong>' . "\r\n";
    $info_window_template .= '    </div>' . "\r\n";
    
    $info_window_template .= '    <div class="wpsl-info-window-address">' . "\r\n";
    $info_window_template .= '        <span><%= address %></span>' . "\r\n";
    $info_window_template .= '        <% if ( address2 ) { %>' . "\r\n";
    $info_window_template .= '        <span><%= address2 %></span>' . "\r\n";
    $info_window_template .= '        <% } %>' . "\r\n";
    $info_window_template .= '        <span><%= city %> <%= state %> <%= zip %></span>' . "\r\n";
    $info_window_template .= '    </div><br>' . "\r\n";

    // 2. Phone Section
    $info_window_template .= '    <% if ( phone ) { %>' . "\r\n";
    $info_window_template .= '    <div class="wpsl-info-window-details">' . "\r\n";
    $info_window_template .= '        <strong>Phone:</strong> <%= phone %>' . "\r\n";
    $info_window_template .= '    </div>' . "\r\n";
    $info_window_template .= '    <% } %>' . "\r\n";

    // 3. Email Section (Matches your image: Label on one line, email on next)
    $info_window_template .= '    <% if ( email ) { %>' . "\r\n";
    $info_window_template .= '    <div class="wpsl-info-window-details">' . "\r\n";
    $info_window_template .= '        <strong>Email:</strong>' . "\r\n";
    $info_window_template .= '        <a href="mailto:<%= email %>"><%= email %></a>' . "\r\n";
    $info_window_template .= '    </div>' . "\r\n";
    $info_window_template .= '    <% } %>' . "\r\n";

    // --- NEW SECTION: Website ---
    // Added below Email as requested
    $info_window_template .= '    <% if ( url ) { %>' . "\r\n";
    $info_window_template .= '    <div class="wpsl-info-window-details" style="margin-top: 5px;">' . "\r\n";
    $info_window_template .= '        <strong>Website:</strong> <a href="<%= url %>" target="_blank" rel="noopener noreferrer"><%= url %></a>' . "\r\n";
    $info_window_template .= '    </div>' . "\r\n";
    $info_window_template .= '    <% } %>' . "\r\n";
    // -----------------------------

    // 4. Directions Link
    $info_window_template .= '    <br><div class="wpsl-info-window-directions" style="margin-top: 10px;">' . "\r\n";
    $info_window_template .= '        <%= createDirectionUrl() %>' . "\r\n"; 
    $info_window_template .= '    </div>' . "\r\n";

    $info_window_template .= '</div>' . "\r\n";

    return $info_window_template;
}