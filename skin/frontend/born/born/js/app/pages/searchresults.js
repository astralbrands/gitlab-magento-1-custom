define([
  'jquery',
  'lodash',
  'backbone',
  'bxslider',
  'bestsellersSlider'
], function($, _, Backbone, bxslider, bestsellersSlider) {

  "use strict";
  var AccountView = Backbone.View.extend({
    initialize: function () {
      bestsellersSlider.init();
    }
  });
  return new AccountView();
});
