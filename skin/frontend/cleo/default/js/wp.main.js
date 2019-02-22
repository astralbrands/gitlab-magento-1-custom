var WeltPixel = {};

(function($) {

    WeltPixel = {

        jumpTo: function(element, callback)
        {
            jQuery('html, body').animate({
                scrollTop: jQuery(element).offset().top - 40
            }, 700, function() {
                if (typeof callback == 'function')
                {
                    callback();
                }
            });
        },

        initQuantity: function()
        {
            function increaseQuantity(elem)
            {
                var currentVal = parseInt(elem.val());

                if ( ! currentVal || currentVal == '' || currentVal == 'NaN') {
                    currentVal = 0;
                }
                
                elem.val(currentVal + 1);

                elem.removeAttr('disabled');
            }

            function decreaseQuantity(elem, allowZero)
            {
                var currentVal = parseInt(elem.val());

                if (currentVal == 'NaN') {
                    currentVal = 1;
                } else if (currentVal >= 1) {
                    currentVal--;
                } else {
                    currentVal = 0;
                }

                if ( ! allowZero && (currentVal == 0)) {
                    currentVal = 1;
                }

                elem.val(currentVal);

                if (currentVal == 0) {
                    elem.attr('disabled', true);
                } else {
                    elem.removeAttr('disabled');
                }

                if (allowZero) {
                    return (currentVal == 0);
                } else {
                    return (currentVal == 1);
                }
            }

            var qty = jQuery('#qty'),
                parent = jQuery(this).parents('.quantity'),
                super_attribute_select = jQuery('.super-attribute-select').eq(0);

            jQuery('.qty-inc').on('click', function() {
                if (qty.size())
                {
                    increaseQuantity(qty);
                    jQuery(this).parent().find('.qty-dec').removeClass('qty-disabled');
                }
                else if (super_attribute_select)
                {
                    var qtys = jQuery('.qty', parent);

                    qtys.each(function(key, value) {
                        increaseQuantity(jQuery(this));
                    });
                }
            });

            jQuery('.qty-dec').on('click', function() {
                if (qty.size())
                {
                    if (decreaseQuantity(qty, false)) {
                        jQuery(this).addClass('qty-disabled');
                    };
                }
                else if (super_attribute_select)
                {
                    var qtys = jQuery('.qty', parent);

                    qtys.each(function(key, value) {
                        decreaseQuantity(jQuery(this), true);
                    });
                }
            });
        },
    
        bttButton: function(settings) {
            /** default settings */
            this.config = {
                button: '.back-to-top',
                offset: 300,
                duration: 700,
            };
    
            /** Allow overriding the default config */
            $.extend(this.config, settings);
    
            var backToTop = $(this.config.button),
                offset = this.config.offset,
                scrollTopDuration = this.config.duration;
    
            $(window).scroll(function () {
                ($(this).scrollTop() > offset) ? backToTop.addClass('cd-is-visible') : backToTop.removeClass('cd-is-visible');
            });
    
            backToTop.on('click', function (event) {
                event.preventDefault();
                $('body,html').animate({
                        scrollTop: 0,
                    }, scrollTopDuration
                );
            });
        }
    };

    jQuery(document).ready(function(){
        WeltPixel.initQuantity();
        if (Modernizr.mq('(max-width: 992px)')) {
            if(jQuery('.col-left')){
                jQuery('.col-left').insertAfter('.col-main');
            }
            if(jQuery('.col-right') && jQuery('.col-right') ){
                jQuery('.col-right').insertAfter('.col-left');
            }
        };
        if (Modernizr.mq('(min-width: 992px)')) {
            if(jQuery('.col-left')){
                jQuery('.col-main').insertAfter('.col-left');
            }
            if(jQuery('.col-right') && jQuery('.col-right') ){
                jQuery('.col-right').insertAfter('.col-main');
            }
        };
        jQuery('.panel-link').on('click', function() {
            jQuery('.panel-link').removeClass('active');
            jQuery(this).addClass('active');
            jQuery('.panel-details').removeClass('active');
            jQuery(this).next().addClass('active');
            return false;
        });
        if(jQuery(window).width() < 961){
                $('.panel-link').on('click', function() {
                    jQuery('.panel-link').removeClass('active');
                    jQuery(this).addClass('active');
                    jQuery('.panel-details').removeClass('active');
                    jQuery(this).next().addClass('active');
                    return false;
            });
        };
        jQuery('.product-cart-actions input.qty').click(function(){
            jQuery(this).next().css('display','inline-block');
        })
    });
    jQuery(window).resize(function(){
        
        if (Modernizr.mq('(max-width: 992px)')) {
             if(jQuery('.col-left')){
                jQuery('.col-left').insertAfter('.col-main');
            }
            if(jQuery('.col-right') && jQuery('.col-right') ){
                jQuery('.col-right').insertAfter('.col-left');
            }
        }
        
        if (Modernizr.mq('(min-width: 992px)')) {
             if(jQuery('.col-left')){
                jQuery('.col-main').insertAfter('.col-left');
            }
            if(jQuery('.col-right') && jQuery('.col-right') ){
                jQuery('.col-right').insertAfter('.col-main');
            }
        }
         $('.panel-link').on('click', function() {
            jQuery('.panel-link').removeClass('active');
            jQuery(this).addClass('active');
            jQuery('.panel-details').removeClass('active');
            jQuery(this).next().addClass('active');
            return false;
        })
        if(jQuery(window).width() < 961){
                jQuery('.panel-link').on('click', function() {
                    jQuery('.panel-link').removeClass('active');
                    jQuery(this).addClass('active');
                    jQuery('.panel-details').removeClass('active');
                    jQuery(this).next().addClass('active');
                    return false;
            })
        }
    });
})(jQuery);