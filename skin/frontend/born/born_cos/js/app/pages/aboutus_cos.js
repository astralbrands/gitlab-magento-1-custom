define([
  'jquery',
  'backbone',
  'teamSlider_cos'
], function ($, Backbone, teamSlider_cos) {

  "use strict";
  var AboutUsView = Backbone.View.extend({
    initialize: function () {
      teamSlider_cos.init();
    }
  });
  return new AboutUsView();
});
