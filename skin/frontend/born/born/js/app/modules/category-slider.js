define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";

  var categorySlider;
  var storySlider;
  var isCategorySliderInit = false;
  var isStorySliderInit = false;
  //var isSlidersInit = false;

  var CategorySliderView = Backbone.View.extend({
    initialize: function () {
    },
    slider: function (width) {
      if (width <= 720 && !isCategorySliderInit) {
        categorySlider = jQuery('.section-1 .slots').bxSlider({
          auto: false,
          slideSelector: 'div.one-third:not(.background-image)',
          swipeThreshold: 100,
          startSlide: 1
        });
        if (categorySlider.length)
          isCategorySliderInit = true;
      }
      else if (width > 720 && isCategorySliderInit) {
        categorySlider.destroySlider();
        isCategorySliderInit = false;
      }
      if (width <= 768 && !isStorySliderInit) {
        storySlider = jQuery('.story .stories-list').bxSlider({
          pager: false,
          swipeThreshold: 100,
          slideSelector: 'div.story-block'
        });
        if (storySlider.length)
          isStorySliderInit = true;
      }
      else if (width > 768 && isStorySliderInit) {
        storySlider.destroySlider();
        isStorySliderInit = false;
      }

    },
    init: function () {
      var categorySlider = this;
      var width = window.innerWidth;
      $(window).on('resize', function () {
        var width = window.innerWidth;
        categorySlider.slider(width);
      });
      categorySlider.slider(width);
    }
  });
  return new CategorySliderView();
});
