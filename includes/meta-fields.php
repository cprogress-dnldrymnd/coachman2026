<?php

/**
 * Standalone native meta-field framework (replaces Carbon Fields).
 *
 * Declarative registration of post meta boxes, term-meta fields and theme
 * options pages, rendered with native WordPress inputs and stored in native
 * meta / options under the `_{name}` key convention the theme already reads
 * with get__post_meta() / get__term_meta() / get__theme_option().
 *
 * Storage formats (match the theme's readers):
 * - simple fields  -> `_{name}` plain scalar          (text, textarea, select,
 * number, url, oembed,
 * date, rich_text)
 * - image / file   -> `_{name}` attachment ID (int)
 * - gallery        -> `_{name}` array of attachment IDs (int)
 * - association    -> `_{name}` array of post IDs (int)
 * - complex        -> `_{name}` array of rows (WP serialises); rows may nest
 *
 * Field definition array:
 * [
 * 'type'      => 'text|textarea|number|url|select|rich_text|oembed|image|
 * file|gallery|date|association|complex',
 * 'name'      => 'price',              // stored at _price
 * 'label'     => 'Price',
 * 'width'     => 25,                   // optional layout hint (percent)
 * 'desc'      => 'help text',          // optional
 * 'options'   => ['v' => 'Label'],     // select
 * 'post_type' => 'page',               // association target
 * 'max'       => 1,                    // association max selections
 * 'mime'      => 'application/pdf',    // file accept filter
 * 'fields'    => [ ...subfields ],     // complex
 * 'header'    => 'listing_name',       // complex: subfield shown in row head
 * 'button'    => 'Add row',            // complex: add-row button label
 * ]
 *
 * @package Coachman
 * @author  Digitally Disruptive - Donald Raymundo
 * @link    https://digitallydisruptive.co.uk/
 */

if (! defined('ABSPATH')) {
    exit;
}

/* ========================================================================== */
/* Registry                                                                   */
/* ========================================================================== */

final class CM_Meta
{
    /** @var array<int,array> Post meta boxes. */
    public static $boxes = array();

    /** @var array<int,array> Term meta boxes. */
    public static $term_boxes = array();

    /** @var array<int,array> Options pages. */
    public static $options_pages = array();

    /** * Register a post meta box. 
     * * @param array $args The arguments for registering the meta box.
     */
    public static function add_box(array $args)
    {
        $args += array('id' => '', 'title' => '', 'screen' => 'post', 'context' => 'normal', 'priority' => 'default', 'fields' => array());
        self::$boxes[] = $args;
    }

    /** * Register a term meta box (one or more taxonomies). 
     * * @param array $args The arguments for registering the term meta box.
     */
    public static function add_term_box(array $args)
    {
        $args += array('id' => '', 'title' => '', 'taxonomies' => array(), 'fields' => array());
        self::$term_boxes[] = $args;
    }

    /** * Register a theme options page (optionally nested under $parent). 
     * * @param array $args The arguments for registering the options page.
     */
    public static function add_options_page(array $args)
    {
        $args += array('id' => '', 'title' => '', 'menu_title' => '', 'parent' => null, 'capability' => 'manage_options', 'fields' => array());
        if ($args['menu_title'] === '') {
            $args['menu_title'] = $args['title'];
        }
        self::$options_pages[] = $args;
    }

    /** * Flatten the field list for a screen, recursing is NOT needed (top level only saves). 
     * * @param string $post_type The post type to retrieve boxes for.
     * @return array Array of registered boxes for the given screen.
     */
    public static function boxes_for_post_type($post_type)
    {
        $out = array();
        foreach (self::$boxes as $box) {
            $screens = (array) $box['screen'];
            if (in_array($post_type, $screens, true)) {
                $out[] = $box;
            }
        }
        return $out;
    }
}

/* ========================================================================== */
/* Boot hooks                                                                 */
/* ========================================================================== */

