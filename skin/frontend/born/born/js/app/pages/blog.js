define([
  'jquery',
  'backbone',
  'blogSlider',
  'blogEvents',
  'videoInImage'
], function ($, Backbone, blogSlider, blogEvents, videoInImage) {

  "use strict";

  var BlogView = Backbone.View.extend({
    initialize: function () {
      blogSlider.init();
      blogEvents.init();
      videoInImage.init();
    }
  });

  return new BlogView();
});

