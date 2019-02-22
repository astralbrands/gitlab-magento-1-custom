/** Custom js goes here */
var customJs = (function ($) {
    return {
        init: function () {
            var that = this,
                minicart = $('#quick-access').find('li.mini-cart');
            /** open mini-cart wrapper on top mini-cart icon click */
            $('#quick-access').on('click', '.mini-cart .summary.qa-link, .btn-minicart-toggle, .minicart-overlay', function(event) {
                that.openCloseMinicart(event, minicart);
            });
            /** open mini-cart wrapper after wpqv-addtocart-response is closed */
            $('body').on('DOMNodeRemoved', '#wpqv-addtocart-response',function(event){
                if (!minicart.hasClass('mini-cart-open')) {
                    that.openCloseMinicart(event, minicart);
                }
            });
            /** make application tabs same height as width */
            var sameHeightContainers = [
                '.product-tabs .application .tab-section',
                '.how-to-container .front',
                '.how-to-container .back-container',
                '.routines-wrap .routine-product '
            ];
            /** vertical align content */
            var verticalAlign = [
                '.hightlights-wrap .description-text',
                '.hightlights-wrap .description-image'
            ];
            $(window).on('load', function() {
                that.sameHeightAsWidth(sameHeightContainers);
                that.verticalAlign(verticalAlign);
                that.fixHeightSlider();
            });

            $(window).on('resize', function() {
                clearTimeout(window.resizedFinished);
                window.resizedFinished = setTimeout(function(){
                    that.sameHeightAsWidth(sameHeightContainers);
                    that.verticalAlign(verticalAlign);
                    that.fixHeightSlider();
                }, 150);
            });

            $('.how-to-card-eye').on('click', function(){
                that.showHideInstructions(this);
            });

            $('.header-link').on('mouseenter mouseleave', function(e) {
                that.navToggle(this, e);
            });

            /** layered navigation toggle */
            var filterBtn = $('.toggle-filters');
            var horizontalLayeredNav = $('.block-content-horizontal');
            var filterOverlay = $('.col-main.main').find('.filter-overlay');
            if (!horizontalLayeredNav.length) {
                if ($('.block-layered-nav').parent().hasClass('open')) {
                    filterBtn.find('span').text('See Results');
                    filterOverlay.show();
                }
                filterBtn.on('click', function() {
                    that.layeredNavToggle($(this));
                });
                filterOverlay.on('click', function() {
                    that.layeredNavToggle(filterBtn);
                });
            } else {
                filterBtn.on('click', function() {
                    that.toggleHorizontalLayeredNav($(this));
                });
            }

            /** Responsive: tabs to accordion */
            $('.tab-container .accordion-heading').on('click', function() {
                var i = 1,
                    clicked = $(this),
                    target = $('.tab-container .tab-accordion:first-child h3'),
                    tabContent = clicked.closest('.tab-accordion').find('.tab-content');

                if (!tabContent.is(':visible')) {
                    $('.tab-container .tab-content').each(function() {
                        $(this).hide();
                        if (i == $('.tab-container .tab-content').length) {
                            tabContent.slideDown('slow');
                            $('html, body').stop().animate({
                                scrollTop: target.offset().top - 50
                            }, 1000);
                        }
                        i++;
                    });
                }
            });
        },
        openCloseMinicart: function(event, minicart) {
            var target = $(event.target);
            if ((target.is('a') && target.hasClass('qa-link')) || target.closest('a.qa-link').length) {
                event.preventDefault();
            }
            minicart.toggleClass('mini-cart-open');
        },
        sameHeightAsWidth: function(sameHeightContainers) {
            for(var i = 0; i < sameHeightContainers.length; i++) {
                $(sameHeightContainers[i]).each(function() {
                    if ($(this).length) {
                        var parent = $(this).closest('.tab-content'),
                            width = $(this).outerWidth();

                        if (parent.length) {
                            var parentVisibility = parent.css('display');
                            if (parentVisibility == 'none') {
                                $(this).closest('.tab-content').css({
                                    'visibility': 'hidden',
                                    'display': 'block'
                                });
                                /** rewrite width */
                                width = $(this).outerWidth();
                            }
                        }

                        /** set same height */
                        $(this).height(width);

                        if (parent.length) {
                            parent.css({
                                'visibility': 'visible',
                                'display': parentVisibility
                            });
                        }
                    }
                });
            }
        },
        verticalAlign: function(el) {
            for(var i = 0; i < el.length; i++) {
                $(el[i]).each(function() {
                    if ($(this).length) {
                        var parent = $(this).parent(),
                            parentHeight = parent.height(),
                            top = parseInt((parentHeight - $(this).outerHeight()) / 2);

                        if ($(window).width() > 991) {
                            $(this).css('top', top + 'px');
                        } else {
                            $(this).css('top', '');
                        }
                    }
                });
            }
        },
        showHideInstructions: function(el)
        {
            var current = $(el),
                parent = current.closest('.toggle-parent'),
                toggleCurrent = parent.find('.toggle-visibility');
            if (toggleCurrent.length) {
                $('.toggle-visibility').each(function() {
                    if ($(this).attr('class') != toggleCurrent.attr('class')) {
                        $(this).removeClass('visible');
                    }
                });
                toggleCurrent.toggleClass('visible');
            }
        },
        navToggle: function(el, e) {
            var current = $(el),
                accountLinks = current.parent().find('.account-links');

            if (current.length) {
                if (e.type == 'mouseenter') {
                    accountLinks.addClass('open');
                    accountLinks.on('mouseenter', function(){
                        $(this).addClass('open');
                    });
                    accountLinks.on('mouseleave', function(){
                        $(this).removeClass('open');
                    })
                } else {
                    accountLinks.removeClass('open');
                }
            }
        },
        layeredNavToggle: function(button) {
            var filtersContainer = $('.block-layered-nav').parent(),
                colMain = $('.col-main.main'),
                filterOverlay = colMain.find('.filter-overlay');

            if (!filtersContainer.hasClass('open')) {
                filtersContainer.animate({
                    left: '0'
                },{
                    duration: 500,
                    start: function() {
                        filterOverlay.show();
                        $('body').addClass('no-scroll');
                        filtersContainer.addClass('open');
                        button.find('span').text('See Results');
                    },
                    queue: false
                });
            } else {
                filtersContainer.animate({
                    left: '-100%'
                },{
                    duration: 500,
                    start: function() {
                        filterOverlay.hide();
                        $('body').removeClass('no-scroll');
                        filtersContainer.removeClass('open');
                        button.find('span').text('Open Filters');
                    },
                    queue: false
                });
            }
        },
        toggleHorizontalLayeredNav: function(button) {
            var filtersContainer = $('.block-content-horizontal');
            if (!filtersContainer.hasClass('open')) {
                filtersContainer.animate({
                    top: '0'
                },{
                    duration: 500,
                    start: function() {
                        filtersContainer.addClass('open');
                        button.find('span').text('Close Filters');
                    },
                    queue: false
                });
            } else {
                filtersContainer.animate({
                    top: '-42'
                },{
                    duration: 500,
                    start: function() {
                        filtersContainer.removeClass('open');
                        button.find('span').text('Open Filters');
                    },
                    queue: false
                });
            }
        },
        fixHeightSlider: function() {

            var slider = $('#slider'),
                sliderCaption = slider.find('.slider-caption'),
                swiperSlide = slider.find('.swiper-slide');

            setTimeout(function() {
                /** reset previously added min-height */
                slider.css('min-height', '');
                slider.parent().css('min-height', '');
                swiperSlide.css('min-height', '');

                if (slider.height() < sliderCaption.height()) {
                    slider.css('min-height', parseInt(sliderCaption.height()));
                    slider.parent().css('min-height', parseInt(sliderCaption.height()));
                    swiperSlide.css('min-height', parseInt(sliderCaption.height()));
                } else {
                    slider.css('min-height', '');
                    slider.parent().css('min-height', '');
                    swiperSlide.css('min-height', '');
                }
                if (parseInt(sliderCaption.css('top')) < 0) {
                    sliderCaption.css('top', 0);
                }
            }, 300);
        }
    }
})(jQuery);