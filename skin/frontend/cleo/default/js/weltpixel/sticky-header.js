jQuery(document).ready(function(){


    /**
    * IE 8 fix for header
    * 
    * should be moved outside a js for ie
    * 
    */
    jQuery('#wpmm-nav li.parent').hover(function() {
        var openedElement = jQuery( this ).find('.wpmm-nav-content');
        if (jQuery('html').hasClass('lt-ie9')) {
            jQuery('#page-header').height(jQuery('#page-header').height() + openedElement.height() + 'px');
        }
    }, function() {
        if (jQuery('html').hasClass('lt-ie9')) {
            jQuery('#page-header').height('auto');
        }
    });
    /**
    * IE 8 fix for header
    */

    var stickyHeader = 0;
    if (jQuery('#endHeader').length) {
        stickyHeader = jQuery('#endHeader').offset().top;
    }

    if (stickyHeader === 0){
        stickyHeader = jQuery('#page-header').height();
    }

    jQuery('#page-header').addClass('sticky-header');
    jQuery('.page').css('position', 'relative');
    if (!jQuery('.body_merged_header').length) {
        jQuery('.page').css('top', stickyHeader + 'px');
    }

    jQuery(window).scroll(function() {

        if (jQuery(window).scrollTop()>1) {
            jQuery('#page-header').addClass('sticky-header-moved');
        } else {
            jQuery('#page-header').removeClass('sticky-header-moved');
            jQuery('.header-options').removeClass('open');
            jQuery('.header-settings').removeClass('rotated');
        }
    });
    
    jQuery(window).resize(function() {
        var stickyHeader = 0;
        if (jQuery('#endHeader').length) {
            stickyHeader = jQuery('#page-header').height();
        }

        if (!jQuery('.body_merged_header').length) {
            jQuery('.page').css('top', stickyHeader + 'px');
        }

    })


	
});