add_action('add_meta_boxes', 'cm_meta_register_post_boxes');
add_action('save_post', 'cm_meta_save_post', 10, 2);
add_action('admin_menu', 'cm_meta_register_options_pages');
add_action('admin_enqueue_scripts', 'cm_meta_admin_assets');
add_action('init', 'cm_meta_register_term_boxes', 20);

/** * Wire term add/edit form + save hooks once taxonomies exist. 
 */
function cm_meta_register_term_boxes()
{
    foreach (CM_Meta::$term_boxes as $box) {
        foreach ((array) $box['taxonomies'] as $tax) {
            add_action("{$tax}_add_form_fields", 'cm_meta_term_add_fields', 10, 1);
            add_action("{$tax}_edit_form_fields", 'cm_meta_term_edit_fields', 10, 2);
            add_action("created_{$tax}", 'cm_meta_save_term', 10, 1);
            add_action("edited_{$tax}", 'cm_meta_save_term', 10, 1);
        }
    }
}

/* ========================================================================== */
/* Post meta boxes                                                            */
/* ========================================================================== */

/**
 * Registers meta boxes for the current post type.
 *
 * @param string $post_type Current post type.
 */
function cm_meta_register_post_boxes($post_type)
{
    foreach (CM_Meta::boxes_for_post_type($post_type) as $box) {
        add_meta_box(
            'cm-' . $box['id'],
            $box['title'],
            'cm_meta_render_post_box',
            $box['screen'],
            $box['context'],
            $box['priority'],
            array('box' => $box)
        );
    }
}

/**
 * Renders the HTML inside a post meta box.
 *
 * @param WP_Post $post    The post object.
 * @param array   $metabox Metabox arguments.
 */
function cm_meta_render_post_box($post, $metabox)
{
    $box = $metabox['args']['box'];
    wp_nonce_field('cm_meta_save', 'cm_meta_nonce');
    $values = cm_meta_collect_values($box['fields'], 'post', $post->ID);
    echo cm_meta_render_fields($box['fields'], 'cm_meta', $values);
}

/**
 * Handles saving post meta data on the 'save_post' hook.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function cm_meta_save_post($post_id, $post)
{
    if (! isset($_POST['cm_meta_nonce']) || ! wp_verify_nonce($_POST['cm_meta_nonce'], 'cm_meta_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    $raw = isset($_POST['cm_meta']) && is_array($_POST['cm_meta']) ? wp_unslash($_POST['cm_meta']) : array();

    foreach (CM_Meta::boxes_for_post_type($post->post_type) as $box) {
        cm_meta_save_fields($box['fields'], $raw, 'post', $post_id);
    }
}

/* ========================================================================== */
/* Term meta boxes                                                            */
/* ========================================================================== */

/** * Return the term box whose taxonomies include $taxonomy. 
 *
 * @param string $taxonomy Taxonomy slug.
 * @return array|null The box array or null if none found.
 */
function cm_meta_term_box_for($taxonomy)
{
    foreach (CM_Meta::$term_boxes as $box) {
        if (in_array($taxonomy, (array) $box['taxonomies'], true)) {
            return $box;
        }
    }
    return null;
}

/** * Add-new-term screen (stacked divs, no existing values). 
 *
 * @param string $taxonomy Taxonomy slug.
 */
function cm_meta_term_add_fields($taxonomy)
{
    $box = cm_meta_term_box_for($taxonomy);
    if (! $box) {
        return;
    }
    wp_nonce_field('cm_meta_save', 'cm_meta_nonce');
    echo '<div class="form-field"><div class="cm-meta-box">';
    echo cm_meta_render_fields($box['fields'], 'cm_meta', array());
    echo '</div></div>';
}

/** * Edit-term screen (table rows). 
 *
 * @param WP_Term $term     Term object.
 * @param string  $taxonomy Taxonomy slug.
 */
