jQuery(document).ready(function () {
    swiper_sliders();
    //fancybox();
    mega_menu();
    // search_stock();
    listings();
    read_more();
    accordion();
    updateScrollStatus();
    dealer();
    ajax_details();
    careers();
    find_dealer();
});

function find_dealer() {
    jQuery('.wpsl-search').addClass('container');
    jQuery('body').css('--margin', jQuery('.wpsl-search').css('margin-left'));
}

function careers() {
    jQuery('a[href="#careers--modal"]').click(function (e) {
        const bsOffcanvasCareers = new bootstrap.Offcanvas('#offCanvas26165');

        $job_title = jQuery(this).parents('.accordion--item').find('.wp-block-post-title').text();
        $job_category = jQuery(this).parents('.accordion--item').find('.careers--category').clone();
        jQuery('.job--title').text($job_title);
        jQuery('input[name="job_title"]').val($job_title);
        jQuery('.job--category').html($job_category);

        bsOffcanvasCareers.show();
        e.preventDefault();
    });
}

function dealer() {
    jQuery('body').on('click', '.btn-appointment a', function () {
        $originalText = jQuery('.request--appointment--dealer h5').text();
        $dealerName = jQuery(this).parents('.store--listing').find('h4').text();
        $dealerEmail = jQuery(this).parents('.store--listing').find('.email').text();
        $new_text = $originalText.replace('[dealer_name]', $dealerName);

        jQuery('.request--appointment--dealer h5').text($new_text);
        jQuery('input[name="Dealer"]').val($dealerName);
        jQuery('input[name="Dealer_email"]').val($dealerEmail);
        console.log($dealerEmail);
    });
}

function ajax_details() {
    if (jQuery('#offCanvas25765').length > 0) {
        const bsOffcanvas = new bootstrap.Offcanvas('#offCanvas25765');
        jQuery('body').on('click', '.btn-stock a', function () {
            var $this = jQuery(this);
            var post_id = $this.parents('.store--listing').attr('data-store-id');
            $this.addClass('loading');
            console.log(post_id);
            jQuery.ajax({
                url: ajax_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'dealer_details_ajax',
                    post_id: post_id,
                },
                success: function (response) {
                    jQuery('#listing--details--results').html(response);
                    bsOffcanvas.show();
                    $this.removeClass('loading');

                },
                error: function (error) {
                    console.error('Error:', error);
                }
            });
        });

        const myOffcanvas = document.getElementById('offCanvas25765')

        myOffcanvas.addEventListener('hidden.bs.offcanvas', event => {
            jQuery('#listing--details--results').html('');
        })
    }
}

function updateScrollStatus() {
    if (jQuery(window).scrollTop() === 0) {
        jQuery('body').removeClass('sticky--header');
    } else {
        jQuery('body').addClass('sticky--header');
    }
}

// Attach the updateScrollStatus function to the window's scroll event
jQuery(window).on('scroll', function () {
    updateScrollStatus();
});

function accordion() {
    if (jQuery('.accordion--custom').length > 0) {
        jQuery('.accordion--item').each(function (index, element) {
            var $this = jQuery(this);
            $accordion_button = $this.find('.accordion--button');
            $accordion_content = $this.find('.accordion--content');
            jQuery('<span class="plus-minus"></span>').appendTo($accordion_button);

            $accordion_button_height = $accordion_button.outerHeight();
            $accordion_content_height = $accordion_content.outerHeight();

            $this.css('--accordion_button_height', $accordion_button_height + 'px');
            $this.css('--accordion_content_height', $accordion_content_height + 'px');
            $this.addClass('initialized');
            $accordion_button.click(function (e) {
                $this.addClass('clicked');
                $this.parents('.accordion--custom').find('.accordion--item:not(.clicked)').removeClass('active');
                $this.toggleClass('active');
                $this.removeClass('clicked');
                e.preventDefault();
            });
        });
    }
}
function read_more() {
    jQuery('.read-more-button').click(function (e) {
        jQuery('.read-more-content').removeClass('d-none');
        jQuery(this).addClass('d-none');
        e.preventDefault();

    });
}

