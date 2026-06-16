<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package orca
 */

$footer = get__theme_option('footer');

?>
<footer class="main-footer">
    <?php echo do_shortcode('[template template_id=' . $footer . ']'); ?>
</footer>
<?php

if (is_page()) {
    $templates = get_posts(array(
        'post_type' => 'template',
        'fields' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'template_category',
                'field'    => 'slug',
                'terms'    => 'modal'
            )
        ),
        'meta_query' => array(
            array(
                'key'   => '_display_on',
                'value' => get_the_ID(),
                'compare' => 'LIKE',
            )
        )
    ));

    foreach ($templates as $template) {
        echo do_shortcode('[modal id=' . $template . ']');
    }
}


?>

</div><!-- #page -->

<?php wp_footer(); ?>

<?php
get_template_part('template-parts/offcanvas/menu');
?>

</body>

</html>