function cm_meta_term_edit_fields($term, $taxonomy)
{
    $box = cm_meta_term_box_for($taxonomy);
    if (! $box) {
        return;
    }
    $values = cm_meta_collect_values($box['fields'], 'term', $term->term_id);
    echo '<tr class="form-field"><td colspan="2">';
    wp_nonce_field('cm_meta_save', 'cm_meta_nonce');
    echo '<div class="cm-meta-box">';
    if ($box['title']) {
        echo '<h2 style="margin-top:0">' . esc_html($box['title']) . '</h2>';
    }
    echo cm_meta_render_fields($box['fields'], 'cm_meta', $values);
    echo '</div></td></tr>';
}

/**
 * Handles saving term meta data.
 *
 * @param int $term_id Term ID.
 */
function cm_meta_save_term($term_id)
{
    if (! isset($_POST['cm_meta_nonce']) || ! wp_verify_nonce($_POST['cm_meta_nonce'], 'cm_meta_save')) {
        return;
    }
    if (! current_user_can('manage_categories')) {
        return;
    }
    $term = get_term($term_id);
    if (! $term || is_wp_error($term)) {
        return;
    }
    $box = cm_meta_term_box_for($term->taxonomy);
    if (! $box) {
        return;
    }
    $raw = isset($_POST['cm_meta']) && is_array($_POST['cm_meta']) ? wp_unslash($_POST['cm_meta']) : array();
    cm_meta_save_fields($box['fields'], $raw, 'term', $term_id);
}

/* ========================================================================== */
/* Options pages                                                              */
/* ========================================================================== */

/**
 * Registers administrative option pages.
 */
function cm_meta_register_options_pages()
{
    foreach (CM_Meta::$options_pages as $page) {
        $cb = function () use ($page) {
            cm_meta_render_options_page($page);
        };
        if (! empty($page['parent'])) {
            add_submenu_page($page['parent'], $page['title'], $page['menu_title'], $page['capability'], 'cm-opts-' . $page['id'], $cb);
        } else {
            add_menu_page($page['title'], $page['menu_title'], $page['capability'], 'cm-opts-' . $page['id'], $cb, 'dashicons-admin-generic', 59);
        }
    }
}

/**
 * Renders the HTML structure for a given options page.
 *
 * @param array $page Options page configuration array.
 */
function cm_meta_render_options_page($page)
{
    if (! current_user_can($page['capability'])) {
        wp_die(esc_html__('You do not have permission to access this page.', 'glossop-caravans'));
    }

    $saved = false;
    if (isset($_POST['cm_meta_options_save'])) {
        check_admin_referer('cm_meta_save', 'cm_meta_nonce');
        $raw = isset($_POST['cm_meta']) && is_array($_POST['cm_meta']) ? wp_unslash($_POST['cm_meta']) : array();
        cm_meta_save_fields($page['fields'], $raw, 'option', 0);
        $saved = true;
    }

    $values = cm_meta_collect_values($page['fields'], 'option', 0);

    echo '<div class="wrap"><h1>' . esc_html($page['title']) . '</h1>';
    if ($saved) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved.', 'glossop-caravans') . '</p></div>';
    }
    echo '<form method="post" class="cm-meta-box">';
    wp_nonce_field('cm_meta_save', 'cm_meta_nonce');
    echo cm_meta_render_fields($page['fields'], 'cm_meta', $values);
    echo '<p class="submit"><button type="submit" name="cm_meta_options_save" value="1" class="button button-primary">' . esc_html__('Save Changes', 'glossop-caravans') . '</button></p>';
    echo '</form></div>';
}

/* ========================================================================== */
/* Value loading                                                              */
/* ========================================================================== */

/** * Read stored value for a single field from the right backend. 
 *
 * @param string $name      Field name.
 * @param string $context   Context ('post', 'term', 'option').
 * @param int    $object_id Object ID.
 * @return mixed The stored value.
 */
function cm_meta_get_value($name, $context, $object_id)
{
    $key = '_' . $name;
    switch ($context) {
        case 'post':
            return get_post_meta($object_id, $key, true);
        case 'term':
            return get_term_meta($object_id, $key, true);
        case 'option':
            return get_option($key);
    }
    return '';
}

