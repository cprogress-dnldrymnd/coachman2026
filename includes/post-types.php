<?php

class newPostType
{
    public $name;
    public $key;
    public $singular_name;
    public $icon;
    public $supports;
    public $rewrite;
    public $show_in_rest = false;
    public $exclude_from_search = false;
    public $publicly_queryable = true;
    public $show_in_admin_bar = true;
    public $has_archive = true;
    public $hierarchical = false;

    function __construct()
    {

        add_action('init', array($this, 'create_post_type'));
    }


    function create_post_type()
    {
        register_post_type(
            strtolower($this->key),
            array(
                'labels'              => array(
                    'name'               => _x($this->name, 'post type general name'),
                    'singular_name'      => _x($this->singular_name, 'post type singular name'),
                    'menu_name'          => _x($this->name, 'admin menu'),
                    'name_admin_bar'     => _x($this->singular_name, 'add new on admin bar'),
                    'add_new'            => _x('Add New', strtolower($this->name)),
                    'add_new_item'       => __('Add New ' . $this->singular_name),
                    'new_item'           => __('New ' . $this->singular_name),
                    'edit_item'          => __('Edit ' . $this->singular_name),
                    'view_item'          => __('View ' . $this->singular_name),
                    'all_items'          => __('All ' . $this->name),
                    'search_items'       => __('Search ' . $this->name),
                    'parent_item_colon'  => __('Parent :' . $this->name),
                    'not_found'          => __('No ' . strtolower($this->name) . ' found.'),
                    'not_found_in_trash' => __('No ' . strtolower($this->name) . ' found in Trash.')
                ),
                'show_in_rest'        => $this->show_in_rest,
                'supports'            => $this->supports,
                'public'              => true,
                'has_archive'         => $this->has_archive,
                'hierarchical'        => $this->hierarchical,
                'rewrite'             => $this->rewrite,
                'menu_icon'           => $this->icon,
                'capability_type'     => 'page',
                'exclude_from_search' => $this->exclude_from_search,
                'publicly_queryable'  => $this->publicly_queryable,
                'show_in_admin_bar'   => $this->show_in_admin_bar,
            )
        );
    }
}

/*-----------------------------------------------------------------------------------*/
/* Taxonomy
/*-----------------------------------------------------------------------------------*/
class newTaxonomy
{
    public $taxonomy;
    public $post_type;
    public $args;

    function __construct()
    {
        add_action('init', array($this, 'create_taxonomy'));
        add_action('restrict_manage_posts', array($this, 'filter_by_taxonomy'), 10, 2);
        add_filter('manage_' . $this->post_type . '_posts_columns', array($this, 'change_table_column_titles'));
        add_filter('manage_' . $this->post_type . '_posts_custom_column', array($this, 'change_column_rows'), 10, 2);
        add_filter('manage_edit-' . $this->post_type . '_sortable_columns', array($this, 'change_sortable_columns'));
    }

    function create_taxonomy()
    {
        register_taxonomy($this->taxonomy, $this->post_type, $this->args);
    }

    function filter_by_taxonomy($post_type, $which)
    {
        // Apply this only on a specific post type
        if ($this->post_type !== $post_type)
            return;

        // A list of taxonomy slugs to filter by
        $taxonomies = array($this->taxonomy);

        foreach ($taxonomies as $taxonomy_slug) {

            // Retrieve taxonomy data
            $taxonomy_obj = get_taxonomy($taxonomy_slug);
            $taxonomy_name = $taxonomy_obj->labels->name;

            // Retrieve taxonomy terms
            $terms = get_terms($taxonomy_slug);

            // Display filter HTML
            echo "<select name='{$taxonomy_slug}' id='{$taxonomy_slug}' class='postform'>";
            echo '<option value="">' . sprintf(esc_html__('Show All %s', 'text_domain'), $taxonomy_name) . '</option>';
            foreach ($terms as $term) {
                printf(
                    '<option value="%1$s" %2$s>%3$s (%4$s)</option>',
                    $term->slug,
                    ((isset($_GET[$taxonomy_slug]) && ($_GET[$taxonomy_slug] == $term->slug)) ? ' selected="selected"' : ''),
                    $term->name,
                    $term->count
                );
            }
            echo '</select>';
        }
    }
    function change_table_column_titles($columns)
    {
        unset($columns['date']); // temporarily remove, to have custom column before date column
        $columns[$this->taxonomy] = $this->args['label'];
        $columns['date'] = 'Date'; // readd the date column
        return $columns;
    }

