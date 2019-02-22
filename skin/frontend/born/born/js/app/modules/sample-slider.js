define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";

  var sampleSlider;
  var isSliderInit = false;
  var isMobileSlider;

  var SampleSliderView = Backbone.View.extend({

    initialize: function () {

    },
    defaultSlider: function () {

      var defaultSliderOptions = {
        slideSelector: 'div.ampromo-slide',
        slideWidth: 215,
        minSlides: 1,
        infiniteLoop: false,
        hideControlOnEnd: true,
        swipeThreshold: 100,
        maxSlides: 4,
        moveSlides: 1,
        pager: false,
        useCSS: false
      };

      if (isSliderInit && !isMobileSlider) {
        sampleSlider.destroySlider();
        sampleSlider = jQuery('#ampromo-carousel-content').bxSlider(defaultSliderOptions);
      }
      else if (!isSliderInit) {
        sampleSlider = jQuery('#ampromo-carousel-content').bxSlider(defaultSliderOptions);
      }
      isSliderInit = true;
      isMobileSlider = true;

    },
    mobileSlider: function () {

      var mobileSliderOptions = {
        slideSelector: 'div.ampromo-slide',
        swipeThreshold: 100,
        infiniteLoop: false,
        hideControlOnEnd: true,
        pager: false
      };

      if (isSliderInit && isMobileSlider) {
        sampleSlider.destroySlider();
        sampleSlider = jQuery('#ampromo-carousel-content').bxSlider(mobileSliderOptions);
      }
      else if (!isSliderInit) {
        sampleSlider = jQuery('#ampromo-carousel-content').bxSlider(mobileSliderOptions);
      }
      isSliderInit = true;
      isMobileSlider = false;
    },
    init: function () {
      var SampleSlider = this;

      if (jQuery('#ampromo-carousel-content')[0]) {
        window.innerWidth > 768 ? SampleSlider.defaultSlider() : SampleSlider.mobileSlider();
      }

      $(window).on('resize', function () {
        if (jQuery('#ampromo-carousel-content')[0]) {
          window.innerWidth > 768 ? SampleSlider.defaultSlider() : SampleSlider.mobileSlider();
        }
      });

      //Checkbox inside slide
      $('.label-text').on('click.slideClick', function (e) {
        e.preventDefault();
        var checkBox = $(this).prev('input');
        var isChecked = checkBox.prop('checked');
        checkBox.prop('checked', !isChecked);
      });
    }
  });
  return new SampleSliderView();
});