function listings() {

    if (jQuery('.nav-tabs-swiper-js:not(.no-active--onload)').length > 0) {
        jQuery('.nav-tabs-swiper-js:not(.no-active--onload) .nav-item:first-child .nav-link').click();
    }



    jQuery('.listings--inner--js').click(function (e) {
        jQuery('.listings--posts > div').each(function (index, element) {
            $height = jQuery(this).outerHeight();
            jQuery(this).parent().css('--height', $height + 'px');
        });

        var $target = jQuery(this).attr('listing-target');

        jQuery('.listings--inner--js').not(this).removeClass('active');
        jQuery('.listings--posts').not(jQuery($target)).removeClass('active');
        jQuery(this).toggleClass('active');

        jQuery(this).parents('.tab-pane').find($target).toggleClass('active');

        e.preventDefault();

    });
}
function search_stock() {
    jQuery('.edit-stock-filter').click(function (e) {
        jQuery(this).parents('.search-stock-mobile').toggleClass('filter--active');
        e.preventDefault();

    });
}
function mega_menu() {
    $height = jQuery('#masthead').outerHeight();
    $admin_bar = jQuery('#wpadminbar').outerHeight();
    jQuery('body').css('--header-height', $height + 'px');
    if (jQuery('#wpadminbar').length > 0) {
        jQuery('body').css('--admin-bar-height', $admin_bar + 'px');

    }
    jQuery('.no--submenu .nav-link').each(function (index, element) {
        jQuery(this).removeAttr('data-bs-toggle');
        jQuery(this).removeAttr('data-bs-target');
    });

    jQuery('#offCanvasMenu .nav-item:not(.no--submenu) .nav-link').click(function (e) {
        jQuery('#offCanvasMenu').removeClass('mega-menu--not-active');
        e.preventDefault();
    });

    if (window.innerWidth < 768) {
        jQuery('.header--navigation .tab-pane').each(function (index, element) {
            $id = jQuery(this).attr('id');
            jQuery(this).insertAfter('button[data-bs-target="#' + $id + '"]');
        });
    }
}

function fancybox() {
    Fancybox.bind("[data-fancybox]", {
        // Your custom options
    });

    jQuery('.zoom').click(function (e) {
        jQuery(this).next().find('.swiper-slide-active a').addClass('sdsdss');
        jQuery(this).next().find('.swiper-slide-active a').trigger('click');
        console.log('mama mo');
        e.preventDefault();
    });
}