/** * Build a [name => value] map for a field list (top level only). 
 *
 * @param array  $fields    Array of field configurations.
 * @param string $context   Context ('post', 'term', 'option').
 * @param int    $object_id Object ID.
 * @return array Mapped field values.
 */
function cm_meta_collect_values($fields, $context, $object_id)
{
    $values = array();
    foreach ($fields as $field) {
        if (empty($field['name'])) {
            continue;
        }
        $values[$field['name']] = cm_meta_get_value($field['name'], $context, $object_id);
    }
    return $values;
}

/* ========================================================================== */
/* Saving / sanitising                                                        */
/* ========================================================================== */

/** * Sanitise + persist every top-level field from the raw $_POST['cm_meta']. 
 *
 * @param array  $fields    Field configurations.
 * @param array  $raw       Raw posted data.
 * @param string $context   Storage context.
 * @param int    $object_id Object ID.
 */
function cm_meta_save_fields($fields, $raw, $context, $object_id)
{
    foreach ($fields as $field) {
        if (empty($field['name'])) {
            continue;
        }
        $name  = $field['name'];
        $value = isset($raw[$name]) ? $raw[$name] : null;
        $clean = cm_meta_sanitize_field($field, $value);
        cm_meta_persist($name, $clean, $context, $object_id);
    }
}

/** * Write (or delete when empty) one field's value to the right backend. 
 *
 * @param string $name      Field name.
 * @param mixed  $clean     Sanitized value.
 * @param string $context   Storage context.
 * @param int    $object_id Object ID.
 */
function cm_meta_persist($name, $clean, $context, $object_id)
{
    $key   = '_' . $name;
    $empty = ($clean === '' || $clean === null || (is_array($clean) && count($clean) === 0));

    switch ($context) {
        case 'post':
            if ($empty) {
                delete_post_meta($object_id, $key);
            } else {
                update_post_meta($object_id, $key, $clean);
            }
            break;
        case 'term':
            if ($empty) {
                delete_term_meta($object_id, $key);
            } else {
                update_term_meta($object_id, $key, $clean);
            }
            break;
        case 'option':
            if ($empty) {
                delete_option($key);
            } else {
                update_option($key, $clean);
            }
            break;
    }
}

/** * Recursively sanitise a single field's raw value. 
 *
 * @param array $field Field configuration.
 * @param mixed $value Raw value.
 * @return mixed Sanitized value.
 */
function cm_meta_sanitize_field($field, $value)
{
    $type = isset($field['type']) ? $field['type'] : 'text';

    switch ($type) {
        case 'textarea':
            return is_scalar($value) ? sanitize_textarea_field((string) $value) : '';

        case 'rich_text':
            return is_scalar($value) ? wp_kses_post((string) $value) : '';

        case 'url':
            return is_scalar($value) ? esc_url_raw(trim((string) $value)) : '';

        case 'oembed':
            return is_scalar($value) ? esc_url_raw(trim((string) $value)) : '';

        case 'number':
            if (! is_scalar($value) || trim((string) $value) === '') {
                return '';
            }
            return is_numeric($value) ? (string) (0 + $value) : sanitize_text_field((string) $value);

        case 'image':
        case 'file':
            return ($value === '' || $value === null) ? '' : (int) $value;
            
        case 'gallery':
            $ids = is_scalar($value) ? explode(',', (string) $value) : (array) $value;
            return array_values(array_filter(array_map('intval', $ids)));

        case 'date':
            return is_scalar($value) ? sanitize_text_field((string) $value) : '';

        case 'association':
            $ids = is_array($value) ? $value : (($value === '' || $value === null) ? array() : array($value));
            $ids = array_values(array_filter(array_map('intval', $ids)));
            if (isset($field['max']) && $field['max'] > 0) {
                $ids = array_slice($ids, 0, (int) $field['max']);
            }
            return $ids;

        case 'complex':
            return cm_meta_sanitize_complex($field, $value);

        case 'select':
        case 'text':
        default:
            return is_scalar($value) ? sanitize_text_field((string) $value) : '';
    }
}

