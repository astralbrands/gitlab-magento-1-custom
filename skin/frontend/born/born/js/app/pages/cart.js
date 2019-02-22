define([
  'jquery',
  'backbone',
  'sampleSlider'
], function($, Backbone, sampleSlider) {

  "use strict";
  var CartView = Backbone.View.extend({
    initialize: function(){

      $('.banner-cart').css('background-image', function () {
        return 'url(' + $('.cart-mid-banner').find('img[title="account banner"]').prop('src') + ')';
      });

      $('.shipping h2').on('click', function () {
        $('.shipping-form').slideToggle('fast');
        $(this).toggleClass('form-open');
      });

      sampleSlider.init();
    }
  });
  return new CartView();
});