    function change_column_rows($column_name, $post_id)
    {
        if ($column_name == $this->taxonomy) {
            echo get_the_term_list($post_id, $this->taxonomy, '', ', ', '') . PHP_EOL;
        }
    }

    function change_sortable_columns($columns)
    {
        $columns[$this->taxonomy] = $this->taxonomy;
        return $columns;
    }
}
$Templates = new newPostType();
$Templates->key = 'template';
$Templates->name = 'Templates';
$Templates->singular_name = 'Template';
$Templates->icon = 'dashicons-layout';
$Templates->show_in_rest = true;
$Templates->supports = array('title', 'editor', 'revisions');
$Templates->exclude_from_search = true;
$Templates->publicly_queryable = true;
$Templates->show_in_admin_bar = true;
$Templates->has_archive = false;



$Templates_Category = new newTaxonomy();
$Templates_Category->taxonomy = 'template_category';
$Templates_Category->post_type = 'template';
$Templates_Category->args = array(
    'label'        => 'Template Category',
    'labels' => array(
        'name'                       => _x('Template Category', 'Taxonomy General Name', 'text_domain'),
        'singular_name'              => _x('Template Category', 'Taxonomy Singular Name', 'text_domain'),
        'menu_name'                  => __('Template Category', 'text_domain'),
        'all_items'                  => __('All Template Category', 'text_domain'),
        'parent_item'                => __('Parent Template Category', 'text_domain'),
        'parent_item_colon'          => __('Parent Item:', 'text_domain'),
        'new_item_name'              => __('New Item Name', 'text_domain'),
        'add_new_item'               => __('Add New Item', 'text_domain'),
        'edit_item'                  => __('Edit Item', 'text_domain'),
        'update_item'                => __('Update Item', 'text_domain'),
        'view_item'                  => __('View Item', 'text_domain'),
        'separate_items_with_commas' => __('Separate items with commas', 'text_domain'),
        'add_or_remove_items'        => __('Add or remove Template Category', 'text_domain'),
        'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
        'popular_items'              => __('Popular Template Category', 'text_domain'),
        'search_items'               => __('Search Template Category', 'text_domain'),
        'not_found'                  => __('Not Found', 'text_domain'),
        'no_terms'                   => __('No Template Category', 'text_domain'),
        'items_list'                 => __('Items list', 'text_domain'),
        'items_list_navigation'      => __('Items list navigation', 'text_domain'),
    ),
    'hierarchical' => true,
    'query_var'    => true,
    'show_in_rest' => true,
    'rewrite'      => array(
        'slug'         => 'caravan-model',
    )
);
/*


$Videos = new newPostType();
$Videos->key = 'videos';
$Videos->name = 'Videos';
$Videos->singular_name = 'Video';
$Videos->icon = 'dashicons-video-alt3';
$Videos->show_in_rest = true;
$Videos->supports = array('title', 'editor', 'revisions');
$Videos->exclude_from_search = true;
$Videos->publicly_queryable = true;
$Videos->show_in_admin_bar = false;
$Videos->has_archive = false;

$Teams = new newPostType();
$Teams->key = 'team';
$Teams->name = 'Teams';
$Teams->singular_name = 'Team';
$Teams->icon = 'dashicons-video-alt3';
$Teams->show_in_rest = true;
$Teams->supports = array('title', 'revisions', 'thumbnail', 'excerpt');
$Teams->exclude_from_search = true;
$Teams->publicly_queryable = true;
$Teams->show_in_admin_bar = false;
$Teams->has_archive = false;


$Testimonials = new newPostType();
$Testimonials->key = 'testimonials';
$Testimonials->name = 'Testimonials';
$Testimonials->singular_name = 'Testimonial';
$Testimonials->icon = 'dashicons-video-alt3';
$Testimonials->show_in_rest = true;
$Testimonials->supports = array('title', 'editor', 'revisions', 'excerpt');
$Testimonials->exclude_from_search = true;
$Testimonials->publicly_queryable = true;
$Testimonials->show_in_admin_bar = false;
$Testimonials->has_archive = false;
*/