/** * Sanitise a repeater value: array of rows, each row a map of subfields. 
 *
 * @param array $field Field configuration.
 * @param mixed $value Raw repeater value.
 * @return array Sanitized array of rows.
 */
function cm_meta_sanitize_complex($field, $value)
{
    if (! is_array($value)) {
        return array();
    }
    $subfields = isset($field['fields']) ? $field['fields'] : array();
    $rows = array();

    foreach ($value as $raw_row) {
        if (! is_array($raw_row)) {
            continue;
        }
        $row = array();
        foreach ($subfields as $sub) {
            if (empty($sub['name'])) {
                continue;
            }
            $sub_val = isset($raw_row[$sub['name']]) ? $raw_row[$sub['name']] : null;
            $row[$sub['name']] = cm_meta_sanitize_field($sub, $sub_val);
        }
        if (cm_meta_row_has_content($row)) {
            $rows[] = $row;
        }
    }
    return array_values($rows);
}

/** * True when any leaf in a sanitised row holds a non-empty value. 
 *
 * @param array $row A parsed row structure.
 * @return bool
 */
function cm_meta_row_has_content($row)
{
    foreach ($row as $v) {
        if (is_array($v)) {
            if (count($v) > 0) {
                return true;
            }
        } elseif ($v !== '' && $v !== null) {
            return true;
        }
    }
    return false;
}

/* ========================================================================== */
/* Rendering                                                                  */
/* ========================================================================== */

/**
 * Render a list of fields inside a flex wrapper.
 *
 * @param array  $fields
 * @param string $base    Form-name base, e.g. "cm_meta" or "cm_meta[stocks][0]".
 * @param array  $values  [name => value] for this level.
 * @return string HTML output.
 */
function cm_meta_render_fields($fields, $base, $values)
{
    $html = '<div class="cm-fields">';
    foreach ($fields as $field) {
        $html .= cm_meta_render_field($field, $base, isset($values[$field['name']]) ? $values[$field['name']] : null);
    }
    $html .= '</div>';
    return $html;
}

/** * Render one field wrapped in its layout cell. 
 *
 * @param array  $field Field configuration.
 * @param string $base  Form-name base string.
 * @param mixed  $value Current field value.
 * @return string HTML output.
 */
function cm_meta_render_field($field, $base, $value)
{
    $type  = isset($field['type']) ? $field['type'] : 'text';
    $name  = $field['name'];
    $input = $base . '[' . $name . ']';
    // Keep any {{token}} placeholder intact so cloned repeater rows get unique
    // ids once the token is replaced with a row index client-side.
    $id    = 'cmf_' . str_replace(array('[', ']'), array('_', ''), $input);
    $width = isset($field['width']) ? max(1, min(100, (int) $field['width'])) : 100;
    $label = isset($field['label']) ? $field['label'] : $name;

    $style = 'flex:0 0 ' . $width . '%;max-width:' . $width . '%;';
    $html  = '<div class="cm-field cm-field-' . esc_attr($type) . '" style="' . esc_attr($style) . '">';

    // Complex renders its own header; everything else gets a label.
    if ($type !== 'complex') {
        $html .= '<label class="cm-label" for="' . esc_attr($id) . '">' . esc_html($label) . '</label>';
    }

    $html .= cm_meta_render_input($field, $input, $id, $value);

    if (! empty($field['desc'])) {
        $html .= '<p class="description">' . wp_kses_post($field['desc']) . '</p>';
    }
    $html .= '</div>';
    return $html;
}

/** * Render the input control for a field type. 
 *
 * @param array  $field Field configuration.
 * @param string $input Field input name attribute.
 * @param string $id    Field input ID attribute.
 * @param mixed  $value Field value.
 * @return string HTML output.
 */
