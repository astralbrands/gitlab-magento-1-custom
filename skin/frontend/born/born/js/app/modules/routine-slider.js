define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";

  var routinesSlider;
  var isSliderInit = false;

  var RoutineSliderView = Backbone.View.extend({
    initialize: function () {
    },
    initPressSlider: function () {

      if (window.innerWidth <= 880 && !isSliderInit) {
        var startSlideIndex = $('.current-product').index('.routine-outer') > -1 ?  $('.current-product').index('.routine-outer') : 0;
        try {
          routinesSlider = jQuery('.routines__wrap').bxSlider({
            touchEnabled: true,
            startSlide: startSlideIndex,
            swipeThreshold: 100,
            nextText: 'apply over',
            prevText: 'apply under'
          });
          if (routinesSlider.length)
            isSliderInit = true;
        } catch (err) {
          console.log(err);
        }
      } else if (window.innerWidth > 880 && isSliderInit) {
        routinesSlider.destroySlider();
        isSliderInit = false;
      }
    },
    bindEvents: function () {
      var routineSlider = this;
      $(window).on('resize.checkRoutineSlider', function () {
        $('.routines__wrap').length && routineSlider.initPressSlider();
      });
    },
    init: function () {
      $('.routines__wrap').length && this.initPressSlider();
      this.bindEvents();
    }
  });
  return new RoutineSliderView();
});