$Press_Reviews = new newPostType();
$Press_Reviews->key = 'reviews_post_type';
$Press_Reviews->name = 'Press Reviews';
$Press_Reviews->singular_name = 'Press Review';
$Press_Reviews->icon = 'dashicons-admin-post';
$Press_Reviews->show_in_rest = true;
$Press_Reviews->supports = array('title', 'editor', 'revisions', 'thumbnail', 'excerpt');
$Press_Reviews->exclude_from_search = true;
$Press_Reviews->publicly_queryable = true;
$Press_Reviews->show_in_admin_bar = false;
$Press_Reviews->has_archive = false;
$Press_Reviews->rewrite = array(
    'slug'         => 'press-reviews',
);


$Events = new newPostType();
$Events->key = 'events_post_type';
$Events->name = 'Events';
$Events->singular_name = 'Event';
$Events->icon = 'dashicons-calendar-alt';
$Events->show_in_rest = true;
$Events->supports = array('title', 'editor', 'revisions', 'thumbnail', 'excerpt');
$Events->exclude_from_search = true;
$Events->publicly_queryable = true;
$Events->show_in_admin_bar = false;
$Events->has_archive = false;
$Events->rewrite = array(
    'slug'         => 'events',
);


$Caravans = new newPostType();
$Caravans->key = 'caravan';
$Caravans->name = 'Caravans';
$Caravans->singular_name = 'Caravans';
$Caravans->icon = 'dashicons-editor-ul';
$Caravans->show_in_rest = true;
$Caravans->supports = array('title', 'editor', 'revisions', 'thumbnail', 'excerpt');
$Caravans->exclude_from_search = true;
$Caravans->publicly_queryable = true;
$Caravans->show_in_admin_bar = false;
$Caravans->has_archive = false;



$Caravan_Model = new newTaxonomy();
$Caravan_Model->taxonomy = 'caravan_model';
$Caravan_Model->post_type = 'caravan';
$Caravan_Model->args = array(
    'label'        => 'Caravan Model',
    'labels' => array(
        'name'                       => _x('Caravan Model', 'Taxonomy General Name', 'text_domain'),
        'singular_name'              => _x('Caravan Model', 'Taxonomy Singular Name', 'text_domain'),
        'menu_name'                  => __('Caravan Model', 'text_domain'),
        'all_items'                  => __('All Caravan Model', 'text_domain'),
        'parent_item'                => __('Parent Caravan Model', 'text_domain'),
        'parent_item_colon'          => __('Parent Item:', 'text_domain'),
        'new_item_name'              => __('New Item Name', 'text_domain'),
        'add_new_item'               => __('Add New Item', 'text_domain'),
        'edit_item'                  => __('Edit Item', 'text_domain'),
        'update_item'                => __('Update Item', 'text_domain'),
        'view_item'                  => __('View Item', 'text_domain'),
        'separate_items_with_commas' => __('Separate items with commas', 'text_domain'),
        'add_or_remove_items'        => __('Add or remove Caravan Model', 'text_domain'),
        'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
        'popular_items'              => __('Popular Caravan Model', 'text_domain'),
        'search_items'               => __('Search Caravan Model', 'text_domain'),
        'not_found'                  => __('Not Found', 'text_domain'),
        'no_terms'                   => __('No Caravan Model', 'text_domain'),
        'items_list'                 => __('Items list', 'text_domain'),
        'items_list_navigation'      => __('Items list navigation', 'text_domain'),
    ),
    'hierarchical' => true,
    'query_var'    => true,
    'show_in_rest' => true,
    'rewrite'      => array(
        'slug'         => 'caravan-model',
    )
);
$Motorhomes = new newPostType();
$Motorhomes->key = 'Motorhome';
$Motorhomes->name = 'Motorhomes';
$Motorhomes->singular_name = 'Motorhomes';
$Motorhomes->icon = 'dashicons-editor-ul';
$Motorhomes->show_in_rest = true;
$Motorhomes->supports = array('title', 'editor', 'revisions', 'thumbnail', 'excerpt');
$Motorhomes->exclude_from_search = true;
$Motorhomes->publicly_queryable = true;
$Motorhomes->show_in_admin_bar = false;
$Motorhomes->has_archive = false;



