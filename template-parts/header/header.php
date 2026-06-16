<header id="masthead" class="site-header py-4" role="banner">
    <div class="container">
        <div class="row g-3 justify-content-between align-items-center">
            <div class="col-auto">
                <?php glossop_caravans_display_site_logo(true) ?>
            </div>
            <div class="col-auto">
                <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasHeaderMenu" aria-labelledby="offcanvasHeaderMenuLabel">
                    <div class="offcanvas-header d-block d-lg-none">
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <nav class="navbar">
                            <div class="container-fluid">
                                <?php
                                wp_nav_menu(array(
                                    'theme_location' => 'header-menu',
                                    'container' => false,
                                    'menu_class' => 'fw-semibold flex-row',
                                    'fallback_cb' => '__return_false',
                                    'items_wrap' => '<ul id="%1$s" class="navbar-nav fs-23 %2$s">%3$s</ul>',
                                    'depth' => 2,
                                    'walker' => new bootstrap_5_wp_nav_menu_walker()
                                ));
                                ?>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>