function cm_meta_render_input($field, $input, $id, $value)
{
    $type = isset($field['type']) ? $field['type'] : 'text';

    switch ($type) {
        case 'textarea':
            return '<textarea class="cm-input large-text" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" rows="4">' . esc_textarea((string) $value) . '</textarea>';

        case 'rich_text':
            // Plain textarea upgraded to TinyMCE client-side (works inside repeaters).
            return '<textarea class="cm-input cm-richtext large-text" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" rows="6">' . esc_textarea((string) $value) . '</textarea>';

        case 'number':
            return '<input type="number" step="any" class="cm-input" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" value="' . esc_attr((string) $value) . '">';

        case 'url':
        case 'oembed':
            return '<input type="url" class="cm-input regular-text" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" value="' . esc_attr((string) $value) . '" placeholder="https://">';

        case 'date':
            return '<input type="date" class="cm-input" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" value="' . esc_attr((string) $value) . '">';

        case 'select':
            return cm_meta_render_select($field, $input, $id, $value);

        case 'image':
        case 'file':
            return cm_meta_render_media($field, $input, $id, $value);
            
        case 'gallery':
            return cm_meta_render_gallery($field, $input, $id, $value);

        case 'association':
            return cm_meta_render_association($field, $input, $id, $value);

        case 'complex':
            return cm_meta_render_complex($field, $input, $value);

        case 'text':
        default:
            $html_type = isset($field['input']) ? $field['input'] : 'text';
            return '<input type="' . esc_attr($html_type) . '" class="cm-input regular-text" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" value="' . esc_attr((string) $value) . '">';
    }
}

/**
 * Render select dropdown structure.
 *
 * @param array  $field Field configuration.
 * @param string $input Field input name attribute.
 * @param string $id    Field input ID attribute.
 * @param mixed  $value Field value.
 * @return string HTML output.
 */
