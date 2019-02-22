define([
  'jquery',
  'backbone',
  'bxslider',
  'shippingModal',
  'quickviewModalPHP',
  'wishlist',
  'modal', // modal is required for init modal windows
  'fixedHeader',
  'backToTop',
  'headerHoverThumbnail',
  'minicart',
  'browserDetect',
  'shareModalPHP',
  'undertoneModal',
  'informationModal',
  'jqueryFormstyler'  
], function ($, Backbone, bxslider, shippingModalCallback, quickviewModalPHP, wishlist, modal, fixedHeader, backToTop, headerHoverThumbnail, minicart, browserDetect, shareModalPHP, undertoneModal, informationModal, styler) {

  "use strict";

  var CommonView = Backbone.View.extend({
    initialize: function () {

      $(document).ready(handler);

      // now .bind extends from prototype.js
      var trueBind = Function.prototype.bind;

      function handler() {
        // common variables
        var html = $('html').addClass(browserDetect.browser);
        // menuBreakPoint = 980;
        var menuBreakPoint = $('body').hasClass('cos-b2c') ? 1150 : 980;

        minicart.init();

        // shipping & returns modals
        $('#shipping-modal').on('mcallback', shippingModalCallback.bind($(this)));

        // quickview modals
        $('[data-modal-name = "quickview-php"]')
          .off('onParse')
          .off('mcallback')
          .on('onParse', quickviewModalPHP.quickviewPHPParseTemplate.bind($(this)))
          .on('mcallback', quickviewModalPHP.quickviewPHPCallback.bind($(this)));

        $('[data-modal-name = "shareModal-php"]')
          .off('onParse')
          .on('onParse', shareModalPHP.bind($(this)));

        $('[data-modal-name = "undertone-guide"]')
          .off('onParse')
          .on('onParse', undertoneModal.bind($(this)));

        $('[data-modal-name = "information-modal"]')
          .off('mcallback')
          .on('mcallback', informationModal.bind($(this)));

        $('.required-entry')
          .off('input')
          .on('input', function () {
          if ($(this).val()) {
            $(this).next('.validation-advice').fadeOut();
          } else $(this).next('.validation-advice').fadeIn();
        });

        //footer dropdown links
        $('.footer .block-title')
          .off('click')
          .on('click', function (e) {
          e.preventDefault();
          $(this).parent(".links").toggleClass('open-footer-links').siblings().removeClass('open-footer-links');
        });

        $(window).on('ready.setMobileClass resize.setMobileClass', function () {
          var $headerNav;
          if (window.innerWidth < menuBreakPoint && !html.hasClass('_narrow')) {
            $headerNav = $('#header-nav');
            $headerNav.addClass('_fix-animation');
            setTimeout(function () {
              $headerNav.removeClass('_fix-animation');
            }, 200);
          }
          window.innerWidth < menuBreakPoint ? html.addClass('_narrow') : html.removeClass('_narrow');
        });

        $(document)
          .off('click.addMenuOpenClassToHtml', '.level0 > .arrow')
          .on('click.addMenuOpenClassToHtml', '.level0 > .arrow', function (e) {
          e.preventDefault();
          var $that = $(this),
              $parent = $that.parent();
          if ($parent.hasClass('_open-submenu')) {
            $parent.removeClass('_open-submenu');
          } else {
            $('.level0').removeClass('_open-submenu');
            $parent.addClass('_open-submenu');
          }
        });
        $(document)
          .off('click.addMenuOpenClassToHtml', '.level1 .arrow')
          .on('click.addMenuOpenClassToHtml', '.level1 .arrow', function (e) {
          e.preventDefault();
          var $that = $(this),
              $parent = $that.parent();
          if ($parent.hasClass('_open-submenu')) {
            $parent.removeClass('_open-submenu');
          } else {
            $('.level1').removeClass('_open-submenu');
            $parent.addClass('_open-submenu');
          }
        });

        bindSwatchClick();

      }

      var App = (function (headerHoverThumbnail, fixedHeader, backToTop) {

        var $html = $('html');

        function setDeviceValues() {
          var isSafari= (browserDetect.browser.toLowerCase() == 'safari' && !navigator.userAgent.match('CriOS'));
          if (isSafari) {
            $html.addClass('coherent-safari');
          }

          window.isMobile = window.isMobile || false;
          (function (a) {
            if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4)))window.isMobile = true
          })(navigator.userAgent || navigator.vendor || window.opera);
          if (window.isMobile) {
            $html.addClass('_mobile');
            window.isMobile = true;
          }
          window.innerWidth < window.innerHeight ? $html.removeClass('_landscape').addClass('_portrait') : $html.removeClass('_portrait').addClass('_landscape');
        }

        window.checkFieldsValueLength = function checkFieldsValueLength() {
          $('.input-text').each(function () {
            $(this).val().length ? $(this).parents('.input-box').addClass('_filled') : $(this).parents('.input-box').removeClass('_filled');
          });
        };

        function bindEvents() {
          $(document)
            .off('input.checkFieldsValLength', '.input-text')
            .off('click', '.label-text')
            .off('click.toggleSearch', '.form-search-toggle-btn')
            .off('click.closeSearch', '.form-search__close-btn')
            // input label behavior
            .on('input.checkFieldsValLength', '.input-text', function () {
              $(this).val().length ? $(this).parents('.input-box').addClass('_filled') : $(this).parents('.input-box').removeClass('_filled');
            })
            .on('click', '.label-text', function () {
              $(this).siblings('.input-text').focus();
            })

            // search
            .on('click.toggleSearch', '.form-search-toggle-btn', function (e) {
              e.preventDefault();
              var $parent = $(this).parents('.form-search-wrap');
              $('#search_mini_form').slideToggle('fast');
              $parent.hasClass('_open-form') ? $parent.removeClass('_open-form') : $parent.addClass('_open-form');
            })
            .on('click.closeSearch', '.form-search__close-btn', function (e) {
              e.preventDefault();
              $('#search_mini_form').slideUp();
              $(this).parents('.form-search-wrap').removeClass('_open-form');
            });
        }

        return {
          init: function () {

            setDeviceValues();

            // init modules
            headerHoverThumbnail.init();
            fixedHeader.init();
            backToTop.init();

            // etc
            bindEvents();

            // style forms
              window.checkFieldsValueLength && window.checkFieldsValueLength();

            var config = $('.grouped-items-table').length ? {
              onSelectClosed: function () {
                preorderState.update();
              }
            } : undefined;

            jQuery('select:not(#shipping_method):not(.bv-dropdown-select)').styler(config);
          }
        };
      }(headerHoverThumbnail, fixedHeader, backToTop));
      $(document).ready(function () {
        App.init();
      });

      //Toggle swathes opening
      $('.color-swatches .btns-swatches')
        .off('click')
        .on('click', function (e) {
          $(this).closest('.color-swatches').toggleClass('swatches-open');
          $(this).closest('.color-swatches').find('dl:first-child').addClass('slider-swatch');
          this.slider = toggleSlider(this);
          e.stopPropagation();
          e.preventDefault();
        });
    }
  });

  function toggleSlider(element) {
    var swatchSlider = null;
    var countOfSlides = 4;
    var swatchContainer = jQuery(element).closest('.color-swatches').find('.slider-swatch');
    function initSlider() {
      if (swatchContainer.find('dd').length > countOfSlides) {
        try {
          swatchSlider = swatchContainer.bxSlider({
            mode: 'vertical',
            infiniteLoop: true,
            preventDefaultSwipeY: true,
            swipeThreshold: 100,
            slideSelector: 'dd',
            minSlides: countOfSlides,
            moveSlides: countOfSlides,
            pager: false,
            onSlideAfter: bindSwatchClick
          });
        } catch (err) {
          console.log(err);
        }
      }
    }
    element.slider ? element.slider.destroySlider() : initSlider();
    return swatchSlider;
  }

  function bindSwatchClick () {
    $('.color-swatches').find('dd').find('img')
      .off('click')
      .on('click', function () {
      var newSrc = $(this).attr('data-product-image');
      var newHoverSrc = $(this).attr('data-product-alt-image');
      var parent = $(this).closest('.color-swatches');
      if (newSrc) {
        var img = parent.siblings('a.product-image').find('.product-image-default');
        img.attr('src', newSrc);
      }
      if (newHoverSrc) {
        var hoverImg = parent.siblings('a.product-image').find('.product-image-hover');
        hoverImg.attr('src', newHoverSrc);
      }
    });

  }

  return new CommonView();
});
