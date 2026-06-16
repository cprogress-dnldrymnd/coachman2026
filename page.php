<?php get_header() ?>

<div class="site-content site-content--default">
    <?php while (have_posts()) { ?>
        <div class="title-description py-5">
            <div class="container small-container">
                <h1 class="m-0"><?php the_title() ?></h1>
            </div>
        </div>
        <div class="site-content--inner md-padding-top md-padding-bottom overflow-hidden has-lightgray-2-background-color md-padding-top md-padding-bottom default-template">
            <div class="container small-container">
                <?php the_post() ?>
                <?php the_content() ?>
            </div>
        </div>
    <?php } ?>
</div>

<?php get_footer() ?>