$Motorhome_Model = new newTaxonomy();
$Motorhome_Model->taxonomy = 'motorhome_model';
$Motorhome_Model->post_type = 'motorhome';
$Motorhome_Model->args = array(
    'label'        => 'Motorhome Model',
    'labels' => array(
        'name'                       => _x('Motorhome Model', 'Taxonomy General Name', 'text_domain'),
        'singular_name'              => _x('Motorhome Model', 'Taxonomy Singular Name', 'text_domain'),
        'menu_name'                  => __('Motorhome Model', 'text_domain'),
        'all_items'                  => __('All Motorhome Model', 'text_domain'),
        'parent_item'                => __('Parent Motorhome Model', 'text_domain'),
        'parent_item_colon'          => __('Parent Item:', 'text_domain'),
        'new_item_name'              => __('New Item Name', 'text_domain'),
        'add_new_item'               => __('Add New Item', 'text_domain'),
        'edit_item'                  => __('Edit Item', 'text_domain'),
        'update_item'                => __('Update Item', 'text_domain'),
        'view_item'                  => __('View Item', 'text_domain'),
        'separate_items_with_commas' => __('Separate items with commas', 'text_domain'),
        'add_or_remove_items'        => __('Add or remove Motorhome Model', 'text_domain'),
        'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
        'popular_items'              => __('Popular Motorhome Model', 'text_domain'),
        'search_items'               => __('Search Motorhome Model', 'text_domain'),
        'not_found'                  => __('Not Found', 'text_domain'),
        'no_terms'                   => __('No Motorhome Model', 'text_domain'),
        'items_list'                 => __('Items list', 'text_domain'),
        'items_list_navigation'      => __('Items list navigation', 'text_domain'),
    ),
    'hierarchical' => true,
    'query_var'    => true,
    'show_in_rest' => true,
    'rewrite'      => array(
        'slug'         => 'motorhome-model',
    )
);

$Campervans = new newPostType();
$Campervans->key = 'Campervan';
$Campervans->name = 'Campervans';
$Campervans->singular_name = 'Campervans';
$Campervans->icon = 'dashicons-editor-ul';
$Campervans->show_in_rest = true;
$Campervans->supports = array('title', 'editor', 'revisions', 'thumbnail', 'excerpt');
$Campervans->exclude_from_search = true;
$Campervans->publicly_queryable = true;
$Campervans->show_in_admin_bar = false;
$Campervans->has_archive = false;



$Campervan_Model = new newTaxonomy();
$Campervan_Model->taxonomy = 'campervan_model';
$Campervan_Model->post_type = 'campervan';
$Campervan_Model->args = array(
    'label'        => 'Campervan Model',
    'labels' => array(
        'name'                       => _x('Campervan Model', 'Taxonomy General Name', 'text_domain'),
        'singular_name'              => _x('Campervan Model', 'Taxonomy Singular Name', 'text_domain'),
        'menu_name'                  => __('Campervan Model', 'text_domain'),
        'all_items'                  => __('All Campervan Model', 'text_domain'),
        'parent_item'                => __('Parent Campervan Model', 'text_domain'),
        'parent_item_colon'          => __('Parent Item:', 'text_domain'),
        'new_item_name'              => __('New Item Name', 'text_domain'),
        'add_new_item'               => __('Add New Item', 'text_domain'),
        'edit_item'                  => __('Edit Item', 'text_domain'),
        'update_item'                => __('Update Item', 'text_domain'),
        'view_item'                  => __('View Item', 'text_domain'),
        'separate_items_with_commas' => __('Separate items with commas', 'text_domain'),
        'add_or_remove_items'        => __('Add or remove Campervan Model', 'text_domain'),
        'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
        'popular_items'              => __('Popular Campervan Model', 'text_domain'),
        'search_items'               => __('Search Campervan Model', 'text_domain'),
        'not_found'                  => __('Not Found', 'text_domain'),
        'no_terms'                   => __('No Campervan Model', 'text_domain'),
        'items_list'                 => __('Items list', 'text_domain'),
        'items_list_navigation'      => __('Items list navigation', 'text_domain'),
    ),
    'hierarchical' => true,
    'query_var'    => true,
    'show_in_rest' => true,
    'rewrite'      => array(
        'slug'         => 'campervan-model',
    )
);


