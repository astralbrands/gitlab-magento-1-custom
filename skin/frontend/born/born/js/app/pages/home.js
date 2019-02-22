define([
  'jquery',
  'backbone',
  'lookModal',
  'videoInImage'
], function($, Backbone, lookModal, videoInImage) {

  "use strict";
  var HomePageView = Backbone.View.extend({
    initialize: function () {
      // scroll down - Hero section
      $('#home-scroll-down').on('click', function () {
        $('html, body').animate({
          scrollTop: $($.attr(this, 'href')).offset().top
        }, 500);
        return false;
      });

      // init modal look modal
      $('[data-modal-name = "look"]')
          .on('onParse', lookModal.lookParseTemplate.bind($(this)))
          .on('mcallback', lookModal.lookCallback.bind($(this)));

      videoInImage.init();
    }
  });
  return new HomePageView();
});
