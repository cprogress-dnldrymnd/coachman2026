<?php
/*-----------------------------------------------------------------------------------*/
/* Define the version so we can easily replace it throughout the theme
/*-----------------------------------------------------------------------------------*/
define('version', 1);
define('theme_dir', get_template_directory_uri() . '/');
define('assets_dir', theme_dir . 'assets/');
define('image_dir', assets_dir . 'images/');
define('vendor_dir', assets_dir . 'vendors/');

/*-----------------------------------------------------------------------------------*/
/* After Theme Setup
/*-----------------------------------------------------------------------------------*/

function action_after_setup_theme()
{
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'action_after_setup_theme');

function action_wp_enqueue_scripts()
{
    wp_enqueue_style('fancybox', vendor_dir . 'fancybox/css/fancybox.css');
    wp_enqueue_style('style', theme_dir . 'style.css');

    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap', vendor_dir . 'bootstrap/dist/js/bootstrap.min.js');
    wp_enqueue_script('swiper', vendor_dir . 'swiper/js/swiper-bundle.min.js');
    wp_enqueue_script('fancybox', vendor_dir . 'fancybox/js/fancybox.umd.js');
    wp_enqueue_script('main', assets_dir . 'javascripts/main.js');
    wp_localize_script('main', 'ajax_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ajax_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'action_wp_enqueue_scripts', 20);

/*-----------------------------------------------------------------------------------*/
/* Register Carbofields
/*-----------------------------------------------------------------------------------*/
add_action('carbon_fields_register_fields', 'tissue_paper_register_custom_fields');
function tissue_paper_register_custom_fields()
{
    require_once('includes/post-meta.php');
}
function get__post_meta($value)
{
    return get_post_meta(get_the_ID(), '_' . $value, true);
}

function get__term_meta($term_id, $value)
{
    return get_term_meta($term_id, '_' . $value, true);
}

function get__post_meta_by_id($id, $value)
{
    return get_post_meta($id, '_' . $value, true);
}
function get__theme_option($value)
{
    return get_option('_' . $value);
}

function arrayKeyStartsWith($array, $prefix)
{
    $matchingKeys = [];
    foreach ($array as $key => $value) {
        if (strpos($key, $prefix) === 0) {
            $matchingKeys[$key] = $value;
        }
    }
    return $matchingKeys;
}

require_once('includes/bootstrap-navwalker.php');
require_once('includes/customizer.php');
require_once('includes/menus.php');
require_once('includes/theme-widgets.php');
require_once('includes/post-types.php');
require_once('includes/shortcodes.php');
require_once('includes/custom-functions.php');
require_once('includes/listing-functions.php');
require_once('includes/hooks.php');
require_once('includes/wpsl.php');
require_once('includes/ajax.php');



/**
 * WordPress CSV to Custom Post Type Importer
 *
 * This script reads a CSV file, creates new posts of the 'downloads' post type,
 * assigns 'downloads_category' taxonomy terms, uploads PDF files from URLs,
 * and sets the uploaded PDF's attachment ID as a post meta field '_file'.
 *
 * IMPORTANT: Always back up your WordPress database and files before running this script.
 *
 * To run this script:
 * 1. Place this code in your theme's functions.php file or a custom plugin.
 * 2. Access the importer via the new "Downloads CSV Importer" menu item under "Tools" in your WordPress admin.
 */

/**
 * Adds a new menu item under 'Tools' in the WordPress admin.
 */
function my_csv_importer_admin_menu()
{
    add_management_page(
        'Downloads CSV Importer',          // Page title
        'Downloads CSV Importer',          // Menu title
        'manage_options',                  // Capability required to access
        'downloads-csv-importer',          // Menu slug
        'my_csv_importer_page_content'     // Function to display the page content
    );
}
add_action('admin_menu', 'my_csv_importer_admin_menu');

/**
 * Displays the content of the admin page for the CSV importer.
 * Handles CSV file upload and triggers the import process.
 */
function my_csv_importer_page_content()
{
    // Check if the current user has capabilities to manage options (e.g., Administrator)
    if (! current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Handle CSV upload and import if the form is submitted
    if (isset($_POST['submit_csv_import']) && check_admin_referer('csv_import_nonce', 'csv_import_nonce_field')) {
        handle_csv_upload_and_import();
    }
?>
    <div class="wrap">
        <h1>Downloads CSV Importer</h1>
        <p>Upload a CSV file to import downloads into your 'downloads' custom post type.</p>
        <p>The CSV file should have the following columns in order:</p>
        <ol>
            <li><strong>Post Title:</strong> The title of the download post.</li>
            <li><strong>Taxonomy Terms:</strong> Comma-separated terms for the 'downloads_category' taxonomy (e.g., "Reports, Annual").</li>
            <li><strong>PDF Link:</strong> The full URL to the PDF file to be uploaded.</li>
        </ol>

        <form action="" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('csv_import_nonce', 'csv_import_nonce_field'); ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="csv_file">Select CSV File</label></th>
                        <td><input type="file" name="csv_file" id="csv_file" accept=".csv" required></td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button('Import CSV', 'primary', 'submit_csv_import'); ?>
        </form>
    </div>
<?php
}

/**
 * Handles the uploaded CSV file and initiates the import process.
 */
function handle_csv_upload_and_import()
{
    // Include WordPress necessary files for media handling
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    if (! isset($_FILES['csv_file']) || empty($_FILES['csv_file']['tmp_name'])) {
        add_settings_error('csv_import_errors', 'csv_file_missing', 'No CSV file uploaded. Please select a file.', 'error');
        return;
    }

    $csv_file = $_FILES['csv_file'];

    // Validate file type
    $file_type = wp_check_filetype($csv_file['name'], null);
    if ($file_type['ext'] !== 'csv') {
        add_settings_error('csv_import_errors', 'invalid_file_type', 'Invalid file type. Please upload a CSV file.', 'error');
        return;
    }

    // Get the temporary path of the uploaded file
    $csv_tmp_path = $csv_file['tmp_name'];

    // Open the CSV file for reading
    $handle = fopen($csv_tmp_path, 'r');

    if ($handle === FALSE) {
        add_settings_error('csv_import_errors', 'file_open_error', 'Could not open uploaded CSV file for reading.', 'error');
        return;
    }

    // Initialize counters
    $imported_count = 0;
    $skipped_count = 0;
    $row_number = 0;

    // Loop through each row in the CSV file
    while (($data = fgetcsv($handle, 0, ',')) !== FALSE) { // Using 0 for max_len for unlimited line length
        $row_number++;

        // Skip the header row (assuming first row is header)
        if ($row_number === 1) {
            continue;
        }

        // Ensure we have at least 3 columns (Title, Category, PDF Link)
        if (count($data) < 3) {
            error_log("CSV Import Warning: Skipping row $row_number due to insufficient columns. Data: " . implode(',', $data));
            $skipped_count++;
            continue;
        }

        // Extract data from CSV columns
        $post_title     = sanitize_text_field($data[0]); // First column: Post Title
        $post_content = (wpautop($data[1])); // Second column: Taxonomy Terms (comma-separated)
        $taxonomy_terms = sanitize_text_field($data[2]); // Second column: Taxonomy Terms (comma-separated)

        // --- 1. Validate extracted data ---
        if (empty($post_title)) {
            error_log("CSV Import Warning: Skipping row $row_number due to empty post title. Data: " . implode(',', $data));
            $skipped_count++;
            continue;
        }

        // Check if a post with this title already exists to prevent duplicates
        $existing_post = get_page_by_title($post_title, OBJECT, 'downloads');
        if ($existing_post) {
            error_log("CSV Import Warning: Skipping row $row_number. Post with title '{$post_title}' already exists (ID: {$existing_post->ID}).");
            $skipped_count++;
            continue;
        }

        // --- 3. Create the 'downloads' post ---
        $post_data = array(
            'post_title'    => $post_title,
            'post_content' => $post_content,
            'post_status'   => 'publish', // Or 'draft' if you want to review them first
            'post_type'     => 'faqs', // Your custom post type name
            'post_author'   => get_current_user_id(), // Assign to the current user
        );

        // Insert the post into the database
        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            error_log("CSV Import Error: Failed to create post '{$post_title}'. Error: " . $post_id->get_error_message());
            $skipped_count++;
            continue;
        } elseif ($post_id === 0) {
            error_log("CSV Import Error: wp_insert_post returned 0 for post '{$post_title}'.");
            $skipped_count++;
            continue;
        } else {
            $imported_count++;
            error_log("CSV Import Success: Post '{$post_title}' created with ID: {$post_id}.");
        }

        // --- 4. Assign Taxonomy Terms ---
        if (! empty($taxonomy_terms)) {
            $terms_array = explode(',', $taxonomy_terms);
            $terms_array = array_map('trim', $terms_array); // Trim whitespace from terms

            // Set the terms for the 'downloads_category' taxonomy
            $set_terms_result = wp_set_object_terms($post_id, $terms_array, 'faqs_category', false); // false = append terms, true = replace terms

            if (is_wp_error($set_terms_result)) {
                error_log("CSV Import Error: Failed to set terms '{$taxonomy_terms}' for post ID {$post_id}. Error: " . $set_terms_result->get_error_message());
            } else {
                error_log("CSV Import Success: Terms '{$taxonomy_terms}' assigned to post ID {$post_id}.");
            }
        } else {
            error_log("CSV Import Warning: No taxonomy terms provided for post ID {$post_id}.");
        }
    }
    // Close the CSV file
    fclose($handle);

    // Provide a summary message and display it to the user
    $message = "CSV Import Finished: Successfully imported {$imported_count} downloads. Skipped {$skipped_count} rows.";
    add_settings_error('csv_import_messages', 'csv_import_success', $message, 'success');
    settings_errors('csv_import_messages'); // Display success message
}

// Display any errors or success messages from the import process
function display_csv_import_admin_notices()
{
    settings_errors('csv_import_errors'); // Display error messages
}
add_action('admin_notices', 'display_csv_import_admin_notices');


// Function to add custom meta query parameters to the front-end query
function query_loop_block_query_vars__artist($query, $block)
{

    if (get_the_ID() == 123) {
        $today = date('Y-m-d');
        $query['meta_query'] = array(
            'relation' => 'AND', // Both conditions below must be met.

            // Clause to filter out past events.
            // Only include posts where 'event_end_date' is today or in the future.
            'end_date_clause' => array(
                'key'     => '_event_end_date',
                'value'   => $today,
                'compare' => '>=', // Greater than or equal to today.
                'type'    => 'DATE',
            ),

            // Clause to identify the field we want to sort by.
            // We'll refer to this clause name in 'orderby'.
            'start_date_clause' => array(
                'key'  => '_event_date',
                'type' => 'DATE', // Ensures WordPress treats this as a date for sorting.
            ),
        );
        $query['orderby'] = array(
            'start_date_clause' => 'ASC', // 'ASC' for ascending (earliest to latest). Use 'DESC' for descending.
        );
    }
    return $query;
}
add_filter('query_loop_block_query_vars', 'query_loop_block_query_vars__artist', 10, 2);