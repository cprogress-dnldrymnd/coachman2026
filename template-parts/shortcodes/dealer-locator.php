<?php
$dealer_cat = get_terms(array(
    'taxonomy' => 'wpsl_store_category',
    'hide_empty' => false,
    'orderby' => 'ID',
));

$category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'all';
?>
<div class="dealer--locator--holder">
    <div class="container">
        <div class="swiper swiper-nav-tabs-swiper nav-tabs-swiper overflow-visible sm-margin-bottom nav-tabs-swiper-js">
            <ul class="swiper-wrapper nav nav-tabs  flex-row " id="Dealers-Navigation" role="tablist" aria-live="polite">
                <li class="swiper-slide nav-item">

                    <a class="nav-link <?= $category == 'all' ? 'active' : '' ?>" href="/find-a-dealer/">
                        <p>All Dealers</p>
                    </a>
                </li>

                <?php foreach ($dealer_cat as $dealer) { ?>
                    <li class="swiper-slide nav-item">
                        <a class="nav-link <?= $category == $dealer->slug ? 'active' : '' ?>" href="?category=<?= $dealer->slug ?>">
                            <p><?= $dealer->name ?></p>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div class="dealer--locator">
        <?php
        if ($category == 'all')
            echo do_shortcode('[wpsl template="default"]');
        else
            echo
            do_shortcode('[wpsl template="default" category="' . $category . '"]');
        ?>
    </div>
</div>
<?php if (isset($_GET['wpsl-search-input'])) { ?>
    <style id="wpsl-custom-style">
        #wpsl-result-list,
        #wpsl-gmap {
            opacity: 0;
        }
    </style>
    <script>
        jQuery(document).ready(function() {
            jQuery('input[name="wpsl-search-input"]').val('<?= $_GET['wpsl-search-input'] ?>');
            setTimeout(function() {
                jQuery('#wpsl-search-btn').click();
            }, 500);
            setTimeout(function() {
                jQuery('#wpsl-custom-style').remove();
            }, 501);
        });
    </script>

<?php } ?>