<?php
/*-----------------------------------------------------------------------------------*/
/* Template Name: Full Width Page 
/*-----------------------------------------------------------------------------------*/
?>
<?php get_header() ?>

<div class="site-content full-width-content">
    <?php while (have_posts()) { ?>
        <?php the_post() ?>
        <?php the_content() ?>
    <?php } ?>
</div>

<?php get_footer() ?>