function cm_meta_render_select($field, $input, $id, $value)
{
    $options = isset($field['options']) ? $field['options'] : array();
    if ($options instanceof Closure) {
        $options = (array) $options();
    }
    $html = '<select class="cm-input" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '">';
    if (empty($field['no_empty'])) {
        $html .= '<option value="">' . esc_html__('— Select —', 'glossop-caravans') . '</option>';
    }
    foreach ($options as $val => $opt_label) {
        $html .= '<option value="' . esc_attr($val) . '"' . selected((string) $value, (string) $val, false) . '>' . esc_html($opt_label) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

/**
 * Render an individual media picker (image or file).
 *
 * @param array  $field Field configuration.
 * @param string $input Field input name attribute.
 * @param string $id    Field input ID attribute.
 * @param mixed  $value Field value.
 * @return string HTML output.
 */
function cm_meta_render_media($field, $input, $id, $value)
{
    $is_image    = (isset($field['type']) && $field['type'] === 'image');
    $att_id      = (int) $value;
    $has         = $att_id > 0;
    $preview     = '';
    if ($has) {
        if ($is_image) {
            $img = wp_get_attachment_image($att_id, 'thumbnail');
            $preview = $img ? $img : '';
        } else {
            $preview = '<span class="cm-file-name">' . esc_html(get_the_title($att_id)) . '</span>';
        }
    }
    $mime = isset($field['mime']) ? $field['mime'] : '';

    $html  = '<div class="cm-media' . ($is_image ? ' cm-media-image' : ' cm-media-file') . '" data-mime="' . esc_attr($mime) . '" data-type="' . ($is_image ? 'image' : 'file') . '">';
    $html .= '<input type="hidden" class="cm-media-id" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" value="' . esc_attr((string) $att_id) . '">';
    $html .= '<div class="cm-media-preview">' . $preview . '</div>';
    $html .= '<button type="button" class="button cm-media-select">' . esc_html__('Select', 'glossop-caravans') . '</button> ';
    $html .= '<button type="button" class="button cm-media-remove"' . ($has ? '' : ' style="display:none"') . '>' . esc_html__('Remove', 'glossop-caravans') . '</button>';
    $html .= '</div>';
    return $html;
}

/**
 * Render a native gallery field for selecting multiple images simultaneously.
 * Retrieves an array of image attachments and outputs preview elements inline.
 *
 * @param array  $field Field configuration.
 * @param string $input Field input name attribute.
 * @param string $id    Field input ID attribute.
 * @param mixed  $value Saved Array of attachment IDs.
 * @return string HTML output.
 */
function cm_meta_render_gallery($field, $input, $id, $value)
{
    $ids        = is_array($value) ? $value : array();
    $ids_string = implode(',', $ids);

    $html  = '<div class="cm-media cm-gallery" data-type="gallery">';
    $html .= '<input type="hidden" class="cm-gallery-ids" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" value="' . esc_attr($ids_string) . '">';
    $html .= '<div class="cm-gallery-preview" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px;">';

    foreach ($ids as $att_id) {
        if ($att_id > 0) {
            $img = wp_get_attachment_image($att_id, 'thumbnail', false, array('style' => 'max-width:80px; height:auto; border:1px solid #ddd;'));
            if ($img) {
                $html .= '<div class="cm-gallery-item" data-id="' . esc_attr((string) $att_id) . '" style="position:relative; display:inline-block;">';
                $html .= $img;
                $html .= '<button type="button" class="cm-gallery-remove-item" style="position:absolute; top:-5px; right:-5px; background:#d63638; color:#fff; border:none; border-radius:50%; cursor:pointer; width:20px; height:20px; line-height:1; padding:0;" title="' . esc_attr__('Remove image', 'glossop-caravans') . '">&times;</button>';
                $html .= '</div>';
            }
        }
    }

    $html .= '</div>';
    $html .= '<button type="button" class="button cm-gallery-select">' . esc_html__('Manage Gallery', 'glossop-caravans') . '</button> ';
    $html .= '<button type="button" class="button cm-gallery-clear"' . (empty($ids) ? ' style="display:none"' : '') . '>' . esc_html__('Clear Gallery', 'glossop-caravans') . '</button>';
    $html .= '</div>';

    return $html;
}

/**
 * Render post association selector.
 *
 * @param array  $field Field configuration.
 * @param string $input Field input name attribute.
 * @param string $id    Field input ID attribute.
 * @param mixed  $value Field value.
 * @return string HTML output.
 */
function cm_meta_render_association($field, $input, $id, $value)
{
    $post_type = isset($field['post_type']) ? $field['post_type'] : 'post';
    $max       = isset($field['max']) ? (int) $field['max'] : 0;

    // Accept both the native format (array of int IDs) and Carbon's legacy
    // "type:subtype:id" strings, so editing a term/post before the migration has
    // run still shows the correct selection (and re-saves it cleanly as IDs).
    $selected = array();
    if (is_array($value)) {
        foreach ($value as $v) {
            if (is_numeric($v)) {
                $selected[] = (int) $v;
            } elseif (is_string($v) && strpos($v, ':') !== false) {
                $parts = explode(':', $v);
                $selected[] = (int) end($parts);
            }
        }
    }

    $posts = get_posts(array(
        'post_type'      => $post_type,
        'post_status'    => array('publish', 'draft', 'private', 'pending'),
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'suppress_filters' => false,
    ));

    $multiple = ($max !== 1);
    $name_attr = $input . ($multiple ? '[]' : '');

    $html = '<select class="cm-input cm-association" id="' . esc_attr($id) . '" name="' . esc_attr($name_attr) . '"' . ($multiple ? ' multiple size="6"' : '') . ' data-max="' . esc_attr((string) $max) . '">';
    if (! $multiple) {
        $html .= '<option value="">' . esc_html__('— Select —', 'glossop-caravans') . '</option>';
    }
    foreach ($posts as $p) {
        $html .= '<option value="' . esc_attr((string) $p->ID) . '"' . (in_array($p->ID, $selected, true) ? ' selected' : '') . '>' . esc_html($p->post_title ? $p->post_title : '(no title) #' . $p->ID) . '</option>';
    }
    $html .= '</select>';
    if ($multiple) {
        $html .= '<p class="description">' . esc_html__('Hold Ctrl/Cmd to select multiple.', 'glossop-caravans') . '</p>';
    }
    return $html;
}

/**
 * Render a repeater. Existing rows are rendered with real indices; a <template>
 * prototype (with a {{token}} index placeholder unique to this repeater path)
 * drives client-side "add row". Nesting works because each level's token is a
 * distinct path string (e.g. "stocks" vs "stocks.years").
 *
 * @param array  $field Field configuration.
 * @param string $input Field input name attribute.
 * @param mixed  $value Array of row configurations.
 * @return string HTML output.
 */
function cm_meta_render_complex($field, $input, $value)
{
    $subfields = isset($field['fields']) ? $field['fields'] : array();
    $label     = isset($field['label']) ? $field['label'] : $field['name'];
    $button    = isset($field['button']) ? $field['button'] : sprintf(__('Add %s', 'glossop-caravans'), $label);
    $header    = isset($field['header']) ? $field['header'] : '';

    // Token = the bracket path of this repeater, e.g. cm_meta[stocks] -> "stocks",
    // cm_meta[stocks][{{stocks}}][years] -> "stocks.years". Stable + unique.
    $token = trim(str_replace(array('cm_meta[', '][', ']', '{{', '}}'), array('', '.', '', '', ''), $input), '.');

    $rows = is_array($value) ? $value : array();

    $html  = '<div class="cm-field-head"><span class="cm-label">' . esc_html($label) . '</span></div>';
    $html .= '<div class="cm-repeater" data-token="' . esc_attr($token) . '">';
    $html .= '<div class="cm-rows">';

    foreach ($rows as $i => $row) {
        $html .= cm_meta_render_complex_row($subfields, $input, (string) $i, $row, $header);
    }
    $html .= '</div>';

    // Prototype for new rows (index = {{token}} placeholder).
    $html .= '<template class="cm-row-prototype">';
    $html .= cm_meta_render_complex_row($subfields, $input, '{{' . $token . '}}', array(), $header);
    $html .= '</template>';

    $html .= '<p><button type="button" class="button cm-add-row">' . esc_html($button) . '</button></p>';
    $html .= '</div>';
    return $html;
}

/** * Render a single repeater row at $index for base $input (e.g. cm_meta[stocks]). 
 *
 * @param array  $subfields    Repeater subfields definition.
 * @param string $input        Base input string.
 * @param string $index        Current row index constraint.
 * @param array  $row          Current row values.
 * @param string $header_field Name of the subfield to use as the row header label.
 * @return string HTML markup for the row segment.
 */
function cm_meta_render_complex_row($subfields, $input, $index, $row, $header_field)
{
    $row_base = $input . '[' . $index . ']';
    $header_text = '';
    if ($header_field && isset($row[$header_field]) && is_scalar($row[$header_field])) {
        $header_text = (string) $row[$header_field];
    }

    $html  = '<div class="cm-row">';
    $html .= '<div class="cm-row-handle"><span class="cm-row-title">' . esc_html($header_text) . '</span>';
    $html .= '<button type="button" class="button-link cm-remove-row" aria-label="' . esc_attr__('Remove row', 'glossop-caravans') . '">&times;</button></div>';
    $html .= '<div class="cm-row-body">';
    $html .= cm_meta_render_fields($subfields, $row_base, is_array($row) ? $row : array());
    $html .= '</div></div>';
    return $html;
}

/* ========================================================================== */
/* Admin assets                                                               */
/* ========================================================================== */

/**
 * Enqueue scripts and styles necessary for meta framework operations.
 *
 * @param string $hook Page hook indicator.
 */
function cm_meta_admin_assets($hook)
{
    // Post/term edit screens, plus our options pages (hooks like
    // "toplevel_page_cm-opts-..." / "{parent}_page_cm-opts-...").
    $screens = array('post.php', 'post-new.php', 'edit-tags.php', 'term.php');
    if (! in_array($hook, $screens, true) && strpos($hook, 'cm-opts-') === false) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_editor();
    wp_enqueue_style('cm-meta-fields', assets_dir . 'admin/meta-fields.css', array(), version);
    wp_enqueue_script('cm-meta-fields', assets_dir . 'admin/meta-fields.js', array('jquery', 'wp-util'), version, true);
}