define([
    'jquery',
    'backbone'
], function ($, Backbone) {

    "use strict";

    var accountSlider;
    var isSliderInit = false;

    var AccountSliderView = Backbone.View.extend({
        initialize: function () {
        },
        slider: function (width) {

            if (width <= 720) {
                if (isSliderInit) {
                    return;
                }
              try {
                accountSlider = jQuery('#block-bestseller').bxSlider({
                  slideSelector: 'li.item',
                  swipeThreshold: 100
                });
                if (accountSlider.length)
                  isSliderInit = true;
              } catch (err) {
                console.log(err);
              }
            } else {
                if (isSliderInit) {
                  try {
                    accountSlider.destroySlider();
                    isSliderInit = false;
                  } catch (err) {
                    console.log(err);
                  }
                }
            }
        },
        init: function () {
            var accountSlider = this;
            var width = window.innerWidth;
            $(window).on('resize', function () {
                var width = window.innerWidth;
                accountSlider.slider(width);
            });
            accountSlider.slider(width);
        }
    });
    return new AccountSliderView();
});