$Careers = new newPostType();
$Careers->key = 'careers';
$Careers->name = 'Careers';
$Careers->singular_name = 'Career';
$Careers->icon = 'dashicons-admin-users';
$Careers->show_in_rest = true;
$Careers->supports = array('title', 'editor', 'revisions', 'thumbnail', 'excerpt');
$Careers->exclude_from_search = true;
$Careers->publicly_queryable = true;
$Careers->show_in_admin_bar = false;
$Careers->has_archive = false;



$Careers_Category = new newTaxonomy();
$Careers_Category->taxonomy = 'careers_category';
$Careers_Category->post_type = 'careers';
$Careers_Category->args = array(
    'label'        => 'Careers Category',
    'labels' => array(
        'name'                       => _x('Careers Category', 'Taxonomy General Name', 'text_domain'),
        'singular_name'              => _x('Careers Category', 'Taxonomy Singular Name', 'text_domain'),
        'menu_name'                  => __('Careers Category', 'text_domain'),
        'all_items'                  => __('All Careers Category', 'text_domain'),
        'parent_item'                => __('Parent Careers Category', 'text_domain'),
        'parent_item_colon'          => __('Parent Item:', 'text_domain'),
        'new_item_name'              => __('New Item Name', 'text_domain'),
        'add_new_item'               => __('Add New Item', 'text_domain'),
        'edit_item'                  => __('Edit Item', 'text_domain'),
        'update_item'                => __('Update Item', 'text_domain'),
        'view_item'                  => __('View Item', 'text_domain'),
        'separate_items_with_commas' => __('Separate items with commas', 'text_domain'),
        'add_or_remove_items'        => __('Add or remove Careers Category', 'text_domain'),
        'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
        'popular_items'              => __('Popular Careers Category', 'text_domain'),
        'search_items'               => __('Search Careers Category', 'text_domain'),
        'not_found'                  => __('Not Found', 'text_domain'),
        'no_terms'                   => __('No Careers Category', 'text_domain'),
        'items_list'                 => __('Items list', 'text_domain'),
        'items_list_navigation'      => __('Items list navigation', 'text_domain'),
    ),
    'hierarchical' => true,
    'query_var'    => true,
    'show_in_rest' => true,
    'rewrite'      => array(
        'slug'         => 'careers-category',
    )
);


$Downloads = new newPostType();
$Downloads->key = 'downloads';
$Downloads->name = 'Downloads';
$Downloads->singular_name = 'Download';
$Downloads->icon = 'dashicons-download';
$Downloads->show_in_rest = true;
$Downloads->supports = array('title', 'editor', 'revisions', 'thumbnail', 'excerpt');
$Downloads->exclude_from_search = true;
$Downloads->publicly_queryable = true;
$Downloads->show_in_admin_bar = false;
$Downloads->has_archive = false;


$Downloads_Category = new newTaxonomy();
$Downloads_Category->taxonomy = 'downloads_category';
$Downloads_Category->post_type = 'downloads';
$Downloads_Category->args = array(
    'label'        => 'Downloads Category',
    'labels' => array(
        'name'                       => _x('Downloads Category', 'Taxonomy General Name', 'text_domain'),
        'singular_name'              => _x('Downloads Category', 'Taxonomy Singular Name', 'text_domain'),
        'menu_name'                  => __('Downloads Category', 'text_domain'),
        'all_items'                  => __('All Downloads Category', 'text_domain'),
        'parent_item'                => __('Parent Downloads Category', 'text_domain'),
        'parent_item_colon'          => __('Parent Item:', 'text_domain'),
        'new_item_name'              => __('New Item Name', 'text_domain'),
        'add_new_item'               => __('Add New Item', 'text_domain'),
        'edit_item'                  => __('Edit Item', 'text_domain'),
        'update_item'                => __('Update Item', 'text_domain'),
        'view_item'                  => __('View Item', 'text_domain'),
        'separate_items_with_commas' => __('Separate items with commas', 'text_domain'),
        'add_or_remove_items'        => __('Add or remove Downloads Category', 'text_domain'),
        'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
        'popular_items'              => __('Popular Downloads Category', 'text_domain'),
        'search_items'               => __('Search Downloads Category', 'text_domain'),
        'not_found'                  => __('Not Found', 'text_domain'),
        'no_terms'                   => __('No Downloads Category', 'text_domain'),
        'items_list'                 => __('Items list', 'text_domain'),
        'items_list_navigation'      => __('Items list navigation', 'text_domain'),
    ),
    'hierarchical' => true,
    'query_var'    => true,
    'show_in_rest' => true,
    'rewrite'      => array(
        'slug'         => 'Downloads-category',
    )
);

