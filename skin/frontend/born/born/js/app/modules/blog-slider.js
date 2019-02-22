define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";

  var blogSlider;
  var isMobileSlider = false;
  var isSliderInit = false;
  var defaultSliderOptions = {
    slideSelector: 'li.item',
    slideWidth: 215,
    swipeThreshold: 100,
    minSlides: 4,
    maxSlides: 4,
    pager: false
  };
  var mobileSliderOptions = {
    slideSelector: 'li.item',
    swipeThreshold: 100,
    pager: false
  };

  var BlogSliderView = Backbone.View.extend({

    initialize: function () {
    },
    slider: function (width) {

      if (!isSliderInit) {
        if (width > 768){
          initSliders(defaultSliderOptions);
          isMobileSlider = false;
        }
        else {
          initSliders(mobileSliderOptions);
          isMobileSlider = true;
        }
        isSliderInit = true;
      } else {
        if (width > 768 && isMobileSlider) {
          reloadSliders(defaultSliderOptions);
          isMobileSlider = false;
        }
        else if (width <= 768 && !isMobileSlider) {
          reloadSliders(mobileSliderOptions);
          isMobileSlider = true;
        }
      }

      function initSliders (options) {
        blogSlider = [];
        $('.blog-slider').each(function (index, element) {
          var slider = jQuery(this).bxSlider(options);
          blogSlider[index] = slider;
        });
      }
      function reloadSliders (options) {
        for (var i = 0; i < blogSlider.length; i++) {
          blogSlider[i].reloadSlider(options);
        }
      }
    },
    init: function () {
      var blogSliderView = this;
      blogSliderView.slider(window.innerWidth);

      $(window).on('resize', function () {
        blogSliderView.slider(window.innerWidth);
      });
    }
  });
  return new BlogSliderView();
});
