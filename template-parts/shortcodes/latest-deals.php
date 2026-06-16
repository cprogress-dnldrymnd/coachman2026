<div class="deals-swiper-holder">
    <div class="tab-nav-holder mb-5">
        <div class="container p-0">
            <div class="nav-tabs-swiper nav-tabs-swiper swiper overflow-visible">
                <ul class="swiper-wrapper nav nav-tabs nav-tabs-style-2" id="myTabDeals-Swiper" role="tablist">
                    <li class="swiper-slide nav-item" role="presentation">
                        <button class="nav-link active" id="Weekly-Deals-tab" data-bs-toggle="tab" data-bs-target="#Weekly-Deals-tab-pane" type="button" role="tab" aria-controls="Weekly-Deals-tab-pane" aria-selected="true">Weekly Deals</button>
                    </li>
                    <li class="swiper-slide nav-item" role="presentation">
                        <button class="nav-link" id="New-Caravan-Offers-tab" data-bs-toggle="tab" data-bs-target="#New-Caravan-Offers-tab-pane" type="button" role="tab" aria-controls="New-Caravan-Offers-tab-pane" aria-selected="false">New Caravan Offers</button>
                    </li>
                    <li class="swiper-slide nav-item" role="presentation">
                        <button class="nav-link" id="New-Motorhome-Offers-tab" data-bs-toggle="tab" data-bs-target="#New-Motorhome-Offers-tab-pane" type="button" role="tab" aria-controls="New-Motorhome-Offers-tab-pane" aria-selected="false">New Motorhome Offers</button>
                    </li>
                    <li class="swiper-slide nav-item" role="presentation">
                        <button class="nav-link text-red" id="Clearance-tab" data-bs-toggle="tab" data-bs-target="#Clearance-tab-pane" type="button" role="tab" aria-controls="Clearance-tab-pane" aria-selected="false">Clearance</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="tab-cotnent-holder">
        <div class="container p-0">
            <div class="tab-content" id="myTabDeals-SwiperContent">
                <div class="tab-pane fade show active" id="Weekly-Deals-tab-pane" role="tabpanel" aria-labelledby="Weekly-Deals-tab" tabindex="0">
                    <div class="swiper-holder">
                        <div class="swiper swiper-listing swiper-mobile-style">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="tab-pane fade" id="New-Caravan-Offers-tab-pane" role="tabpanel" aria-labelledby="New-Caravan-Offers-tab" tabindex="0">
                    <div class="swiper-holder">
                        <div class="swiper swiper-listing swiper-mobile-style">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="New-Motorhome-Offers-tab-pane" role="tabpanel" aria-labelledby="New-Motorhome-Offers-tab" tabindex="0">
                    <div class="swiper-holder">
                        <div class="swiper swiper-listing swiper-mobile-style">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="Clearance-tab-pane" role="tabpanel" aria-labelledby="Clearance-tab" tabindex="0">
                    <div class="swiper-holder">
                        <div class="swiper swiper-listing swiper-mobile-style">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                                <div class="swiper-slide">
                                    <?= do_shortcode('[listing_grid]') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>