$FAQs = new newPostType();
$FAQs->key = 'faqs';
$FAQs->name = 'FAQs';
$FAQs->singular_name = 'FAQ';
$FAQs->icon = 'dashicons-info';
$FAQs->show_in_rest = true;
$FAQs->supports = array('title', 'editor', 'revisions', 'thumbnail', 'excerpt');
$FAQs->exclude_from_search = true;
$FAQs->publicly_queryable = true;
$FAQs->show_in_admin_bar = false;
$FAQs->has_archive = false;

$FAQS_Category = new newTaxonomy();
$FAQS_Category->taxonomy = 'faqs_category';
$FAQS_Category->post_type = 'faqs';
$FAQS_Category->args = array(
    'label'        => 'FAQS Category',
    'labels' => array(
        'name'                       => _x('FAQS Category', 'Taxonomy General Name', 'text_domain'),
        'singular_name'              => _x('FAQS Category', 'Taxonomy Singular Name', 'text_domain'),
        'menu_name'                  => __('FAQS Category', 'text_domain'),
        'all_items'                  => __('All FAQS Category', 'text_domain'),
        'parent_item'                => __('Parent FAQS Category', 'text_domain'),
        'parent_item_colon'          => __('Parent Item:', 'text_domain'),
        'new_item_name'              => __('New Item Name', 'text_domain'),
        'add_new_item'               => __('Add New Item', 'text_domain'),
        'edit_item'                  => __('Edit Item', 'text_domain'),
        'update_item'                => __('Update Item', 'text_domain'),
        'view_item'                  => __('View Item', 'text_domain'),
        'separate_items_with_commas' => __('Separate items with commas', 'text_domain'),
        'add_or_remove_items'        => __('Add or remove FAQS Category', 'text_domain'),
        'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
        'popular_items'              => __('Popular FAQS Category', 'text_domain'),
        'search_items'               => __('Search FAQS Category', 'text_domain'),
        'not_found'                  => __('Not Found', 'text_domain'),
        'no_terms'                   => __('No FAQS Category', 'text_domain'),
        'items_list'                 => __('Items list', 'text_domain'),
        'items_list_navigation'      => __('Items list navigation', 'text_domain'),
    ),
    'hierarchical' => true,
    'query_var'    => true,
    'show_in_rest' => true,
    'rewrite'      => array(
        'slug'         => 'faqs-category',
    )
);

$Partners = new newPostType();
$Partners->key = 'partners';
$Partners->name = 'Partners';
$Partners->singular_name = 'Partner';
$Partners->icon = 'dashicons-buddicons-groups';
$Partners->show_in_rest = true;
$Partners->supports = array('title', 'editor', 'revisions', 'thumbnail', 'excerpt');
$Partners->exclude_from_search = true;
$Partners->publicly_queryable = true;
$Partners->show_in_admin_bar = false;
$Partners->has_archive = false;

$Teams = new newPostType();
$Teams->key = 'teams';
$Teams->name = 'Teams';
$Teams->singular_name = 'Team';
$Teams->icon = 'dashicons-groups';
$Teams->show_in_rest = true;
$Teams->supports = array('title', 'editor', 'revisions', 'thumbnail', 'excerpt');
$Teams->exclude_from_search = true;
$Teams->publicly_queryable = true;
$Teams->show_in_admin_bar = false;
$Teams->has_archive = false;



$Timeline = new newPostType();
$Timeline->key = 'timeline';
$Timeline->name = 'Timeline';
$Timeline->singular_name = 'Timeline';
$Timeline->icon = 'dashicons-calendar';
$Timeline->show_in_rest = true;
$Timeline->supports = array('title', 'editor', 'revisions', 'thumbnail', 'excerpt');
$Timeline->exclude_from_search = true;
$Timeline->publicly_queryable = true;
$Timeline->show_in_admin_bar = false;
$Timeline->has_archive = false;
