define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";

  var BackToTopView = Backbone.View.extend({
    initialize: function () {
    },
    bindEvents: function () {
      var isToTopVisible = false,
        $html = $('html');
      $(window)
        .on('load.backToTop scroll.backToTop', function () {
          if ($(this).scrollTop() > ($(this).outerHeight() / 2)) {
            if (!isToTopVisible) {
              $html.addClass('_show-to-top-btn');
              isToTopVisible = true;
            }
          } else {
            if (isToTopVisible) {
              $html.removeClass('_show-to-top-btn');
              isToTopVisible = false;
            }
          }
        });
      $(document)
        .on('click.scrollToTop', '.page-up', function (e) {
          e.preventDefault();
          $('html, body').animate({scrollTop: 0});
        });
    },
    init: function () {
      this.bindEvents();
    }
  });
  return new BackToTopView();
});
