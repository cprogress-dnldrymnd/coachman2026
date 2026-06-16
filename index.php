<?php get_header() ?>
<section class="py-5">
    <div class="container">
        <h1 class="mb-5"><?= get_the_archive_title() ?></h1>
        <?= do_shortcode('[template template_id=28166]') ?>
    </div>
</section>

<?php get_footer() ?>