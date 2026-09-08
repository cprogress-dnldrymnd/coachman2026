<?php get_header() ?>
<section class="py-5">
    <div class="container">
        <h1 class="mb-5"><?= get_the_archive_title() ?></h1>
        <?php
        // Categories template (28166) still carries leftover Swiper classes from
        // when it was built off a slider pattern — strip them so archives render
        // as the intended 3-column grid, not a carousel.
        $archive_posts = do_shortcode('[template template_id=28166]');
        $archive_posts = str_replace(
            array(
                'swiper swiper-post--style-1 overflow-hidden',
                'swiper-wrapper',
            ),
            array(
                '',
                '',
            ),
            $archive_posts
        );
        echo $archive_posts;
        ?>
    </div>
</section>

<?php get_footer() ?>