function swiper_sliders() {
    var swiper_on_mobile = new Swiper('.swiper-nav-tabs-swiper', {
        slidesPerView: 'auto',
        spaceBetween: 20,
        freeMode: true,
    });

    jQuery('.swiper-slider-holder').each(function (index, element) {
        $atts = jQuery(this).attr('swiper_atts');
        $atts = JSON.parse($atts);
        $id = '#' + jQuery(this).find('.swiper').attr('id');
        if (jQuery(this).hasClass('swiper-nav-style-2')) {
            jQuery(this).find('.swiper-button-prev').appendTo(jQuery(this).find('.swiper-pagination-navigation-style-2'));
            jQuery(this).find('.swiper-pagination').appendTo(jQuery(this).find('.swiper-pagination-navigation-style-2'));
            jQuery(this).find('.swiper-button-next').appendTo(jQuery(this).find('.swiper-pagination-navigation-style-2'));
        }
        var swiper_slider_block = new Swiper($id, $atts);
    });



    var swiper_listing_taxonomy = new Swiper(".swiper-listings-taxonomy", {
        slidesPerView: 'auto',
        spaceBetween: 40,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });


    if (jQuery('.swiper-post--style-1').length > 0) {
        jQuery('.swiper-post--style-1').each(function (index, element) {
            $pagination = jQuery('<div class="swiper-pagination-navigation-style-2 d-flex d-lg-none mt-5"> <div class="swiper-button-prev"></div> <div class="swiper-pagination"></div> <div class="swiper-button-next"></div> </div>');

            $pagination.insertAfter(jQuery(this).find('.swiper-wrapper'));


            jQuery(this).attr('class', 'swiper-post--style-1');
            jQuery(this).find('.swiper-wrapper').attr('class', 'swiper-wrapper p-0');
            jQuery(this).find('.wp-block-post').attr('class', 'swiper-slide');

            $id = 'swiper-post--style-1-' + index;
            jQuery(this).attr('id', $id);

            var swiper_post_style_1 = new Swiper('#' + $id, {
                loop: true,
                spaceBetween: 25,
                breakpoints: {
                    575: {
                        slidesPerView: 1,
                    },
                    768: {
                        slidesPerView: 2,
                    },
                    1024: {
                        slidesPerView: 3,
                    },
                },
                navigation: {
                    nextEl: '#' + $id + ' .swiper-button-next',
                    prevEl: '#' + $id + ' .swiper-button-prev',
                },
                pagination: {
                    el: '#' + $id + ' .swiper-pagination',
                    clickable: true,
                },
            });
        });
    }

    if (jQuery('.swiper-logo-slider').length > 0) {
        jQuery('.swiper-logo-slider').each(function (index, element) {
            jQuery(this).attr('class', 'swiper-logo-slider');
            jQuery(this).find('.swiper-wrapper').attr('class', 'swiper-wrapper');
            jQuery(this).find('.wp-block-post').clone().appendTo(jQuery(this).find('.swiper-wrapper'));
            jQuery(this).find('.wp-block-post').attr('class', 'swiper-slide w-auto');
            jQuery(this).find('.swiper-slide').each(function (index, element) {
                $width = jQuery(this).find('>div').outerWidth();
                jQuery(this).css('--width', $width + 'px');
            });

            $id = 'swiper-logo-slider-' + index;
            jQuery(this).attr('id', $id);

            var swiper_logo_slider = new Swiper('#' + $id, {
                loop: true,
                freeMode: true,
                slidesPerView: 'auto',
                spaceBetween: 0,
                speed: 3000,
                autoplay: {
                    delay: 0,
                    disableOnInteraction: false
                },
            });
        });
    }

    if (jQuery('.swiper-team-slider').length > 0) {
        jQuery('.swiper-team-slider').each(function (index, element) {
            $pagination = jQuery('<div class="swiper-pagination-navigation-style-2"> <div class="swiper-button-prev"></div> <div class="swiper-pagination"></div> <div class="swiper-button-next"></div> </div>');

            $pagination.insertAfter(jQuery(this).find('.swiper-wrapper'));


            jQuery(this).attr('class', 'swiper-team-slider');
            jQuery(this).find('.swiper-wrapper').attr('class', 'swiper-wrapper p-0');
            jQuery(this).find('.wp-block-post').attr('class', 'swiper-slide');

            $id = 'swiper-team-slider-' + index;
            jQuery(this).attr('id', $id);

            var swiper_team_slider = new Swiper('#' + $id, {
                loop: true,
                slidesPerView: 1,
                spaceBetween: 25,
                pagination: {
                    el: '#' + $id + ' .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '#' + $id + ' .swiper-button-next',
                    prevEl: '#' + $id + ' .swiper-button-prev',
                },
            });
        });
    }
    if (jQuery('.swiper-slider-timeline').length > 0) {
        jQuery('.swiper-slider-timeline').each(function (index, element) {
            if (window.innerWidth > 767) {
                $navigation = jQuery('<div class="swiper-pagination swiper-pagination-style--timeline d-none d-lg-flex"> </div> <div class="swiper-pagination-navigation-style-2"> <div class="swiper-button-prev"></div> <div class="swiper-button-next"></div> </div>');
            } else {
                $navigation = jQuery('<div class="swiper-pagination-navigation-style-2 swiper-pagination-navigation-style-2--static mt-4"> <div class="swiper-button-prev"></div> <div class="swiper-pagination"></div> <div class="swiper-button-next"></div> </div>');
            }

            $navigation.insertAfter(jQuery(this).find('.swiper-wrapper'));

            jQuery(this).attr('class', 'swiper-slider-timeline');
            jQuery(this).find('.swiper-wrapper').attr('class', 'swiper-wrapper p-0');
            jQuery(this).find('.wp-block-post').attr('class', 'swiper-slide');

            $image_height = jQuery(this).find('.wp-block-post-featured-image').outerHeight();
            jQuery(this).css('--image-height', $image_height + 'px');

            $id = 'swiper-slider-timeline-' + index;
            jQuery(this).attr('id', $id);

            var swiper_team_slider = new Swiper('#' + $id, {
                loop: true,
                slidesPerView: 1,
                spaceBetween: 25,
                pagination: {
                    el: '#' + $id + ' .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '#' + $id + ' .swiper-button-next',
                    prevEl: '#' + $id + ' .swiper-button-prev',
                },
            });
        });
    }
}
