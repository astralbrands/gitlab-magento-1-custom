define([
    'jquery',
    'backbone',
    'bestsellersSlider'
], function($, Backbone, bestsellersSlider) {

    "use strict";
    var AccountView = Backbone.View.extend({
        initialize: function () {
          if (window.innerWidth <= 768) {
            $('.block-account .current a').on('click', function (e) {
              e.preventDefault();
              $(this).parents('.block-content').toggleClass('_open');
            });
          }
            bestsellersSlider.init();
        }
    });
    return new AccountView();
});

